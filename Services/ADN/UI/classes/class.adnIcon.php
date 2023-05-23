<?php
/* Copyright (c) 2010 Leifos, GPL, see docs/LICENSE */

/**
 * ADN main menu GUI class
 *
 * Defines the main menu entries and order
 *
 * @author Alex Killing <killing@leifos.de>
 */
class adnIcon
{
    public static function getIconPath(string $key) : string {
        switch ($key) {
            case adnMainMenuGUI::TA:
                return "01_Schulungsverwaltung/00_Schulungsverwaltung.svg";
            case adnMainMenuGUI::TA_TPS:
                return "01_Schulungsverwaltung/01_Schulungsveranstalter.svg";
            case adnMainMenuGUI::TA_TES:
                return "01_Schulungsverwaltung/02_Schulungstermine.svg";
            case adnMainMenuGUI::TA_ILS:
                return "01_Schulungsverwaltung/03_Merkblaetter.svg";
            case adnMainMenuGUI::TA_AES:
                return "01_Schulungsverwaltung/04_Fachgebiete.svg";
            case adnMainMenuGUI::ED:
                return "02_Pruefungselemente/00_Pruefunsgelemente.svg";
            case adnMainMenuGUI::ED_OBS:
                return "02_Pruefungselemente/01_Pruefungsziele.svg";
            case adnMainMenuGUI::ED_NQS:
                return "02_Pruefungselemente/02_AnzahlFragen.svg";
            case adnMainMenuGUI::ED_EQS:
                return "02_Pruefungselemente/03_Pruefungsfragen.svg";
            case adnMainMenuGUI::ED_CAS:
                return "02_Pruefungselemente/04_Situationsbeschreibung.svg";
            case adnMainMenuGUI::ED_LIC:
                return "02_Pruefungselemente/05_Zulassungszeugnis.svg";
            case adnMainMenuGUI::ED_GTS:
                return "02_Pruefungselemente/06_Stoffe.svg";
            case adnMainMenuGUI::EP:
                return "03_Pruefungsvorbereitung/00_Pruefungsvorbereitung.svg";
            case adnMainMenuGUI::EP_ILS:
                return "03_Pruefungsvorbereitung/01_Merkblaetter.svg";
            case adnMainMenuGUI::EP_EES:
                return "03_Pruefungsvorbereitung/02_Pruefungstermine.svg";
            case adnMainMenuGUI::EP_ECS:
                return "03_Pruefungsvorbereitung/03_Pruefungskandidaten.svg";
            case adnMainMenuGUI::EP_CES:
                return "03_Pruefungsvorbereitung/04_KandidatenzuTerminen.svg";
            case adnMainMenuGUI::EP_INS:
                return "03_Pruefungsvorbereitung/05_Einladungen.svg";
            case adnMainMenuGUI::EP_ASS:
                return "03_Pruefungsvorbereitung/06_Pruefungsboegen.svg";
            case adnMainMenuGUI::EP_ALS:
                return "03_Pruefungsvorbereitung/07_Teilnahmelisten.svg";
            case adnMainMenuGUI::EP_ACS:
                return "03_Pruefungsvorbereitung/08_Onlinepruefung.svg";
            case adnMainMenuGUI::ES:
                return "04_Pruefungsnachbereitung/00_Pruefungsnachbearbeitung.svg";
            case adnMainMenuGUI::ES_SCS:
                return "04_Pruefungsnachbereitung/01_Korrektur.svg";
            case adnMainMenuGUI::ES_CTS:
                return "04_Pruefungsnachbereitung/02_ADN_Bescheinigungen.svg";
            case adnMainMenuGUI::ES_SNS:
                return "04_Pruefungsnachbereitung/03_Antwortschreiben.svg";
            case adnMainMenuGUI::ES_OAS:
                return "04_Pruefungsnachbereitung/04_Online_Antwortboegen.svg";
            case adnMainMenuGUI::CP:
                return "05_Sachkundigenverwaltung/00_Sachkundigenverwaltung.svg";
            case adnMainMenuGUI::CP_CTS:
                return "05_Sachkundigenverwaltung/01_Bescheinigungen.svg";
            case adnMainMenuGUI::CP_CPR:
                return "05_Sachkundigenverwaltung/02_Personendaten.svg";
            case adnMainMenuGUI::CP_DIR:
                return "05_Sachkundigenverwaltung/03_Verzeichnis.svg";
            case adnMainMenuGUI::CP_PDM:
                return "05_Sachkundigenverwaltung/04_Archiv.svg";
            case adnMainMenuGUI::ST:
                return "06_Statistiken/00_Statistiken.svg";
            case adnMainMenuGUI::ST_EXS:
                return "06_Statistiken/01_Durchgef_Pruefungen.svg";
            case adnMainMenuGUI::ST_ERS:
                return "06_Statistiken/02_Verlaengerung_Wiederholungskrs.svg";
            case adnMainMenuGUI::ST_EES:
                return "06_Statistiken/03_Verlaengerung_Arbeitszeit.svg";
            case adnMainMenuGUI::ST_COS:
                return "06_Statistiken/04_SonsigeAntraege.svg";
            case adnMainMenuGUI::ST_TGC:
                return "06_Statistiken/05_GasChemie.svg";
            case adnMainMenuGUI::ST_TNS:
                return "06_Statistiken/06_Summe.svg";
            case adnMainMenuGUI::MD:
                return "07_Stammdaten/00_Stammdaten.svg";
            case adnMainMenuGUI::MD_WOS:
                return "07_Stammdaten/01_GDWSen.svg";
            case adnMainMenuGUI::MD_CNS:
                return "07_Stammdaten/02_Laender.svg";
            case adnMainMenuGUI::AD_MNT:
                return "07_Stammdaten/03_Wartungsmodus.svg";
            case adnMainMenuGUI::AD_CHR:
                return "07_Stammdaten/04_Sonderzeichen.svg";
            case adnMainMenuGUI::AD_USR:
                return "07_Stammdaten/05_Systembenutzer.svg";
            case adnMainMenuGUI::AD_MCX:
                return "07_Stammdaten/06_Fragen_Import.svg";
            case adnMainMenuGUI::AD_ICP:
                return "07_Stammdaten/07_Sachkundige_importieren.svg";
        }
        return "";
    }

    public static function setTitleIcon(string $key) : void
    {
        global $tpl;

        $tpl->setTitleIcon(\ILIAS\ADN\ADNMainBarProvider::IMAGE_PATH . "/" . self::getIconPath($key));
    }

}
