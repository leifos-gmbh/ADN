<?php
/* Copyright (c) 2010 Leifos, GPL, see docs/LICENSE */

include_once './Services/ADN/Report/classes/class.adnReport.php';

/**
 * Generation statistic reports
 * All kind of statistics (submenu statitics)
 * - examinations
 * - extended validity
 * - certificates
 * - certificates sum
 *
 * @author Stefan Meyer <meyer@leifos.com>
 * @version $Id: class.adnReportStatistics.php 30175 2011-08-07 13:56:30Z smeyer $
 *
 * @ingroup ServicesADN
 */

class adnReportStatistics extends adnReport
{
    const TYPE_EXAM = 1;
    const TYPE_EXTENSION_EXP = 2;
    const TYPE_EXTENSION_REF = 3;
    const TYPE_CERTIFICATES_OTHER = 4;
    const TYPE_CERTIFICATES_GC = 5;
    const TYPE_CERTIFICATES_SUM = 6;
    
    private $data = array();
    
    private $from = null;
    private $until = null;

    private $wmo = 0;

    /**
     * Contructor
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }
    
    /**
     * Get relative data dir
     * @return string
     */
    public function getRelativeDataDir()
    {
        return 'stat';
    }

    /**
     * Set report type
     * @param string $a_type
     * @return void
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
     * Set wmo
     * @param int $a_wmo
     */
    public function setWmo($a_wmo)
    {
        $this->wmo = $a_wmo;
    }

    /**
     * Get wmo
     * @return int wmo
     */
    public function getWmo()
    {
        return $this->wmo;
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
     * @return
     */
    public function setDuration(ilDateTime $from, ilDateTime $to)
    {
        $this->from = $from;
        $this->until = $to;
    }
    
    /**
     * Get duration from
     * @return ilDateTime
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
     * Create statistics report
     * Use set type before to define the kind of statistic
     * Receive the path to the generated report by using method getOutfile()
     *
     * @see setType
     * @see getOutfile
     * @throws adnReportException
     */
    public function create()
    {
        include_once './Services/ADN/Report/classes/class.adnReportFileUtils.php';

        $map = $this->buildHeader(array());

        switch ($this->getType()) {
            case self::TYPE_EXAM:
                $form = adnReportFileUtils::getTemplatePathByType(
                    adnReportFileUtils::TPL_REPORT_STAT_EXAM
                );
                $map = $this->createExam($map, $form);
                $this->transform($map, $form);
                break;
                
            case self::TYPE_EXTENSION_EXP:
                $form = adnReportFileUtils::getTemplatePathByType(
                    adnReportFileUtils::TPL_REPORT_STAT_EXTENSION_EXP
                );
                $map = $this->createExtensionExperience($map);
                $this->transform($map, $form);
                break;

            case self::TYPE_EXTENSION_REF:
                $form = adnReportFileUtils::getTemplatePathByType(
                    adnReportFileUtils::TPL_REPORT_STAT_EXTENSION_REF
                );
                $map = $this->createExtensionRefresh($map);
                $this->transform($map, $form);
                break;

            case self::TYPE_CERTIFICATES_OTHER:
                $form = adnReportFileUtils::getTemplatePathByType(
                    adnReportFileUtils::TPL_REPORT_STAT_CERTIFICATES_OTHER
                );
                $map = $this->createCertificatesOther($map);
                $this->transform($map, $form);
                break;

            case self::TYPE_CERTIFICATES_GC:
                $form = adnReportFileUtils::getTemplatePathByType(
                    adnReportFileUtils::TPL_REPORT_STAT_CERTIFICATES_GC
                );
                $map = $this->createCertificatesGC($map);
                $this->transform($map, $form);
                break;

            case self::TYPE_CERTIFICATES_SUM:
                $form = adnReportFileUtils::getTemplatePathByType(
                    adnReportFileUtils::TPL_REPORT_STAT_CERTIFICATES_SUM
                );
                $map = $this->createCertificatesSum($map);
                $this->transform($map, $form);
                break;
        }
    }
    
    /**
     * Create exam fields
     * @return array field mapping
     */
    protected function createExam($map)
    {
        global $lng;

        $map['stat_type'] = $lng->txt('adn_st_exs');
        $map['stat_type'] = $this->appendWmo($map['stat_type']);

        $num = 1;
        foreach ($this->getData() as $data) {
            $map['x' . $num] = (string) $data['type'];
            $map['a' . $num] = (string) $data['events'];
            $map['b' . $num] = (string) $data['participants'];
            $map['c' . $num] = (string) $data['success'];
            $map['d' . $num] = (string) sprintf("%.2f", $data['quota']) . '%';
            
            $num++;
        }
        return $map;
    }
    
    /**
     * Create extension experience mapping
     * @return
     */
    protected function createExtensionExperience($map)
    {
        global $lng;

        $map['stat_type'] = $lng->txt('adn_report_type_extension_exp');
        $map['stat_type'] = $this->appendWmo($map['stat_type']);

        $num = 1;
        foreach ($this->getData() as $data) {
            $map['x' . $num] = (string) $data['type'];
            $map['a' . $num] = (string) $data['count'];
            
            $num++;
        }
        return $map;
    }
    
    /**
     * Create extension experience mapping
     * @return
     */
    protected function createExtensionRefresh($map)
    {
        global $lng;

        $map['stat_type'] = $lng->txt('adn_report_type_extension_ref');
        $map['stat_type'] = $this->appendWmo($map['stat_type']);

        $num = 1;
        foreach ($this->getData() as $data) {
            $map['x' . $num] = (string) $data['type'];
            $map['a' . $num] = (string) $data['count'];
            
            $num++;
        }
        return $map;
    }

    /**
     * Create extension experience mapping
     * @return
     */
    protected function createCertificatesOther($map)
    {
        global $lng;
        
        $map['stat_type'] = $lng->txt('adn_report_type_cert_others');
        $map['stat_type'] = $this->appendWmo($map['stat_type']);

        $num = 1;
        foreach ($this->getData() as $data) {
            $map['x' . $num] = (string) $data['type'];
            $map['a' . $num] = (string) $data['count'];
            
            $num++;
        }
        return $map;
    }

    /**
     * Create extension experience mapping
     * @return
     */
    protected function createCertificatesGC($map)
    {
        global $lng;

        $map['stat_type'] = $lng->txt('adn_report_type_cert_gc');
        $map['stat_type'] = $this->appendWmo($map['stat_type']);

        $num = 1;
        foreach ($this->getData() as $data) {
            $map['x' . $num] = (string) $data['type'];
            $map['a' . $num] = (string) $data['count'];
            
            $num++;
        }
        return $map;
    }

    /**
     * Create extension experience mapping
     * @return
     */
    protected function createCertificatesSum($map)
    {
        global $lng;

        $map['stat_type'] = $lng->txt('adn_report_type_cert_sum');
        $map['stat_type'] = $this->appendWmo($map['stat_type']);

        $sum = 0;
        foreach ($this->getData() as $data) {
            $sum += $data['count'];
        }
        $map['a1'] = (string) $sum;

        return $map;
    }

    /**
     * Transform to pdf.
     * Throws adnREportException in case of failures.
     * @param array $map
     * @return void
     * @throw adnReportException
     */
    protected function transform($map, $form)
    {
        // Write on task (fillPdfTemplate for every candidate) and finally merge them in one PDF.
        include_once './Services/ADN/Report/classes/class.adnTaskScheduleWriter.php';
        $writer = new adnTaskScheduleWriter();
        $writer->xmlStartTag('tasks');
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
     * Build statistics header
     * @global ilLanguage $lng
     * @param array $map
     * @return array $map
     */
    protected function buildHeader($map)
    {
        global $lng;
        
        $map['date_period'] = sprintf(
            $lng->txt('adn_report_stat_header'),
            $this->getDurationFrom()->get(IL_CAL_FKT_DATE, 'd.m.Y'),
            $this->getDurationTo()->get(IL_CAL_FKT_DATE, 'd.m.Y')
        );
        
        return $map;
    }

    /**
     * Append wmo from filter
     * @param string $a_str
     * @return string
     */
    protected function appendWmo($a_str)
    {
        if (!$this->getWmo()) {
            return $a_str;
        }

        $a_str .= ', ';

        include_once './Services/ADN/MD/classes/class.adnWMO.php';
        $wmo = new adnWMO($this->getWmo());
        return $a_str . ' ' . $wmo->getName() . '(' . $wmo->getSubtitle() . ')';
    }
}
