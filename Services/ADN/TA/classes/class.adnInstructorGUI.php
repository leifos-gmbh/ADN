<?php
/* Copyright (c) 2010 Leifos, GPL, see docs/LICENSE */

/**
 * ADN instructor GUI class
 *
 * Instructors list, forms and persistence, provider has to be pre-selected
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @version $Id: class.adnInstructorGUI.php 27891 2011-02-28 11:46:53Z jluetzen $
 *
 * @ilCtrl_Calls adnInstructorGUI:
 *
 * @ingroup ServicesADN
 */
class adnInstructorGUI
{
    // current provider id
    protected $provider_id = null;
    
    // current instructor object
    protected $instructor = null;
    
    /**
     * Constructor
     */
    public function __construct()
    {
        global $ilCtrl;

        $this->provider_id = (int) $_REQUEST["tp_id"];
        
        // save provider and instructor ID through requests
        $ilCtrl->saveParameter($this, array("tp_id"));
        $ilCtrl->saveParameter($this, array("is_id"));
        
        $this->readInstructor();
    }
    
    /**
     * Execute command
     */
    public function executeCommand()
    {
        global $ilCtrl;
        
        $next_class = $ilCtrl->getNextClass();
        
        // forward command to next gui class in control flow
        switch ($next_class) {
            // no next class:
            // this class is responsible to process the command
            default:
                $cmd = $ilCtrl->getCmd("listInstructors");
                
                switch ($cmd) {
                    // commands that need read permission
                    case "listInstructors":
                        if (adnPerm::check(adnPerm::TA, adnPerm::READ)) {
                            $this->$cmd();
                        }
                        break;
                    
                    // commands that need write permission
                    case "addInstructor":
                    case "saveInstructor":
                    case "editInstructor":
                    case "updateInstructor":
                    case "confirmInstructorsDeletion":
                    case "deleteInstructors":
                        if (adnPerm::check(adnPerm::TA, adnPerm::WRITE)) {
                            $this->$cmd();
                        }
                        break;
                    
                }
                break;
        }
    }
    
    /**
     * Read instructor
     */
    protected function readInstructor()
    {
        if ((int) $_GET["is_id"] > 0) {
            include_once("./Services/ADN/TA/classes/class.adnInstructor.php");
            $this->instructor = new adnInstructor((int) $_GET["is_id"]);
        }
    }
    
    /**
     * List instructors for training provider
     */
    public function listInstructors()
    {
        global $tpl, $lng, $ilTabs, $ilCtrl, $ilToolbar;

        $ilTabs->setBackTarget(
            $lng->txt("back"),
            $ilCtrl->getLinkTargetByClass("adnTrainingProviderGUI", "listTrainingProviders")
        );

        // add instructor
        $ilToolbar->addButton(
            $lng->txt("adn_add_instructor"),
            $ilCtrl->getLinkTarget($this, "addInstructor")
        );

        include_once("./Services/ADN/TA/classes/class.adnInstructorTableGUI.php");
        $table = new adnInstructorTableGUI($this, "listInstructors", $this->provider_id);

        $tpl->setContent($table->getHTML());
    }
    
    /**
     * Add new instructor form
     *
     * @param ilPropertyFormGUI $a_form
     */
    protected function addInstructor(ilPropertyFormGUI $a_form = null)
    {
        global $tpl, $ilTabs, $ilCtrl, $lng;

        $ilTabs->setBackTarget($lng->txt("back"), $ilCtrl->getLinkTarget($this, "listInstructors"));

        if (!$a_form) {
            $a_form = $this->initInstructorForm("create");
        }
        $tpl->setContent($a_form->getHTML());
    }
    
    /**
     * Edit instructor form
     *
     * @param ilPropertyFormGUI $a_form
     */
    protected function editInstructor(ilPropertyFormGUI $a_form = null)
    {
        global $tpl, $ilTabs, $ilCtrl, $lng;

        $ilTabs->setBackTarget($lng->txt("back"), $ilCtrl->getLinkTarget($this, "listInstructors"));

        if (!$a_form) {
            $a_form = $this->initInstructorForm("edit");
        }
        $tpl->setContent($a_form->getHTML());
    }
    
    /**
     * Init instructor form
     *
     * @param string $a_mode form mode ("create" | "edit")
     * @return ilPropertyFormGUI
     */
    protected function initInstructorForm($a_mode = "edit")
    {
        global $lng, $ilCtrl;

        include_once "Services/ADN/TA/classes/class.adnTrainingProvider.php";
        $provider = new adnTrainingProvider($this->provider_id);
        
        // get form object and add input fields
        include_once("Services/Form/classes/class.ilPropertyFormGUI.php");
        $form = new ilPropertyFormGUI();
        
        // last name
        $name = new ilTextInputGUI($lng->txt("adn_last_name"), "name");
        $name->setMaxLength(50);
        $name->setRequired(true);
        $form->addItem($name);

        // first name
        $fname = new ilTextInputGUI($lng->txt("adn_first_name"), "fname");
        $fname->setMaxLength(50);
        $fname->setRequired(true);
        $form->addItem($fname);

        // training types (static values)
        $types = new ilCheckboxGroupInputGUI($lng->txt("adn_types_of_training"), "train_type");
        $form->addItem($types);
        include_once("./Services/ADN/TA/classes/class.adnTypesOfTraining.php");
        foreach (adnTypesOfTraining::getAllTypes() as $type => $tlng) {
            $cb = new ilCheckboxOption($tlng, $type);
            $types->addOption($cb);
        }

        // areas of expertise (foreign key, but no archive flag)
        include_once("./Services/ADN/TA/classes/class.adnAreaOfExpertise.php");
        $areas = new ilCheckboxGroupInputGUI($lng->txt("adn_areas_of_expertise"), "area_expertise");
        $form->addItem($areas);
        foreach (adnAreaOfExpertise::getAreasOfExpertiseSelect() as $id => $caption) {
            $cb = new ilCheckboxOption($caption, $id);
            $areas->addOption($cb);
        }
        
        if ($a_mode == "create") {
            // creation: save/cancel buttons and title
            $form->addCommandButton("saveInstructor", $lng->txt("save"));
            $form->addCommandButton("listInstructors", $lng->txt("cancel"));
            $form->setTitle($lng->txt("adn_add_instructor") . ": " . $provider->getName());
        } else {
            $name->setValue($this->instructor->getLastName());
            $fname->setValue($this->instructor->getFirstName());
            $types->setValue($this->instructor->getTypesOfTraining());
            $areas->setValue($this->instructor->getAreasOfExpertise());
            
            // editing: update/cancel buttons and title
            $form->addCommandButton("updateInstructor", $lng->txt("save"));
            $form->addCommandButton("listInstructors", $lng->txt("cancel"));
            $form->setTitle($lng->txt("adn_edit_instructor") . ": " . $provider->getName());
        }
        
        $form->setFormAction($ilCtrl->getFormAction($this));

        return $form;
    }
    
    /**
     * Create new instructor
     */
    protected function saveInstructor()
    {
        global $tpl, $lng, $ilCtrl;
        
        $form = $this->initInstructorForm("create");
        
        // check input
        if ($form->checkInput()) {
            // input ok: create new instructor
            include_once("./Services/ADN/TA/classes/class.adnInstructor.php");
            $instructor = new adnInstructor();
            $instructor->setProvider($this->provider_id);
            $instructor->setLastName($form->getInput("name"));
            $instructor->setFirstName($form->getInput("fname"));
            $instructor->setTypesOfTraining((array) $form->getInput("train_type"));
            $instructor->setAreasOfExpertise((array) $form->getInput("area_expertise"));

            if ($instructor->save()) {
                // show success message and return to list
                ilUtil::sendSuccess($lng->txt("adn_instructor_created"), true);
                $ilCtrl->redirect($this, "listInstructors");
            }
        }

        // input not valid: show form again
        $form->setValuesByPost();
        $this->addInstructor($form);
    }
    
    /**
     * Update instructor
     */
    protected function updateInstructor()
    {
        global $lng, $ilCtrl, $tpl;
        
        $form = $this->initInstructorForm("edit");
        
        // check input
        if ($form->checkInput()) {
            // perform update
            $this->instructor->setLastName($form->getInput("name"));
            $this->instructor->setFirstName($form->getInput("fname"));
            $this->instructor->setTypesOfTraining((array) $form->getInput("train_type"));
            $this->instructor->setAreasOfExpertise((array) $form->getInput("area_expertise"));

            if ($this->instructor->update()) {
                // show success message and return to list
                ilUtil::sendSuccess($lng->txt("adn_instructor_updated"), true);
                $ilCtrl->redirect($this, "listInstructors");
            }
        }
        
        // input not valid: show form again
        $form->setValuesByPost();
        $this->editInstructor($form);
    }
    
    /**
     * Confirm instructors deletion
     */
    protected function confirmInstructorsDeletion()
    {
        global $ilCtrl, $tpl, $lng, $ilTabs;
        
        // check whether at least one item has been seleced
        if (!is_array($_POST["instructor_id"]) || count($_POST["instructor_id"]) == 0) {
            ilUtil::sendFailure($lng->txt("no_checkbox"), true);
            $ilCtrl->redirect($this, "listInstructors");
        } else {
            $ilTabs->setBackTarget(
                $lng->txt("back"),
                $ilCtrl->getLinkTarget($this, "listInstructors")
            );

            // display confirmation message
            include_once("./Services/Utilities/classes/class.ilConfirmationGUI.php");
            $cgui = new ilConfirmationGUI();
            $cgui->setFormAction($ilCtrl->getFormAction($this));
            $cgui->setHeaderText($lng->txt("adn_sure_delete_instructors"));
            $cgui->setCancel($lng->txt("cancel"), "listInstructors");
            $cgui->setConfirm($lng->txt("delete"), "deleteInstructors");

            // list objects that should be deleted
            include_once("./Services/ADN/TA/classes/class.adnInstructor.php");
            foreach ($_POST["instructor_id"] as $i) {
                $cgui->addItem("instructor_id[]", $i, adnInstructor::lookupName($i));
            }
            
            $tpl->setContent($cgui->getHTML());
        }
    }
    
    /**
     * Delete instructors
     */
    protected function deleteInstructors()
    {
        global $ilCtrl, $lng;
        
        include_once("./Services/ADN/TA/classes/class.adnInstructor.php");
        
        if (is_array($_POST["instructor_id"])) {
            foreach ($_POST["instructor_id"] as $i) {
                $instructor = new adnInstructor($i);
                $instructor->delete();
            }
        }
        ilUtil::sendSuccess($lng->txt("adn_instructor_deleted"), true);
        $ilCtrl->redirect($this, "listInstructors");
    }
}
