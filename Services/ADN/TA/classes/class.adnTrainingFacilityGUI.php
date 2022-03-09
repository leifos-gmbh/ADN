<?php
/* Copyright (c) 2010 Leifos, GPL, see docs/LICENSE */

/**
 * ADN training facility GUI class
 *
 * Facility list, forms and persistence, a provider has to be pre-selected
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @version $Id: class.adnTrainingFacilityGUI.php 34281 2012-04-18 14:42:03Z jluetzen $
 *
 * @ilCtrl_Calls adnTrainingFacilityGUI:
 *
 * @ingroup ServicesADN
 */
class adnTrainingFacilityGUI
{
    // current provider id
    protected $provider_id = null;
    
    // current facility object
    protected $facility = null;
    
    /**
     * Constructor
     */
    public function __construct()
    {
        global $ilCtrl;

        $this->provider_id = (int) $_REQUEST["tp_id"];
        
        // save provider and facility ID through requests
        $ilCtrl->saveParameter($this, array("tp_id"));
        $ilCtrl->saveParameter($this, array("tf_id"));
        
        $this->readTrainingFacility();
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
                $cmd = $ilCtrl->getCmd("listTrainingFacilities");
                
                switch ($cmd) {
                    // commands that need read permission
                    case "listTrainingFacilities":
                        if (adnPerm::check(adnPerm::TA, adnPerm::READ)) {
                            $this->$cmd();
                        }
                        break;
                    
                    // commands that need write permission
                    case "addTrainingFacility":
                    case "saveTrainingFacility":
                    case "editTrainingFacility":
                    case "updateTrainingFacility":
                    case "confirmTrainingFacilitiesDeletion":
                    case "deleteTrainingFacilities":
                        if (adnPerm::check(adnPerm::TA, adnPerm::WRITE)) {
                            $this->$cmd();
                        }
                        break;
                    
                }
                break;
        }
    }
    
    /**
     * Read facility
     */
    protected function readTrainingFacility()
    {
        if ((int) $_GET["tf_id"] > 0) {
            include_once("./Services/ADN/TA/classes/class.adnTrainingFacility.php");
            $this->facility = new adnTrainingFacility((int) $_GET["tf_id"]);
        }
    }
    
    /**
     * List facilties for training provider
     */
    public function listTrainingFacilities()
    {
        global $tpl, $lng, $ilTabs, $ilCtrl, $ilToolbar;

        $ilTabs->setBackTarget(
            $lng->txt("back"),
            $ilCtrl->getLinkTargetByClass("adnTrainingProviderGUI", "listTrainingProviders")
        );

        // add facility
        $ilToolbar->addButton(
            $lng->txt("adn_add_training_facility"),
            $ilCtrl->getLinkTarget($this, "addTrainingFacility")
        );

        include_once("./Services/ADN/TA/classes/class.adnTrainingFacilityTableGUI.php");
        $table = new adnTrainingFacilityTableGUI(
            $this,
            "listTrainingFacilities",
            $this->provider_id
        );

        $tpl->setContent($table->getHTML());
    }
    
    /**
     * Add new facility form
     *
     * @param ilPropertyFormGUI $a_form
     */
    protected function addTrainingFacility(ilPropertyFormGUI $a_form = null)
    {
        global $tpl, $ilTabs, $ilCtrl, $lng;

        $ilTabs->setBackTarget($lng->txt("back"), $ilCtrl->getLinkTarget(
            $this,
            "listTrainingFacilities"
        ));

        if (!$a_form) {
            $a_form = $this->initTrainingFacilityForm("create");
        }
        $tpl->setContent($a_form->getHTML());
    }
    
    /**
     * Edit facility form
     *
     * @param ilPropertyFormGUI $a_form
     */
    protected function editTrainingFacility(ilPropertyFormGUI $a_form = null)
    {
        global $tpl, $ilTabs, $ilCtrl, $lng;

        $ilTabs->setBackTarget($lng->txt("back"), $ilCtrl->getLinkTarget(
            $this,
            "listTrainingFacilities"
        ));

        if (!$a_form) {
            $a_form = $this->initTrainingFacilityForm("edit");
        }
        $tpl->setContent($a_form->getHTML());
    }
    
    /**
     * Init facility form
     *
     * @param string $a_mode form mode ("create" | "edit")
     * @return ilPropertyFormGUI
     */
    protected function initTrainingFacilityForm($a_mode = "edit")
    {
        global $lng, $ilCtrl;

        include_once "Services/ADN/TA/classes/class.adnTrainingProvider.php";
        $provider = new adnTrainingProvider($this->provider_id);
        
        // get form object and add input fields
        include_once("Services/Form/classes/class.ilPropertyFormGUI.php");
        $form = new ilPropertyFormGUI();
        
        // name
        $name = new ilTextAreaInputGUI($lng->txt("adn_city"), "name");
        $name->setCols(80);
        $name->setRows(5);
        $name->setRequired(true);
        $form->addItem($name);

        if ($a_mode == "create") {
            // creation: save/cancel buttons and title
            $form->addCommandButton("saveTrainingFacility", $lng->txt("save"));
            $form->addCommandButton("listTrainingFacilities", $lng->txt("cancel"));
            $form->setTitle($lng->txt("adn_add_training_facility") . ": " . $provider->getName());
        } else {
            $name->setValue($this->facility->getName());
            
            // editing: update/cancel buttons and title
            $form->addCommandButton("updateTrainingFacility", $lng->txt("save"));
            $form->addCommandButton("listTrainingFacilities", $lng->txt("cancel"));
            $form->setTitle($lng->txt("adn_edit_training_facility") . ": " . $provider->getName());
        }
        
        $form->setFormAction($ilCtrl->getFormAction($this));

        return $form;
    }
    
    /**
     * Create new facility entry
     */
    protected function saveTrainingFacility()
    {
        global $tpl, $lng, $ilCtrl;
        
        $form = $this->initTrainingFacilityForm("create");
        
        // check input
        if ($form->checkInput()) {
            // input ok: create new facility
            include_once("./Services/ADN/TA/classes/class.adnTrainingFacility.php");
            $facility = new adnTrainingFacility();
            $facility->setProvider($this->provider_id);
            $facility->setName($form->getInput("name"));
            
            if ($facility->save()) {
                // show success message and return to list
                ilUtil::sendSuccess($lng->txt("adn_training_facility_created"), true);
                $ilCtrl->redirect($this, "listTrainingFacilities");
            }
        }

        // input not valid: show form again
        $form->setValuesByPost();
        $this->addTrainingFacility($form);
    }
    
    /**
     * Update facility
     */
    protected function updateTrainingFacility()
    {
        global $lng, $ilCtrl, $tpl;
        
        $form = $this->initTrainingFacilityForm("edit");
        
        // check input
        if ($form->checkInput()) {
            // perform update
            $this->facility->setName($form->getInput("name"));
            
            if ($this->facility->update()) {
                // show success message and return to list
                ilUtil::sendSuccess($lng->txt("adn_training_facility_updated"), true);
                $ilCtrl->redirect($this, "listTrainingFacilities");
            }
        }
        
        // input not valid: show form again
        $form->setValuesByPost();
        $this->editTrainingFacility($form);
    }
    
    /**
     * Confirm facilities deletion
     */
    protected function confirmTrainingFacilitiesDeletion()
    {
        global $ilCtrl, $tpl, $lng, $ilTabs;
        
        // check whether at least one item has been seleced
        if (!is_array($_POST["facility_id"]) || count($_POST["facility_id"]) == 0) {
            ilUtil::sendFailure($lng->txt("no_checkbox"), true);
            $ilCtrl->redirect($this, "listTrainingFacilities");
        } else {
            $ilTabs->setBackTarget(
                $lng->txt("back"),
                $ilCtrl->getLinkTarget($this, "listTrainingFacilities")
            );
            
            // display confirmation message
            include_once("./Services/Utilities/classes/class.ilConfirmationGUI.php");
            $cgui = new ilConfirmationGUI();
            $cgui->setFormAction($ilCtrl->getFormAction($this));
            $cgui->setHeaderText($lng->txt("adn_sure_delete_training_facilities"));
            $cgui->setCancel($lng->txt("cancel"), "listTrainingFacilities");
            $cgui->setConfirm($lng->txt("delete"), "deleteTrainingFacilities");

            // list objects that should be deleted
            include_once("./Services/ADN/TA/classes/class.adnTrainingFacility.php");
            foreach ($_POST["facility_id"] as $i) {
                $cgui->addItem("facility_id[]", $i, adnTrainingFacility::lookupName($i));
            }
            
            $tpl->setContent($cgui->getHTML());
        }
    }
    
    /**
     * Delete facilities
     */
    protected function deleteTrainingFacilities()
    {
        global $ilCtrl, $lng;
        
        include_once("./Services/ADN/TA/classes/class.adnTrainingFacility.php");
        
        if (is_array($_POST["facility_id"])) {
            foreach ($_POST["facility_id"] as $i) {
                $facility = new adnTrainingFacility($i);
                $facility->delete();
            }
        }
        ilUtil::sendSuccess($lng->txt("adn_training_facility_deleted"), true);
        $ilCtrl->redirect($this, "listTrainingFacilities");
    }
}
