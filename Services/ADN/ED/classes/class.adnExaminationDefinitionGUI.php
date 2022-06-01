<?php
/* Copyright (c) 2010 Leifos, GPL, see docs/LICENSE */

/**
 * Examination definition GUI base class
 *
 * Calls the module GUI classes as a controller
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id: class.adnExaminationDefinitionGUI.php 27873 2011-02-25 16:21:55Z jluetzen $
 *
 * @ingroup ServicesADN
 *
 * @ilCtrl_Calls adnExaminationDefinitionGUI: adnObjectiveGUI, adnMCQuestionGUI
 * @ilCtrl_Calls adnExaminationDefinitionGUI: adnCaseQuestionGUI, adnGoodInTransitGUI
 * @ilCtrl_Calls adnExaminationDefinitionGUI: adnQuestionTargetNumbersGUI, adnCaseGUI
 * @ilCtrl_Calls adnExaminationDefinitionGUI: adnLicenseGUI
 */
class adnExaminationDefinitionGUI
{
    protected ilGlobalTemplateInterface $tpl;
    protected ilLanguage $lng;
    protected ilCtrl $ctrl;

    public function __construct()
    {
        global $DIC;
        $this->tpl = $DIC->ui()->mainTemplate();
        $this->lng = $DIC->language();
        $this->ctrl = $DIC->ctrl();
    }
    /**
     * Execute command
     */
    public function executeCommand()
    {

        // set page title
        $this->tpl->setTitle($this->lng->txt("adn_ed"));

        $next_class = $this->ctrl->getNextClass();
        $cmd = $this->ctrl->getCmd();

        // menu item triggered?
        if ($cmd == "processMenuItem") {
            // determine cmd and cmdClass from menu item
            include_once("./Services/ADN/UI/classes/class.adnMainMenuGUI.php");
            switch ($_GET["menu_item"]) {
                // list objectives
                case adnMainMenuGUI::ED_OBS:
                    $this->ctrl->setCmdClass("adnobjectivegui");
                    $this->ctrl->setCmd("listMCObjectives");
                    break;

                // list (mc) questions
                case adnMainMenuGUI::ED_EQS:
                    $this->ctrl->setCmdClass("adnmcquestiongui");
                    $this->ctrl->setCmd("listMCQuestions");
                    break;

                // number of questions per objective
                case adnMainMenuGUI::ED_NQS:
                    $this->ctrl->setCmdClass("adnquestiontargetnumbersgui");
                    $this->ctrl->setCmd("listTargets");
                    break;

                // goods in transit
                case adnMainMenuGUI::ED_GTS:
                    $this->ctrl->setCmdClass("adngoodintransitgui");
                    $this->ctrl->setCmd("listGasGoods");
                    break;

                // cases
                case adnMainMenuGUI::ED_CAS:
                    $this->ctrl->setCmdClass("adncasegui");
                    $this->ctrl->setCmd("editCase");
                    break;

                // licenses
                case adnMainMenuGUI::ED_LIC:
                    $this->ctrl->setCmdClass("adnlicensegui");
                    $this->ctrl->setCmd("listLicenses");
                    break;
            }
            $next_class = $this->ctrl->getNextClass();
        } elseif ($next_class == "") {
            // If no next class is responsible for handling the
            // command, set the default class

            // default: objectives overview
            $this->ctrl->setCmd("");
            $this->ctrl->setCmdClass("adnobjectivegui");
            $next_class = $this->ctrl->getNextClass();
        }

        // forward command to next gui class in control flow
        switch ($next_class) {
            case "adnobjectivegui":
                include_once("./Services/ADN/ED/classes/class.adnObjectiveGUI.php");
                $ob_gui = new adnObjectiveGUI();
                $this->ctrl->forwardCommand($ob_gui);
                break;

            case "adnmcquestiongui":
                include_once("./Services/ADN/ED/classes/class.adnMCQuestionGUI.php");
                $mc_gui = new adnMCQuestionGUI();
                $this->ctrl->forwardCommand($mc_gui);
                break;

            case "adncasequestiongui":
                include_once("./Services/ADN/ED/classes/class.adnCaseQuestionGUI.php");
                $case_gui = new adnCaseQuestionGUI();
                $this->ctrl->forwardCommand($case_gui);
                break;

            case "adngoodintransitgui":
                include_once("./Services/ADN/ED/classes/class.adnGoodInTransitGUI.php");
                $good_gui = new adnGoodInTransitGUI();
                $this->ctrl->forwardCommand($good_gui);
                break;

            case "adnquestiontargetnumbersgui":
                include_once("./Services/ADN/ED/classes/class.adnQuestionTargetNumbersGUI.php");
                $tgt_gui = new adnQuestionTargetNumbersGUI();
                $this->ctrl->forwardCommand($tgt_gui);
                break;

            case "adncasegui":
                include_once("./Services/ADN/ED/classes/class.adnCaseGUI.php");
                $case_gui = new adnCaseGUI();
                $this->ctrl->forwardCommand($case_gui);
                break;

            case "adnlicensegui":
                include_once("./Services/ADN/ED/classes/class.adnLicenseGUI.php");
                $lic_gui = new adnLicenseGUI();
                $this->ctrl->forwardCommand($lic_gui);
                break;
        }

        adnBaseGUI::setHelpButton($this->ctrl->getCmdClass());
    }
}
