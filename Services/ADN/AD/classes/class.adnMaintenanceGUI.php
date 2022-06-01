<?php
/* Copyright (c) 2010 Leifos, GPL, see docs/LICENSE */

/**
 * Maintenance mode GUI class
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.de>
 * @version $Id: class.adnMaintenanceGUI.php 28757 2011-05-02 14:38:22Z jluetzen $
 *
 * @ilCtrl_Calls adnMaintenanceGUI:
 *
 * @ingroup ServicesADN
 */
class adnMaintenanceGUI
{
    protected ilCtrl $ctrl;
    protected ilLanguage $lng;
    protected ilTabsGUI $tabs;
    protected ilGlobalTemplateInterface $tpl;
    protected ilSetting $setting;

    public function __construct()
    {
        global $DIC;

        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();
        $this->tabs = $DIC->tabs();
        $this->tpl = $DIC->ui()->mainTemplate();
        $this->setting = $DIC->settings();
    }
    /**
     * Execute command
     */
    public function executeCommand()
    {
        
        $next_class = $this->ctrl->getNextClass();
        
        // forward command to next gui class in control flow
        switch ($next_class) {
            // no next class:
            // this class is responsible to process the command
            default:
                $cmd = $this->ctrl->getCmd("editMode");

                switch ($cmd) {
                    // commands that need write permission
                    case "editMode":
                    case "updateMode":
                        if (adnPerm::check(adnPerm::AD, adnPerm::WRITE)) {
                            $this->$cmd();
                        }
                        break;
                    
                }
                break;
        }
    }

    /**
     * Edit mode form
     *
     * @param ilPropertyFormGUI $a_form
     */
    protected function editMode(ilPropertyFormGUI $a_form = null)
    {

        if (!$a_form) {
            $a_form = $this->initMaintenanceForm();
        }
        $this->tpl->setContent($a_form->getHTML());
    }

    /**
     * Update mode
     */
    protected function updateMode()
    {

        $form = $this->initMaintenanceForm();
        if ($form->checkInput()) {
            // save into global settings
            $this->setting->set("adn_maintenance", (bool) $form->getInput("mode"));
            
            ilUtil::sendSuccess($this->lng->txt("settings_saved"), true);
            $this->ctrl->redirect($this, "editMode");
        }

        $form->setValuesByPost();
        $this->editMode($form);
    }

    /**
     * Build mode form
     *
     * @return ilPropertyFormGUI
     */
    protected function initMaintenanceForm()
    {

        include_once "Services/Form/classes/class.ilPropertyFormGUI.php";
        $form = new ilPropertyFormGUI();
        $form->setTitle($this->lng->txt("adn_maintenance_mode"));
        $form->setFormAction($this->ctrl->getFormAction($this, "editMode"));

        $mode = new ilCheckboxInputGUI($this->lng->txt("adn_maintenance_toggle"), "mode");
        $mode->setChecked($this->setting->get("adn_maintenance"));
        $form->addItem($mode);

        $form->addCommandButton("updateMode", $this->lng->txt("save"));
        
        return $form;
    }
}
