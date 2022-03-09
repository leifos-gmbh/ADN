<?php
/* Copyright (c) 2010 Leifos, GPL, see docs/LICENSE */


include_once './Services/ADN/Report/classes/class.adnReport.php';

/**
 * Class for the generation of PDF certificates.
 * See menu "Administration of Certified Professionals -> ADN Certificates"
 * Use method createDuplicate() for the generation of certificate duplicates.
 * Use method createExnsion() for the generation of certificate duplicates.
 *
 * @author Stefan Meyer <meyer@leifos.com>
 * @version $Id: class.adnReportCertificate.php 46939 2013-12-19 12:55:55Z smeyer $
 *
 * @ingroup ServicesADN
 */
class adnReportCertificate extends adnReport
{
    const TYPE_DUPLICATE = 1;

    private $certificate_ids = null;
    private $type = null;

    /**
     * Contructor
     */
    public function __construct($certificate_ids = array())
    {
        parent::__construct();
        $this->certificate_ids = $certificate_ids;
    }
    
    /**
     * Check if invoice is available
     * @param int $a_cert_id
     * @return bool
     * @access static
     */
    public static function hasCertificate($a_cert_id)
    {
        return file_exists(
            ilUtil::getDataDir() . '/adn/report/cer/' . $a_cert_id . '.pdf'
        );
    }

    // cr-008
    /**
     * Delete certificate file
     * @param int $a_cert_id
     */
    public static function deleteCertificate($a_cert_id)
    {
        $file = ilUtil::getDataDir() . '/adn/report/cer/' . $a_cert_id . '.pdf';
        if (file_exists($file)) {
            unlink($file);
        }
    }
    // cr-008

    /**
     * Get invoice
     * @param int $a_cert_id
     * @return
     * @access static
     */
    public static function lookupCertificate($a_cert_id)
    {
        return ilUtil::getDataDir() . '/adn/report/cer/' . $a_cert_id . '.pdf';
    }
    
    
    /**
     * Get relative data dir
     * @return string
     */
    public function getRelativeDataDir()
    {
        return 'cer';
    }
    
    /**
     * Get certificate ids
     * @return array
     */
    public function getCertificates()
    {
        return (array) $this->certificate_ids;
    }
    

    /**
     * Create report
     * Throws wrapped adnREportException in case of underlying rpc faults.
     * @return
     */
    public function create()
    {
        // Write on task (fillPdfTemplate for every candidate) and finally merge them in one PDF.
        include_once './Services/ADN/Report/classes/class.adnTaskScheduleWriter.php';
        $writer = new adnTaskScheduleWriter();
        $writer->xmlStartTag('tasks');
    
        include_once './Services/ADN/Report/classes/class.adnReportFileUtils.php';
        $form = adnReportFileUtils::getTemplatePathByType(
            adnReportFileUtils::TPL_REPORT_CERTIFICATE
        );
        
        $all_outfiles = array();
        foreach ($this->getCertificates() as $cert_id) {
            $writer->xmlStartTag(
                'action',
                array(
                    'method' => 'fillPdfTemplate'
                )
            );
            
            $outfile = $this->getDataDir() . '/' . $cert_id . '.pdf';
            
            $writer->addParameter('string', $form);
            $writer->addParameter('string', $outfile);
                
            $map = $this->fillMap($cert_id);

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

        //$GLOBALS['ilLog']->write($writer->xmlDumpMem(true));
        
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
     * Create extension
     * Throws wrapped adnReportException in case of underlying rpc faults.
     * @return
     */
    public function createExtension($cert_id)
    {
        // Write on task (fillPdfTemplate for every candidate) and finally merge them in one PDF.
        include_once './Services/ADN/Report/classes/class.adnTaskScheduleWriter.php';
        $writer = new adnTaskScheduleWriter();
        $writer->xmlStartTag('tasks');
    
        include_once './Services/ADN/Report/classes/class.adnReportFileUtils.php';
        $form = adnReportFileUtils::getTemplatePathByType(
            adnReportFileUtils::TPL_REPORT_CERTIFICATE
        );
        
        $writer->xmlStartTag(
            'action',
            array(
                'method' => 'fillPdfTemplate'
            )
        );
            
        $outfile = $this->getDataDir() . '/' . $cert_id . '.pdf';
            
        $writer->addParameter('string', $form);
        $writer->addParameter('string', $outfile);
                
        $map = $this->fillMap($cert_id);

        $writer->addParameter('map', $map);
        $writer->xmlEndTag('action');
        $writer->xmlEndTag('tasks');

        try {
            $adapter = new adnRpcAdapter();
            $adapter->transformationTaskScheduler(
                $writer->xmlDumpMem()
            );
        } catch (adnReportException $e) {
            throw $e;
        }
        return true;
    }
    
    /**
     * Create certificate duplicate
     * Throws wrapped adnREportException in case of underlying rpc faults.
     * @return
     */
    public function createDuplicate($cert_id)
    {
        // Write on task (fillPdfTemplate for every candidate) and finally merge them in one PDF.
        include_once './Services/ADN/Report/classes/class.adnTaskScheduleWriter.php';
        $writer = new adnTaskScheduleWriter();
        $writer->xmlStartTag('tasks');
    
        include_once './Services/ADN/Report/classes/class.adnReportFileUtils.php';
        $form = adnReportFileUtils::getTemplatePathByType(
            adnReportFileUtils::TPL_REPORT_CERTIFICATE_DUPLICATE
        );
        
        $writer->xmlStartTag(
            'action',
            array(
                'method' => 'fillPdfTemplate'
            )
        );
            
        $outfile = $this->getDataDir() . '/' . $cert_id . '.pdf';
            
        $writer->addParameter('string', $form);
        $writer->addParameter('string', $outfile);
                
        $map = $this->fillMap($cert_id);

        $writer->addParameter('map', $map);
        $writer->xmlEndTag('action');
        $writer->xmlEndTag('tasks');

        try {
            $adapter = new adnRpcAdapter();
            $adapter->transformationTaskScheduler(
                $writer->xmlDumpMem()
            );
        } catch (adnReportException $e) {
            throw $e;
        }
        return true;
    }

    /**
     * Fill map
     * @param array $map
     * @return array $map
     */
    protected function fillMap($cert_id)
    {
        global $lng;

        include_once './Services/ADN/ES/classes/class.adnCertificate.php';
        $cert = new adnCertificate($cert_id);
        
        include_once './Services/ADN/ES/classes/class.adnCertifiedProfessional.php';
        $pro = new adnCertifiedProfessional($cert->getCertifiedProfessionalId());
        
        $map['number'] = (string) $cert->getFullCertificateNumber();
        $map['lastname'] = (string) $pro->getLastName();
        $map['firstname'] = (string) $pro->getFirstName();
        $map['birthday'] = (string) $pro->getBirthdate()->get(IL_CAL_FKT_DATE, 'd') . '. ' .
            $lng->txt('month_' . $pro->getBirthdate()->get(IL_CAL_FKT_DATE, 'm') . '_long') . ' ' .
            $pro->getBirthdate()->get(IL_CAL_FKT_DATE, 'Y');

        include_once "Services/ADN/MD/classes/class.adnCountry.php";
        $country = new adnCountry($pro->getCitizenship());
        $map['nationality'] = $country->getName();
        
        $map['stability_a'] = 'XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX';
        $map['stability_b'] = 'XXXXXXXXXXXXXXX';
        $map['stability_a'] = '';
        $map['stability_b'] = '';

        $map['dry_no'] = (string) $cert->getType(adnCertificate::DRY_MATERIAL)
            ? '' : 'XXXXXXXXXXXXXXXXXXXXX';
        $map['tank_no'] = (string) $cert->getType(adnCertificate::TANK) ? '' : 'XXXXXXXXXXXXXXX';
        $map['gas_no'] = (string) $cert->getType(adnCertificate::GAS) ? '' : 'XXXXXXX';
        $map['chem_no'] = (string) $cert->getType(adnCertificate::CHEMICALS) ? '' : 'XXXXXXX';
        
        $map['valid_until'] = (string) $cert->getValidUntil()->get(IL_CAL_FKT_DATE, 'd') . '. ' .
            $lng->txt('month_' . $cert->getValidUntil()->get(IL_CAL_FKT_DATE, 'm') . '_long') . ' ' .
            $cert->getValidUntil()->get(IL_CAL_FKT_DATE, 'Y');
        
        include_once './Services/ADN/MD/classes/class.adnWMO.php';
        $wmo = new adnWMO($cert->getIssuedByWmo());
        $map['issued_by'] = (string) $wmo->getName();

        $map['issued_on'] = (string) $cert->getLatestIssuedOn()->get(IL_CAL_FKT_DATE, 'd') . '. ' .
            $lng->txt('month_' . $cert->getLatestIssuedOn()->get(IL_CAL_FKT_DATE, 'm') . '_long') . ' ' .
            $cert->getLatestIssuedOn()->get(IL_CAL_FKT_DATE, 'Y');
        $map['ia'] = "i.A. " . $cert->getSignedBy();
        #$map['signed_by'] = $cert->getSignedBy();
        
        return $map;
    }
}
