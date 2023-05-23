<?php
/* Copyright (c) 2010 Leifos, GPL, see docs/LICENSE */

/**
 * ADN area of expertise GUI class
 *
 * Areas list, forms and persistence
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @version $Id: class.adnAreaOfExpertiseGUI.php 27891 2011-02-28 11:46:53Z jluetzen $
 *
 * @ilCtrl_Calls adnAreaOfExpertiseGUI:
 *
 * @ingroup ServicesADN
 */
class adnAreaOfExpertiseGUI
{
    // current area object
    protected $area = null;
    
    /**
     * Constructor
     */
    public function __construct()
    {
        global $ilCtrl;

        // save area ID through requests
        $ilCtrl->saveParameter($this, array("ae_id"));
        
        $this->readAreaOfExpertise();
    }
    
    /**
     * Execute command
     */
    public function executeCommand()
    {
        global $ilCtrl, $tpl, $lng;

        $tpl->setTitle($lng->txt("adn_ta") . " - " . $lng->txt("adn_ta_aes"));
        adnIcon::setTitleIcon("ta_aes");

        $next_class = $ilCtrl->getNextClass();
        
        // forward command to next gui class in control flow
        switch ($next_class) {
            // no next class:
            // this class is responsible to process the command
            default:
                $cmd = $ilCtrl->getCmd("listAreasOfExpertise");
                
                switch ($cmd) {
                    // commands that need read permission
                    case "listAreasOfExpertise":
                        if (adnPerm::check(adnPerm::TA, adnPerm::READ)) {
                            $this->$cmd();
                        }
                        break;
                    
                    // commands that need write permission
                    case "addAreaOfExpertise":
                    case "saveAreaOfExpertise":
                    case "editAreaOfExpertise":
                    case "updateAreaOfExpertise":
                    case "confirmAreasOfExpertiseDeletion":
                    case "deleteAreasOfExpertise":
                        if (adnPerm::check(adnPerm::TA, adnPerm::WRITE)) {
                            $this->$cmd();
                        }
                        break;
                    
                }
                break;
        }
    }
    
    /**
     * Read area
     */
    protected function readAreaOfExpertise()
    {
        if ((int) $_GET["ae_id"] > 0) {
            include_once("./Services/ADN/TA/classes/class.adnAreaOfExpertise.php");
            $this->area = new adnAreaOfExpertise((int) $_GET["ae_id"]);
        }
    }
    
    /**
     * List areas
     */
    public function listAreasOfExpertise()
    {
        global $tpl, $lng, $ilCtrl, $ilToolbar;

        if (adnPerm::check(adnPerm::TA, adnPerm::WRITE)) {
            $ilToolbar->addButton(
                $lng->txt("adn_add_area_of_expertise"),
                $ilCtrl->getLinkTarget($this, "addAreaOfExpertise")
            );
        }

        include_once("./Services/ADN/TA/classes/class.adnAreaOfExpertiseTableGUI.php");
        $table = new adnAreaOfExpertiseTableGUI($this, "listAreasOfExpertise");

        $tpl->setContent($table->getHTML());
    }
    
    /**
     * Add new area form
     *
     * @param ilPropertyFormGUI $a_form
     */
    protected function addAreaOfExpertise(ilPropertyFormGUI $a_form = null)
    {
        global $tpl, $ilTabs, $ilCtrl, $lng;

        $ilTabs->setBackTarget($lng->txt("back"), $ilCtrl->getLinkTarget($this, "listAreasOfExpertise"));

        if (!$a_form) {
            $a_form = $this->initAreaOfExpertiseForm("create");
        }
        $tpl->setContent($a_form->getHTML());
    }
    
    /**
     * Edit area form
     *
     * @param ilPropertyFormGUI $a_form
     */
    protected function editAreaOfExpertise(ilPropertyFormGUI $a_form = null)
    {
        global $tpl, $ilTabs, $ilCtrl, $lng;

        $ilTabs->setBackTarget($lng->txt("back"), $ilCtrl->getLinkTarget($this, "listAreasOfExpertise"));

        if (!$a_form) {
            $a_form = $this->initAreaOfExpertiseForm("edit");
        }
        $tpl->setContent($a_form->getHTML());
    }
    
    /**
     * Init area form
     *
     * @param string $a_mode form mode ("create" | "edit")
     * @return ilPropertyFormGUI
     */
    protected function initAreaOfExpertiseForm($a_mode = "edit")
    {
        global $lng, $ilCtrl;
        
        // get form object and add input fields
        include_once("Services/Form/classes/class.ilPropertyFormGUI.php");
        $form = new ilPropertyFormGUI();
        
        // name
        $name = new ilTextInputGUI($lng->txt("adn_name"), "name");
        $name->setMaxLength(200);
        $name->setRequired(true);
        $form->addItem($name);

        if ($a_mode == "create") {
            // creation: save/cancel buttons and title
            $form->addCommandButton("saveAreaOfExpertise", $lng->txt("save"));
            $form->addCommandButton("listAreasOfExpertise", $lng->txt("cancel"));
            $form->setTitle($lng->txt("adn_add_area_of_expertise"));
        } else {
            $name->setValue($this->area->getName());
            
            // editing: update/cancel buttons and title
            $form->addCommandButton("updateAreaOfExpertise", $lng->txt("save"));
            $form->addCommandButton("listAreasOfExpertise", $lng->txt("cancel"));
            $form->setTitle($lng->txt("adn_edit_area_of_expertise"));
        }
        
        $form->setFormAction($ilCtrl->getFormAction($this));

        return $form;
    }
    
    /**
     * Create new area
     */
    protected function saveAreaOfExpertise()
    {
        global $tpl, $lng, $ilCtrl;
        
        $form = $this->initAreaOfExpertiseForm("create");
        
        // check input
        if ($form->checkInput()) {
            // input ok: create new area
            include_once("./Services/ADN/TA/classes/class.adnAreaOfExpertise.php");
            $area = new adnAreaOfExpertise();
            $area->setName($form->getInput("name"));
            
            if ($area->save()) {
                // show success message and return to list
                ilUtil::sendSuccess($lng->txt("adn_area_of_expertise_created"), true);
                $ilCtrl->redirect($this, "listAreasOfExpertise");
            }
        }

        // input not valid: show form again
        $form->setValuesByPost();
        $this->addAreaOfExpertise($form);
    }
    
    /**
     * Update area
     */
    protected function updateAreaOfExpertise()
    {
        global $lng, $ilCtrl, $tpl;
        
        $form = $this->initAreaOfExpertiseForm("edit");
        
        // check input
        if ($form->checkInput()) {
            // perform update
            $this->area->setName($form->getInput("name"));
            
            if ($this->area->update()) {
                // show success message and return to list
                ilUtil::sendSuccess($lng->txt("adn_area_of_expertise_updated"), true);
                $ilCtrl->redirect($this, "listAreasOfExpertise");
            }
        }
        
        // input not valid: show form again
        $form->setValuesByPost();
        $this->editAreaOfExpertise($form);
    }
    
    /**
     * Confirm areas deletion
     */
    protected function confirmAreasOfExpertiseDeletion()
    {
        global $ilCtrl, $tpl, $lng, $ilTabs;
        
        // check whether at least one item has been seleced
        if (!is_array($_POST["area_id"]) || count($_POST["area_id"]) == 0) {
            ilUtil::sendFailure($lng->txt("no_checkbox"), true);
            $ilCtrl->redirect($this, "listAreasOfExpertise");
        } else {
            $ilTabs->setBackTarget(
                $lng->txt("back"),
                $ilCtrl->getLinkTarget($this, "listAreasOfExpertise")
            );
            
            // display confirmation message
            include_once("./Services/Utilities/classes/class.ilConfirmationGUI.php");
            $cgui = new ilConfirmationGUI();
            $cgui->setFormAction($ilCtrl->getFormAction($this));
            $cgui->setHeaderText($lng->txt("adn_sure_delete_areas_of_expertise"));
            $cgui->setCancel($lng->txt("cancel"), "listAreasOfExpertise");
            $cgui->setConfirm($lng->txt("delete"), "deleteAreasOfExpertise");

            // list objects that should be deleted
            include_once("./Services/ADN/TA/classes/class.adnAreaOfExpertise.php");
            foreach ($_POST["area_id"] as $i) {
                $cgui->addItem("area_id[]", $i, adnAreaOfExpertise::lookupName($i));
            }
            
            $tpl->setContent($cgui->getHTML());
        }
    }
    
    /**
     * Delete areas
     */
    protected function deleteAreasOfExpertise()
    {
        global $ilCtrl, $lng;
        
        include_once("./Services/ADN/TA/classes/class.adnAreaOfExpertise.php");
        
        if (is_array($_POST["area_id"])) {
            foreach ($_POST["area_id"] as $i) {
                $area = new adnAreaOfExpertise($i);
                $area->delete();
            }
        }
        ilUtil::sendSuccess($lng->txt("adn_area_of_expertise_deleted"), true);
        $ilCtrl->redirect($this, "listAreasOfExpertise");
    }
}
