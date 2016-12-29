<?php
/* Copyright (c) 2010 Leifos, GPL, see docs/LICENSE */

/**
 * ADN main menu GUI class
 *
 * Defines the main menu entries and order
 *
 * @author Alex Killing <killing@leifos.de>
 * @version $Id: class.adnMainMenuGUI.php 27883 2011-02-27 19:30:41Z akill $
 *
 * @ingroup ServicesADN
 */
class adnMainMenuGUI
{
	// training administration
	const TA = "ta";
	const TA_TPS = "ta_tps";	// training providers
	const TA_TES = "ta_tes";	// training events
	const TA_ILS = "ta_ils";	// information letters
	const TA_AES = "ta_aes";	// expertise

	// examination definition
	const ED = "ed";
	const ED_OBS = "ed_obs";	// objectives
	const ED_EQS = "ed_eqs";	// questions
	const ED_NQS = "ed_nqs";	// target number of questions
	const ED_CAS = "ed_cas";	// case
	const ED_LIC = "ed_lic";	// license
	const ED_GTS = "ed_gts";	// goods

	// exam preparation
	const EP = "ep";
	const EP_ILS = "ep_ils";	// information letters
	const EP_EES = "ep_ees";	// examination events
	const EP_ECS = "ep_ecs";	// candidates
	const EP_CES = "ep_ces";	// candidates/events
	const EP_INS = "ep_ins";	// invitations
	const EP_ASS = "ep_ass";	// answer sheets
	const EP_ACS = "ep_acs";	// access codes (online)
	const EP_ALS = "ep_als";	// attendance

	// examination scoring
	const ES = "es";
	const ES_SCS = "es_scs";	// scoring
	const ES_CTS = "es_cts";	// certificates
	const ES_SNS = "es_sns";	// notification
	const ES_OAS = "es_oas";	// online answer sheets
	
	// certified professionals
	const CP = "cp";
	const CP_CTS = "cp_cts";	// certificates
	const CP_DIR = "cp_dir";	// directory
	const CP_CPR = "cp_cpr";	// professionals

	// statistics
	const ST = "st";
	const ST_EXS = "st_exs";	// exams
	const ST_ERS = "st_ers";	// extensions, refreshed
	const ST_EES = "st_ees";	// extensions, experience
	const ST_COS = "st_cos";	// certificates, other applications
	const ST_TNS = "st_tns";	// certificates, total
	const ST_TGC = "st_tgc";	// certificates, gas/chemicals

	// master data
	const MD = "md";
	const MD_WOS = "md_wos";	// wmos
	const MD_CNS = "md_cns";	// countries

	// administration
	const AD = "ad";	
	const AD_MNT = "ad_mnt";	// maintenance mode
	const AD_CHR = "ad_chr";	// special characters
	const AD_USR = "ad_usr";	// user
	const AD_MCX = "ad_mcx";	// export mc questions
	const AD_ICP = "ad_icp";	// import professionals


	// template
	protected $tpl;

	/**
	 * Constructor
	 */
	function __construct()
	{
		global $lng;

		$lng->loadLanguageModule("adn");

		// get template object
		$this->tpl = new ilTemplate("tpl.adn_main_menu.html", true, true, "Services/ADN/UI");
	}

	/**
	 * Get menu
	 *
	 * @return array
	 */
	function getAllMenuItems()
	{
		$items = array();

		include_once "Services/ADN/Base/classes/class.adnPerm.php";
		
		if(adnPerm::check(adnPerm::TA, adnPerm::READ))
		{
			$items[adnMainMenuGUI::TA] = array(
					adnMainMenuGUI::TA_TPS,
					adnMainMenuGUI::TA_TES,
					adnMainMenuGUI::TA_ILS,
					adnMainMenuGUI::TA_AES
				);
		}

		if(adnPerm::check(adnPerm::ED, adnPerm::READ))
		{
			$items[adnMainMenuGUI::ED] = array(
					adnMainMenuGUI::ED_OBS,
					adnMainMenuGUI::ED_NQS,
					adnMainMenuGUI::ED_EQS,
					adnMainMenuGUI::ED_CAS,
					adnMainMenuGUI::ED_LIC,
					adnMainMenuGUI::ED_GTS,
				);
		}


		if(adnPerm::check(adnPerm::EP, adnPerm::READ))
		{
			$items[adnMainMenuGUI::EP] = array(
					adnMainMenuGUI::EP_ILS,
					adnMainMenuGUI::EP_EES,
					adnMainMenuGUI::EP_ECS,
					adnMainMenuGUI::EP_CES,
					adnMainMenuGUI::EP_INS,
					adnMainMenuGUI::EP_ASS,
					adnMainMenuGUI::EP_ALS,
					adnMainMenuGUI::EP_ACS
				);
		}

		if(adnPerm::check(adnPerm::ES, adnPerm::READ))
		{
			$items[adnMainMenuGUI::ES] = array(
					adnMainMenuGUI::ES_SCS,
					adnMainMenuGUI::ES_CTS,
					adnMainMenuGUI::ES_SNS,
					adnMainMenuGUI::ES_OAS
				);
		}

		if(adnPerm::check(adnPerm::CP, adnPerm::READ))
		{
			$items[adnMainMenuGUI::CP] = array(
					adnMainMenuGUI::CP_CTS,
					adnMainMenuGUI::CP_CPR,
					adnMainMenuGUI::CP_DIR
				);
		}

		if(adnPerm::check(adnPerm::ST, adnPerm::READ))
		{
			$items[adnMainMenuGUI::ST] = array(
					adnMainMenuGUI::ST_EXS,
					adnMainMenuGUI::ST_ERS,
					adnMainMenuGUI::ST_EES,
					adnMainMenuGUI::ST_COS,
					adnMainMenuGUI::ST_TGC,
					adnMainMenuGUI::ST_TNS
				);
		}

		// MD and AD have been combined into 1 main menu entry
		if(adnPerm::check(adnPerm::MD, adnPerm::READ) || adnPerm::check(adnPerm::AD, adnPerm::READ))
		{
			$items[adnMainMenuGUI::MD] = array();
			
			if(adnPerm::check(adnPerm::MD, adnPerm::READ))
			{
				$items[adnMainMenuGUI::MD][] = adnMainMenuGUI::MD_WOS;
				$items[adnMainMenuGUI::MD][] = adnMainMenuGUI::MD_CNS;
			}

			if(adnPerm::check(adnPerm::AD, adnPerm::READ))
			{
				$items[adnMainMenuGUI::MD][] = adnMainMenuGUI::AD_MNT;
				$items[adnMainMenuGUI::MD][] = adnMainMenuGUI::AD_CHR;
				$items[adnMainMenuGUI::MD][] = adnMainMenuGUI::AD_USR;
				$items[adnMainMenuGUI::MD][] = adnMainMenuGUI::AD_MCX;
				$items[adnMainMenuGUI::MD][] = adnMainMenuGUI::AD_ICP;
			}
		}

		return $items;
	}

	/**
	 * Get menues
	 *
	 * @param string $a_submenu
	 * @return array
	 */
	function getSubmenuItems($a_submenu)
	{
		global $lng, $ilCtrl;

		$all = $this->getAllMenuItems();

		return $all[$a_submenu];
	}
	
	/**
	 * Set menu item active
	 * 
	 * @param string $a_active
	 */
	function setActive($a_active)
	{
		$this->active = $a_active;
	}

	/**
	 * Get main menu HTML
	 *
	 * @return string HTML code
	 */
	function getHTML()
	{
		global $rbacsystem, $lng, $tree, $ilUser, $ilSetting;

		// no menu during online tests
		if ($_SESSION["adn_online_test"])
		{
			return "";
		}

		foreach ($this->getAllMenuItems() as $menu => $items)
		{
			$this->tpl->setCurrentBlock("submenu");
			$this->tpl->setVariable("SUBMENU",
				$this->getSubMenu($menu));
			$this->tpl->parseCurrentBlock();
		}
		
		// account information
		if ($_SESSION["AccountId"] != ANONYMOUS_USER_ID)
		{
			$this->tpl->setCurrentBlock("userisloggedin");
			$this->tpl->setVariable("TXT_LOGIN_AS",$lng->txt("login_as"));
			$this->tpl->setVariable("TXT_LOGOUT2",$lng->txt("logout"));
			$this->tpl->setVariable("LINK_LOGOUT2", "logout.php?lang=".
				$ilUser->getCurrentLanguage());
			$this->tpl->setVariable("USERNAME",$ilUser->getFullname());
			$this->tpl->parseCurrentBlock();

			$this->tpl->setVariable("TXT_LOGOUT", $lng->txt("logout"));
		}
		
		$this->tpl->setVariable("LOCATION_STYLESHEET", ilUtil::getStyleSheetLocation());
		$this->tpl->setVariable("HEADER_BG_IMAGE", ilUtil::getImagePath("HeaderBackground.gif"));

		$this->tpl->parseCurrentBlock();

		return $this->tpl->get();
	}

	/**
	 * Get submenu
	 *
	 * @param string $a_menu
	 * @return string
	 */
	function getSubMenu($a_menu)
	{
		global $lng, $rbacsystem, $ilSetting;

		include_once("./Services/UIComponent/AdvancedSelectionList/".
			"classes/class.ilAdvancedSelectionListGUI.php");
		$selection = new ilAdvancedSelectionListGUI();
		$selection->setFormSelectMode("url_ref_id", "ilNavHistorySelect", true,
			"goto.php?target=navi_request", "ilNavHistory", "ilNavHistoryForm",
			"_top", $lng->txt("go"), "ilNavHistorySubmit");

		if($a_menu != "md")
		{
			$caption = $lng->txt("adn_".$a_menu);
		}
		else
		{
			$caption = $lng->txt("adn_ad");
		}
		$selection->setListTitle($caption);

		$selection->setId("menu_".$a_menu);
		$selection->setSelectionHeaderClass("MMInactive");
		$selection->setHeaderIcon(ilAdvancedSelectionListGUI::NO_ICON);
		$selection->setItemLinkClass("small");
		$selection->setUseImages(false);

		$maintenance = $ilSetting->get("adn_maintenance");

		$sub_menu = $this->getSubMenuItems($a_menu);
		foreach ($sub_menu as $item)
		{
			// disable answer sheets in maintenance mode
			if($maintenance && $item == "ep_ass")
			{
				continue;
			}

			$selection->addItem(
				$lng->txt("adn_".$item), $item,
				"ilias.php?baseClass=adnBaseGUI&amp;cmd=processMenuItem&amp;".
				"menu_item=".$item, "", "", "_top");
		}

		// add ILIAS administration to administration submenu
		if ($a_menu == "md" && $rbacsystem->checkAccess("visible,read", SYSTEM_FOLDER_ID))
		{
			$selection->addItem(
				"ILIAS ".$lng->txt("administration"), "il_adm",
				"ilias.php?baseClass=ilAdministrationGUI",
				 "", "", "_top");
		}

		$html = $selection->getHTML();

		return $html;
	}
}

?>