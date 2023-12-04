<?php
/* Copyright (c) 2010 Leifos, GPL, see docs/LICENSE */

include_once './Services/ADN/Report/classes/class.adnReport.php';

/**
 * Generation of invitation reports
 * See menu "Exam preparation -> "Invitation Letters".
 * Different types for dry materials, gas and chemicals.
 *
 * @author Stefan Meyer <meyer@leifos.com>
 * @version $Id: class.adnReportInvitation.php 30967 2011-10-04 10:32:48Z smeyer $
 *
 * @ingroup ServicesADN
 */

class adnReportInvitation extends adnReport
{
    const TYPE_DM = 1;
    const TYPE_GAS = 2;
    const TYPE_CHEM = 3;
    
    private $event = null;
    private $type = null;
    
    private $candidates = array();

    /**
     * Contructor
     * @return
     */
    public function __construct(adnExaminationEvent $event)
    {
        parent::__construct();
        $this->event = $event;
    }
    
    /**
     * Get relative data dir
     * @return
     */
    public function getRelativeDataDir()
    {
        return 'inv';
    }

    /**
     * Set report type
     * @param object $a_type
     */
    public function setType($a_type)
    {
        $this->type = $a_type;
    }
    
    /**
     * Get report type
     * @return int
     */
    public function getType()
    {
        return $this->type;
    }
    
    /**
     * get certificate object
     * @return object adnCertificate
     */
    public function getEvent()
    {
        return $this->event;
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
     * Throw wrapped adnReportExeption in case of underlying RPC exceptions.
     * @return
     *
     * @throws adnReportException
     */
    public function create()
    {
        global $ilUser;
        
        include_once './Services/ADN/Report/classes/class.adnReportFileUtils.php';

        // Template by type

        switch ($this->getEvent()->getType()) {
                
            case 'gas':
                $this->setType(self::TYPE_GAS);
                $form = adnReportFileUtils::getTemplatePathByType(
                    adnReportFileUtils::TPL_REPORT_INVITATION_GAS
                );
                break;
                
            case 'chem':
                $this->setType(self::TYPE_CHEM);
                $form = adnReportFileUtils::getTemplatePathByType(
                    adnReportFileUtils::TPL_REPORT_INVITATION_CHEM
                );
                break;

            case 'dm':
                $this->setType(self::TYPE_DM);
                $form = adnReportFileUtils::getTemplatePathByType(
                    adnReportFileUtils::TPL_REPORT_INVITATION_DRY
                );
                break;

            case 'tank':
                $this->setType(self::TYPE_DM);
                $form = adnReportFileUtils::getTemplatePathByType(
                    adnReportFileUtils::TPL_REPORT_INVITATION_TANK
                );
                break;

            case 'comb':
                $this->setType(self::TYPE_DM);
                $form = adnReportFileUtils::getTemplatePathByType(
                    adnReportFileUtils::TPL_REPORT_INVITATION_COMB
                );
                break;
            default:
        }
        
        // Write on task (fillPdfTemplate for every candidate) and finally merge them in one PDF.
        include_once './Services/ADN/Report/classes/class.adnTaskScheduleWriter.php';
        $writer = new adnTaskScheduleWriter();
        $writer->xmlStartTag('tasks');
        
        include_once './Services/ADN/MD/classes/class.adnExamFacility.php';
        $fac = new adnExamFacility($this->getEvent()->getFacility());
        $wmo = $fac->getWMO();

        $all_outfiles = array();
        foreach ($this->getCandidates() as $cand) {
            $writer->xmlStartTag(
                'action',
                array(
                    'method' => 'fillPdfTemplate'
                )
            );
            
            $outfile = $this->getDataDir() . '/' . $this->getEvent()->getId() . '_' . $cand;
            
            $writer->addParameter('string', $form);
            $writer->addParameter('string', $outfile);
                
            
            // DONE: fill map
            $map = $this->addStandardRightColumn(
                $map,
                $wmo,
                $ilUser->getId()
            );
            
            //  Fill standard address
            $map = $this->addStandardAddress(
                $map,
                $wmo,
                $cand
            );
            
            // Fill invitation fields
            $map = $this->addInvitationFields(
                $map,
                $wmo,
                $cand
            );

            $writer->addParameter('map', $map);

            $writer->xmlEndTag('action');
            
            $all_outfiles[] = $outfile;
        }
        
        // Merge all single pdf's to one pdf
        $writer->xmlStartTag(
            'action',
            array(
                'method' => 'mergePdf'
            )
        );
        $writer->addParameter('vector', $all_outfiles);
        $writer->addParameter('string', $this->initOutfile());
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
     * Add invitation specific fields
     * @param array $map
     * @param int wmo
     * @param int candidate
     * @return
     */
    public function addInvitationFields($map, $wmo, $candidate)
    {
        global $lng,$ilUser;
        
        $lng->loadLanguageModule('dateplaner');
        
        include_once './Services/ADN/MD/classes/class.adnWMO.php';
        include_once './Services/Calendar/classes/class.ilCalendarUtil.php';
        $wmo = new adnWMO($wmo);

        include_once './Services/ADN/ES/classes/class.adnCertifiedProfessional.php';
        $cand = new adnCertifiedProfessional($candidate);
        $map['rcp_salutation'] =
            $lng->txt('adn_report_salutation_' . $cand->getSalutation()) . ' ' .
            $cand->getLastName() . ', ';
        
        $map['iss_lastname'] = $ilUser->getLastname();

        include_once './Services/ADN/MD/classes/class.adnExamFacility.php';
        $fac = new adnExamFacility($this->getEvent()->getFacility());
        $facility = $fac->getName() . "\n";
        $facility .= $fac->getStreet() . ' ' . $fac->getStreetNumber() . "\n";
        $facility .= $fac->getZip() . ' ' . $fac->getCity();

        $map['exam_fac'] = $facility;

        // Costs
        $costs = $this->getEvent()->getCosts();
        $event_type = $this->getEvent()->getType();
        if ($event_type == "gas" || $event_type == "chem") {
            $e_cost = $wmo->getCostExamGasChem();
        } else {
            $e_cost = $wmo->getCostExam();
        }
        $costs += $e_cost['value'];
        $map['exam_charge'] = sprintf('%01.2f €', $costs);
        $map['exam_charge'] = str_replace('.', ',', $map['exam_charge']);
        

        $cost = $wmo->getCostCertificate();
        $map['cert_charge'] = sprintf('%01.2f €', $cost['value']);
        $map['cert_charge'] = str_replace('.', ',', $map['cert_charge']);
        
        // Date of exam
        $exam = $this->getEvent()->getDateFrom();
        $weekday = $exam->get(IL_CAL_FKT_DATE, 'D');
        $weekday = $lng->txt(substr($weekday, 0, 2) . '_long');
        $map['exam_date'] = sprintf(
            $lng->txt('adn_date_long'),
            $weekday,
            $exam->get(IL_CAL_FKT_DATE, 'd.m.Y') . ', ' . $exam->get(IL_CAL_FKT_DATE, 'H:i')
        ) . ' ' . $lng->txt('adn_report_date_clock');
        
        
        return $map;
    }

    // cr-008 start
    /**
     * Delete
     *
     * @param int $a_cand candidate id
     */
    public function delete($a_cand)
    {
        $outfile = $this->getDataDir() . '/' . $this->getEvent()->getId() . '_' . $a_cand;
        if (is_file($outfile)) {
            unlink($outfile);
        }
    }
    // cr-008 end
}
