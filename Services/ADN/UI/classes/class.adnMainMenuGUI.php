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
    const AD_CARD = 'ad_card';  // card administration settings

    // cr-008 start
    const CP_PDM = "cp_pdm";	// maintenance personal data
    // cr-008 end


    /**
     * @var ilTemplate
     */
    protected $tpl;

    /**
     * Constructor
     */
    public function __construct($a_mm_gui)
    {
        global $lng, $tpl;

        $this->mm_gui = $a_mm_gui;


        if ($a_mm_gui != null) {
            $this->tpl = $this->mm_gui->tpl;
            $lng->loadLanguageModule("adn");
            $tpl->addCss("./Services/ADN/UI/css/adn.css");
        }
    }

    /**
     * Get menu
     *
     * @return array
     */
    public function getAllMenuItems()
    {
        $items = array();

        include_once "Services/ADN/Base/classes/class.adnPerm.php";
        
        if (adnPerm::check(adnPerm::TA, adnPerm::READ)) {
            $items[adnMainMenuGUI::TA] = array(
                    adnMainMenuGUI::TA_TPS,
                    adnMainMenuGUI::TA_TES,
                    adnMainMenuGUI::TA_ILS,
                    adnMainMenuGUI::TA_AES
                );
        }

        if (adnPerm::check(adnPerm::ED, adnPerm::READ)) {
            $items[adnMainMenuGUI::ED] = array(
                    adnMainMenuGUI::ED_OBS,
                    adnMainMenuGUI::ED_NQS,
                    adnMainMenuGUI::ED_EQS,
                    adnMainMenuGUI::ED_CAS,
                    adnMainMenuGUI::ED_LIC,
                    adnMainMenuGUI::ED_GTS,
                );
        }


        if (adnPerm::check(adnPerm::EP, adnPerm::READ)) {
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

        if (adnPerm::check(adnPerm::ES, adnPerm::READ)) {
            $items[adnMainMenuGUI::ES] = array(
                    adnMainMenuGUI::ES_SCS,
                    adnMainMenuGUI::ES_CTS,
                    adnMainMenuGUI::ES_SNS,
                    adnMainMenuGUI::ES_OAS
                );
        }

        if (adnPerm::check(adnPerm::CP, adnPerm::READ)) {
            $items[adnMainMenuGUI::CP] = array(
                    adnMainMenuGUI::CP_CTS,
                    adnMainMenuGUI::CP_CPR,
                    adnMainMenuGUI::CP_DIR,
                    // cr-008 start
                    adnMainMenuGUI::CP_PDM
                    // cr-008 end
                );
        }

        if (adnPerm::check(adnPerm::ST, adnPerm::READ)) {
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
        if (adnPerm::check(adnPerm::MD, adnPerm::READ) || adnPerm::check(adnPerm::AD, adnPerm::READ)) {
            $items[adnMainMenuGUI::MD] = array();
            
            if (adnPerm::check(adnPerm::MD, adnPerm::READ)) {
                $items[adnMainMenuGUI::MD][] = adnMainMenuGUI::MD_WOS;
                $items[adnMainMenuGUI::MD][] = adnMainMenuGUI::MD_CNS;
            }

            if (adnPerm::check(adnPerm::AD, adnPerm::READ)) {
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
    public function getSubmenuItems($a_submenu)
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
    public function setActive($a_active)
    {
        $this->active = $a_active;
    }

    /**
     * Get HTML
     *
     * @param
     * @return
     */
    public function getHTML()
    {
        global $lng, $ilias;



        $this->tpl->setVariable("MAIN_MENU_LIST_ENTRIES", $this->getMenuEntries());

        $link_dir = (defined("ILIAS_MODULE"))
            ? "../"
            : "";

        include_once "Services/jQuery/classes/class.iljQueryUtil.php";
        iljQueryUtil::initjQuery();

        include_once 'Services/MediaObjects/classes/class.ilPlayerUtil.php';
        ilPlayerUtil::initMediaElementJs();


        include_once("./Modules/SystemFolder/classes/class.ilObjSystemFolder.php");
        $header_top_title = ilObjSystemFolder::_getHeaderTitle();
        if (trim($header_top_title) != "" && $this->tpl->blockExists("header_top_title")) {
            $this->tpl->setCurrentBlock("header_top_title");
            $this->tpl->setVariable("TXT_HEADER_TITLE", $header_top_title);
            $this->tpl->parseCurrentBlock();
        }

        if ($GLOBALS["help_link"] != "") {
            if ($this->tpl->blockExists("adn_help")) {
                $this->tpl->setCurrentBlock("adn_help");
                $this->tpl->setVariable("ADN_TXT_HELP", $lng->txt("help"));
                $this->tpl->setVariable("ADN_LINK_HELP", $GLOBALS["help_link"]);
                $this->tpl->parseCurrentBlock();
            }
        }

        $this->tpl->setCurrentBlock("userisloggedin");
        $this->tpl->setVariable("TXT_LOGIN_AS", $lng->txt("login_as"));
        $user_img_src = $ilias->account->getPersonalPicturePath("small", true);
        $user_img_alt = $ilias->account->getFullname();
        $this->tpl->setVariable("USER_IMG", ilUtil::img($user_img_src, $user_img_alt));
        #$this->tpl->setVariable("USR_LINK_PROFILE", "ilias.php?baseClass=ilPersonalDesktopGUI&cmd=jumpToProfile");
        #$this->tpl->setVariable("USR_TXT_PROFILE", $lng->txt("personal_profile"));
        #$this->tpl->setVariable("USR_LINK_SETTINGS", "ilias.php?baseClass=ilPersonalDesktopGUI&cmd=jumpToSettings");
        #$this->tpl->setVariable("USR_TXT_SETTINGS", $lng->txt("personal_settings"));
        $this->tpl->setVariable("TXT_LOGOUT2", $lng->txt("logout"));
        $this->tpl->setVariable("LINK_LOGOUT2", $link_dir . "logout.php?lang=" . $ilias->account->getCurrentLanguage());
        $this->tpl->setVariable("USERNAME", $ilias->account->getFullname());
        
        foreach ($GLOBALS['rbacreview']->getGlobalRoles() as $gr) {
            if (
                $GLOBALS['rbacreview']->isAssigned(
                    $GLOBALS['ilUser']->getId(),
                    $gr
                )) {
                $this->tpl->setVariable('USER_ROLE', ilObject::_lookupTitle($gr));
                break;
            }
        }
        
        $this->tpl->setVariable("LOGIN", $ilias->account->getLogin());
        $this->tpl->setVariable("MATRICULATION", $ilias->account->getMatriculation());
        $this->tpl->setVariable("EMAIL", $ilias->account->getEmail());
        $this->tpl->parseCurrentBlock();

        $this->tpl->setVariable("LOCATION_STYLESHEET", ilUtil::getStyleSheetLocation());


        $this->tpl->setVariable("TXT_MAIN_MENU", $lng->txt("main_menu"));

        $this->tpl->parseCurrentBlock();

        return $this->tpl->get();
    }


    /**
     * Get main menu HTML
     *
     * @return string HTML code
     */
    public function getMenuEntries()
    {
        global $rbacsystem, $lng, $tree, $ilUser, $ilSetting;

        // no menu during online tests
        if ($_SESSION["adn_online_test"]) {
            return "";
        }

        $mm_tpl = new ilTemplate("tpl.adn_main_menu.html", true, true, "Services/ADN/UI");

        foreach ($this->getAllMenuItems() as $menu => $items) {
            $this->renderSubMenu($mm_tpl, $menu);
        }
        return $mm_tpl->get();



        $tpl->setCurrentBlock("cust_menu");
        $tpl->setVariable(
            "TXT_CUSTOM",
            lfCustomMenu::lookupTitle("it", $menu["id"], $ilUser->getLanguage(), true)
        );
        $tpl->setVariable("MM_CLASS", "MMInactive");

        if (is_file("./templates/default/images/mm_down_arrow.png")) {
            $tpl->setVariable("ARROW_IMG", ilUtil::getImagePath("mm_down_arrow.png"));
        } else {
            $tpl->setVariable("ARROW_IMG", ilUtil::getImagePath("mm_down_arrow.gif"));
        }
        $tpl->setVariable("CUSTOM_CONT_OV", $gl->getHTML());
        $tpl->setVariable("MM_ID", $menu["id"]);
        $tpl->parseCurrentBlock();
        $tpl->setCurrentBlock("c_item");
        $tpl->parseCurrentBlock();
    }

    /**
     * Rebder sub menu
     *
     * @param $a_mm_tpl
     * @param $a_menu
     */
    public function renderSubMenu($a_mm_tpl, $a_menu)
    {
        global $lng, $rbacsystem, $ilSetting;

        $a_mm_tpl->setCurrentBlock("submenu");

        include_once("./Services/UIComponent/GroupedList/classes/class.ilGroupedListGUI.php");
        $gl = new ilGroupedListGUI();
        $gl->setAsDropDown(true);

        if ($a_menu != "md") {
            $caption = $lng->txt("adn_" . $a_menu);
        } else {
            $caption = $lng->txt("adn_ad");
        }
        $a_mm_tpl->setVariable("TXT_SUBMENU", $caption);
        $a_mm_tpl->setVariable("SUBMENU_ID", "menu_" . $a_menu);

        $maintenance = $ilSetting->get("adn_maintenance");

        $sub_menu = $this->getSubMenuItems($a_menu);
        foreach ($sub_menu as $item) {
            // disable answer sheets in maintenance mode
            if ($maintenance && $item == "ep_ass") {
                continue;
            }

            $gl->addEntry(
                $lng->txt("adn_" . $item),
                "ilias.php?baseClass=adnBaseGUI&amp;cmd=processMenuItem&amp;" .
                "menu_item=" . $item
            );
        }

        // add ILIAS administration to administration submenu
        if ($a_menu == "md" && $rbacsystem->checkAccess("visible,read", SYSTEM_FOLDER_ID)) {
            global $tree;

            //$adm_nodes = $tree->getChilds(SYSTEM_FOLDER_ID);
            //var_dump($adm_nodes); exit;
            $gl->addEntry(
                "ILIAS " . $lng->txt("administration"),
                "ilias.php?baseClass=ilAdministrationGUI&cmd=jump&ref_id=" . SYSTEM_FOLDER_ID
            );
        }

        $a_mm_tpl->setVariable("SUBMENU", $gl->getHTML());
        $a_mm_tpl->parseCurrentBlock();
    }

    /**
     * Add admin menu to left nav bar
     *
     * @param
     */
    public function addAdminMenu()
    {
        global $tpl, $tree, $rbacsystem, $lng;

        include_once("./Services/UIComponent/GroupedList/classes/class.ilGroupedListGUI.php");
        $gl = new ilGroupedListGUI();

        $objects = $tree->getChilds(SYSTEM_FOLDER_ID);
        foreach ($objects as $object) {
            $new_objects[$object["title"] . ":" . $object["child"]]
                = $object;
            //have to set it manually as translation type of main node cannot be "sys" as this type is a orgu itself.
            if ($object["type"] == "orgu") {
                $new_objects[$object["title"] . ":" . $object["child"]]["title"] = $lng->txt("obj_orgu");
            }
        }

        // add entry for switching to repository admin
        // note: please see showChilds methods which prevents infinite look
        $new_objects[$lng->txt("repository_admin") . ":" . ROOT_FOLDER_ID] =
            array(
                "tree" => 1,
                "child" => ROOT_FOLDER_ID,
                "ref_id" => ROOT_FOLDER_ID,
                "depth" => 3,
                "type" => "root",
                "title" => $lng->txt("repository_admin"),
                "description" => $lng->txt("repository_admin_desc"),
                "desc" => $lng->txt("repository_admin_desc"),
            );

        //$nd = $tree->getNodeData(SYSTEM_FOLDER_ID);
        //var_dump($nd);
        $new_objects[$lng->txt("general_settings") . ":" . SYSTEM_FOLDER_ID] =
            array(
                "tree" => 1,
                "child" => SYSTEM_FOLDER_ID,
                "ref_id" => SYSTEM_FOLDER_ID,
                "depth" => 2,
                "type" => "adm",
                "title" => $lng->txt("general_settings"),
            );
        ksort($new_objects);

        // determine items to show
        $items = array();
        foreach ($new_objects as $c) {
            // check visibility
            if ($tree->getParentId($c["ref_id"]) == ROOT_FOLDER_ID && $c["type"] != "adm" &&
                $_GET["admin_mode"] != "repository") {
                continue;
            }
            // these objects may exist due to test cases that didnt clear
            // data properly
            if ($c["type"] == "" || $c["type"] == "objf" ||
                $c["type"] == "xxx") {
                continue;
            }
            $accessible = $rbacsystem->checkAccess('visible,read', $c["ref_id"]);
            if (!$accessible) {
                continue;
            }
            if ($c["ref_id"] == ROOT_FOLDER_ID &&
                !$rbacsystem->checkAccess('write', $c["ref_id"])) {
                continue;
            }
            if ($c["type"] == "rolf" && $c["ref_id"] != ROLE_FOLDER_ID) {
                continue;
            }
            $items[] = $c;
        }

        $titems = array();
        foreach ($items as $i) {
            $titems[$i["type"]] = $i;
        }
        // admin menu layout
        $layout = array(
            1 => array(
                "adn" =>
                    array("xaad", "xaec","xaed", "xaes", "xaep", "xacp", "xata", "xamd", "xast"),
                "basic" =>
                    array("adm", "stys", "adve", "lngf", "hlps", "accs", "cmps", "extt"),
                "user_administration" =>
                    array("usrf", 'tos', "rolf", "auth", "ps", "orgu"),
                "learning_outcomes" =>
                    array("skmg", "cert", "trac")
            ),
            2 => array(
                "user_services" =>
                    array("pdts", "prfa", "nwss", "awra", "cadm", "cals", "mail"),
                "content_services" =>
                    array("seas", "mds", "tags", "taxs", 'ecss', "pays", "otpl"),
                "maintenance" =>
                    array('sysc', "recf", 'logs', "root")
            ),
            3 => array(
                "container" =>
                    array("reps", "crss", "grps", "prgs"),
                "content_objects" =>
                    array("bibs", "blga", "chta", "excs", "facs", "frma",
                        "lrss", "mcts", "mobs", "svyf", "assf", "wbrs", "wiks")
            )
        );

        // now get all items and groups that are accessible
        $groups = array();
        for ($i = 1; $i <= 3; $i++) {
            $groups[$i] = array();
            foreach ($layout[$i] as $group => $entries) {
                $groups[$i][$group] = array();
                $entries_since_last_sep = false;
                foreach ($entries as $e) {
                    if ($e == "---" || $titems[$e]["type"] != "") {
                        if ($e == "---" && $entries_since_last_sep) {
                            $groups[$i][$group][] = $e;
                            $entries_since_last_sep = false;
                        } elseif ($e != "---") {
                            $groups[$i][$group][] = $e;
                            $entries_since_last_sep = true;
                        }
                    }
                }
            }
        }

        include_once("./Services/UIComponent/GroupedList/classes/class.ilGroupedListGUI.php");
        $gl = new ilGroupedListGUI();

        for ($i = 1; $i <= 3; $i++) {
            if ($i > 1) {
                //				$gl->nextColumn();
            }
            foreach ($groups[$i] as $group => $entries) {
                if (count($entries) > 0) {
                    $gl->addGroupHeader($lng->txt("adm_" . $group));

                    foreach ($entries as $e) {
                        if ($e == "---") {
                            $gl->addSeparator();
                        } else {
                            $path = ilObject::_getIcon("", "tiny", $titems[$e]["type"]);
                            $icon = ($path != "")
                                ? ilUtil::img($path) . " "
                                : "";

                            if ($_GET["admin_mode"] == "settings" && $titems[$e]["ref_id"] == ROOT_FOLDER_ID) {
                                $gl->addEntry(
                                    $icon . $titems[$e]["title"],
                                    "ilias.php?baseClass=ilAdministrationGUI&amp;ref_id=" .
                                    $titems[$e]["ref_id"] . "&amp;admin_mode=repository",
                                    "_top",
                                    "",
                                    "",
                                    "mm_adm_rep",
                                    ilHelp::getMainMenuTooltip("mm_adm_rep"),
                                    "bottom center",
                                    "top center",
                                    false
                                );
                            } else {
                                $gl->addEntry(
                                    $icon . $titems[$e]["title"],
                                    "ilias.php?baseClass=ilAdministrationGUI&amp;ref_id=" .
                                    $titems[$e]["ref_id"] . "&amp;cmd=jump",
                                    "_top",
                                    "",
                                    "",
                                    "mm_adm_" . $titems[$e]["type"],
                                    ilHelp::getMainMenuTooltip("mm_adm_" . $titems[$e]["type"]),
                                    "bottom center",
                                    "top center",
                                    false
                                );
                            }
                        }
                    }
                }
            }
        }

        //$gl->addSeparator();

        $tpl->setLeftNavContent("<div id='adn_adm_side_menu'>" . $gl->getHTML() . "</div>");
    }
}
