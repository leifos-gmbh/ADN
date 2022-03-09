<?php
/* Copyright (c) 2010 Leifos, GPL, see docs/LICENSE */

include_once './Services/ADN/Report/classes/class.adnReport.php';

/**
 * Generation of attendance list reports.
 * See menu "Exam preparation -> Attendance List"
 *
 * @author Stefan Meyer <meyer@leifos.com>
 * @version $Id: class.adnReportAttendanceList.php 46931 2013-12-19 10:56:58Z smeyer $
 *
 * @ingroup ServicesADN
 */

class adnReportAttendanceList extends adnReport
{
    const TYPE_DM = 1;
    const TYPE_GAS = 2;
    const TYPE_CHEM = 3;
    
    const LIMIT_ENTRIES = 12;
    
    private $event = null;
    private $wmo = null;
    private $type = null;
    
    private $candidates = array();

    /**
     * Contructor
     * @return
     */
    public function __construct(adnExaminationEvent $event = null)
    {
        parent::__construct();
        
        if ($event) {
            $this->event = $event;
        
            include_once './Services/ADN/MD/classes/class.adnWMO.php';
            include_once './Services/ADN/MD/classes/class.adnExamFacility.php';
            $fac = new adnExamFacility($this->getEvent()->getFacility());
            $this->wmo = new adnWMO($fac->getWMO());
        }
    }
    
    /**
     * Lookup last file
     * @param object $a_event_id
     * @return
     * @access static
     */
    public static function lookupLastFile($a_event_id)
    {
        $file = ilUtil::getDataDir() . '/adn/report/exa/' . $a_event_id . '.pdf';
        
        if (file_exists($file)) {
            if ($time = filemtime(ilUtil::getDataDir() . '/adn/report/exa/' . $a_event_id . '.pdf')) {
                return new ilDateTime($time, IL_CAL_UNIX);
            }
        }
        return null;
    }
    /**
     * Get attendance list path
     * @param int $a_event_id
     * @return
     */
    public function getFile($a_event_id)
    {
        return $this->getDataDir() . '/' . $a_event_id . '.pdf';
    }
    
    /**
     * Get relative data dir
     * @return string
     */
    public function getRelativeDataDir()
    {
        return 'exa';
    }

    /**
     * Set report type
     * @param object $a_type
     * @return
     */
    public function setType($a_type)
    {
        $this->type = $a_type;
    }
    
    /**
     * Get report type
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }
    
    /**
     * Get certificate object
     * @return object adnCertificate
     */
    public function getEvent()
    {
        return $this->event;
    }
    
    /**
     * Get wmo of event
     * @return object adnWMO
     */
    public function getWMO()
    {
        return $this->wmo;
    }
    
    /**
     * Set candidates
     *
     * @param array candidates
     * @return
     */
    public function setCandidates($a_cand)
    {
        $this->candidates = $a_cand;
    }

    /**
     * Get candidates
     * @return
     */
    public function getCandidates()
    {
        return (array) $this->candidates;
    }
    

    /**
     * Create report
     * Throws adnREportException in case of underlying rpc faults
     * @return
     *
     * @throws adnReportException
     */
    public function create()
    {
        global $ilUser;
        
        include_once './Services/ADN/Report/classes/class.adnReportFileUtils.php';
        
        // Write one task (fillPdfTemplate for every candidate) and finally merge them in one PDF.
        include_once './Services/ADN/Report/classes/class.adnTaskScheduleWriter.php';
        $writer = new adnTaskScheduleWriter();
        $writer->xmlStartTag('tasks');

        
        $num_candidates = count($this->readCandidates());
        
        $offset = 0;
        $outfiles = array();
        do {
            // Template by type
            switch ($this->getEvent()->getType()) {
                case 'gas':
                    $map = $this->getBaseMap(array());
                    $map = $this->addCandidates($map, true, $offset);
                    $form = adnReportFileUtils::getTemplatePathByType(
                        adnReportFileUtils::TPL_REPORT_ATTENDANCE_LIST_G
                    );
                    break;

                case 'chem':
                    $map = $this->getBaseMap(array());
                    $map = $this->addCandidates($map, true, $offset);
                    $form = adnReportFileUtils::getTemplatePathByType(
                        adnReportFileUtils::TPL_REPORT_ATTENDANCE_LIST_C
                    );
                    break;


                case 'dm':
                default:
                    $map = $this->getBaseMap(array());
                    $map = $this->addCandidates($map, false, $offset);
                    $form = adnReportFileUtils::getTemplatePathByType(
                        adnReportFileUtils::TPL_REPORT_ATTENDANCE_LIST
                    );
                    break;
            }

            // Write one task (fillPdfTemplate for every candidate) and finally merge them in one PDF.
            include_once './Services/ADN/Report/classes/class.adnTaskScheduleWriter.php';
            $writer->xmlStartTag(
                'action',
                array(
                    'method' => 'fillPdfTemplate'
                )
            );

            
            $this->initOutfile();
            $outfiles[] = $this->getOutfile();
            
            $writer->addParameter('string', $form);
            $writer->addParameter('string', $this->getOutfile());
            $writer->addParameter('map', $map);
            $writer->xmlEndTag('action');
            
            
            $offset += self::LIMIT_ENTRIES;
            $num_candidates -= self::LIMIT_ENTRIES;
        } while ($num_candidates >= 0);
        
        $GLOBALS['ilLog']->write(__METHOD__ . ': outfiles ' . print_r($outfiles, true));
        
        
        $writer->xmlStartTag(
            'action',
            array(
                    'method' => 'mergePdf'
                )
        );
        $writer->addParameter('vector', (array) $outfiles);
        $outfile = $this->getDataDir() . '/' . $this->getEvent()->getId() . '.pdf';
        $writer->addParameter('string', $outfile);
        $writer->xmlEndTag('action');
        
        
        $writer->xmlEndTag('tasks');

        $GLOBALS['ilLog']->write($writer->xmlDumpMem(true));
        
        try {
            $adapter = new adnRpcAdapter();
            $adapter->transformationTaskScheduler(
                $writer->xmlDumpMem()
            );
        } catch (adnReportException $e) {
            throw $e;
        }
    }
    
    /**
     * Get fields for base map
     * @return
     */
    protected function getBaseMap($map)
    {
        global $lng;
        
        $map['wsd_name'] = $this->getWMO()->getName();

        switch ($this->getEvent()->getType()) {
            case 'gas':
                $map['exam_title'] = $lng->txt('adn_report_attendance_header_gas');
                break;

            case 'chem':
                $map['exam_title'] = $lng->txt('adn_report_attendance_header_gas');
                break;

            default:
                $map['exam_title'] = $lng->txt('adn_report_attendance_header_def');
                break;
        }

        include_once './Services/ADN/MD/classes/class.adnExamFacility.php';
        $header = sprintf(
            $lng->txt('adn_report_exam_list_header'),
            $lng->txt('adn_subject_area_' . $this->getEvent()->getType()),
            $this->getEvent()->getDateFrom()->get(IL_CAL_FKT_DATE, 'd.m.Y'),
            $this->getEvent()->getDateFrom()->get(IL_CAL_FKT_DATE, 'H:i'),
            '________',
            adnExamFacility::lookupCity($this->getEvent()->getFacility())
        );
        
        $map['exam_desc'] = $header;
        
        $map['wsd_first'] = str_pad($this->getCoChairName(
            $this->getEvent()->getChairman()
        ) . ' ', 150, '.');
        $map['wsd_second'] = str_pad($this->getCoChairName(
            $this->getEvent()->getCoChair1()
        ) . ' ', 150, '.');
        $map['wsd_third'] = str_pad($this->getCoChairName(
            $this->getEvent()->getCoChair2()
        ) . ' ', 150, '.');
        
        $contact = $this->getWMO()->getName() . ', ' .
            $this->getWMO()->getPostalStreet() . ' ' . $this->getWMO()->getPostalStreetNumber() . ' in ' .
            $this->getWMO()->getPostalZip() . ' ' . $this->getWMO()->getPostalCity();
        
        $map['wsd_contact'] = sprintf($lng->txt('adn_report_legal_remedies'), $contact);
        
        return $map;
    }
    
    /**
     * Lookup coChair name
     * @param int $a_id
     * @return string
     */
    protected function getCoChairName($a_id)
    {
        global $lng;
        
        include_once './Services/ADN/MD/classes/class.adnCoChair.php';
        $co = new adnCoChair($a_id);
        
        return $lng->txt('salutation_' . $co->getSalutation()) . ' ' . $co->getName();
    }
    
    /**
     * Read candidates
     * @param object $map
     * @return
     */
    protected function addCandidates($map, $is_gc = false, $a_offset = 0)
    {
        include_once './Services/ADN/EP/classes/class.adnAssignment.php';
        
        $counter = 0;
        $segment = 0;

        $candidates = $this->readCandidates();

        #$candidates = array_keys($candidates);
        foreach ($candidates as $candidate_id) {
            if ($segment++ < $a_offset) {
                continue;
            }
            
            $counter++;
            
            if ($counter > self::LIMIT_ENTRIES) {
                break;
            }

            include_once './Services/ADN/ES/classes/class.adnCertifiedProfessional.php';
            $cand = new adnCertifiedProfessional($candidate_id);
            
            include_once './Services/ADN/MD/classes/class.adnCountry.php';
            $country = new adnCountry($cand->getCitizenship());
            
            $map['counter_' . $counter] = $segment;
            
            if ($is_gc) {
                $map['name_' . $counter] = $cand->getLastName() . ', ' . $cand->getFirstName();
            } else {
                $map['name_' . $counter] = $cand->getLastName();
                $map['firstname_' . $counter] = $cand->getFirstName();
            }
            $map['born_' . $counter] = $cand->getBirthdate()->get(IL_CAL_FKT_DATE, 'd.m.Y');
            $map['country_' . $counter] = $country->getName();
        }
        
        return $map;
    }
    
    /**
     * Get candidates
     * @return array
     */
    protected function readCandidates()
    {
        include_once './Services/ADN/EP/classes/class.adnAssignment.php';
        $candidates_arr = adnAssignment::getAllAssignments(
            array('event_id' => $this->getEvent()->getId()),
            array()
        );

        $candidates = array();
        foreach ((array) $candidates_arr as $null => $data) {
            $candidates[] = $data['cp_professional_id'];
        }
        return $candidates;
    }
}
