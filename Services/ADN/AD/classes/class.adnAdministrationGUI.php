<?php
/* Copyright (c) 2010 Leifos, GPL, see docs/LICENSE */

/**
 * Administration module GUI base class
 *
 * Calls the module GUI classes as a controller
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @version $Id: class.adnAdministrationGUI.php 27873 2011-02-25 16:21:55Z jluetzen $
 *
 * @ingroup ServicesADN
 *
 * @ilCtrl_Calls adnAdministrationGUI: adnMaintenanceGUI, adnCharacterGUI, adnMCQuestionExportGUI
 * @ilCtrl_Calls adnAdministrationGUI: adnUserGUI, adnProfessionalImportGUI
 */
class adnAdministrationGUI
{
    public function executeCommand()
    {
        global $DIC;
        
        $tpl = $DIC->ui()->mainTemplate();
        $ilCtrl = $DIC->ctrl();
        $lng = $DIC->language();

        // set page title
        $this->tpl->setTitle($this->lng->txt("adn_ad"));

        $next_class = $this->ctrl->getNextClass();
        $cmd = $this->ctrl->getCmd();

        // menu item triggered
        if ($cmd == "processMenuItem") {
            // determine cmd and cmdClass from menu item
            include_once("./Services/ADN/UI/classes/class.adnMainMenuGUI.php");
            switch ($_GET["menu_item"]) {
                // maintenance mode
                case adnMainMenuGUI::AD_MNT:
                    $this->ctrl->setCmdClass("adnmaintenancegui");
                    $this->ctrl->setCmd("editMode");
                    break;

                // special characters
                case adnMainMenuGUI::AD_CHR:
                    $this->ctrl->setCmdClass("adncharactergui");
                    $this->ctrl->setCmd("listCharacters");
                    break;

                // export mc
                case adnMainMenuGUI::AD_MCX:
                    $this->ctrl->setCmdClass("adnmcquestionexportgui");
                    $this->ctrl->setCmd("listFiles");
                    break;

                // users
                case adnMainMenuGUI::AD_USR:
                    $this->ctrl->setCmdClass("adnusergui");
                    $this->ctrl->setCmd("listUsers");
                    break;

                // import professionals
                case adnMainMenuGUI::AD_ICP:
                    $this->ctrl->setCmdClass("adnprofessionalimportgui");
                    $this->ctrl->setCmd("importFile");
                    break;

            }
            $next_class = $this->ctrl->getNextClass();
        }

        // If no next class is responsible for handling the
        // command, set the default class
        if ($next_class == "") {
            // default: maintenance mode
            $this->ctrl->setCmd("");
            $this->ctrl->setCmdClass("adnmaintenancegui");
            $next_class = $this->ctrl->getNextClass();
        }

        // forward command to next gui class in control flow
        switch ($next_class) {
            case "adnmaintenancegui":
                include_once("./Services/ADN/AD/classes/class.adnMaintenanceGUI.php");
                $ct_gui = new adnMaintenanceGUI();
                $this->ctrl->forwardCommand($ct_gui);
                break;

            case "adncharactergui":
                include_once("./Services/ADN/AD/classes/class.adnCharacterGUI.php");
                $ct_gui = new adnCharacterGUI();
                $this->ctrl->forwardCommand($ct_gui);
                break;

            case "adnmcquestionexportgui":
                include_once("./Services/ADN/AD/classes/class.adnMCQuestionExportGUI.php");
                $mcx_gui = new adnMCQuestionExportGUI();
                $this->ctrl->forwardCommand($mcx_gui);
                break;

            case "adnusergui":
                include_once("./Services/ADN/AD/classes/class.adnUserGUI.php");
                $usr_gui = new adnUserGUI();
                $this->ctrl->forwardCommand($usr_gui);
                break;

            case "adnprofessionalimportgui":
                include_once("./Services/ADN/AD/classes/class.adnProfessionalImportGUI.php");
                $pro_gui = new adnProfessionalImportGUI();
                $this->ctrl->forwardCommand($pro_gui);
                break;

        }

        adnBaseGUI::setHelpButton($this->ctrl->getCmdClass());
    }
}
