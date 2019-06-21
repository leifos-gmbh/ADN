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
	/**
	 * Execute command
	 */
	public function executeCommand()
	{
		global $ilCtrl, $lng, $tpl;

		// set page title
		$tpl->setTitle($lng->txt("adn_es"));

		$next_class = $ilCtrl->getNextClass();
		$cmd = $ilCtrl->getCmd();

		if ($cmd == "processMenuItem")	// menu item triggered
		{
			// determine cmd and cmdClass from menu item
			include_once("./Services/ADN/UI/classes/class.adnMainMenuGUI.php");
			switch ($_GET["menu_item"])
			{
				// edit scoring
				case adnMainMenuGUI::ES_SCS:
					$ilCtrl->setCmdClass("adnscoringgui");
					$ilCtrl->setCmd("listEvents");
					break;

				// edit certificates
				case adnMainMenuGUI::ES_CTS:
					$ilCtrl->setCmdClass("adncertificatescoringgui");
					$ilCtrl->setCmd("listEvents");
					break;

				// score notification
				case adnMainMenuGUI::ES_SNS:
					$ilCtrl->setCmdClass("adnscorenotificationgui");
					$ilCtrl->setCmd("listEvents");
					break;

				// online answer sheets
				case adnMainMenuGUI::ES_OAS:
					$ilCtrl->setCmdClass("adnonlineanswersheetgui");
					$ilCtrl->setCmd("listEvents");
					break;
			}
			$next_class = $ilCtrl->getNextClass();
		}

		// If no next class is responsible for handling the
		// command, set the default class
		if ($next_class == "")
		{
			// default: scoring 
			$ilCtrl->setCmd("");
			$ilCtrl->setCmdClass("adnscoringgui");
			$next_class = $ilCtrl->getNextClass();
		}

		// forward command to next gui class in control flow
		switch ($next_class)
		{
			case "adnscoringgui":
				include_once("./Services/ADN/ES/classes/class.adnScoringGUI.php");
				$ct_gui = new adnScoringGUI();
				$ilCtrl->forwardCommand($ct_gui);
				break;

			case "adncertificatescoringgui":
				include_once("./Services/ADN/ES/classes/class.adnCertificateScoringGUI.php");
				$ct_gui = new adnCertificateScoringGUI();
				$ilCtrl->forwardCommand($ct_gui);
				break;

			case "adnscorenotificationgui":
				include_once("./Services/ADN/ES/classes/class.adnScoreNotificationGUI.php");
				$sn_gui = new adnScoreNotificationGUI();
				$ilCtrl->forwardCommand($sn_gui);
				break;

			case "adnonlineanswersheetgui":
				include_once("./Services/ADN/ES/classes/class.adnOnlineAnswerSheetGUI.php");
				$as_gui = new adnOnlineAnswerSheetGUI();
				$ilCtrl->forwardCommand($as_gui);
				break;
		}

		adnBaseGUI::setHelpButton($ilCtrl->getCmdClass());
	}
}

?>