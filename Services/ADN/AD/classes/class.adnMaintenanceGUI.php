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
    /**
     * Execute command
     */
    public function executeCommand()
    {
        global $ilCtrl, $tpl, $lng;
        
        $next_class = $ilCtrl->getNextClass();
        $tpl->setTitle($lng->txt("adn_md") . " - " . $lng->txt("adn_ad_mnt"));
        adnIcon::setTitleIcon("ad_mnt");

        // forward command to next gui class in control flow
        switch ($next_class) {
            // no next class:
            // this class is responsible to process the command
            default:
                $cmd = $ilCtrl->getCmd("editMode");

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
        global $tpl, $lng, $ilTabs, $ilCtrl;

        if (!$a_form) {
            $a_form = $this->initMaintenanceForm();
        }
        $tpl->setContent($a_form->getHTML());
    }

    /**
     * Update mode
     */
    protected function updateMode()
    {
        global $tpl, $lng, $ilCtrl, $ilSetting;

        $form = $this->initMaintenanceForm();
        if ($form->checkInput()) {
            // save into global settings
            $ilSetting->set("adn_maintenance", (bool) $form->getInput("mode"));
            
            ilUtil::sendSuccess($lng->txt("settings_saved"), true);
            $ilCtrl->redirect($this, "editMode");
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
        global  $lng, $ilCtrl, $ilSetting;

        include_once "Services/Form/classes/class.ilPropertyFormGUI.php";
        $form = new ilPropertyFormGUI();
        $form->setTitle($lng->txt("adn_maintenance_mode"));
        $form->setFormAction($ilCtrl->getFormAction($this, "editMode"));

        $mode = new ilCheckboxInputGUI($lng->txt("adn_maintenance_toggle"), "mode");
        $mode->setChecked($ilSetting->get("adn_maintenance"));
        $form->addItem($mode);

        $form->addCommandButton("updateMode", $lng->txt("save"));
        
        return $form;
    }
}
