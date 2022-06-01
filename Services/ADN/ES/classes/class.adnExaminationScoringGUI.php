<?php
/* Copyright (c) 2010 Leifos, GPL, see docs/LICENSE */

/**
 * Examination scoring GUI base class. The main UI class of the ES component.
 * This class delegates the command to the next responsible class in the control flow.
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @version $Id: class.adnExaminationScoringGUI.php 27884 2011-02-27 21:01:07Z akill $
 *
 * @ingroup ServicesADN
 *
 * @ilCtrl_Calls adnExaminationScoringGUI: adnScoringGUI, adnCertificateScoringGUI
 * @ilCtrl_Calls adnExaminationScoringGUI: adnScoreNotificationGUI, adnOnlineAnswerSheetGUI
 */
class adnExaminationScoringGUI
{
    protected ilLanguage $lng;
    protected ilCtrl $ctrl;
    protected ilGlobalTemplateInterface $tpl;

    public function __construct()
    {
        global $DIC;
        $this->lng = $DIC->language();
        $this->ctrl = $DIC->ctrl();
        $this->tpl = $DIC->ui()->mainTemplate();
    }

    public function executeCommand()
    {

        // set page title
        $this->tpl->setTitle($this->lng->txt("adn_es"));

        $next_class = $this->ctrl->getNextClass();
        $cmd = $this->ctrl->getCmd();

        if ($cmd == "processMenuItem") {	// menu item triggered
            // determine cmd and cmdClass from menu item
            include_once("./Services/ADN/UI/classes/class.adnMainMenuGUI.php");
            switch ($_GET["menu_item"]) {
                // edit scoring
                case adnMainMenuGUI::ES_SCS:
                    $this->ctrl->setCmdClass("adnscoringgui");
                    $this->ctrl->setCmd("listEvents");
                    break;

                // edit certificates
                case adnMainMenuGUI::ES_CTS:
                    $this->ctrl->setCmdClass("adncertificatescoringgui");
                    $this->ctrl->setCmd("listEvents");
                    break;

                // score notification
                case adnMainMenuGUI::ES_SNS:
                    $this->ctrl->setCmdClass("adnscorenotificationgui");
                    $this->ctrl->setCmd("listEvents");
                    break;

                // online answer sheets
                case adnMainMenuGUI::ES_OAS:
                    $this->ctrl->setCmdClass("adnonlineanswersheetgui");
                    $this->ctrl->setCmd("listEvents");
                    break;
            }
            $next_class = $this->ctrl->getNextClass();
        }

        // If no next class is responsible for handling the
        // command, set the default class
        if ($next_class == "") {
            // default: scoring
            $this->ctrl->setCmd("");
            $this->ctrl->setCmdClass("adnscoringgui");
            $next_class = $this->ctrl->getNextClass();
        }

        // forward command to next gui class in control flow
        switch ($next_class) {
            case "adnscoringgui":
                include_once("./Services/ADN/ES/classes/class.adnScoringGUI.php");
                $ct_gui = new adnScoringGUI();
                $this->ctrl->forwardCommand($ct_gui);
                break;

            case "adncertificatescoringgui":
                include_once("./Services/ADN/ES/classes/class.adnCertificateScoringGUI.php");
                $ct_gui = new adnCertificateScoringGUI();
                $this->ctrl->forwardCommand($ct_gui);
                break;

            case "adnscorenotificationgui":
                include_once("./Services/ADN/ES/classes/class.adnScoreNotificationGUI.php");
                $sn_gui = new adnScoreNotificationGUI();
                $this->ctrl->forwardCommand($sn_gui);
                break;

            case "adnonlineanswersheetgui":
                include_once("./Services/ADN/ES/classes/class.adnOnlineAnswerSheetGUI.php");
                $as_gui = new adnOnlineAnswerSheetGUI();
                $this->ctrl->forwardCommand($as_gui);
                break;
        }

        adnBaseGUI::setHelpButton($this->ctrl->getCmdClass());
    }
}
