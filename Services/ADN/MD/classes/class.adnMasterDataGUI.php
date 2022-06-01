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
    protected ilCtrl $ctrl;
    protected ilLanguage $lng;
    protected ilGlobalTemplateInterface $tpl;

    public function __construct()
    {
        global $DIC;
        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();
        $this->tpl = $DIC->ui()->mainTemplate();
    }
    /**
     * Execute command
     */
    public function executeCommand()
    {

        // set page title
        $this->tpl->setTitle($this->lng->txt("adn_md"));

        $next_class = $this->ctrl->getNextClass();
        $cmd = $this->ctrl->getCmd();

        if ($cmd == "processMenuItem") {	// menu item triggered
            // determine cmd and cmdClass from menu item
            include_once("./Services/ADN/UI/classes/class.adnMainMenuGUI.php");
            switch ($_GET["menu_item"]) {
                // waterway management office
                case adnMainMenuGUI::MD_WOS:
                    $this->ctrl->setCmdClass("adnwmogui");
                    $this->ctrl->setCmd("listWMOs");
                    break;

                // countries
                case adnMainMenuGUI::MD_CNS:
                    $this->ctrl->setCmdClass("adncountrygui");
                    $this->ctrl->setCmd("listCountries");
                    break;
            }
            $next_class = $this->ctrl->getNextClass();
        }

        // If no next class is responsible for handling the
        // command, set the default class
        if ($next_class == "") {
            // default: wmo overview
            $this->ctrl->setCmd("");
            $this->ctrl->setCmdClass("adnwmogui");
            $next_class = $this->ctrl->getNextClass();
        }

        // forward command to next gui class in control flow
        switch ($next_class) {
            case "adnwmogui":
                include_once("./Services/ADN/MD/classes/class.adnWMOGUI.php");
                $ct_gui = new adnWMOGUI();
                $this->ctrl->forwardCommand($ct_gui);
                break;

            case "adncountrygui":
                include_once("./Services/ADN/MD/classes/class.adnCountryGUI.php");
                $ct_gui = new adnCountryGUI();
                $this->ctrl->forwardCommand($ct_gui);
                break;
        }

        adnBaseGUI::setHelpButton($this->ctrl->getCmdClass());
    }
}
