<?php namespace ILIAS\ADN;

use ILIAS\GlobalScreen\Helper\BasicAccessCheckClosures;
use ILIAS\GlobalScreen\Scope\MainMenu\Provider\AbstractStaticMainMenuProvider;
use ILIAS\MainMenu\Provider\StandardTopItemsProvider;
use ilPDSelectedItemsBlockViewSettings;

/**
 * Class ADNMainBarProvider
 */
class ADNMainBarProvider extends AbstractStaticMainMenuProvider
{
    // training administration
    protected const TA = "ta";
    protected const TA_TPS = "ta_tps";	// training providers
    protected const TA_TES = "ta_tes";	// training events
    protected const TA_ILS = "ta_ils";	// information letters
    protected const TA_AES = "ta_aes";	// expertise

    // examination definition
    protected const ED = "ed";
    protected const ED_OBS = "ed_obs";	// objectives
    protected const ED_EQS = "ed_eqs";	// questions
    protected const ED_NQS = "ed_nqs";	// target number of questions
    protected const ED_CAS = "ed_cas";	// case
    protected const ED_LIC = "ed_lic";	// license
    protected const ED_GTS = "ed_gts";	// goods

    // exam preparation
    protected const EP = "ep";
    protected const EP_ILS = "ep_ils";	// information letters
    protected const EP_EES = "ep_ees";	// examination events
    protected const EP_ECS = "ep_ecs";	// candidates
    protected const EP_CES = "ep_ces";	// candidates/events
    protected const EP_INS = "ep_ins";	// invitations
    protected const EP_ASS = "ep_ass";	// answer sheets
    protected const EP_ACS = "ep_acs";	// access codes (online)
    protected const EP_ALS = "ep_als";	// attendance

    // examination scoring
    protected const ES = "es";
    protected const ES_SCS = "es_scs";	// scoring
    protected const ES_CTS = "es_cts";	// certificates
    protected const ES_SNS = "es_sns";	// notification
    protected const ES_OAS = "es_oas";	// online answer sheets

    // certified professionals
    protected const CP = "cp";
    protected const CP_CTS = "cp_cts";	// certificates
    protected const CP_DIR = "cp_dir";	// directory
    protected const CP_CPR = "cp_cpr";	// professionals

    // statistics
    protected const ST = "st";
    protected const ST_EXS = "st_exs";	// exams
    protected const ST_ERS = "st_ers";	// extensions, refreshed
    protected const ST_EES = "st_ees";	// extensions, experience
    protected const ST_COS = "st_cos";	// certificates, other applications
    protected const ST_TNS = "st_tns";	// certificates, total
    protected const ST_TGC = "st_tgc";	// certificates, gas/chemicals

    // master data
    protected const MD = "md";
    protected const MD_WOS = "md_wos";	// wmos
    protected const MD_CNS = "md_cns";	// countries

    // administration
    protected const AD = "ad";
    protected const AD_MNT = "ad_mnt";	// maintenance mode
    protected const AD_CHR = "ad_chr";	// special characters
    protected const AD_USR = "ad_usr";	// user
    protected const AD_MCX = "ad_mcx";	// export mc questions
    protected const AD_ICP = "ad_icp";	// import professionals

    // cr-008 start
    protected const CP_PDM = "cp_pdm";	// maintenance personal data
    // cr-008 end

    public function getStaticTopItems() : array
    {
        if (isset($_SESSION["adn_online_test"])) {
            return [];
        }

        $this->dic->language()->loadLanguageModule("adn");
        $items = [];
        $pos = 2;
        foreach ($this->getAllMenuItems() as $key => $sub) {
            if ($key != "md") {
                $title = $this->dic->language()->txt("adn_" . $key);
            } else {
                $title = $this->dic->language()->txt("adn_ad");
            }

            //$icon = $this->dic->ui()->factory()->symbol()->icon()->standard(Standard::REP, $title)->withIsOutlined(
            //    true
            //);

            $id = $this->if->identifier($key);

            $items[] = $this->mainmenu->topParentItem($id)
                                         ->withVisibilityCallable(function () use ($key) {
                                             return $this->checkVisibility($key);
                                         })
                                         //->withSymbol($icon)
                                         ->withTitle($this->dic->language()->txt("adn_" . $key))
                                         ->withPosition($pos);
            $pos += 2;
        }
        return $items;
    }


    /**
     * @inheritDoc
     */
    public function getStaticSubItems() : array
    {
        $this->dic->language()->loadLanguageModule("adn");
        $items = [];
        foreach ($this->getAllMenuItems() as $key => $subs) {
            $parent_id = $this->if->identifier($key);
            $pos = 10;
            foreach ($subs as $sub) {
                $items[] = $this->mainmenu->link($this->if->identifier($sub))
                                          ->withAction("ilias.php?baseClass=adnBaseGUI&amp;cmd=processMenuItem&amp;" .
                                              "menu_item=" . $sub)
                                          ->withParent($parent_id)
                                          ->withTitle($this->dic->language()->txt("adn_" . $sub))
                                          //->withSymbol($icon)
                                          ->withPosition($pos)
                                          ->withVisibilityCallable(function () use ($key, $sub) {
                                              return $this->checkSubVisibility($key, $sub);
                                          });
            }
            $pos += 10;
        }
        return $items;
    }


    protected function checkVisibility(string $key) : bool
    {
        switch ($key) {
            case self::TA:
                return (\adnPerm::check(\adnPerm::TA, \adnPerm::READ));
            case self::ED:
                return (\adnPerm::check(\adnPerm::ED, \adnPerm::READ));
            case self::EP:
                return (\adnPerm::check(\adnPerm::EP, \adnPerm::READ));
            case self::ES:
                return (\adnPerm::check(\adnPerm::ES, \adnPerm::READ));
            case self::CP:
                return (\adnPerm::check(\adnPerm::CP, \adnPerm::READ));
            case self::ST:
                return (\adnPerm::check(\adnPerm::ST, \adnPerm::READ));
            case self::MD:
                return (\adnPerm::check(\adnPerm::MD, \adnPerm::READ) || \adnPerm::check(\adnPerm::AD, \adnPerm::READ));
        }
        return false;
    }

    protected function checkSubVisibility(string $key, string $sub) : bool
    {
        if ($key == self::MD) {
            if (in_array($sub, [self::MD_WOS, self::MD_CNS])) {
                return (\adnPerm::check(\adnPerm::MD, \adnPerm::READ));
            }
            if (in_array($sub, [self::AD_MNT, self::AD_CHR, self::AD_USR, self::AD_MCX, self::AD_ICP])) {
                return (\adnPerm::check(\adnPerm::AD, \adnPerm::READ));
            }
            return false;
        }
        return true;    // all others, are checked on the top level
    }

    protected function getAllMenuItems() : array
    {
        $items = array();

        $items[self::TA] = array(
            self::TA_TPS,
            self::TA_TES,
            self::TA_ILS,
            self::TA_AES
        );

        $items[self::ED] = array(
            self::ED_OBS,
            self::ED_NQS,
            self::ED_EQS,
            self::ED_CAS,
            self::ED_LIC,
            self::ED_GTS,
        );

        $items[self::EP] = array(
            self::EP_ILS,
            self::EP_EES,
            self::EP_ECS,
            self::EP_CES,
            self::EP_INS,
            self::EP_ASS,
            self::EP_ALS,
            self::EP_ACS
        );

        $items[self::ES] = array(
            self::ES_SCS,
            self::ES_CTS,
            self::ES_SNS,
            self::ES_OAS
        );

        $items[self::CP] = array(
            self::CP_CTS,
            self::CP_CPR,
            self::CP_DIR,
            // cr-008 start
            self::CP_PDM
            // cr-008 end
        );

        $items[self::ST] = array(
            self::ST_EXS,
            self::ST_ERS,
            self::ST_EES,
            self::ST_COS,
            self::ST_TGC,
            self::ST_TNS
        );

        $items[self::MD] = array();

        $items[self::MD][] = self::MD_WOS;
        $items[self::MD][] = self::MD_CNS;
        $items[self::MD][] = self::AD_MNT;
        $items[self::MD][] = self::AD_CHR;
        $items[self::MD][] = self::AD_USR;
        $items[self::MD][] = self::AD_MCX;
        $items[self::MD][] = self::AD_ICP;

        return $items;
    }
}
