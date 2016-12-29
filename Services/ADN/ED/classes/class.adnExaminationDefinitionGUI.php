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
	/**
	 * Execute command
	 */
	public function executeCommand()
	{
		global $ilCtrl, $lng, $tpl;

		// set page title
		$tpl->setTitle($lng->txt("adn_ed"));

		$next_class = $ilCtrl->getNextClass();
		$cmd = $ilCtrl->getCmd();

		// menu item triggered?
		if ($cmd == "processMenuItem")
		{
			// determine cmd and cmdClass from menu item
			include_once("./Services/ADN/UI/classes/class.adnMainMenuGUI.php");
			switch ($_GET["menu_item"])
			{
				// list objectives
				case adnMainMenuGUI::ED_OBS:
					$ilCtrl->setCmdClass("adnobjectivegui");
					$ilCtrl->setCmd("listMCObjectives");
					break;

				// list (mc) questions
				case adnMainMenuGUI::ED_EQS:
					$ilCtrl->setCmdClass("adnmcquestiongui");
					$ilCtrl->setCmd("listMCQuestions");
					break;

				// number of questions per objective
				case adnMainMenuGUI::ED_NQS:
					$ilCtrl->setCmdClass("adnquestiontargetnumbersgui");
					$ilCtrl->setCmd("listTargets");
					break;

				// goods in transit
				case adnMainMenuGUI::ED_GTS:
					$ilCtrl->setCmdClass("adngoodintransitgui");
					$ilCtrl->setCmd("listGasGoods");
					break;

				// cases
				case adnMainMenuGUI::ED_CAS:
					$ilCtrl->setCmdClass("adncasegui");
					$ilCtrl->setCmd("editCase");
					break;

				// licenses
				case adnMainMenuGUI::ED_LIC:
					$ilCtrl->setCmdClass("adnlicensegui");
					$ilCtrl->setCmd("listLicenses");
					break;
			}
			$next_class = $ilCtrl->getNextClass();
		}
		else if ($next_class == "")
		{
			// If no next class is responsible for handling the
			// command, set the default class

			// default: objectives overview
			$ilCtrl->setCmd("");
			$ilCtrl->setCmdClass("adnobjectivegui");
			$next_class = $ilCtrl->getNextClass();
		}

		// forward command to next gui class in control flow
		switch ($next_class)
		{
			case "adnobjectivegui":
				include_once("./Services/ADN/ED/classes/class.adnObjectiveGUI.php");
				$ob_gui = new adnObjectiveGUI();
				$ilCtrl->forwardCommand($ob_gui);
				break;

			case "adnmcquestiongui":
				include_once("./Services/ADN/ED/classes/class.adnMCQuestionGUI.php");
				$mc_gui = new adnMCQuestionGUI();
				$ilCtrl->forwardCommand($mc_gui);
				break;

			case "adncasequestiongui":
				include_once("./Services/ADN/ED/classes/class.adnCaseQuestionGUI.php");
				$case_gui = new adnCaseQuestionGUI();
				$ilCtrl->forwardCommand($case_gui);
				break;

			case "adngoodintransitgui":
				include_once("./Services/ADN/ED/classes/class.adnGoodInTransitGUI.php");
				$good_gui = new adnGoodInTransitGUI();
				$ilCtrl->forwardCommand($good_gui);
				break;

			case "adnquestiontargetnumbersgui":
				include_once("./Services/ADN/ED/classes/class.adnQuestionTargetNumbersGUI.php");
				$tgt_gui = new adnQuestionTargetNumbersGUI();
				$ilCtrl->forwardCommand($tgt_gui);
				break;

			case "adncasegui":
				include_once("./Services/ADN/ED/classes/class.adnCaseGUI.php");
				$case_gui = new adnCaseGUI();
				$ilCtrl->forwardCommand($case_gui);
				break;

			case "adnlicensegui":
				include_once("./Services/ADN/ED/classes/class.adnLicenseGUI.php");
				$lic_gui = new adnLicenseGUI();
				$ilCtrl->forwardCommand($lic_gui);
				break;
		}

		adnBaseGUI::setHelpButton($ilCtrl->getCmdClass());
	}
}

?>