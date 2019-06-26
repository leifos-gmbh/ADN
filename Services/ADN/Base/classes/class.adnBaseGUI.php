<?php
/* Copyright (c) 2010 Leifos, GPL, see docs/LICENSE */

include_once("./Services/ADN/Base/classes/class.adnPerm.php");

/**
 * ADN Base GUI class
 *
 * Base class for all GUI classes, calls the individual module GUI classes
 * Context sensitive help is implemented here to be reachable by all GUI classes
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id: class.adnBaseGUI.php 40192 2013-02-28 09:28:06Z jluetzen $
 *
 * @ingroup ServicesADN
 *
 * @ilCtrl_Calls adnBaseGUI: adnTrainingAdministrationGUI, adnExaminationDefinitionGUI
 * @ilCtrl_Calls adnBaseGUI: adnExaminationPreparationGUI, adnMasterDataGUI
 * @ilCtrl_Calls adnBaseGUI: adnCertifiedProfessionalGUI, adnExaminationScoringGUI
 * @ilCtrl_Calls adnBaseGUI: adnAdministrationGUI, adnStatisticsGUI, adnTestGUI
 * @ilCtrl_Calls adnBaseGUI: adnELearningGUI
 */
class adnBaseGUI
{
	protected static $help_done;

	/**
	 * Constructor
	 */
	public function __construct()
	{
		include_once "./Services/ADN/Base/classes/class.adnDBBase.php";

		// relative dates are not to be used
		ilDatePresentation::setUseRelativeDates(false);
	}

	/**
	 * Execute command
	 */
	public function executeCommand()
	{
		global $ilCtrl, $tpl, $lng, $ilClientIniFile,$ilUser;

		$lng->loadLanguageModule("adn");

		// set the standard template
		$tpl->getStandardTemplate();

		$next_class = $ilCtrl->getNextClass();
		$cmd = $ilCtrl->getCmd();

		// online test
		if ($_SESSION["adn_online_test"])
		{
			$next_class = "adntestgui";
			if ($ilCtrl->getNextClass() == "")
			{
				$ilCtrl->setCmdClass("adntestgui");
			}
		}

		// e-learning section
		if (is_object($ilClientIniFile))
		{
			if ($ilClientIniFile->readVariable("system", "ELEARNING_MODE") == "1" and
				$ilUser->getId() == ANONYMOUS_USER_ID)
			{
				$next_class = "adnelearninggui";
				if ($ilCtrl->getNextClass() == "")
				{
					$ilCtrl->setCmdClass("adnelearninggui");
				}
			}
		}

		// If no next class is responsible for handling the
		// command, set the default class
		// @todo: set this dependent on user role
		if ($next_class == "" && $cmd != "processMenuItem")
		{
			// default: training administration start screen
			$ilCtrl->setCmd("");
			$ilCtrl->setCmdClass("adntrainingadministrationgui");
			$next_class = $ilCtrl->getNextClass();
		}
		else if ($cmd == "processMenuItem" && $next_class != "adntestgui"
			&& $next_class != "adnelearninggui")	// menu item triggered
		{
			// extract responsible component and forward to component
			include_once("./Services/ADN/UI/classes/class.adnMainMenuGUI.php");
			$menu_item = explode("_", $_GET["menu_item"]);
			switch ($menu_item[0])
			{
				case adnMainMenuGUI::TA:
					$ilCtrl->setCmdClass("adntrainingadministrationgui");
					break;

				case adnMainMenuGUI::ED:
					$ilCtrl->setCmdClass("adnexaminationdefinitiongui");
					break;

				case adnMainMenuGUI::EP:
					$ilCtrl->setCmdClass("adnexaminationpreparationgui");
					break;

				case adnMainMenuGUI::CP:
					$ilCtrl->setCmdClass("adncertifiedprofessionalgui");
					break;

				case adnMainMenuGUI::ES:
					$ilCtrl->setCmdClass("adnexaminationscoringgui");
					break;

				case adnMainMenuGUI::MD:
					$ilCtrl->setCmdClass("adnmasterdatagui");
					break;

				case adnMainMenuGUI::AD:
					$ilCtrl->setCmdClass("adnadministrationgui");
					break;

				case adnMainMenuGUI::ST:
					$ilCtrl->setCmdClass("adnstatisticsgui");
					break;
			}
			$next_class = $ilCtrl->getNextClass();
		}

		// forward command to next gui class in control flow
		switch ($next_class)
		{
			case "adntrainingadministrationgui":
				include_once("./Services/ADN/TA/classes/class.adnTrainingAdministrationGUI.php");
				$ta_gui = new adnTrainingAdministrationGUI();
				$ilCtrl->forwardCommand($ta_gui);
				break;

			case "adnexaminationdefinitiongui":
				include_once("./Services/ADN/ED/classes/class.adnExaminationDefinitionGUI.php");
				$ed_gui = new adnExaminationDefinitionGUI();
				$ilCtrl->forwardCommand($ed_gui);
				break;

			case "adnexaminationpreparationgui":
				include_once("./Services/ADN/EP/classes/class.adnExaminationPreparationGUI.php");
				$ep_gui = new adnExaminationPreparationGUI();
				$ilCtrl->forwardCommand($ep_gui);
				break;

			case "adncertifiedprofessionalgui":
				include_once("./Services/ADN/CP/classes/class.adnCertifiedProfessionalGUI.php");
				$ta_gui = new adnCertifiedProfessionalGUI();
				$ilCtrl->forwardCommand($ta_gui);
				break;

			case "adnexaminationscoringgui":
				include_once("./Services/ADN/ES/classes/class.adnExaminationScoringGUI.php");
				$ta_gui = new adnExaminationScoringGUI();
				$ilCtrl->forwardCommand($ta_gui);
				break;

			case "adnmasterdatagui":
				include_once("./Services/ADN/MD/classes/class.adnMasterDataGUI.php");
				$ta_gui = new adnMasterDataGUI();
				$ilCtrl->forwardCommand($ta_gui);
				break;

			case "adnadministrationgui":
				include_once("./Services/ADN/AD/classes/class.adnAdministrationGUI.php");
				$ad_gui = new adnAdministrationGUI();
				$ilCtrl->forwardCommand($ad_gui);
				break;

			case "adnstatisticsgui":
				include_once("./Services/ADN/ST/classes/class.adnStatisticsGUI.php");
				$st_gui = new adnStatisticsGUI();
				$ilCtrl->forwardCommand($st_gui);
				break;

			case "adntestgui":
				include_once("./Services/ADN/EC/classes/class.adnTestGUI.php");
				$test_gui = new adnTestGUI();
				$ilCtrl->forwardCommand($test_gui);
				break;

			case "adnelearninggui":
				include_once("./Services/ADN/EL/classes/class.adnELearningGUI.php");
				$el_gui = new adnELearningGUI();
				$ilCtrl->forwardCommand($el_gui);
				break;
		}

		// output the screen
		$tpl->show();
	}


	/**
	 * Scale image (and keep aspect ratio)
	 *
	 * @param string $a_file
	 * @param int $a_max maximum dimension in pixel
	 * @return array (width, height)
	 */
	public static function resizeImage($a_file, $a_max = 50)
	{
		// workaound for #143
		if (!is_file($a_file))
		{
			return array("width" => $a_max, "height" => $a_max);
		}

		$img = getimagesize($a_file);
		$width = $img[0];
		$height = $img[1];
		if($width > $height && $width > $a_max)
		{
			$ratio = $width/$a_max;
			$width = $a_max;
			$height = (int)round($height/$ratio);
		}
		else if($height > $a_max)
		{
			$ratio = $height/$a_max;
			$height = $a_max;
			$width = (int)round($width/$ratio);
		}
		return array("width" => $width, "height" => $height);
	}

	/**
	 * Build (context sensitive) help button
	 *
	 * @param string $a_key
	 * @return string
	 */
	public static function setHelpButton($a_key)
	{
		global $ilCtrl, $lng, $tpl;

		if(self::$help_done)
		{
			return;
		}

		// remove adn...gui
		$key = substr($a_key, 3, -3);

		$map = array(

			// ta
			"trainingprovider" => "schulungsveranstalter",
			"trainingevent" => "schulungstermine",
			"instructor" => "dozentenbearbeiten",
			"trainingfacility" => "schulungsortebearbeiten",
			"informationletter" => "merkbltter",
			"areaofexpertise" => "fachgebiete",

			// ed
			"objective" => "prfungsziele",
			"subobjective" => "mcprfungsziele",
			"questiontargetnumbers" => "anzahlzugenerierenderfragen",
			"mcquestion" => "mcprfungsfragenverwalten",
			"casequestion" => "kasusprfungsfragen",
			"goodrelatedanswer" => "kasusprfungsfragebearbeiten",
			"case" => "situationsbeschreibungen",
			"license" => "zulassungszeugnisse",
			"goodintransit" => "stoffe",
			"goodintransitcategory" => "stoffkategorienverwalten",

			// ep
			"examinfoletter" => "merkbltterundantrge",
			"examinationevent" => "prfungstermine",
			"preparationcandidate" => "prfungskandidaten",
			"assignment" => "kandidatentermine",
			"examinationinvitation" => "einladungen",
			"answersheet" => "prfungsbgen",
			"attendance" => "teilnahmelisten",
			"testpreparation" => "onlineprfung",

			// es
			"scoring" => "korrekturen",
			"certificatescoring" => "adnbescheinigungen",
			"scorenotification" => "antwortschreiben",
			"onlineanswersheet" => "onlineantwortbgen",

			// cp
			"certificate" => "sachkundigebescheinigungen",
			"certifiedprofessionaldata" => "sachkundigepersonendaten",
			"certifiedprofessionaldirectory" => "verzeichnisdersachkundigen",

			// st
			"statistics" => "statistikenanzeigen",

			// md
			"wmo" => "wsd",
			"country" => "lnder",

			// ad
			"maintenance" => "wartungsmodus",
			"character" => "sonderzeichen",
			"user" => "systembenutzer",
			"mcquestionexport" => "fragenkatalogexportierenimportieren"

			);

		if(!array_key_exists($key, $map))
		{
			return;
		}

		self::$help_done = true;
		
		// current version of DocToHelp shortens file names to 20 chars
		$filename = substr($map[$key], 0, 20);
		
		$path = "Services/ADN/Manual/default.htm#!Documents/".$filename.".htm";
		$link = "<a href=\"".$path."\" target=\"blank\">".$lng->txt("help")."</a>";

		$GLOBALS["help_link"] = $path;

		//$tpl->setCurrentBlock("adn_help");
		//$tpl->setVariable("ADN_HELP_BUTTON", $link);
		//$tpl->parseCurrentBlock();
	}
}

?>