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
 * @ilCtrl_Calls adnAdministrationGUI: adnUserGUI, adnProfessionalImportGUI, adnCardAdministrationGUI
 */
class adnAdministrationGUI
{
    /**
     * Execute command
     */
    public function executeCommand()
    {
        global $ilCtrl, $lng, $tpl;

        // set page title
        $tpl->setTitle($lng->txt("adn_ad"));

        $next_class = $ilCtrl->getNextClass();
        $cmd = $ilCtrl->getCmd();

        // menu item triggered
        if ($cmd == "processMenuItem") {
            // determine cmd and cmdClass from menu item
            include_once("./Services/ADN/UI/classes/class.adnMainMenuGUI.php");
            switch ($_GET["menu_item"]) {
                // maintenance mode
                case adnMainMenuGUI::AD_MNT:
                    $ilCtrl->setCmdClass("adnmaintenancegui");
                    $ilCtrl->setCmd("editMode");
                    break;

                // special characters
                case adnMainMenuGUI::AD_CHR:
                    $ilCtrl->setCmdClass("adncharactergui");
                    $ilCtrl->setCmd("listCharacters");
                    break;

                // export mc
                case adnMainMenuGUI::AD_MCX:
                    $ilCtrl->setCmdClass("adnmcquestionexportgui");
                    $ilCtrl->setCmd("listFiles");
                    break;

                // users
                case adnMainMenuGUI::AD_USR:
                    $ilCtrl->setCmdClass("adnusergui");
                    $ilCtrl->setCmd("listUsers");
                    break;

                // import professionals
                case adnMainMenuGUI::AD_ICP:
                    $ilCtrl->setCmdClass("adnprofessionalimportgui");
                    $ilCtrl->setCmd("importFile");
                    break;

                case adnMainMenuGUI::AD_CARD:
                    $ilCtrl->setCmdClass(adnCardAdministrationGUI::class);
                    $ilCtrl->setCmd('settings');
                    break;
            }
            $next_class = $ilCtrl->getNextClass();
        }

        // If no next class is responsible for handling the
        // command, set the default class
        if ($next_class == "") {
            // default: maintenance mode
            $ilCtrl->setCmd("");
            $ilCtrl->setCmdClass("adnmaintenancegui");
            $next_class = $ilCtrl->getNextClass();
        }

        // forward command to next gui class in control flow
        switch ($next_class) {
            case "adnmaintenancegui":
                include_once("./Services/ADN/AD/classes/class.adnMaintenanceGUI.php");
                $ct_gui = new adnMaintenanceGUI();
                $ilCtrl->forwardCommand($ct_gui);
                break;

            case "adncharactergui":
                include_once("./Services/ADN/AD/classes/class.adnCharacterGUI.php");
                $ct_gui = new adnCharacterGUI();
                $ilCtrl->forwardCommand($ct_gui);
                break;

            case "adnmcquestionexportgui":
                include_once("./Services/ADN/AD/classes/class.adnMCQuestionExportGUI.php");
                $mcx_gui = new adnMCQuestionExportGUI();
                $ilCtrl->forwardCommand($mcx_gui);
                break;

            case "adnusergui":
                include_once("./Services/ADN/AD/classes/class.adnUserGUI.php");
                $usr_gui = new adnUserGUI();
                $ilCtrl->forwardCommand($usr_gui);
                break;
            
            case "adnprofessionalimportgui":
                include_once("./Services/ADN/AD/classes/class.adnProfessionalImportGUI.php");
                $pro_gui = new adnProfessionalImportGUI();
                $ilCtrl->forwardCommand($pro_gui);
                break;

            case strtolower(adnCardAdministrationGUI::class):
                $card = new adnCardAdministrationGUI();
                $ilCtrl->forwardCommand($card);
                break;
        }

        adnBaseGUI::setHelpButton($ilCtrl->getCmdClass());
    }
}
