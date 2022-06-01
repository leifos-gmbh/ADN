<?php
/* Copyright (c) 2010 Leifos, GPL, see docs/LICENSE */

include_once './Services/ADN/Report/classes/class.adnReport.php';

/**
 * Generation directory reports
 * See menu "Administration of Certified PRofessionals -> Directory of Certified Professionals".
 *
 * @author Stefan Meyer <meyer@leifos.com>
 * @version $Id: class.adnReportDirectory.php 31039 2011-10-08 07:22:03Z smeyer $
 *
 * @ingroup ServicesADN
 */

class adnReportDirectory extends adnReport
{
    /**
     * @var string[]
     */
    private array $data = [];
    
    private ?ilDateTime $from = null;
    private ?ilDateTime $until = null;

    /**
     * @var int[]
     */
    private array $wmo_ids = [];

    /**
     * Contructor
     */
    public function __construct()
    {
        parent::__construct();
    }
    
    /**
     * Get relative data dir
     * @return
     */
    public function getRelativeDataDir()
    {
        return 'dir';
    }

    /**
     * Set data
     * @param array $a_data
     * @return void
     */
    public function setData($a_data)
    {
        $this->data = $a_data;
    }

    /**
     * Set wmo ids
     * @param array $a_ids
     */
    public function setWmoIds($a_ids)
    {
        $this->wmo_ids = $a_ids;
    }

    /**
     * Get wmo ids of directory
     * @return array
     */
    public function getWmoIds()
    {
        return $this->wmo_ids;
    }
    
    /**
     * get data
     * @return
     */
    public function getData()
    {
        return $this->data;
    }
    
    /**
     * Set duration of stat
     * @param ilDate $from
     * @param ilDate $to
     * @return void
     */
    public function setDuration(ilDateTime $from, ilDateTime $to)
    {
        $this->from = $from;
        $this->until = $to;
    }
    
    /**
     * Get duration from
     * @return ilDAteTime
     */
    public function getDurationFrom()
    {
        return $this->from;
    }
    
    /**
     * Get duration until
     * @return ilDateTime
     */
    public function getDurationTo()
    {
        return $this->until;
    }


    /**
     * Create report.
     * Throws wrapped adnREportException in case of underlying rpc faults.
     * @return void
     *
     * @throws adnReportException
     */
    public function create()
    {
        include_once './Services/ADN/Report/classes/class.adnReportFileUtils.php';

        $map = $this->buildHeader(array());
        $form = adnReportFileUtils::getTemplatePathByType(adnReportFileUtils::TPL_REPORT_DIRECTORY);
        
        // Fill pdf. If more than 12 entries exist, merge multiple reports
        include_once './Services/ADN/Report/classes/class.adnTaskScheduleWriter.php';
        $writer = new adnTaskScheduleWriter();
        $writer->xmlStartTag('tasks');
        
        $all_outfiles = array();
        $i = 0;
        foreach ($this->getData() as $set) {
            if (!($i % 12)) {
                if ($i) {
                    $writer->addParameter('map', $map);
                    $writer->xmlEndTag('action');
                }
            
                $writer->xmlStartTag(
                    'action',
                    array(
                        'method' => 'fillPdfTemplate'
                    )
                );
                $writer->addParameter('string', $form);
                $writer->addParameter('string', $this->initOutfile());
                $all_outfiles[] = $this->getOutfile();
                $map = array();
                $row = 1;

                // Add legend
                $counter = 0;
                foreach ($this->getWmoIds() as $wmo_id => $code_nr) {
                    include_once './Services/ADN/MD/classes/class.adnWMO.php';
                    $map['le_' . ++$counter] = $code_nr . ' - ' . adnWMO::lookupName($wmo_id);
                }
            }


            $map['num_' . $row] = $set['full_nr'];
            $map['name_' . $row] = $set['last_name'] . ', ' . $set['first_name'];
            $born = new ilDate($set['birthdate'], IL_CAL_DATE);
            $map['born_' . $row] = $born->get(IL_CAL_FKT_DATE, 'd.m.Y');
            $map['country_' . $row] = $set['citizenship'];
            $map['typ_' . $row] = $set['type'];
            $valid = new ilDate($set['valid_until'], IL_CAL_DATE);
            $map['valid_' . $row] = $valid->get(IL_CAL_FKT_DATE, 'd.m.Y');
            $issued = new ilDate($set['issued_on'], IL_CAL_DATE);
            $map['issued_' . $row] = $issued->get(IL_CAL_FKT_DATE, 'd.m.Y');
            $map['signed_' . $row] = $set['signed_by'];
            

            $i++;
            $row++;
        }

        $writer->addParameter('map', $map);
        $writer->xmlEndTag('action');
        
        if (count($all_outfiles) > 1) {
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
        }
        $writer->xmlEndTag('tasks');
        
        $this->log->info($writer->xmlDumpMem(true));

        try {
            // Start tranformation
            include_once './Services/ADN/Base/classes/class.adnRpcAdapter.php';
            $adapter = new adnRpcAdapter();
            $adapter->transformationTaskScheduler(
                $writer->xmlDumpMem()
            );
        } catch (adnReportException $e) {
            throw $e;
        }
    }
    

    /**
     * Transform to pdf
     * Throws wrapped adnReportException in case of underlying rpc faults
     * @param array $map
     * @return void
     * @throws adnReportException
     */
    protected function transform($map, $form)
    {
        $writer->xmlStartTag(
            'action',
            array(
                'method' => 'fillPdfTemplate'
            )
        );
            
        $outfile = $this->initOutfile();
        
        $writer->addParameter('string', $form);
        $writer->addParameter('string', $outfile);
        $writer->addParameter('map', $map);
        $writer->xmlEndTag('action');
        $writer->xmlEndTag('tasks');

        try {
            $adapter = new adnRpcAdapter();
            $adapter->transformationTaskScheduler(
                $writer->xmlDumpMem()
            );
            return true;
        } catch (adnReportException $e) {
            throw $e;
        }
    }
    
    /**
     * Build PDF header
     * @global ilLanguage $lng
     * @param array $map
     * @return array $map
     */
    protected function buildHeader($map)
    {
        
        return true;
        
        $map['date_period'] = sprintf(
            $this->lng->txt('adn_report_stat_header'),
            $this->getDurationFrom()->get(IL_CAL_FKT_DATE, 'd.m.Y'),
            $this->getDurationTo()->get(IL_CAL_FKT_DATE, 'd.m.Y')
        );
        
        return $map;
    }
}
