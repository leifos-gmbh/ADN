<?php
/* Copyright (c) 2010 Leifos, GPL, see docs/LICENSE */

/**
 * Examination preparation GUI base class
 *
 * Calls the module GUI classes as a controller
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @version $Id: class.adnExaminationPreparationGUI.php 27888 2011-02-28 11:09:28Z jluetzen $
 *
 * @ingroup ServicesADN
 *
 * @ilCtrl_Calls adnExaminationPreparationGUI: adnExaminationEventGUI, adnPreparationCandidateGUI
 * @ilCtrl_Calls adnExaminationPreparationGUI: adnAssignmentGUI, adnAnswerSheetGUI
 * @ilCtrl_Calls adnExaminationPreparationGUI: adnExamInfoLetterGUI, adnExaminationInvitationGUI
 * @ilCtrl_Calls adnExaminationPreparationGUI: adnAttendanceGUI, adnTestPreparationGUI
 */
class adnExaminationPreparationGUI
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
        $this->tpl->setTitle($this->lng->txt("adn_ep"));

        $next_class = $this->ctrl->getNextClass();
        $cmd = $this->ctrl->getCmd();

        if ($cmd == "processMenuItem") {	// menu item triggered
            // determine cmd and cmdClass from menu item
            include_once("./Services/ADN/UI/classes/class.adnMainMenuGUI.php");
            switch ($_GET["menu_item"]) {
                // answer sheets
                case adnMainMenuGUI::EP_ILS:
                    $this->ctrl->setCmdClass("adnexaminfolettergui");
                    $this->ctrl->setCmd("listLetters");
                    break;

                // examination events
                case adnMainMenuGUI::EP_EES:
                    $this->ctrl->setCmdClass("adnexaminationeventgui");
                    $this->ctrl->setCmd("listEvents");
                    break;

                // examination candidates
                case adnMainMenuGUI::EP_ECS:
                    $this->ctrl->setCmdClass("adnpreparationcandidategui");
                    $this->ctrl->setCmd("listCandidates");
                    break;

                // candidate assignments
                case adnMainMenuGUI::EP_CES:
                    $this->ctrl->setCmdClass("adnassignmentgui");
                    $this->ctrl->setCmd("listEvents");
                    break;

                // answer sheets
                case adnMainMenuGUI::EP_ASS:
                    $this->ctrl->setCmdClass("adnanswersheetgui");
                    $this->ctrl->setCmd("listEvents");
                    break;

                // invitations
                case adnMainMenuGUI::EP_INS:
                    $this->ctrl->setCmdClass("adnexaminationinvitationgui");
                    $this->ctrl->setCmd("listEvents");
                    break;

                // attendance
                case adnMainMenuGUI::EP_ALS:
                    $this->ctrl->setCmdClass("adnattendancegui");
                    $this->ctrl->setCmd("listEvents");
                    break;

                // test preparation
                case adnMainMenuGUI::EP_ACS:
                    $this->ctrl->setCmdClass("adntestpreparationgui");
                    $this->ctrl->setCmd("listEvents");
                    break;
            }
            $next_class = $this->ctrl->getNextClass();
        }

        // If no next class is responsible for handling the
        // command, set the default class
        if ($next_class == "") {
            // default: information sheet overview
            $this->ctrl->setCmdClass("adnexaminfolettergui");
            $this->ctrl->setCmd("listLetters");
            $next_class = $this->ctrl->getNextClass();
        }

        // forward command to next gui class in control flow
        switch ($next_class) {
            case "adnexaminfolettergui":
                include_once("./Services/ADN/EP/classes/class.adnExamInfoLetterGUI.php");
                $is_gui = new adnExamInfoLetterGUI();
                $this->ctrl->forwardCommand($is_gui);
                break;

            case "adnexaminationeventgui":
                include_once("./Services/ADN/EP/classes/class.adnExaminationEventGUI.php");
                $ee_gui = new adnExaminationEventGUI();
                $this->ctrl->forwardCommand($ee_gui);
                break;

            case "adnpreparationcandidategui":
                include_once("./Services/ADN/EP/classes/class.adnPreparationCandidateGUI.php");
                $ct_gui = new adnPreparationCandidateGUI();
                $this->ctrl->forwardCommand($ct_gui);
                break;

            case "adnassignmentgui":
                include_once("./Services/ADN/EP/classes/class.adnAssignmentGUI.php");
                $ct_gui = new adnAssignmentGUI();
                $this->ctrl->forwardCommand($ct_gui);
                break;

            case "adnanswersheetgui":
                include_once("./Services/ADN/EP/classes/class.adnAnswerSheetGUI.php");
                $as_gui = new adnAnswerSheetGUI();
                $this->ctrl->forwardCommand($as_gui);
                break;

            case "adnexaminationinvitationgui":
                include_once("./Services/ADN/EP/classes/class.adnExaminationInvitationGUI.php");
                $inv_gui = new adnExaminationInvitationGUI();
                $this->ctrl->forwardCommand($inv_gui);
                break;

            case "adnattendancegui":
                include_once("./Services/ADN/EP/classes/class.adnAttendanceGUI.php");
                $att_gui = new adnAttendanceGUI();
                $this->ctrl->forwardCommand($att_gui);
                break;

            case "adntestpreparationgui":
                include_once("./Services/ADN/EP/classes/class.adnTestPreparationGUI.php");
                $att_gui = new adnTestPreparationGUI();
                $this->ctrl->forwardCommand($att_gui);
                break;
        }

        adnBaseGUI::setHelpButton($this->ctrl->getCmdClass());
    }
}
