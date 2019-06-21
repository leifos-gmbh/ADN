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
	/**
	 * Execute command
	 */
	public function executeCommand()
	{
		global $ilCtrl, $lng, $tpl;

		// set page title
		$tpl->setTitle($lng->txt("adn_ta"));

		$next_class = $ilCtrl->getNextClass();
		$cmd = $ilCtrl->getCmd();

		// menu item triggered
		if ($cmd == "processMenuItem")
		{
			// determine cmd and cmdClass from menu item
			include_once("./Services/ADN/UI/classes/class.adnMainMenuGUI.php");
			switch ($_GET["menu_item"])
			{
				// list training providers
				case adnMainMenuGUI::TA_TPS:
					$ilCtrl->setCmdClass("adntrainingprovidergui");
					$ilCtrl->setCmd("listTrainingProviders");
					break;

				// list training providers
				case adnMainMenuGUI::TA_TES:
					$ilCtrl->setCmdClass("adntrainingeventgui");
					$ilCtrl->setCmd("listTrainingEvents");
					break;

				// list information letters
				case adnMainMenuGUI::TA_ILS:
					$ilCtrl->setCmdClass("adninformationlettergui");
					$ilCtrl->setCmd("listInformationLetters");
					break;

				// list areas of expertise
				case adnMainMenuGUI::TA_AES:
					$ilCtrl->setCmdClass("adnareaofexpertisegui");
					$ilCtrl->setCmd("listAreasOfExpertise");
					break;
			}
			$next_class = $ilCtrl->getNextClass();
		}
		else if ($next_class == "")
		{
			// If no next class is responsible for handling the
			// command, set the default class
			// default: training provider overview
			$ilCtrl->setCmd("");
			$ilCtrl->setCmdClass("adntrainingprovidergui");
			$next_class = $ilCtrl->getNextClass();
		}


		// forward command to next gui class in control flow
		switch ($next_class)
		{
			case "adntrainingprovidergui":
				include_once("./Services/ADN/TA/classes/class.adnTrainingProviderGUI.php");
				$tp_gui = new adnTrainingProviderGUI();
				$ilCtrl->forwardCommand($tp_gui);
				break;

			case "adntrainingeventgui":
				include_once("./Services/ADN/TA/classes/class.adnTrainingEventGUI.php");
				$tp_gui = new adnTrainingEventGUI();
				$ilCtrl->forwardCommand($tp_gui);
				break;

			case "adninformationlettergui":
				include_once("./Services/ADN/TA/classes/class.adnInformationLetterGUI.php");
				$il_gui = new adnInformationLetterGUI();
				$ilCtrl->forwardCommand($il_gui);
				break;

			case "adnareaofexpertisegui":
				include_once("./Services/ADN/TA/classes/class.adnAreaOfExpertiseGUI.php");
				$ae_gui = new adnAreaOfExpertiseGUI();
				$ilCtrl->forwardCommand($ae_gui);
				break;
		}

		adnBaseGUI::setHelpButton($ilCtrl->getCmdClass());
	}
}

?>