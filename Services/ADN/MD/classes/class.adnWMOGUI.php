<?php
/* Copyright (c) 2010 Leifos, GPL, see docs/LICENSE */

/**
 * WMO GUI class
 *
 * WMO list, forms and persistence
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.de>
 * @version $Id: class.adnWMOGUI.php 34281 2012-04-18 14:42:03Z jluetzen $
 *
 * @ilCtrl_Calls adnWMOGUI: adnCoChairGUI, adnExamFacilityGUI
 *
 * @ingroup ServicesADN
 */
class adnWMOGUI
{
    // current office object
    protected $wmo = null;
    
    /**
     * Constructor
     */
    public function __construct()
    {
        global $ilCtrl;
        
        // save wmo ID through requests
        $ilCtrl->saveParameter($this, array("wmo_id"));
        
        $this->readWMO();
    }
    
    /**
     * Execute command
     */
    public function executeCommand()
    {
        global $ilCtrl, $tpl, $lng;
        
        $next_class = $ilCtrl->getNextClass();
        $tpl->setTitle($lng->txt("adn_md") . " - " . $lng->txt("adn_md_wos"));
        adnIcon::setTitleIcon("md_wos");

        // forward command to next gui class in control flow
        switch ($next_class) {
            case "adncochairgui":
                include_once "Services/ADN/MD/classes/class.adnCoChairGUI.php";
                $gui = new adnCoChairGUI();
                $ilCtrl->forwardCommand($gui);
                break;

            case "adnexamfacilitygui":
                include_once "Services/ADN/MD/classes/class.adnExamFacilityGUI.php";
                $gui = new adnExamFacilityGUI();
                $ilCtrl->forwardCommand($gui);
                break;

            // no next class:
            // this class is responsible to process the command
            default:
                $cmd = $ilCtrl->getCmd("listWMOs");

                switch ($cmd) {
                    // commands that need read permission
                    case "listWMOs":
                        if (adnPerm::check(adnPerm::MD, adnPerm::READ)) {
                            $this->$cmd();
                        }
                        break;
                    
                    // commands that need write permission
                    case "editWMO":
                    case "addWMO":
                    case "saveWMO":
                    case "updateWMO":
                    case "confirmDeleteWMOs":
                    case "deleteWMOs":
                        if (adnPerm::check(adnPerm::MD, adnPerm::WRITE)) {
                            $this->$cmd();
                        }
                        break;
                    
                }
                break;
        }
    }
    
    /**
     * Read wmo
     */
    protected function readWMO()
    {
        if ((int) $_GET["wmo_id"] > 0) {
            include_once("./Services/ADN/MD/classes/class.adnWMO.php");
            $this->office = new adnWMO((int) $_GET["wmo_id"]);
        }
    }
    
    /**
     * List all offices
     */
    protected function listWMOs()
    {
        global $tpl, $ilCtrl, $ilToolbar, $lng;

        if (adnPerm::check(adnPerm::MD, adnPerm::WRITE)) {
            $ilToolbar->addButton(
                $lng->txt("adn_add_wmo"),
                $ilCtrl->getLinkTarget($this, "addWMO")
            );
        }

        // table of offices
        include_once("./Services/ADN/MD/classes/class.adnWMOTableGUI.php");
        $table = new adnWMOTableGUI($this, "listWMOs");
        
        // output table
        $tpl->setContent($table->getHTML());
    }

    /**
     * Add office form
     *
     * @param ilPropertyFormGUI $a_form
     */
    protected function addWMO(ilPropertyFormGUI $a_form = null)
    {
        global $tpl, $lng, $ilTabs, $ilCtrl;

        $ilTabs->setBackTarget($lng->txt("back"), $ilCtrl->getLinkTarget($this, "listWMOs"));

        if (!$a_form) {
            $a_form = $this->initWMOForm(true);
        }
        $tpl->setContent($a_form->getHTML());
    }

    /**
     * Create new office
     */
    protected function saveWMO()
    {
        global $tpl, $lng, $ilCtrl;

        $form = $this->initWMOForm(true);
        if ($form->checkInput()) {
            include_once("./Services/ADN/MD/classes/class.adnWMO.php");
            $office = new adnWMO();
            $office->setName($form->getInput("name"));
            $office->setSubtitle($form->getInput('subtitle'));
            $office->setCode($form->getInput("code"));
            $office->setPhone($form->getInput("fon"));
            $office->setFax($form->getInput("fax"));
            $office->setEmail($form->getInput("email"));
            $office->setNotificationEmail($form->getInput('emailnoti'));
            $office->setURL($form->getInput("url"));
            $office->setPostalStreet($form->getInput("pstreet"));
            $office->setPostalStreetNumber($form->getInput("pstreetno"));
            $office->setPostalZip($form->getInput("pzip"));
            $office->setPostalCity($form->getInput("pcity"));

            if ($form->getInput("vcbox")) {
                $office->setVisitorStreet($form->getInput("vstreet"));
                $office->setVisitorStreetNumber($form->getInput("vstreetno"));
                $office->setVisitorZip($form->getInput("vzip"));
                $office->setVisitorCity($form->getInput("vcity"));
            }

            $office->setBankInstitute($form->getInput("bbank"));
            $office->setBankCode($form->getInput("bcode"));
            $office->setBankAccount($form->getInput("baccount"));
            $office->setBankBIC($form->getInput("bbic"));
            $office->setBankIBAN($form->getInput("biban"));

            $office->setCostCertificate(
                $form->getInput("ccno"),
                $form->getInput("ccdesc"),
                $form->getInput("ccval")
            );
            $office->setCostDuplicate(
                $form->getInput("cdno"),
                $form->getInput("cddesc"),
                $form->getInput("cdval")
            );
            $office->setCostExtension(
                $form->getInput("ceno"),
                $form->getInput("cedesc"),
                $form->getInput("ceval")
            );
            $office->setCostExam(
                $form->getInput("cxno"),
                $form->getInput("cxdesc"),
                $form->getInput("cxval")
            );

            if ($office->save()) {
                ilUtil::sendSuccess($lng->txt("adn_wmo_created"), true);
                $ilCtrl->redirect($this, "listWMOs");
            }
        }

        $form->setValuesByPost();
        $this->addWMO($form);
    }

    /**
     * Edit office form
     *
     * @param ilPropertyFormGUI $a_form
     */
    protected function editWMO(ilPropertyFormGUI $a_form = null)
    {
        global $tpl, $lng, $ilTabs, $ilCtrl;

        $ilTabs->setBackTarget($lng->txt("back"), $ilCtrl->getLinkTarget($this, "listWMOs"));

        if (!$a_form) {
            $a_form = $this->initWMOForm();
        }
        $tpl->setContent($a_form->getHTML());
    }

    /**
     * Update existing office
     */
    protected function updateWMO()
    {
        global $tpl, $lng, $ilCtrl;

        $form = $this->initWMOForm();
        if ($form->checkInput()) {
            $this->office->setName($form->getInput("name"));
            $this->office->setSubtitle($form->getInput('subtitle'));
            $this->office->setCode($form->getInput("code"));
            $this->office->setPhone($form->getInput("fon"));
            $this->office->setFax($form->getInput("fax"));
            $this->office->setEmail($form->getInput("email"));
            $this->office->setNotificationEmail($form->getInput('emailnoti'));
            $this->office->setURL($form->getInput("url"));
            $this->office->setPostalStreet($form->getInput("pstreet"));
            $this->office->setPostalStreetNumber($form->getInput("pstreetno"));
            $this->office->setPostalZip($form->getInput("pzip"));
            $this->office->setPostalCity($form->getInput("pcity"));

            if ($form->getInput("vcbox")) {
                $this->office->setVisitorStreet($form->getInput("vstreet"));
                $this->office->setVisitorStreetNumber($form->getInput("vstreetno"));
                $this->office->setVisitorZip($form->getInput("vzip"));
                $this->office->setVisitorCity($form->getInput("vcity"));
            } else {
                $this->office->setVisitorStreet(null);
                $this->office->setVisitorStreetNumber(null);
                $this->office->setVisitorZip(null);
                $this->office->setVisitorCity(null);
            }

            $this->office->setBankInstitute($form->getInput("bbank"));
            $this->office->setBankCode($form->getInput("bcode"));
            $this->office->setBankAccount($form->getInput("baccount"));
            $this->office->setBankBIC($form->getInput("bbic"));
            $this->office->setBankIBAN($form->getInput("biban"));
            
            $this->office->setCostCertificate(
                $form->getInput("ccno"),
                $form->getInput("ccdesc"),
                $form->getInput("ccval")
            );
            $this->office->setCostDuplicate(
                $form->getInput("cdno"),
                $form->getInput("cddesc"),
                $form->getInput("cdval")
            );
            $this->office->setCostExtension(
                $form->getInput("ceno"),
                $form->getInput("cedesc"),
                $form->getInput("ceval")
            );
            $this->office->setCostExam(
                $form->getInput("cxno"),
                $form->getInput("cxdesc"),
                $form->getInput("cxval")
            );
            $this->office->setCostExamGasChem(
                $form->getInput("cxgcno"),
                $form->getInput("cxgcdesc"),
                $form->getInput("cxgcval")
            );
        

            if ($this->office->update()) {
                ilUtil::sendSuccess($lng->txt("adn_wmo_updated"), true);
                $ilCtrl->redirect($this, "listWMOs");
            }
        }

        $form->setValuesByPost();
        $this->editWMO($form);
    }

    /**
     * Build wmo form
     *
     * @return ilPropertyFormGUI
     */
    protected function initWMOForm($a_create = false)
    {
        global  $lng, $ilCtrl;

        include_once "Services/Form/classes/class.ilPropertyFormGUI.php";
        $form = new ilPropertyFormGUI();
        $form->setTitle($lng->txt("adn_wmo"));
        $form->setFormAction($ilCtrl->getFormAction($this, "listWMOs"));

        $name = new ilTextInputGUI($lng->txt("adn_name"), "name");
        $name->setRequired(true);
        $name->setMaxLength(100);
        $form->addItem($name);

        $stitle = new ilTextInputGUI($lng->txt("adn_subtitle"), "subtitle");
        $stitle->setRequired(false);
        $stitle->setMaxLength(100);
        $form->addItem($stitle);

        $code = new ilTextInputGUI($lng->txt("adn_wmo_code"), "code");
        $code->setRequired(true);
        $code->setMaxLength(10);
        $code->setSize(10);
        $form->addItem($code);

        $fon = new ilTextInputGUI($lng->txt("adn_phone"), "fon");
        $fon->setRequired(true);
        $fon->setMaxLength(30);
        $fon->setSize(30);
        $form->addItem($fon);

        $fax = new ilTextInputGUI($lng->txt("adn_fax"), "fax");
        $fax->setRequired(true);
        $fax->setMaxLength(30);
        $fax->setSize(30);
        $form->addItem($fax);

        $mail = new ilTextInputGUI($lng->txt("adn_email"), "email");
        $mail->setRequired(true);
        $mail->setMaxLength(50);
        $form->addItem($mail);

        $mailnoti = new ilTextInputGUI($lng->txt("adn_email_noti"), "emailnoti");
        $mailnoti->setRequired(true);
        $mailnoti->setMaxLength(50);
        $form->addItem($mailnoti);

        $url = new ilTextInputGUI($lng->txt("adn_url"), "url");
        $url->setRequired(true);
        $url->setMaxLength(50);
        $form->addItem($url);


        $sub = new ilFormSectionHeaderGUI();
        $sub->setTitle($lng->txt("adn_postal_address"));
        $form->addItem($sub);

        $pstreet = new ilTextInputGUI($lng->txt("adn_street"), "pstreet");
        $pstreet->setRequired(true);
        $pstreet->setMaxLength(50);
        $form->addItem($pstreet);

        $pstreet_no = new ilTextInputGUI($lng->txt("adn_street_number"), "pstreetno");
        $pstreet_no->setRequired(true);
        $pstreet_no->setMaxLength(10);
        $pstreet_no->setSize(10);
        $form->addItem($pstreet_no);

        $pzip = new ilTextInputGUI($lng->txt("adn_zip"), "pzip");
        $pzip->setRequired(true);
        $pzip->setMaxLength(10);
        $pzip->setSize(10);
        $form->addItem($pzip);

        $pcity = new ilTextInputGUI($lng->txt("adn_city"), "pcity");
        $pcity->setRequired(true);
        $pcity->setMaxLength(50);
        $form->addItem($pcity);


        $vcbox = new ilCheckboxInputGUI($lng->txt("adn_visitors_address"), "vcbox");
        $form->addItem($vcbox);

        $vstreet = new ilTextInputGUI($lng->txt("adn_street"), "vstreet");
        $vstreet->setMaxLength(50);
        $vcbox->addSubItem($vstreet);

        $vstreet_no = new ilTextInputGUI($lng->txt("adn_street_number"), "vstreetno");
        $vstreet_no->setMaxLength(10);
        $vstreet_no->setSize(10);
        $vcbox->addSubItem($vstreet_no);

        $vzip = new ilTextInputGUI($lng->txt("adn_zip"), "vzip");
        $vzip->setMaxLength(10);
        $vzip->setSize(10);
        $vcbox->addSubItem($vzip);

        $vcity = new ilTextInputGUI($lng->txt("adn_city"), "vcity");
        $vcity->setMaxLength(50);
        $vcbox->addSubItem($vcity);


        $sub = new ilFormSectionHeaderGUI();
        $sub->setTitle($lng->txt("adn_banking_details"));
        $form->addItem($sub);

        $bbank = new ilTextInputGUI($lng->txt("adn_bank"), "bbank");
        $bbank->setRequired(true);
        $bbank->setMaxLength(64);
        $form->addItem($bbank);

        if (!$a_create) {
            if ($this->office->getBankCode()) {
                $bcode = new ilNumberInputGUI($lng->txt("adn_bank_code"), "bcode");
                $bcode->setMaxLength(20);
                $bcode->setSize(20);
                $form->addItem($bcode);
            }

            if ($this->office->getBankAccount()) {
                $baccount = new ilNumberInputGUI($lng->txt("adn_bank_account"), "baccount");
                $baccount->setMaxLength(20);
                $baccount->setSize(20);
                $form->addItem($baccount);
            }
        }

        $biban = new ilTextInputGUI($lng->txt("adn_bank_iban"), "biban");
        $biban->setRequired(true);
        $biban->setMaxLength(34);
        $biban->setSize(20);
        $form->addItem($biban);

        $bbic = new ilTextInputGUI($lng->txt("adn_bank_bic"), "bbic");
        $bbic->setRequired(true);
        $bbic->setMaxLength(20);
        $bbic->setSize(20);
        $form->addItem($bbic);


        $sub = new ilFormSectionHeaderGUI();
        $sub->setTitle($lng->txt("adn_wmo_cost_certificate"));
        $form->addItem($sub);

        $ccno = new ilTextInputGUI($lng->txt("adn_running_id"), "ccno");
        $ccno->setRequired(true);
        $ccno->setMaxLength(10);
        $ccno->setSize(10);
        $form->addItem($ccno);

        $ccdesc = new ilTextAreaInputGUI($lng->txt("adn_description"), "ccdesc");
        $ccdesc->setCols(80);
        $ccdesc->setRows(5);
        $ccdesc->setRequired(true);
        $form->addItem($ccdesc);

        $ccval = new ilNumberInputGUI($lng->txt("adn_cost"), "ccval");
        $ccval->setRequired(true);
        $ccval->setDecimals(2);
        $ccval->setSize(10);
        $ccval->setSuffix("EUR");
        $ccval->setMaxLength(20);
        $form->addItem($ccval);


        $sub = new ilFormSectionHeaderGUI();
        $sub->setTitle($lng->txt("adn_wmo_cost_duplicate"));
        $form->addItem($sub);

        $cdno = new ilTextInputGUI($lng->txt("adn_running_id"), "cdno");
        $cdno->setRequired(true);
        $cdno->setMaxLength(10);
        $cdno->setSize(10);
        $form->addItem($cdno);

        $cddesc = new ilTextAreaInputGUI($lng->txt("adn_description"), "cddesc");
        $cddesc->setCols(80);
        $cddesc->setRows(5);
        $cddesc->setRequired(true);
        $form->addItem($cddesc);

        $cdval = new ilNumberInputGUI($lng->txt("adn_cost"), "cdval");
        $cdval->setRequired(true);
        $cdval->setDecimals(2);
        $cdval->setSize(10);
        $cdval->setSuffix("EUR");
        $cdval->setMaxLength(20);
        $form->addItem($cdval);


        $sub = new ilFormSectionHeaderGUI();
        $sub->setTitle($lng->txt("adn_wmo_cost_extension"));
        $form->addItem($sub);

        $ceno = new ilTextInputGUI($lng->txt("adn_running_id"), "ceno");
        $ceno->setRequired(true);
        $ceno->setMaxLength(10);
        $ceno->setSize(10);
        $form->addItem($ceno);

        $cedesc = new ilTextAreaInputGUI($lng->txt("adn_description"), "cedesc");
        $cedesc->setCols(80);
        $cedesc->setRows(5);
        $cedesc->setRequired(true);
        $form->addItem($cedesc);

        $ceval = new ilNumberInputGUI($lng->txt("adn_cost"), "ceval");
        $ceval->setRequired(true);
        $ceval->setDecimals(2);
        $ceval->setSize(10);
        $ceval->setSuffix("EUR");
        $ceval->setMaxLength(20);
        $form->addItem($ceval);


        $sub = new ilFormSectionHeaderGUI();
        $sub->setTitle($lng->txt("adn_wmo_cost_exam"));
        $form->addItem($sub);

        $cxno = new ilTextInputGUI($lng->txt("adn_running_id"), "cxno");
        $cxno->setRequired(true);
        $cxno->setMaxLength(10);
        $cxno->setSize(10);
        $form->addItem($cxno);

        $cxdesc = new ilTextAreaInputGUI($lng->txt("adn_description"), "cxdesc");
        $cxdesc->setCols(80);
        $cxdesc->setRows(5);
        $cxdesc->setRequired(true);
        $form->addItem($cxdesc);

        $cxval = new ilNumberInputGUI($lng->txt("adn_cost"), "cxval");
        $cxval->setRequired(true);
        $cxval->setDecimals(2);
        $cxval->setSize(10);
        $cxval->setSuffix("EUR");
        $cxval->setMaxLength(20);
        $form->addItem($cxval);

        $sub = new ilFormSectionHeaderGUI();
        $sub->setTitle($lng->txt("adn_wmo_cost_exam_gas_chem"));
        $form->addItem($sub);

        $cxgcno = new ilTextInputGUI($lng->txt("adn_running_id"), "cxgcno");
        $cxgcno->setRequired(true);
        $cxgcno->setMaxLength(10);
        $cxgcno->setSize(10);
        $form->addItem($cxgcno);

        $cxgcdesc = new ilTextAreaInputGUI($lng->txt("adn_description"), "cxgcdesc");
        $cxgcdesc->setCols(80);
        $cxgcdesc->setRows(5);
        $cxgcdesc->setRequired(true);
        $form->addItem($cxgcdesc);

        $cxgcval = new ilNumberInputGUI($lng->txt("adn_cost"), "cxgcval");
        $cxgcval->setRequired(true);
        $cxgcval->setDecimals(2);
        $cxgcval->setSize(10);
        $cxgcval->setSuffix("EUR");
        $cxgcval->setMaxLength(20);
        $form->addItem($cxgcval);


        if ($a_create) {
            $form->addCommandButton("saveWMO", $lng->txt("save"));
        } else {
            $name->setValue($this->office->getName());
            $stitle->setValue($this->office->getSubtitle());
            $code->setValue($this->office->getCode());
            $fon->setValue($this->office->getPhone());
            $fax->setValue($this->office->getFax());
            $mail->setValue($this->office->getEmail());
            $mailnoti->setValue($this->office->getNotificationEmail());
            $url->setValue($this->office->getURL());
            $pstreet->setValue($this->office->getPostalStreet());
            $pstreet_no->setValue($this->office->getPostalStreetNumber());
            $pzip->setValue($this->office->getPostalZip());
            $pcity->setValue($this->office->getPostalCity());

            if ($this->office->getVisitorStreet()) {
                $vcbox->setChecked(true);
                $vstreet->setValue($this->office->getVisitorStreet());
                $vstreet_no->setValue($this->office->getVisitorStreetNumber());
                $vzip->setValue($this->office->getVisitorZip());
                $vcity->setValue($this->office->getVisitorCity());
            }

            $bbank->setValue($this->office->getBankInstitute());
            
            if ($this->office->getBankCode()) {
                $bcode->setValue($this->office->getBankCode());
            }
            if ($this->office->getBankAccount()) {
                $baccount->setValue($this->office->getBankAccount());
            }
            $bbic->setValue($this->office->getBankBIC());
            $biban->setValue($this->office->getBankIBAN());

            $cost = $this->office->getCostCertificate();
            $ccno->setValue($cost["no"]);
            $ccdesc->setValue($cost["desc"]);
            $ccval->setValue($cost["value"]);

            $cost = $this->office->getCostDuplicate();
            $cdno->setValue($cost["no"]);
            $cddesc->setValue($cost["desc"]);
            $cdval->setValue($cost["value"]);

            $cost = $this->office->getCostExtension();
            $ceno->setValue($cost["no"]);
            $cedesc->setValue($cost["desc"]);
            $ceval->setValue($cost["value"]);

            $cost = $this->office->getCostExam();
            $cxno->setValue($cost["no"]);
            $cxdesc->setValue($cost["desc"]);
            $cxval->setValue($cost["value"]);

            $cost = $this->office->getCostExamGasChem();
            $cxgcno->setValue($cost["no"]);
            $cxgcdesc->setValue($cost["desc"]);
            $cxgcval->setValue($cost["value"]);

            $form->addCommandButton("updateWMO", $lng->txt("save"));
        }
        $form->addCommandButton("listWMOs", $lng->txt("cancel"));

        return $form;
    }

    /**
     * Confirm deletion of wmos
     */
    public function confirmDeleteWMOs()
    {
        global $ilCtrl, $tpl, $lng, $ilTabs;

        // check whether at least one item has been seleced
        if (!is_array($_POST["wmo_id"]) || count($_POST["wmo_id"]) == 0) {
            ilUtil::sendFailure($lng->txt("no_checkbox"), true);
            $ilCtrl->redirect($this, "listWMOs");
        } else {
            $ilTabs->setBackTarget(
                $lng->txt("back"),
                $ilCtrl->getLinkTarget($this, "listWMOs")
            );

            // display confirmation message
            include_once("./Services/Utilities/classes/class.ilConfirmationGUI.php");
            $cgui = new ilConfirmationGUI();
            $cgui->setFormAction($ilCtrl->getFormAction($this));
            $cgui->setHeaderText($lng->txt("adn_sure_delete_wmos"));
            $cgui->setCancel($lng->txt("cancel"), "listWMOs");
            $cgui->setConfirm($lng->txt("delete"), "deleteWMOs");

            include_once("./Services/ADN/MD/classes/class.adnWMO.php");

            // list objects that should be deleted
            foreach ($_POST["wmo_id"] as $i) {
                $cgui->addItem("wmo_id[]", $i, adnWMO::lookupName($i));
            }

            $tpl->setContent($cgui->getHTML());
        }
    }

    /**
     * Delete wmos
     */
    protected function deleteWMOs()
    {
        global $ilCtrl, $lng;

        include_once("./Services/ADN/MD/classes/class.adnWMO.php");

        if (is_array($_POST["wmo_id"])) {
            foreach ($_POST["wmo_id"] as $i) {
                $wmo = new adnWMO($i);
                $wmo->delete();
            }
        }
        ilUtil::sendSuccess($lng->txt("adn_wmo_deleted"), true);
        $ilCtrl->redirect($this, "listWMOs");
    }
}
