<?php
/* Copyright (c) 2010 Leifos, GPL, see docs/LICENSE */

/**
 * Certified professional GUI base class
 *
 * Calls the module GUI classes as a controller
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @version $Id: class.adnCertifiedProfessionalGUI.php 27883 2011-02-27 19:30:41Z akill $
 *
 * @ingroup ServicesADN
 *
 * @ilCtrl_Calls adnCertifiedProfessionalGUI: adnCertificateGUI
 * @ilCtrl_Calls adnCertifiedProfessionalGUI: adnCertifiedProfessionalDirectoryGUI
 * @ilCtrl_Calls adnCertifiedProfessionalGUI: adnCertifiedProfessionalDataGUI
 * cr-008 start
 * @ilCtrl_Calls adnCertifiedProfessionalGUI: adnPersonalDataMaintenanceGUI
 * cr-008 end
 */
class adnCertifiedProfessionalGUI
{
	/**
	 * Execute command
	 */
	public function executeCommand()
	{
		global $ilCtrl, $lng, $tpl;

		// set page title
		$tpl->setTitle($lng->txt("adn_cp"));

		$next_class = $ilCtrl->getNextClass();
		$cmd = $ilCtrl->getCmd();

		if ($cmd == "processMenuItem")	// menu item triggered
		{
			// determine cmd and cmdClass from menu item
			include_once("./Services/ADN/UI/classes/class.adnMainMenuGUI.php");
			switch ($_GET["menu_item"])
			{
				// list adn certificates
				case adnMainMenuGUI::CP_CTS:
					$ilCtrl->setCmdClass("adncertificategui");
					$ilCtrl->setCmd("listCertificates");
					break;

				// create directory
				case adnMainMenuGUI::CP_DIR:
					$ilCtrl->setCmdClass("adncertifiedprofessionaldirectorygui");
					$ilCtrl->setCmd("createDirectoryForm");
					break;

				// professionals
				case adnMainMenuGUI::CP_CPR:
					$ilCtrl->setCmdClass("adncertifiedprofessionaldatagui");
					$ilCtrl->setCmd("listProfessionals");
					break;

				// cr-008 start
				case adnMainMenuGUI::CP_PDM:
					$ilCtrl->setCmdClass("adnpersonaldatamaintenancegui");
					$ilCtrl->setCmd("listPersonalData");
					break;
				// cr-008 end

			}
			$next_class = $ilCtrl->getNextClass();
		}

		// If no next class is responsible for handling the
		// command, set the default class
		if ($next_class == "")
		{
			// default: certificates overview
			$ilCtrl->setCmd("");
			$ilCtrl->setCmdClass("adncertificategui");
			$next_class = $ilCtrl->getNextClass();
		}

		// forward command to next gui class in control flow
		switch ($next_class)
		{
			case "adncertificategui":
				include_once("./Services/ADN/CP/classes/class.adnCertificateGUI.php");
				$ct_gui = new adnCertificateGUI();
				$ilCtrl->forwardCommand($ct_gui);
				break;

			case "adncertifiedprofessionaldirectorygui":
				include_once("./Services/ADN/CP/classes/class.adnCertifiedProfessionalDirectoryGUI.php");
				$dir_gui = new adnCertifiedProfessionalDirectoryGUI();
				$ilCtrl->forwardCommand($dir_gui);
				break;

			case "adncertifiedprofessionaldatagui":
				include_once("./Services/ADN/CP/classes/class.adnCertifiedProfessionalDataGUI.php");
				$dir_gui = new adnCertifiedProfessionalDataGUI();
				$ilCtrl->forwardCommand($dir_gui);
				break;

			// cr-008 start
			case "adnpersonaldatamaintenancegui":
				include_once("./Services/ADN/AD/classes/class.adnPersonalDataMaintenanceGUI.php");
				$pdm_gui = new adnPersonalDataMaintenanceGUI();
				$ilCtrl->forwardCommand($pdm_gui);
				break;
			// cr-008 end

		}

		adnBaseGUI::setHelpButton($ilCtrl->getCmdClass());
	}
}

?>