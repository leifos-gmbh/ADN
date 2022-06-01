<?php
/* Copyright (c) 2010 Leifos, GPL, see docs/LICENSE */

/**
 * ADN training provider GUI class
 *
 * Provider list, forms and persistence
 *
 * @author Alex Killing <killing@leifos.de>
 * @version $Id: class.adnTrainingProviderGUI.php 28346 2011-04-04 11:58:25Z jluetzen $
 *
 * @ilCtrl_Calls adnTrainingProviderGUI: adnTrainingEventGUI, adnInstructorGUI
 * @ilCtrl_Calls adnTrainingProviderGUI: adnTrainingFacilityGUI
 *
 * @ingroup ServicesADN
 */
class adnTrainingProviderGUI
{
    protected ?adnTrainingProvider $training_provider = null;

    protected ilCtrl $ctrl;
    protected ilTabsGUI $tabs;
    protected ilGlobalTemplateInterface $tpl;
    protected ilLanguage $lng;
    protected ilToolbarGUI $toolbar;
    /**
     * Constructor
     */
    public function __construct()
    {
        global $DIC;
        $this->ctrl = $DIC->ctrl();
        $this->tabs = $DIC->tabs();
        $this->tpl = $DIC->ui()->mainTemplate();
        $this->lng = $DIC->language();
        $this->toolbar = $DIC->toolbar();
        
        // save training provider ID through requests
        $this->ctrl->saveParameter($this, array("tp_id"));
        
        $this->readTrainingProvider();
    }
    
    /**
     * Execute command
     */
    public function executeCommand()
    {

        $this->tpl->setTitle($this->lng->txt("adn_ta") . " - " . $this->lng->txt("adn_ta_tps"));

        $next_class = $this->ctrl->getNextClass();
        
        // forward command to next gui class in control flow
        switch ($next_class) {
            case "adntrainingfacilitygui":
                $this->setTabs();
                $this->tabs->activateTab("training_facilities");
                
                include_once("./Services/ADN/TA/classes/class.adnTrainingFacilityGUI.php");
                $tf_gui = new adnTrainingFacilityGUI();
                $this->ctrl->forwardCommand($tf_gui);
                break;

            case "adntrainingeventgui":
                include_once("./Services/ADN/TA/classes/class.adnTrainingEventGUI.php");
                $te_gui = new adnTrainingEventGUI();
                $this->ctrl->forwardCommand($te_gui);
                break;

            case "adninstructorgui":
                $this->setTabs();
                $this->tabs->activateTab("instructors");

                include_once("./Services/ADN/TA/classes/class.adnInstructorGUI.php");
                $is_gui = new adnInstructorGUI();
                $this->ctrl->forwardCommand($is_gui);
                break;
            
            // no next class:
            // this class is responsible to process the command
            default:
                $cmd = $this->ctrl->getCmd("listTrainingProviders");
                
                switch ($cmd) {
                    // commands that need read permission
                    case "listTrainingProviders":
                    case "listInstructors":
                    case "showTrainingProvider":
                        if (adnPerm::check(adnPerm::TA, adnPerm::READ)) {
                            $this->$cmd();
                        }
                        break;
                    
                    // commands that need write permission
                    case "addTrainingProvider":
                    case "saveTrainingProvider":
                    case "editTrainingProvider":
                    case "updateTrainingProvider":
                    case "confirmTrainingProviderDeletion":
                    case "deleteTrainingProvider":
                    case "listTrainingTypes":
                    case "updateTrainingTypes":
                        if (adnPerm::check(adnPerm::TA, adnPerm::WRITE)) {
                            $this->$cmd();
                        }
                        break;
                    
                }
                break;
        }
    }
    
    /**
     * Read training provider
     */
    protected function readTrainingProvider()
    {
        if ((int) $_GET["tp_id"] > 0) {
            include_once("./Services/ADN/TA/classes/class.adnTrainingProvider.php");
            $this->training_provider = new adnTrainingProvider((int) $_GET["tp_id"]);
        }
    }
    
    /**
     * List all training providers
     */
    protected function listTrainingProviders()
    {
        
        if (adnPerm::check(adnPerm::TA, adnPerm::WRITE)) {
            $this->toolbar->addButton(
                $this->lng->txt("adn_add_training_provider"),
                $this->ctrl->getLinkTarget($this, "addTrainingProvider")
            );
        }
        
        // table of training providers
        include_once("./Services/ADN/TA/classes/class.adnTrainingProviderTableGUI.php");
        $table = new adnTrainingProviderTableGUI($this, "listTrainingProviders");
        
        // output table
        $this->tpl->setContent($table->getHTML());
    }
    
    /**
     * Add new training provider form
     *
     * @param ilPropertyFormGUI $a_form
     */
    protected function addTrainingProvider(ilPropertyFormGUI $a_form = null)
    {

        $this->tabs->setBackTarget($this->lng->txt("back"), $this->ctrl->getLinkTarget($this, "listTrainingProviders"));

        if (!$a_form) {
            $a_form = $this->initTrainingProviderForm("create");
        }
        $this->tpl->setContent($a_form->getHTML());
    }
    
    /**
     * Edit training provider form
     *
     * @param ilPropertyFormGUI $a_form
     */
    protected function editTrainingProvider(ilPropertyFormGUI $a_form = null)
    {

        $this->tabs->setBackTarget($this->lng->txt("back"), $this->ctrl->getLinkTarget($this, "listTrainingProviders"));

        $this->setTabs();
        $this->tabs->activateTab("properties");

        if (!$a_form) {
            $a_form = $this->initTrainingProviderForm("edit");
        }
        $this->tpl->setContent($a_form->getHTML());
    }

    /**
     * Show training provider (read-only)
     */
    protected function showTrainingProvider()
    {

        $this->tabs->setBackTarget($this->lng->txt("back"), $this->ctrl->getLinkTarget($this, "listTrainingProviders"));

        $form = $this->initTrainingProviderForm("show");
        $form = $form->convertToReadonly();

        // instructors
        include_once "Services/ADN/TA/classes/class.adnInstructor.php";
        $all = adnInstructor::getInstructorsSelect($this->training_provider->getId());
        if ($all) {
            foreach ($all as $name) {
                $instructors[] = $name;
            }

            $instructor = new ilNonEditableValueGUI($this->lng->txt("adn_instructors"));
            $instructor->setValue(implode("<br />", $instructors));
            $form->addItem($instructor);
        }


        // training facilities
        include_once "Services/ADN/TA/classes/class.adnTrainingFacility.php";
        $all = adnTrainingFacility::getTrainingFacilitiesSelect($this->training_provider->getId());
        if ($all) {
            foreach ($all as $name) {
                $facilities[] = $name;
            }

            $facility = new ilNonEditableValueGUI($this->lng->txt("adn_training_facilities"));
            $facility->setValue(implode("<br />", $facilities));
            $form->addItem($facility);
        }


        // types of training

        $sub = new ilFormSectionHeaderGUI();
        $sub->setTitle($this->lng->txt("adn_approved_types_of_training"));
        $form->addItem($sub);

        $ttform = $this->initTrainingTypeForm();
        $ttform = $ttform->convertToReadOnly();
        foreach ($ttform->getItems() as $item) {
            $form->addItem($item);
        }

        $this->tpl->setContent($form->getHTML());
    }
    
    /**
     * Init training provider form.
     *
     * @param string $a_mode form mode ("create" | "edit" | "show")
     * @return ilPropertyFormGUI
     */
    protected function initTrainingProviderForm($a_mode = "edit")
    {
        
        // get form object and add input fields
        include_once("Services/Form/classes/class.ilPropertyFormGUI.php");
        $form = new ilPropertyFormGUI();
        
        // name
        $name = new ilTextInputGUI($this->lng->txt("adn_company_name"), "name");
        $name->setMaxLength(100);
        $name->setRequired(true);
        $form->addItem($name);

        // contact person
        $contact = new ilTextInputGUI($this->lng->txt("adn_contact_person"), "contact_person");
        $contact->setMaxLength(100);
        $form->addItem($contact);

        // postal code
        $zip = new ilTextInputGUI($this->lng->txt("adn_postal_code"), "postal_code");
        $zip->setMaxLength(10);
        $zip->setSize(10);
        $zip->setRequired(true);
        $form->addItem($zip);

        // city
        $city = new ilTextInputGUI($this->lng->txt("adn_city"), "city");
        $city->setMaxLength(50);
        $city->setRequired(true);
        $form->addItem($city);

        // street
        $street = new ilTextInputGUI($this->lng->txt("adn_street"), "street");
        $street->setMaxLength(100);
        $form->addItem($street);

        // street number
        $street_no = new ilTextInputGUI($this->lng->txt("adn_street_number"), "street_no");
        $street_no->setMaxLength(10);
        $street_no->setSize(10);
        $form->addItem($street_no);

        // po box
        $po = new ilTextInputGUI($this->lng->txt("adn_po_box"), "po_box");
        $po->setMaxLength(20);
        $po->setSize(20);
        $form->addItem($po);

        
        // alternative address

        $altaddr = new ilCheckboxInputGUI($this->lng->txt("adn_alternative_address"), "altaddr");
        $form->addItem($altaddr);

        // postal code
        $azip = new ilTextInputGUI($this->lng->txt("adn_postal_code"), "apostal_code");
        $azip->setMaxLength(10);
        $azip->setSize(10);
        $azip->setRequired(true);
        $altaddr->addSubItem($azip);

        // city
        $acity = new ilTextInputGUI($this->lng->txt("adn_city"), "acity");
        $acity->setMaxLength(50);
        $acity->setRequired(true);
        $altaddr->addSubItem($acity);

        // street
        $astreet = new ilTextInputGUI($this->lng->txt("adn_street"), "astreet");
        $astreet->setMaxLength(100);
        // $astreet->setRequired(true);
        $altaddr->addSubItem($astreet);

        // street number
        $astreet_no = new ilTextInputGUI($this->lng->txt("adn_street_number"), "astreet_no");
        $astreet_no->setMaxLength(10);
        $astreet_no->setSize(10);
        // $astreet_no->setRequired(true);
        $altaddr->addSubItem($astreet_no);

        // po box
        $apo = new ilTextInputGUI($this->lng->txt("adn_po_box"), "apo_box");
        $apo->setMaxLength(20);
        $apo->setSize(20);
        // $apo->setRequired(true);
        $altaddr->addSubItem($apo);


        // phone
        $fon = new ilTextInputGUI($this->lng->txt("adn_phone"), "phone");
        $fon->setMaxLength(50);
        // $fon->setRequired(true);
        $form->addItem($fon);

        // fax
        $fax = new ilTextInputGUI($this->lng->txt("adn_fax"), "fax");
        // $fax->setMaxLength(50);
        $form->addItem($fax);

        // e-mail
        $email = new ilTextInputGUI($this->lng->txt("adn_email"), "email");
        $email->setMaxLength(100);
        // $email->setRequired(true);
        $form->addItem($email);

        if ($a_mode == "create") {
            // creation: save/cancel buttons and title
            $form->addCommandButton("saveTrainingProvider", $this->lng->txt("save"));
            $form->addCommandButton("listTrainingProviders", $this->lng->txt("cancel"));
            $form->setTitle($this->lng->txt("adn_add_training_provider"));
        } else {
            $name->setValue($this->training_provider->getName());
            $contact->setValue($this->training_provider->getContact());
            $street->setValue($this->training_provider->getStreet());
            $street_no->setValue($this->training_provider->getStreetNumber());
            $po->setValue($this->training_provider->getPoBox());
            $zip->setValue($this->training_provider->getZip());
            $city->setValue($this->training_provider->getCity());
            $fon->setValue($this->training_provider->getPhone());
            $fax->setValue($this->training_provider->getFax());
            $email->setValue($this->training_provider->getEmail());

            if ($this->training_provider->getAlternativeZip()) {
                $altaddr->setChecked(true);
                $astreet->setValue($this->training_provider->getAlternativeStreet());
                $astreet_no->setValue($this->training_provider->getAlternativeStreetNumber());
                $apo->setValue($this->training_provider->getAlternativePoBox());
                $azip->setValue($this->training_provider->getAlternativeZip());
                $acity->setValue($this->training_provider->getAlternativeCity());
            }

            if ($a_mode != "show") {
                // editing: update/cancel buttons and title
                $form->addCommandButton("updateTrainingProvider", $this->lng->txt("save"));
                $form->addCommandButton("listTrainingProviders", $this->lng->txt("cancel"));
                $form->setTitle($this->lng->txt("adn_edit_training_provider"));
            } else {
                $form->setTitle($this->lng->txt("adn_training_provider"));
            }
        }
        
        $form->setFormAction($this->ctrl->getFormAction($this));

        return $form;
    }
    
    /**
     * Create new training provider
     */
    protected function saveTrainingProvider()
    {
        
        $form = $this->initTrainingProviderForm("create");
        
        // check input
        if ($form->checkInput()) {
            // input ok: create new training provider
            include_once("./Services/ADN/TA/classes/class.adnTrainingProvider.php");
            $training_provider = new adnTrainingProvider();
            $training_provider->setName($form->getInput("name"));
            $training_provider->setContact($form->getInput("contact_person"));
            $training_provider->setStreet($form->getInput("street"));
            $training_provider->setStreetNumber($form->getInput("street_no"));
            $training_provider->setPoBox($form->getInput("po_box"));
            $training_provider->setZip($form->getInput("postal_code"));
            $training_provider->setCity($form->getInput("city"));
            $training_provider->setPhone($form->getInput("phone"));
            $training_provider->setFax($form->getInput("fax"));
            $training_provider->setEmail($form->getInput("email"));
            
            if ($form->getInput("altaddr")) {
                $training_provider->setAlternativeStreet($form->getInput("astreet"));
                $training_provider->setAlternativeStreetNumber($form->getInput("astreet_no"));
                $training_provider->setAlternativePoBox($form->getInput("apo_box"));
                $training_provider->setAlternativeZip($form->getInput("apostal_code"));
                $training_provider->setAlternativeCity($form->getInput("acity"));
            }

            if ($training_provider->save()) {
                // show success message and return to list
                ilUtil::sendSuccess($this->lng->txt("adn_training_provider_created"), true);

                $this->ctrl->setParameter($this, "tp_id", $training_provider->getId());
                $this->ctrl->redirect($this, "listTrainingTypes");
            }
        }

        // input not valid: show form again
        $form->setValuesByPost();
        $this->addTrainingProvider($form);
    }
    
    /**
     * Update training provider
     */
    protected function updateTrainingProvider()
    {
        
        $form = $this->initTrainingProviderForm("edit");
        
        // check input
        if ($form->checkInput()) {
            // perform update
            $this->training_provider->setName($form->getInput("name"));
            $this->training_provider->setContact($form->getInput("contact_person"));
            $this->training_provider->setStreet($form->getInput("street"));
            $this->training_provider->setStreetNumber($form->getInput("street_no"));
            $this->training_provider->setPoBox($form->getInput("po_box"));
            $this->training_provider->setZip($form->getInput("postal_code"));
            $this->training_provider->setCity($form->getInput("city"));
            $this->training_provider->setPhone($form->getInput("phone"));
            $this->training_provider->setFax($form->getInput("fax"));
            $this->training_provider->setEmail($form->getInput("email"));

            if ($form->getInput("altaddr")) {
                $this->training_provider->setAlternativeStreet($form->getInput("astreet"));
                $this->training_provider->setAlternativeStreetNumber($form->getInput("astreet_no"));
                $this->training_provider->setAlternativePoBox($form->getInput("apo_box"));
                $this->training_provider->setAlternativeZip($form->getInput("apostal_code"));
                $this->training_provider->setAlternativeCity($form->getInput("acity"));
            } else {
                $this->training_provider->setAlternativeStreet(null);
                $this->training_provider->setAlternativeStreetNumber(null);
                $this->training_provider->setAlternativePoBox(null);
                $this->training_provider->setAlternativeZip(null);
                $this->training_provider->setAlternativeCity(null);
            }

            if ($this->training_provider->update()) {
                // show success message and return to list
                ilUtil::sendSuccess($this->lng->txt("adn_training_provider_updated"), true);
                $this->ctrl->redirect($this, "listTrainingProviders");
            }
        }
        
        // input not valid: show form again
        $form->setValuesByPost();
        $this->editTrainingProvider($form);
    }
    
    /**
     * Confirm training provider deletion
     */
    protected function confirmTrainingProviderDeletion()
    {
        
        // check whether at least one item has been seleced
        if (!is_array($_POST["training_provider_id"]) || count($_POST["training_provider_id"]) == 0) {
            ilUtil::sendFailure($this->lng->txt("no_checkbox"), true);
            $this->ctrl->redirect($this, "listTrainingProviders");
        } else {
            $this->tabs->setBackTarget(
                $this->lng->txt("back"),
                $this->ctrl->getLinkTarget($this, "listTrainingProviders")
            );

            // display confirmation message
            include_once("./Services/Utilities/classes/class.ilConfirmationGUI.php");
            $cgui = new ilConfirmationGUI();
            $cgui->setFormAction($this->ctrl->getFormAction($this));
            $cgui->setHeaderText($this->lng->txt("adn_sure_delete_training_provider"));
            $cgui->setCancel($this->lng->txt("cancel"), "listTrainingProviders");
            $cgui->setConfirm($this->lng->txt("delete"), "deleteTrainingProvider");

            // list objects that should be deleted
            include_once("./Services/ADN/TA/classes/class.adnTrainingProvider.php");
            foreach ($_POST["training_provider_id"] as $i) {
                $cgui->addItem("training_provider_id[]", $i, adnTrainingProvider::lookupName($i));
            }
            
            $this->tpl->setContent($cgui->getHTML());
        }
    }
    
    /**
     * Delete training provider
     */
    protected function deleteTrainingProvider()
    {
        
        include_once("./Services/ADN/TA/classes/class.adnTrainingProvider.php");
        
        if (is_array($_POST["training_provider_id"])) {
            foreach ($_POST["training_provider_id"] as $i) {
                $training_provider = new adnTrainingProvider($i);
                $training_provider->delete();
            }
        }
        ilUtil::sendSuccess($this->lng->txt("adn_training_provider_deleted"), true);
        $this->ctrl->redirect($this, "listTrainingProviders");
    }

    /**
     * Build tab bar
     */
    protected function setTabs()
    {
        
        $this->tabs->addTab(
            "properties",
            $this->lng->txt("adn_contact_data"),
            $this->ctrl->getLinkTarget($this, "editTrainingProvider")
        );
        $this->tabs->addTab(
            "types",
            $this->lng->txt("adn_approved_types_of_training"),
            $this->ctrl->getLinkTarget($this, "listTrainingTypes")
        );
        $this->tabs->addTab(
            "instructors",
            $this->lng->txt("adn_instructors"),
            $this->ctrl->getLinkTargetByClass("adninstructorgui", "listInstructors")
        );
        $this->tabs->addTab(
            "training_facilities",
            $this->lng->txt("adn_training_facilities"),
            $this->ctrl->getLinkTargetByClass("adntrainingfacilitygui", "listTrainingFacilities")
        );
    }


    //
    // Training Types (provider sub-data)
    //

    /**
     * Edit approved training types for provider (list all available to be selected)
     */
    public function listTrainingTypes(ilPropertyFormGUI $form = null)
    {

        $this->tabs->setBackTarget($this->lng->txt("back"), $this->ctrl->getLinkTarget($this, "listTrainingProviders"));

        $this->setTabs();
        $this->tabs->activateTab("types");

        if (!$form instanceof ilPropertyFormGUI) {
            $form = $this->initTrainingTypeForm();
        }
        $this->tpl->setContent($form->getHTML());
    }

    /**
     * Init training type form.
     *
     * @return ilPropertyFormGUI
     */
    protected function initTrainingTypeForm()
    {
        
        // get form object and add input fields
        include_once("Services/Form/classes/class.ilPropertyFormGUI.php");
        $form = new ilPropertyFormGUI();

        include_once("./Services/ADN/TA/classes/class.adnTypesOfTraining.php");
        foreach (adnTypesOfTraining::getAllTypes() as $type => $tlng) {
            $cb = new ilCheckboxInputGUI($tlng, "train_type_" . $type);
            $form->addItem($cb);

            $dt = new ilDateTimeInputGUI(
                $this->lng->txt("adn_approved_on"),
                "approved_" . $type
            );
            $dt->setStartYear(1990);
            $dt->setRequired(true);
            $cb->addSubItem($dt);

            if ($date = $this->training_provider->IsTrainingTypeApproved($type)) {
                $cb->setChecked(true);
                $dt->setDate($date);
            }
        }

        // editing: update/cancel buttons and title
        $form->addCommandButton("updateTrainingTypes", $this->lng->txt("save"));
        $form->addCommandButton("listTrainingProviders", $this->lng->txt("cancel"));
        $form->setTitle($this->lng->txt("adn_edit_training_provider") .
            ": " . $this->training_provider->getName());

        $form->setFormAction($this->ctrl->getFormAction($this));

        return $form;
    }

    /**
     * Update approved training types for provider
     */
    public function updateTrainingTypes()
    {

        $form = $this->initTrainingTypeForm();
        if (!$form->checkInput()) {
            ilUtil::sendFailure($this->lng->txt('fill_out_all_required_fields'));
            $form->setValuesByPost();
            return $this->listTrainingTypes($form);
        }

        include_once("./Services/ADN/TA/classes/class.adnTypesOfTraining.php");
        $types = array();
        foreach (adnTypesOfTraining::getAllTypes() as $type => $tlng) {
            if ($form->getInput("train_type_" . $type)) {
                $date = $form->getInput("approved_" . $type);
                $types[$type] = new ilDate($date, IL_CAL_DATE, ilTimeZone::UTC);
            }
        }
        $this->training_provider->setTypesOfTraining($types);
        $this->training_provider->saveTrainingTypes();

        ilUtil::sendSuccess($this->lng->txt("adn_training_types_updated"), true);
        $this->ctrl->redirect($this, "listTrainingProviders");
    }
}
