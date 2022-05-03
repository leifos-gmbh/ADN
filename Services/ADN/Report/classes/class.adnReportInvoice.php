<?php
/* Copyright (c) 2010 Leifos, GPL, see docs/LICENSE */


include_once './Services/ADN/Report/classes/class.adnReport.php';

/**
 * Generation of PDF invoices.
 * See menu "Administration of certified professionals -> Certified Professional"
 *
 * @author Stefan Meyer <meyer@leifos.com>
 * @version $Id: class.adnReportInvoice.php 31770 2011-11-20 10:54:10Z smeyer $
 *
 * @ingroup ServicesADN
 */
class adnReportInvoice extends adnReport
{
    private adnCertificate $cert;
    private adnCertifiedProfessional $pro;
    private adnWMO $wmo;

    private string $invoice_type = '';
    private string $code = '';
    private ?ilDate $due = null;
    

    /**
     * Contructor
     */
    public function __construct(adnCertificate $cert)
    {
        parent::__construct();
        
        $this->cert = $cert;
        
        include_once './Services/ADN/ES/classes/class.adnCertifiedProfessional.php';
        $this->pro = new adnCertifiedProfessional(
            $this->getCertificate()->getCertifiedProfessionalId()
        );
        
        include_once './Services/ADN/AD/classes/class.adnUser.php';
        include_once './Services/ADN/MD/classes/class.adnWMO.php';
        $this->wmo = new adnWMO(adnUser::lookupWMOId());
    }
    
    /**
     * Check if invoice is available
     * @param int $a_cert_id
     * @return bool
     */
    public static function hasInvoice($a_cert_id)
    {
        return file_exists(
            ilUtil::getDataDir() . '/adn/report/invoice/' . $a_cert_id . '.pdf'
        );
    }

    // cr-008 start
    /**
     * Delete invoice
     * @param int $a_cert_id
     */
    public static function deleteInvoice($a_cert_id)
    {
        $file = ilUtil::getDataDir() . '/adn/report/invoice/' . $a_cert_id . '.pdf';
        if (file_exists($file)) {
            unlink($file);
        }
    }
    // cr-008 end
    
    /**
     * Get invoice
     * @param int $a_cert_id
     * @return int
     */
    public static function getInvoice($a_cert_id)
    {
        return ilUtil::getDataDir() . '/adn/report/invoice/' . $a_cert_id . '.pdf';
    }
    
    /**
     * Set invoice type
     * @param string $a_type
     * @return void
     */
    public function setInvoiceType($a_type)
    {
        $this->invoice_type = $a_type;
    }
    
    /**
     * Get invoice type
     * @return string
     */
    public function getInvoiceType()
    {
        return $this->invoice_type;
    }
    
    /**
     * Set due date
     * @param ilDate $due
     * @return
     */
    public function setDue(ilDateTime $due)
    {
        $this->due = $due;
    }
    
    /**
     * get due date
     * @return ilDsteTime
     */
    public function getDue()
    {
        return $this->due;
    }
    
    /**
     * set code
     * @param string $a_code
     * @return
     */
    public function setCode($a_code)
    {
        $this->code = $a_code;
    }
    
    /**
     * get code
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }
    
    /**
     * Get relative data dir
     * @return string
     */
    public function getRelativeDataDir()
    {
        return 'invoice';
    }
    
    /**
     * Get certificate ids
     * @return array
     */
    public function getPro()
    {
        return $this->pro;
    }
    
    /**
     * Get WMO
     * @return adnWMO
     */
    public function getWMO()
    {
        return $this->wmo;
    }
    
    /**
     * Get certificate
     */
    public function getCertificate()
    {
        return $this->cert;
    }
    

    /**
     * Create report
     * Throws wrapped adnReportException in underlying rpc faults
     * @return void
     * @throws adnReportException
     */
    public function create()
    {
        global $ilUser;
    
        // Write on task (fillPdfTemplate for every candidate) and finally merge them in one PDF.
        include_once './Services/ADN/Report/classes/class.adnTaskScheduleWriter.php';
        $writer = new adnTaskScheduleWriter();
        $writer->xmlStartTag('tasks');
    
        include_once './Services/ADN/Report/classes/class.adnReportFileUtils.php';
        $form = adnReportFileUtils::getTemplatePathByType(adnReportFileUtils::TPL_REPORT_INVOICE);
        
        $writer->xmlStartTag(
            'action',
            array(
                'method' => 'fillPdfTemplate'
            )
        );
        // @todo: add unique id
        $outfile = $this->getDataDir() . '/' . $this->getCertificate()->getId() . '.pdf';
        $this->setOutfile($outfile);
            
        $writer->addParameter('string', $form);
        $writer->addParameter('string', $outfile);
                
        $map = $this->addStandardRightColumn(
            array(),
            $this->getWMO()->getId(),
            $ilUser->getId()
        );
            
        //  Fill standard address
        $map = $this->addStandardAddress(
            $map,
            $this->getWMO()->getId(),
            $this->getPro()->getId()
        );
        
        $map = $this->fillMap($map);

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
    }
    
    /**
     * Fill map
     * @param object $map
     * @return
     */
    protected function fillMap($map)
    {
        global $lng,$ilUser;
        
        $cost = $this->getWMO()->getCost($this->getInvoiceType());
        $map['number'] = $cost['no'];

        switch ($this->getInvoiceType()) {
            case adnWMO::COST_CERTIFICATE:
                $map['description'] = $lng->txt('adn_wmo_cost_certificate_short');
                break;

            case adnWMO::COST_DUPLICATE:
                $map['description'] = $lng->txt('adn_wmo_cost_duplicate_short');
                break;

            case adnWMO::COST_EXAM:
                $map['description'] = $lng->txt('adn_wmo_cost_exam_short');
                break;

            case adnWMO::COST_EXTENSION:
                $map['description'] = $lng->txt('adn_wmo_cost_extension_short');
                break;
        }

        $map['cost'] = sprintf('%01.2f EUR', $cost['value']);
        $map['cost'] = str_replace('.', ',', $map['cost']);
        $map['sum'] = sprintf('%01.2f EUR', $cost['value']);
        $map['sum'] = str_replace('.', ',', $map['sum']);

        $until = new ilDate($this->getDue()->get(IL_CAL_UNIX), IL_CAL_UNIX);
        $map['until'] = ilDatePresentation::formatDate($until);
        $map['code'] = $this->getCode();
        
        $map['iban'] = $this->getWMO()->getBankIBAN();
        $map['bic'] = $this->getWMO()->getBankBIC();
        $map['account'] = $this->getWMO()->getBankInstitute();
        
        $map['rcp_salutation'] =
            $lng->txt('adn_report_salutation_' . $this->getPro()->getSalutation()) . ' ' .
            $this->getPro()->getLastName() . ', ';
        
        $map['iss_lastname'] = $ilUser->getLastname();

        $map['legal'] = $this->getLegalRemedies($this->getWMO());

        return $map;
    }
}
