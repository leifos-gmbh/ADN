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

    // cr-008 start
    const CP_PDM = "cp_pdm";	// maintenance personal data
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

            $id = $this->if->identifier($key);

            $item = $this->mainmenu->topParentItem($id)
                                         ->withVisibilityCallable(function () use ($key) {
                                             return $this->checkVisibility($key);
                                         })
                                         ->withTitle($this->dic->language()->txt("adn_" . $key))
                                         ->withPosition($pos);
            if ($this->getIconPath($key) !== "")
            {
                $item = $item->withSymbol(
                    $this->dic->ui()->factory()->symbol()->icon()->custom(
                        "./Customizing/global/skin/adn/images/" . $this->getIconPath($key),
                        $this->dic->language()->txt("adn_" . $key)
                    )
                );
            }
            $items[] = $item;
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
                $item = $this->mainmenu->link($this->if->identifier($sub))
                                          ->withAction("ilias.php?baseClass=adnBaseGUI&amp;cmd=processMenuItem&amp;" .
                                              "menu_item=" . $sub)
                                          ->withParent($parent_id)
                                          ->withTitle($this->dic->language()->txt("adn_" . $sub))
                                          //->withSymbol($icon)
                                          ->withPosition($pos)
                                          ->withVisibilityCallable(function () use ($key, $sub) {
                                              return $this->checkSubVisibility($key, $sub);
                                          });
                if ($this->getIconPath($sub) !== "")
                {
                    $item = $item->withSymbol(
                        $this->dic->ui()->factory()->symbol()->icon()->custom(
                            "./Customizing/global/skin/adn/images/" . $this->getIconPath($sub),
                            $this->dic->language()->txt("adn_" . $sub)
                        )
                    );
                }
                $items[] = $item;
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

    protected function getIconPath(string $key) : string {
        switch ($key) {
            case self::TA:
                return "01_Schulungsverwaltung/00_Schulungsverwaltung.svg";
            case self::TA_TPS:
                return "01_Schulungsverwaltung/01_Schulungsveranstalter.svg";
            case self::TA_TES:
                return "01_Schulungsverwaltung/02_Schulungstermine.svg";
            case self::TA_ILS:
                return "01_Schulungsverwaltung/03_Merkblaetter.svg";
            case self::TA_AES:
                return "01_Schulungsverwaltung/04_Fachgebiete.svg";
            case self::ED:
                return "02_Pruefungselemente/00_Pruefunsgelemente.svg";
            case self::ED_OBS:
                return "02_Pruefungselemente/01_Pruefungsziele.svg";
            case self::ED_NQS:
                return "02_Pruefungselemente/02_AnzahlFragen.svg";
            case self::ED_EQS:
                return "02_Pruefungselemente/03_Pruefungsfragen.svg";
            case self::ED_CAS:
                return "02_Pruefungselemente/04_Situationsbeschreibung.svg";
            case self::ED_LIC:
                return "02_Pruefungselemente/05_Zulassungszeugnis.svg";
            case self::ED_GTS:
                return "02_Pruefungselemente/06_Stoffe.svg";
            case self::EP:
                return "03_Pruefungsvorbereitung/00_Pruefungsvorbereitung.svg";
            case self::EP_ILS:
                return "03_Pruefungsvorbereitung/01_Merkblaetter.svg";
            case self::EP_EES:
                return "03_Pruefungsvorbereitung/02_Pruefungstermine.svg";
            case self::EP_ECS:
                return "03_Pruefungsvorbereitung/03_Pruefungskandidaten.svg";
            case self::EP_CES:
                return "03_Pruefungsvorbereitung/04_KandidatenzuTerminen.svg";
            case self::EP_INS:
                return "03_Pruefungsvorbereitung/05_Einladungen.svg";
            case self::EP_ASS:
                return "03_Pruefungsvorbereitung/06_Pruefungsboegen.svg";
            case self::EP_ALS:
                return "03_Pruefungsvorbereitung/07_Teilnahmelisten.svg";
            case self::EP_ACS:
                return "03_Pruefungsvorbereitung/08_Onlinepruefung.svg";
            case self::ES:
                return "04_Pruefungsnachbereitung/00_Pruefungsnachbearbeitung.svg";
            case self::ES_SCS:
                return "04_Pruefungsnachbereitung/01_Korrektur.svg";
            case self::ES_CTS:
                return "04_Pruefungsnachbereitung/02_ADN_Bescheinigungen.svg";
            case self::ES_SNS:
                return "04_Pruefungsnachbereitung/03_Antwortschreiben.svg";
            case self::ES_OAS:
                return "04_Pruefungsnachbereitung/04_Online_Antwortboegen.svg";
            case self::CP:
                return "05_Sachkundigenverwaltung/00_Sachkundigenverwaltung.svg";
            case self::CP_CTS:
                return "05_Sachkundigenverwaltung/01_Bescheinigungen.svg";
            case self::CP_CPR:
                return "05_Sachkundigenverwaltung/02_Personendaten.svg";
            case self::CP_DIR:
                return "05_Sachkundigenverwaltung/03_Verzeichnis.svg";
            case self::CP_PDM:
                return "05_Sachkundigenverwaltung/04_Archiv.svg";
            case self::ST:
                return "06_Statistiken/00_Statistiken.svg";
            case self::ST_EXS:
                return "06_Statistiken/01_Durchgef_Pruefungen.svg";
            case self::ST_ERS:
                return "06_Statistiken/02_Verlaengerung_Wiederholungskrs.svg";
            case self::ST_EES:
                return "06_Statistiken/03_Verlaengerung_Arbeitszeit.svg";
            case self::ST_COS:
                return "06_Statistiken/04_SonsigeAntraege.svg";
            case self::ST_TGC:
                return "06_Statistiken/05_GasChemie.svg";
            case self::ST_TNS:
                return "06_Statistiken/06_Summe.svg";
            case self::MD:
                return "07_Stammdaten/00_Stammdaten.svg";
            case self::MD_WOS:
                return "07_Stammdaten/01_GDWSen.svg";
            case self::MD_CNS:
                return "07_Stammdaten/02_Laender.svg";
            case self::AD_MNT:
                return "07_Stammdaten/03_Wartungsmodus.svg";
            case self::AD_CHR:
                return "07_Stammdaten/04_Sonderzeichen.svg";
            case self::AD_USR:
                return "07_Stammdaten/05_Systembenutzer.svg";
            case self::AD_MCX:
                return "07_Stammdaten/06_Fragen_Import.svg";
            case self::AD_ICP:
                return "07_Stammdaten/07_Sachkundige_importieren.svg";
        }
        return "";
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
