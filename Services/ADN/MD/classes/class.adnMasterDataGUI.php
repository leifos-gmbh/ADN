<?php
/* Copyright (c) 2010 Leifos, GPL, see docs/LICENSE */

/**
 * Master data GUI base class
 *
 * Calls the module GUI classes as a controller
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @version $Id: class.adnMasterDataGUI.php 27876 2011-02-25 16:51:38Z jluetzen $
 *
 * @ingroup ServicesADN
 *
 * @ilCtrl_Calls adnMasterDataGUI: adnCountryGUI, adnWMOGUI
 */
class adnMasterDataGUI
{
    /**
     * Execute command
     */
    public function executeCommand()
    {
        global $ilCtrl, $lng, $tpl;

        // set page title
        $tpl->setTitle($lng->txt("adn_md"));

        $next_class = $ilCtrl->getNextClass();
        $cmd = $ilCtrl->getCmd();

        if ($cmd == "processMenuItem") {	// menu item triggered
            // determine cmd and cmdClass from menu item
            include_once("./Services/ADN/UI/classes/class.adnMainMenuGUI.php");
            switch ($_GET["menu_item"]) {
                // waterway management office
                case adnMainMenuGUI::MD_WOS:
                    $ilCtrl->setCmdClass("adnwmogui");
                    $ilCtrl->setCmd("listWMOs");
                    break;

                // countries
                case adnMainMenuGUI::MD_CNS:
                    $ilCtrl->setCmdClass("adncountrygui");
                    $ilCtrl->setCmd("listCountries");
                    break;
            }
            $next_class = $ilCtrl->getNextClass();
        }

        // If no next class is responsible for handling the
        // command, set the default class
        if ($next_class == "") {
            // default: wmo overview
            $ilCtrl->setCmd("");
            $ilCtrl->setCmdClass("adnwmogui");
            $next_class = $ilCtrl->getNextClass();
        }

        // forward command to next gui class in control flow
        switch ($next_class) {
            case "adnwmogui":
                include_once("./Services/ADN/MD/classes/class.adnWMOGUI.php");
                $ct_gui = new adnWMOGUI();
                $ilCtrl->forwardCommand($ct_gui);
                break;

            case "adncountrygui":
                include_once("./Services/ADN/MD/classes/class.adnCountryGUI.php");
                $ct_gui = new adnCountryGUI();
                $ilCtrl->forwardCommand($ct_gui);
                break;
        }

        adnBaseGUI::setHelpButton($ilCtrl->getCmdClass());
    }
}
