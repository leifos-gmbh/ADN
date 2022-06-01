<?php
/* Copyright (c) 2010 Leifos, GPL, see docs/LICENSE */

/**
 * ADN certified professional data GUI class
 *
 * Handles editing of certified professionals
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.de>
 * @version $Id: class.adnCertifiedProfessionalDataGUI.php 34281 2012-04-18 14:42:03Z jluetzen $
 *
 * @ilCtrl_Calls adnCertifiedProfessionalDataGUI:
 *
 * @ingroup ServicesADN
 */
class adnCertifiedProfessionalDataGUI
{
    protected ?adnCertifiedProfessional $professional = null;

    protected ilCtrl $ctrl;
    protected ilLanguage $lng;
    protected ilGlobalTemplateInterface $tpl;
    protected ilToolbarGUI $toolbar;
    protected ilTabsGUI $tabs;
    
    /**
     * Constructor
     */
    public function __construct()
    {
        global $DIC;

        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();
        $this->tpl = $DIC->ui()->mainTemplate();
        $this->toolbar = $DIC->toolbar();
        $this->tabs = $DIC->tabs();
        
        // save professional ID through requests
        $this->ctrl->saveParameter($this, array("ct_cpr"));
        
        $this->readProfessional();
    }
    
    /**
     * Execute command
     */
    public function executeCommand()
    {

        $this->tpl->setTitle($this->lng->txt("adn_cp") . " - " . $this->lng->txt("adn_cp_cpr"));
        
        $next_class = $this->ctrl->getNextClass();
        
        // forward command to next gui class in control flow
        switch ($next_class) {
            // no next class:
            // this class is responsible to process the command
            default:
                $cmd = $this->ctrl->getCmd("listProfessionals");
                
                switch ($cmd) {
                    // commands that need read permission
                    case "listProfessionals":
                    case "applyFilter":
                    case "resetFilter":
                        if (adnPerm::check(adnPerm::CP, adnPerm::READ)) {
                            $this->$cmd();
                        }
                        break;
                    
                    // commands that need write permission
                    case "editProfessional":
                    case "updateProfessional":
                        if (adnPerm::check(adnPerm::CP, adnPerm::WRITE)) {
                            $this->$cmd();
                        }
                        break;
                    
                }
                break;
        }
    }
    
    /**
     * Read professional
     */
    protected function readProfessional()
    {
        if ((int) $_GET["ct_cpr"] > 0) {
            include_once("./Services/ADN/ES/classes/class.adnCertifiedProfessional.php");
            $this->professional = new adnCertifiedProfessional((int) $_GET["ct_cpr"]);
        }
    }
    
    /**
     * List all professionals
     */
    protected function listProfessionals()
    {

        // cr-008 start
        include_once("./Services/ADN/EP/classes/class.adnPreparationCandidateGUI.php");
        $this->ctrl->setParameterByClass("adnpreparationcandidategui", "mode", adnPreparationCandidateGUI::MODE_GENERAL);
        $this->toolbar->addButton(
            $this->lng->txt("adn_ad_add_person"),
            $this->ctrl->getLinkTargetByClass(array("adnbasegui", "adnexaminationpreparationgui", "adnpreparationcandidategui"), "createCandidate")
        );
        // cr-008 end


        // table of certificates
        include_once("./Services/ADN/CP/classes/class.adnCertifiedProfessionalDataTableGUI.php");
        $table = new adnCertifiedProfessionalDataTableGUI($this, "listProfessionals");
        
        // output table
        $this->tpl->setContent($table->getHTML());
    }

    /**
     * Apply filter settings (from table GUI)
     */
    protected function applyFilter()
    {
        include_once("./Services/ADN/CP/classes/class.adnCertifiedProfessionalDataTableGUI.php");
        $table = new adnCertifiedProfessionalDataTableGUI($this, "listProfessionals");
        $table->resetOffset();
        $table->writeFilterToSession();

        $this->listProfessionals();
    }

    /**
     * Reset filter settings (from table GUI)
     */
    protected function resetFilter()
    {
        include_once("./Services/ADN/CP/classes/class.adnCertifiedProfessionalDataTableGUI.php");
        $table = new adnCertifiedProfessionalDataTableGUI($this, "listProfessionals");
        $table->resetOffset();
        $table->resetFilter();

        $this->listProfessionals();
    }

    /**
     * Edit professional form
     *
     * @param ilPropertyFormGUI $a_form
     */
    protected function editProfessional(ilPropertyFormGUI $a_form = null)
    {

        $this->tabs->setBackTarget($this->lng->txt("back"), $this->ctrl->getLinkTarget($this, "listProfessionals"));

        if (!$a_form) {
            $a_form = $this->initProfessionalForm();
        }
        $this->tpl->setContent($a_form->getHTML());
    }

    /**
     * Build professional form
     *
     * @return ilPropertyFormGUI
     */
    protected function initProfessionalForm()
    {

        include_once "Services/Form/classes/class.ilPropertyFormGUI.php";
        $form = new ilPropertyFormGUI();
        $form->setFormAction($this->ctrl->getFormAction($this, "listProfessionals"));

        $salutation = new ilSelectInputGUI($this->lng->txt("adn_salutation"), "salutation");
        $salutation->setOptions(array("m" => $this->lng->txt("salutation_m"),
                "f" => $this->lng->txt("salutation_f")));
        $salutation->setRequired(true);
        $form->addItem($salutation);

        $name = new ilTextInputGUI($this->lng->txt("adn_last_name"), "last_name");
        $name->setMaxLength(50);
        $name->setRequired(true);
        $form->addItem($name);

        $first_name = new ilTextInputGUI($this->lng->txt("adn_first_name"), "first_name");
        $first_name->setMaxLength(50);
        $first_name->setRequired(true);
        $form->addItem($first_name);

        $birthdate = new ilDateTimeInputGUI($this->lng->txt("adn_birthdate"), "birthdate");
        $birthdate->setRequired(true);
        $birthdate->setStartYear(date("Y") - 100);
        $form->addItem($birthdate);

        // foreign key
        include_once "Services/ADN/MD/classes/class.adnCountry.php";
        $countries = array();
        $countries[] = $this->professional->getCitizenship();
        if ($this->professional->getPostalCountry()) {
            $countries[] = $this->professional->getPostalCountry();
        }
        if ($this->professional->getShippingCountry()) {
            $countries[] = $this->professional->getShippingCountry();
        }
        $countries = adnCountry::getCountriesSelect($countries);

        $citizenship = new ilSelectInputGUI($this->lng->txt("adn_citizenship"), "citizenship");
        $citizenship->setOptions($countries);
        $citizenship->setRequired(true);
        $form->addItem($citizenship);

        $foreign = new ilCheckboxInputGUI($this->lng->txt("adn_foreign_certificate"), "foreign");
        $form->addItem($foreign);

        $foreign_cert_handed_id = new ilCheckboxInputGUI($this->lng->txt("adn_foreign_cert_handed_in"), "foreign_cert_handed_in");
        $form->addItem($foreign_cert_handed_id);

        $phone = new ilTextInputGUI($this->lng->txt("adn_phone"), "phone");
        $phone->setMaxLength(30);
        $phone->setSize(30);
        $form->addItem($phone);

        $email = new ilEmailInputGUI($this->lng->txt("adn_email"), "email");
        $form->addItem($email);

        // foreign key
        include_once "Services/ADN/MD/classes/class.adnWMO.php";
        $wmos = array();
        $wmos[] = $this->professional->getRegisteredBy();
        if ($this->professional->getBlockedBy()) {
            $wmos[] = $this->professional->getBlockedBy();
        }
        $wmos = adnWMO::getWMOsSelect($wmos);

        $registered_by = new ilSelectInputGUI($this->lng->txt("adn_registered_by"), "registered_by");
        $registered_by->setOptions($wmos);
        $registered_by->setRequired(true);
        $form->addItem($registered_by);

        $comment = new ilTextAreaInputGUI($this->lng->txt("adn_comment"), "comment");
        $comment->setCols(80);
        $comment->setRows(5);
        $form->addItem($comment);

        $holdback = new ilCheckboxInputGUI($this->lng->txt("adn_holdback"), "holdback");
        $form->addItem($holdback);

        $holdback_by = new ilSelectInputGUI($this->lng->txt("adn_holdback_by"), "holdback_by");
        $holdback_by->setOptions($wmos);
        $holdback->addSubItem($holdback_by);

        include_once("./Services/ADN/AD/classes/class.adnUser.php");
        $current_wmo = adnUser::lookupWmoId();
        $holdback_by->setValue($current_wmo);
        
        $holdback_until = new ilDateTimeInputGUI($this->lng->txt("adn_holdback_until"), "holdback_until");
        $holdback->addSubItem($holdback_until);



        $header = new ilFormSectionHeaderGUI();
        $header->setTitle($this->lng->txt("adn_permanent_address"));
        $form->addItem($header);

        $country = new ilSelectInputGUI($this->lng->txt("adn_country"), "country");
        $country->setOptions($countries);
        $country->setRequired(true);
        $form->addItem($country);

        $zip = new ilTextInputGUI($this->lng->txt("adn_zip"), "zip");
        $zip->setMaxLength(10);
        $zip->setSize(10);
        $zip->setRequired(true);
        $form->addItem($zip);

        $city = new ilTextInputGUI($this->lng->txt("adn_city"), "city");
        $city->setMaxLength(50);
        $city->setRequired(true);
        $form->addItem($city);

        $street = new ilTextInputGUI($this->lng->txt("adn_street"), "street");
        $street->setMaxLength(50);
        $street->setRequired(true);
        $form->addItem($street);

        $hno = new ilTextInputGUI($this->lng->txt("adn_house_number"), "hno");
        $hno->setMaxLength(10);
        $hno->setSize(10);
        $hno->setRequired(true);
        $form->addItem($hno);



        $header = new ilFormSectionHeaderGUI();
        $header->setTitle($this->lng->txt("adn_shipping_address"));
        $form->addItem($header);

        $ssalutation = new ilSelectInputGUI($this->lng->txt("adn_salutation"), "ssalutation");
        $ssalutation->setOptions(array("m" => $this->lng->txt("salutation_m"),
            "f" => $this->lng->txt("salutation_f")));
        $form->addItem($ssalutation);

        $sname = new ilTextInputGUI($this->lng->txt("adn_last_name"), "slast_name");
        $sname->setMaxLength(50);
        $form->addItem($sname);

        $sfirst_name = new ilTextInputGUI($this->lng->txt("adn_first_name"), "sfirst_name");
        $sfirst_name->setMaxLength(50);
        $form->addItem($sfirst_name);

        $scountry = new ilSelectInputGUI($this->lng->txt("adn_country"), "scountry");
        $scountry->setOptions($countries);
        $form->addItem($scountry);

        $szip = new ilTextInputGUI($this->lng->txt("adn_zip"), "szip");
        $szip->setMaxLength(10);
        $szip->setSize(10);
        $form->addItem($szip);

        $scity = new ilTextInputGUI($this->lng->txt("adn_city"), "scity");
        $scity->setMaxLength(50);
        $form->addItem($scity);

        $sstreet = new ilTextInputGUI($this->lng->txt("adn_street"), "sstreet");
        $sstreet->setMaxLength(50);
        $form->addItem($sstreet);

        $shno = new ilTextInputGUI($this->lng->txt("adn_house_number"), "shno");
        $shno->setMaxLength(10);
        $shno->setSize(10);
        $form->addItem($shno);

        $cb = new ilCheckboxInputGUI(
            $this->lng->txt("adn_shipping_address_activated"),
            "shipping_address_activated"
        );
        $form->addItem($cb);

        $salutation->setValue($this->professional->getSalutation());
        $name->setValue($this->professional->getLastName());
        $first_name->setValue($this->professional->getFirstName());
        $birthdate->setDate($this->professional->getBirthdate());
        $citizenship->setValue($this->professional->getCitizenship());
        $foreign->setChecked($this->professional->hasForeignCertificate());
        $foreign_cert_handed_id->setChecked($this->professional->hasForeignCertificateHandedIn());
        $phone->setValue($this->professional->getPhone());
        $email->setValue($this->professional->getEmail());
        $registered_by->setValue($this->professional->getRegisteredBy());
        $comment->setValue($this->professional->getComment());
        $country->setValue($this->professional->getPostalCountry());
        $zip->setValue($this->professional->getPostalCode());
        $city->setValue($this->professional->getPostalCity());
        $street->setValue($this->professional->getPostalStreet());
        $hno->setValue($this->professional->getPostalStreetNumber());
        $ssalutation->setValue($this->professional->getShippingSalutation());
        $sname->setValue($this->professional->getShippingLastName());
        $sfirst_name->setValue($this->professional->getShippingFirstName());
        $scountry->setValue($this->professional->getShippingCountry());
        $szip->setValue($this->professional->getShippingCode());
        $scity->setValue($this->professional->getShippingCity());
        $sstreet->setValue($this->professional->getShippingStreet());
        $shno->setValue($this->professional->getShippingStreetNumber());

        if ($this->professional->isShippingActive()) {
            $cb->setChecked(true);
        }

        $blocked = $this->professional->getBlockedBy();
        if ($blocked) {
            $holdback->setChecked(true);
            $holdback_by->setValue($this->professional->getBlockedBy());
            $holdback_until->setDate($this->professional->getBlockedUntil());
        }

        $form->addCommandButton("updateProfessional", $this->lng->txt("save"));
        $form->addCommandButton("listProfessionals", $this->lng->txt("cancel"));
        $form->setTitle($this->lng->txt("adn_edit_professional"));

        return $form;
    }

    /**
     * Update professional
     */
    protected function updateProfessional()
    {

        $form = $this->initProfessionalForm();

        // check input
        if ($form->checkInput()) {
            $this->professional->setSalutation($form->getInput("salutation"));
            $this->professional->setLastName($form->getInput("last_name"));
            $this->professional->setFirstName($form->getInput("first_name"));
            $date = $form->getInput("birthdate");
            $this->professional->setBirthdate(new ilDate($date, IL_CAL_DATE));
            $this->professional->setCitizenship($form->getInput("citizenship"));
            $this->professional->setForeignCertificate($form->getInput("foreign"));
            $this->professional->setForeignCertificateHandedIn($form->getInput("foreign_cert_handed_in"));
            $this->professional->setRegisteredBy($form->getInput("registered_by"));
            $this->professional->setPhone($form->getInput("phone"));
            $this->professional->setEmail($form->getInput("email"));
            $this->professional->setComment($form->getInput("comment"));
            $this->professional->setPostalCountry($form->getInput("country"));
            $this->professional->setPostalCode($form->getInput("zip"));
            $this->professional->setPostalCity($form->getInput("city"));
            $this->professional->setPostalStreet($form->getInput("street"));
            $this->professional->setPostalStreetNumber($form->getInput("hno"));
            $this->professional->setShippingSalutation($form->getInput("ssalutation"));
            $this->professional->setShippingLastName($form->getInput("slast_name"));
            $this->professional->setShippingFirstName($form->getInput("sfirst_name"));
            $this->professional->setShippingCountry($form->getInput("scountry"));
            $this->professional->setShippingCode($form->getInput("szip"));
            $this->professional->setShippingCity($form->getInput("scity"));
            $this->professional->setShippingStreet($form->getInput("sstreet"));
            $this->professional->setShippingStreetNumber($form->getInput("shno"));
            $this->professional->setShippingActive($form->getInput("shipping_address_activated"));

            if ($form->getInput("holdback")) {
                $date = $form->getInput("holdback_until");
                $this->professional->setBlockedBy($form->getInput("holdback_by"));
                $this->professional->setBlockedUntil(new ilDate($date, IL_CAL_DATE));
            } else {
                $this->professional->setBlockedBy(null);
                $this->professional->setBlockedUntil(null);
            }

            if ($this->professional->update()) {
                // show success message and return to list
                ilUtil::sendSuccess($this->lng->txt("adn_certified_professional_updated"), true);
                $this->ctrl->redirect($this, "listProfessionals");
            }
        }

        // input not valid: show form again
        $form->setValuesByPost();
        $this->editProfessional($form);
    }
}
