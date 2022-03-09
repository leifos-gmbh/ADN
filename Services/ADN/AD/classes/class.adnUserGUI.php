<?php
/* Copyright (c) 2010 Leifos, GPL, see docs/LICENSE */

/**
 * User GUI class
 *
 * Users are displayed using a table gui, respective signatures can be edited
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.de>
 * @version $Id: class.adnUserGUI.php 27871 2011-02-25 15:29:26Z jluetzen $
 *
 * @ilCtrl_Calls adnUserGUI:
 *
 * @ingroup ServicesADN
 */
class adnUserGUI
{
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
                $cmd = $ilCtrl->getCmd("listUsers");

                switch ($cmd) {
                    // commands that need read permission
                    case "listUsers":
                        if (adnPerm::check(adnPerm::AD, adnPerm::READ)) {
                            $this->$cmd();
                        }
                        break;
                    
                    // commands that need write permission
                    case "saveUsers":
                        if (adnPerm::check(adnPerm::AD, adnPerm::WRITE)) {
                            $this->$cmd();
                        }
                        break;
                    
                }
                break;
        }
    }

    /**
     * List all users
     */
    protected function listUsers()
    {
        global $tpl;

        // table of countries
        include_once("./Services/ADN/AD/classes/class.adnUserTableGUI.php");
        $table = new adnUserTableGUI($this, "listUsers");
        
        // output table
        $tpl->setContent($table->getHTML());
    }

    /**
     * Save user data
     */
    protected function saveUsers()
    {
        global $lng, $ilCtrl;

        // get values directly from post as we have no form object
        if ($_POST["user"]) {
            include_once './Services/User/classes/class.ilUserDefinedFields.php';
            $definition = ilUserDefinedFields::_getInstance();
            $sign_id = $definition->fetchFieldIdFromName("sign");

            if ($sign_id) {
                // save into user defined data field
                include_once './Services/User/classes/class.ilUserDefinedData.php';
                foreach ($_POST["user"] as $user_id => $sign) {
                    $user_data = new ilUserDefinedData($user_id);
                    $user_data->set("f_" . $sign_id, (string) $sign);
                    $user_data->update();
                }
            }
        }

        ilUtil::sendSuccess($lng->txt("settings_saved"), true);
        $ilCtrl->redirect($this, "listUsers");
    }
}
