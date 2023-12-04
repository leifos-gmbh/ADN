<?php
/* Copyright (c) 2010 Leifos, GPL, see docs/LICENSE */

include_once './Services/ADN/Report/exceptions/class.adnReportException.php';
include_once './Services/ADN/Base/classes/class.adnRpcAdapter.php';

/**
 * Abstract base class for adn reports.
 * Offers utilities for the generation of PDF template base reports.
 *
 * @author Stefan Meyer <meyer@leifos.com>
 * @version $Id: class.adnReport.php 34283 2012-04-18 14:54:16Z jluetzen $
 *
 * @ingroup ServicesADN
 */
abstract class adnReport
{
    const MAX_RIGHT_COL_LENGTH = 26;

    const FORMATTING_BOLD = 1;
    const FORMATTING_UNDERLINE = 2;
    const FORMATTING_HIGHER = 3;
    const FORMATTING_DEEPER = 4;
    
    private $cFormattingStack = array();
    
    
    private $outfile = null;
    
    private $basedir = 'adn/report';
    private $datadir = '';
    
    
    /**
     * Constructor
     * @return
     */
    public function __construct()
    {
        ilUtil::makeDirParents(
            $this->datadir = ilUtil::getDataDir() . '/' . $this->basedir . '/' . $this->getRelativeDataDir()
        );
        $GLOBALS['ilLog']->write(__METHOD__ . ': ' . $this->datadir);
    }
    
    /**
     * Get a certificate if available
     * @return adnCertificate | null
     */
    public function getCertificate()
    {
        return null;
    }


    /**
     * Create report
     * @return
     */
    abstract public function create();
    
    
    /**
     * Get relative data dir for report type
     * @return
     */
    abstract public function getRelativeDataDir();
    
    /**
     * get absolute datadir
     * @return
     */
    public function getDataDir()
    {
        return $this->datadir;
    }
    

    /**
     * Create tempfile
     * @return
     */
    protected function initOutfile()
    {
        $this->outfile = ilUtil::ilTempnam();
        $fp = fopen($this->outfile, 'w');
        fclose($fp);
        return $this->outfile;
    }
    
    /**
     * get outfile
     * @return string
     */
    public function getOutfile()
    {
        return $this->outfile;
    }
    
    /**
     * Set outfile
     * @param int $a_outfile
     * @return void
     */
    public function setOutfile($a_outfile)
    {
        $this->outfile = $a_outfile;
    }
    
    /**
     * Add standard mapping for right column
     * @param array map
     * @param int wsd id
     * @return
     */
    public function addStandardRightColumn($map, $wsd)
    {
        global $ilUser, $lng;
        
        include_once './Services/ADN/MD/classes/class.adnWMO.php';
        include_once './Services/Calendar/classes/class.ilCalendarUtil.php';
        $wmo = new adnWMO($wsd);

        $title = $wmo->getName();
        include_once './Services/Utilities/classes/class.ilStr.php';
        if (ilStr::strLen($wmo->getName()) <= self::MAX_RIGHT_COL_LENGTH) {
            $map['rgt_wmo_name'] = "<br><br>";
        } elseif (ilStr::strLen($wmo->getName()) <= (2 * self::MAX_RIGHT_COL_LENGTH)) {
            $map['rgt_wmo_name'] = "<br>";
        }
        
        if ($this->getCertificate() instanceof adnCertificate) {
            $full_nr = $wmo->getCode() . "-" .
                str_pad($this->getCertificate()->getNumber(), 4, "0", STR_PAD_LEFT) . "-" .
                $this->getCertificate()->getIssuedOn()->get(IL_CAL_FKT_DATE, 'Y');
            $map['rgt_cert_number'] = $full_nr;
        }
        
        // banking details
        $info = $lng->txt('adn_banking_details') . ':';
        $info .= ("\n" . $wmo->getBankInstitute());
        $info .= ("\n" . $lng->txt('adn_bank_iban') . ': ' . $wmo->getBankIBAN());
        $info .= ("\n" . $lng->txt('adn_bank_bic') . ': ' . $wmo->getBankBIC());
        
        $map['rgt_bank_account_info'] = $info;
        
        $map['rgt_wmo_name'] .= $title;
        $map['rgt_wmo_subtitle'] = $wmo->getSubtitle();
        $map['rgt_wmo_street'] = $wmo->getPostalStreet() . ' ' . $wmo->getPostalStreetNumber();
        $map['rgt_wmo_city'] = $wmo->getPostalZip() . ' ' . $wmo->getPostalCity();
        $map['rgt_wmo_phone'] = $wmo->getPhone();
        $map['rgt_wmo_fax'] = strlen($wmo->getFax()) ? $wmo->getFax() : '-';
        $map['rgt_wmo_mail'] = $wmo->getEmail();
        $map['rgt_wmo_url'] = $wmo->getURL();
        
        $map['rgt_date'] = date('d') . '. ' . ilCalendarUtil::_numericMonthToString(date('n')) . ' ' . date('Y');
        
        $map['rgt_iss_identifier'] = $ilUser->getSign();
        $map['rgt_iss_name'] = $ilUser->getFullname();
        $map['rgt_iss_phone'] = $ilUser->getPhoneOffice();
        $map['rgt_iss_fax'] = strlen($ilUser->getFax()) ? $ilUser->getFax() : '-';

        if (ilStr::strLen($ilUser->getEmail()) > self::MAX_RIGHT_COL_LENGTH) {
            $parts = explode('@', $ilUser->getEmail());
            $map['rgt_iss_mail'] = $parts[0] . "@\n" . $parts[1];
        } else {
            $map['rgt_iss_mail'] = $ilUser->getEmail();
        }

        $map['rgt_wmo_web'] = $wmo->getURL();
        
        return (array) $map;
    }
    
    /**
     * Add standard address field
     * @param object $map
     * @param object $wmo
     * @param object $rcp
     * @return
     */
    public function addStandardAddress($map, $wmo, $rcp)
    {
        global $ilUser,$lng;
        
        include_once './Services/ADN/MD/classes/class.adnWMO.php';
        include_once './Services/Calendar/classes/class.ilCalendarUtil.php';
        $wmo = new adnWMO($wmo);

        $map['sender_wmo_name'] = $wmo->getName();
        $map['sender_wmo_address'] =
            $wmo->getPostalStreet() . ' ' . $wmo->getPostalStreetNumber() . ' ' .
            $wmo->getPostalZip() . ' ' . $wmo->getPostalCity();
        
        include_once './Services/ADN/ES/classes/class.adnCertifiedProfessional.php';
        $cand = new adnCertifiedProfessional($rcp);
        
        
        // Base fields
        $sal = $lng->txt('salutation_' . $cand->getSalutation());
        $name = $cand->getFirstName() . ' ' . $cand->getLastName();
        $street = $cand->getPostalStreet() . ' ' . $cand->getPostalStreetNumber();
        
        $city = '';
            
        include_once './Services/ADN/MD/classes/class.adnCountry.php';
        $country = new adnCountry($cand->getPostalCountry());
        if ($country->getCode() != 'DE') {
            $city .= $country->getCode() . '-';
        }
        
        $city .= $cand->getPostalCode() . ' ' . $cand->getPostalCity();
        
        // Overwrite with shipping adress if available and enabled
        if ($cand->isShippingActive()) {
            $sal = $lng->txt('salutation_' . $cand->getShippingSalutation());
            $name = $cand->getShippingFirstName() . ' ' . $cand->getShippingLastName();
            $street = $cand->getShippingStreet() . ' ' . $cand->getShippingStreetNumber();
                                    
            $city = '';
            
            $country = new adnCountry($cand->getShippingCountry());
            if ($country->getCode() != 'DE') {
                $city .= $country->getCode() . '-';
            }
            
            $city .= $cand->getShippingCode() . ' ' . $cand->getShippingCity();
        }
        
        $map['rcp_address'] = $sal . "\n" . $name . "\n" . $street . "\n" . $city;
        #$GLOBALS['ilLog']->write(print_r($map,true));
        
        return $map;
    }
    
    /**
     * Parse formatting of questions, solutions.
     * Each e.g [f]abc[\f] is parsed as <phrase bold="1">abc</phrase>
     */
    protected function parseFormatting($writer, $text)
    {
        include_once './Services/Utilities/classes/class.ilStr.php';
        
        $cFormattingStack = array();
        $currentText = '';
    
        // do not parse text if if it does not contain formattings.
        if (!preg_match('/\[[fuht]\]/', $text)) {
            $GLOBALS['ilLog']->write(__METHOD__ . ': not parsed ' . $text);
            $this->writePhrase($writer, $cFormattingStack, $text);
            return true;
        }
        
        
        for ($pos = 0; $pos < ilStr::strLen($text); $pos++) {
            // Test start tag
            $pStart = ilStr::subStr($text, $pos, 3);
            #$GLOBALS['ilLog']->write(__METHOD__.': Position '.$pos);
            #$GLOBALS['ilLog']->write(__METHOD__.': Start    '.$pStart);
            // Start Elements
            if (strcmp($pStart, '[f]') === 0) {
                $this->writePhrase($writer, $cFormattingStack, $currentText);
                array_push($cFormattingStack, self::FORMATTING_BOLD);
                $currentText = '';
                $pos += 2;
                continue;
            }
            if (strcmp($pStart, '[u]') === 0) {
                $this->writePhrase($writer, $cFormattingStack, $currentText);
                array_push($cFormattingStack, self::FORMATTING_UNDERLINE);
                $currentText = '';
                $pos += 2;
                continue;
            }
            if (strcmp($pStart, '[h]') === 0) {
                $this->writePhrase($writer, $cFormattingStack, $currentText);
                array_push($cFormattingStack, self::FORMATTING_HIGHER);
                $currentText = '';
                $pos += 2;
                continue;
            }
            if (strcmp($pStart, '[t]') === 0) {
                $this->writePhrase($writer, $cFormattingStack, $currentText);
                array_push($cFormattingStack, self::FORMATTING_DEEPER);
                $currentText = '';
                $pos += 2;
                continue;
            }
            
            
            // End elements
            $pend = ilStr::subStr($text, $pos, 4);

            #$GLOBALS['ilLog']->write(__METHOD__.': No start tag');
            #$GLOBALS['ilLog']->write(__METHOD__.': End    '.$pend);
            
            if (strcmp($pend, "[/f]") === 0) {
                $this->writePhrase($writer, $cFormattingStack, $currentText);
                @array_pop($cFormattingStack);
                $currentText = '';
                $pos += 3;
                continue;
            }
            if (strcmp($pend, "[/u]") === 0) {
                $this->writePhrase($writer, $cFormattingStack, $currentText);
                $GLOBALS['ilLog']->write('CURRENT STACK: ' . print_r($cFormattingStack, true));
                @array_pop($cFormattingStack);
                $GLOBALS['ilLog']->write('CURRENT STACK: ' . print_r($cFormattingStack, true));
                $currentText = '';
                $pos += 3;
                continue;
            }
            if (strcmp($pend, "[/h]") === 0) {
                $this->writePhrase($writer, $cFormattingStack, $currentText);
                @array_pop($cFormattingStack);
                $currentText = '';
                $pos += 3;
                continue;
            }
            if (strcmp($pend, "[/t]") === 0) {
                $this->writePhrase($writer, $cFormattingStack, $currentText);
                @array_pop($cFormattingStack);
                $currentText = '';
                $pos += 3;
                continue;
            }
            
            #$GLOBALS['ilLog']->write(__METHOD__.': No end tag');

            $currentText .= ilStr::subStr($text, $pos, 1);
            #$GLOBALS['ilLog']->write(__METHOD__.': ct => ' . $currentText);
        }
        
        // Write the phrase
        $this->writePhrase($writer, $cFormattingStack, $currentText);
        return true;
    }
        
    /**
     * Write one phrase
     * @param object $writer
     * @param object $formatting
     * @param object $text
     * @return
     */
    protected function writePhrase($writer, $formatting, $text)
    {
        #$GLOBALS['ilLog']->write('------------------------');
        if (!strlen($text)) {
            return true;
        }
        
        #$GLOBALS['ilLog']->write(__METHOD__.': Writing phrase => '.$text);
        
        $att = array();
        if (in_array(self::FORMATTING_BOLD, $formatting)) {
            $att['bold'] = 1;
        }
        if (in_array(self::FORMATTING_UNDERLINE, $formatting)) {
            $att['underline'] = 1;
        }
        if (in_array(self::FORMATTING_DEEPER, $formatting)) {
            $att['deeper'] = 1;
        }
        if (in_array(self::FORMATTING_HIGHER, $formatting)) {
            $att['higher'] = 1;
        }
        
        #$GLOBALS['ilLog']->write('Writing: '.$text);
        #$GLOBALS['ilLog']->write(__METHOD__.': '.print_r($att,true));
        $writer->xmlElement('phrase', $att, $text);
    }

    /**
     * get legal remedies text
     * @global ilLanguage $lng
     * @param adnWMO $wmo
     * @return string
     */
    protected function getLegalRemedies(adnWMO $wmo)
    {
        global $lng;

        $contact = $wmo->getName() . ', ' .
            $wmo->getPostalStreet() . ' ' . $wmo->getPostalStreetNumber() . ' in ' .
            $wmo->getPostalZip() . ' ' . $wmo->getPostalCity();

        return sprintf($lng->txt('adn_report_legal_remedies'), $contact);
    }
}
