<?php
/* Copyright (c) 2010 Leifos, GPL, see docs/LICENSE */

/**
 * Training administration GUI base class
 *
 * Calls the module GUI classes as a controller
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id: class.adnTrainingAdministrationGUI.php 27891 2011-02-28 11:46:53Z jluetzen $
 *
 * @ingroup ServicesADN
 *
 * @ilCtrl_Calls adnTrainingAdministrationGUI: adnTrainingProviderGUI, adnTrainingEventGUI
 * @ilCtrl_Calls adnTrainingAdministrationGUI: adnInformationLetterGUI, adnAreaOfExpertiseGUI
 */
class adnTrainingAdministrationGUI
{
    protected ilCtrl $ctrl;
    protected ilGlobalTemplateInterface $tpl;
    protected ilLanguage $lng;

    public function __construct()
    {
        global $DIC;
        $this->tpl = $DIC->ui()->mainTemplate();
        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();
    }

    /**
     * Execute command
     */
    public function executeCommand()
    {

        // set page title
        $this->tpl->setTitle($this->lng->txt("adn_ta"));

        $next_class = $this->ctrl->getNextClass();
        $cmd = $this->ctrl->getCmd();

        // menu item triggered
        if ($cmd == "processMenuItem") {
            // determine cmd and cmdClass from menu item
            include_once("./Services/ADN/UI/classes/class.adnMainMenuGUI.php");
            switch ($_GET["menu_item"]) {
                // list training providers
                case adnMainMenuGUI::TA_TPS:
                    $this->ctrl->setCmdClass("adntrainingprovidergui");
                    $this->ctrl->setCmd("listTrainingProviders");
                    break;

                // list training providers
                case adnMainMenuGUI::TA_TES:
                    $this->ctrl->setCmdClass("adntrainingeventgui");
                    $this->ctrl->setCmd("listTrainingEvents");
                    break;

                // list information letters
                case adnMainMenuGUI::TA_ILS:
                    $this->ctrl->setCmdClass("adninformationlettergui");
                    $this->ctrl->setCmd("listInformationLetters");
                    break;

                // list areas of expertise
                case adnMainMenuGUI::TA_AES:
                    $this->ctrl->setCmdClass("adnareaofexpertisegui");
                    $this->ctrl->setCmd("listAreasOfExpertise");
                    break;
            }
            $next_class = $this->ctrl->getNextClass();
        } elseif ($next_class == "") {
            // If no next class is responsible for handling the
            // command, set the default class
            // default: training provider overview
            $this->ctrl->setCmd("");
            $this->ctrl->setCmdClass("adntrainingprovidergui");
            $next_class = $this->ctrl->getNextClass();
        }


        // forward command to next gui class in control flow
        switch ($next_class) {
            case "adntrainingprovidergui":
                include_once("./Services/ADN/TA/classes/class.adnTrainingProviderGUI.php");
                $tp_gui = new adnTrainingProviderGUI();
                $this->ctrl->forwardCommand($tp_gui);
                break;

            case "adntrainingeventgui":
                include_once("./Services/ADN/TA/classes/class.adnTrainingEventGUI.php");
                $tp_gui = new adnTrainingEventGUI();
                $this->ctrl->forwardCommand($tp_gui);
                break;

            case "adninformationlettergui":
                include_once("./Services/ADN/TA/classes/class.adnInformationLetterGUI.php");
                $il_gui = new adnInformationLetterGUI();
                $this->ctrl->forwardCommand($il_gui);
                break;

            case "adnareaofexpertisegui":
                include_once("./Services/ADN/TA/classes/class.adnAreaOfExpertiseGUI.php");
                $ae_gui = new adnAreaOfExpertiseGUI();
                $this->ctrl->forwardCommand($ae_gui);
                break;
        }

        adnBaseGUI::setHelpButton($this->ctrl->getCmdClass());
    }
}
