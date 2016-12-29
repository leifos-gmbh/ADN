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
	/**
	 * Execute command
	 */
	public function executeCommand()
	{
		global $ilCtrl, $lng, $tpl;

		// set page title
		$tpl->setTitle($lng->txt("adn_ep"));

		$next_class = $ilCtrl->getNextClass();
		$cmd = $ilCtrl->getCmd();

		if ($cmd == "processMenuItem")	// menu item triggered
		{
			// determine cmd and cmdClass from menu item
			include_once("./Services/ADN/UI/classes/class.adnMainMenuGUI.php");
			switch ($_GET["menu_item"])
			{
				// answer sheets
				case adnMainMenuGUI::EP_ILS:
					$ilCtrl->setCmdClass("adnexaminfolettergui");
					$ilCtrl->setCmd("listLetters");
					break;

				// examination events
				case adnMainMenuGUI::EP_EES:
					$ilCtrl->setCmdClass("adnexaminationeventgui");
					$ilCtrl->setCmd("listEvents");
					break;

				// examination candidates
				case adnMainMenuGUI::EP_ECS:
					$ilCtrl->setCmdClass("adnpreparationcandidategui");
					$ilCtrl->setCmd("listCandidates");
					break;

				// candidate assignments
				case adnMainMenuGUI::EP_CES:
					$ilCtrl->setCmdClass("adnassignmentgui");
					$ilCtrl->setCmd("listEvents");
					break;

				// answer sheets
				case adnMainMenuGUI::EP_ASS:
					$ilCtrl->setCmdClass("adnanswersheetgui");
					$ilCtrl->setCmd("listEvents");
					break;

				// invitations
				case adnMainMenuGUI::EP_INS:
					$ilCtrl->setCmdClass("adnexaminationinvitationgui");
					$ilCtrl->setCmd("listEvents");
					break;

				// attendance
				case adnMainMenuGUI::EP_ALS:
					$ilCtrl->setCmdClass("adnattendancegui");
					$ilCtrl->setCmd("listEvents");
					break;

				// test preparation
				case adnMainMenuGUI::EP_ACS:
					$ilCtrl->setCmdClass("adntestpreparationgui");
					$ilCtrl->setCmd("listEvents");
					break;
			}
			$next_class = $ilCtrl->getNextClass();
		}

		// If no next class is responsible for handling the
		// command, set the default class
		if ($next_class == "")
		{
			// default: information sheet overview
			$ilCtrl->setCmdClass("adnexaminfolettergui");
			$ilCtrl->setCmd("listLetters");
			$next_class = $ilCtrl->getNextClass();
		}

		// forward command to next gui class in control flow
		switch ($next_class)
		{
			case "adnexaminfolettergui":
				include_once("./Services/ADN/EP/classes/class.adnExamInfoLetterGUI.php");
				$is_gui = new adnExamInfoLetterGUI();
				$ilCtrl->forwardCommand($is_gui);
				break;

			case "adnexaminationeventgui":
				include_once("./Services/ADN/EP/classes/class.adnExaminationEventGUI.php");
				$ee_gui = new adnExaminationEventGUI();
				$ilCtrl->forwardCommand($ee_gui);
				break;

			case "adnpreparationcandidategui":
				include_once("./Services/ADN/EP/classes/class.adnPreparationCandidateGUI.php");
				$ct_gui = new adnPreparationCandidateGUI();
				$ilCtrl->forwardCommand($ct_gui);
				break;

			case "adnassignmentgui":
				include_once("./Services/ADN/EP/classes/class.adnAssignmentGUI.php");
				$ct_gui = new adnAssignmentGUI();
				$ilCtrl->forwardCommand($ct_gui);
				break;

			case "adnanswersheetgui":
				include_once("./Services/ADN/EP/classes/class.adnAnswerSheetGUI.php");
				$as_gui = new adnAnswerSheetGUI();
				$ilCtrl->forwardCommand($as_gui);
				break;

			case "adnexaminationinvitationgui":
				include_once("./Services/ADN/EP/classes/class.adnExaminationInvitationGUI.php");
				$inv_gui = new adnExaminationInvitationGUI();
				$ilCtrl->forwardCommand($inv_gui);
				break;

			case "adnattendancegui":
				include_once("./Services/ADN/EP/classes/class.adnAttendanceGUI.php");
				$att_gui = new adnAttendanceGUI();
				$ilCtrl->forwardCommand($att_gui);
				break;

			case "adntestpreparationgui":
				include_once("./Services/ADN/EP/classes/class.adnTestPreparationGUI.php");
				$att_gui = new adnTestPreparationGUI();
				$ilCtrl->forwardCommand($att_gui);
				break;
		}

		adnBaseGUI::setHelpButton($ilCtrl->getCmdClass());
	}
}

?>