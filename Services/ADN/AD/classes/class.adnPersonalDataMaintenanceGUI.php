<?php
// cr-008 start
/* Copyright (c) 2017 Leifos, GPL, see docs/LICENSE */

/**
 * Personal data maintenance GUI
 *
 * @author Alex Killing <killing@leifos.de>
 * @version $Id$
 *
 * @ilCtrl_Calls adnPersonalDataMaintenanceGUI:
 *
 * @ingroup ServicesADN
 */
class adnPersonalDataMaintenanceGUI
{
    protected int $pid;

    protected string $mode;

    protected const MODE_ALL = "all";
    protected const MODE_CAND = "cand";
    protected const MODE_CERT = "cert";

    protected ilLanguage $lng;
    protected ilCtrl $ctrl;
    protected ilGlobalTemplateInterface $tpl;
    protected ilToolbarGUI $toolbar;
    protected ilTabsGUI $tabs;

    /**
     * Constructor
     */
    public function __construct()
    {
        global $DIC;

        $this->pid = (int) $_GET["pid"];
        $this->lng = $DIC->language();
        $this->ctrl = $DIC->ctrl();
        $this->tpl = $DIC->ui()->mainTemplate();
        $this->toolbar = $DIC->toolbar();
        $this->tabs = $DIC->tabs();

        $this->mode = $_GET["mode"];
        if ($_GET["mode"] == "") {
            $this->mode = self::MODE_ALL;
        }
        $this->ctrl->saveParameter($this, "mode");
    }

    /**
     * Set mode
     *
     * @param string $a_mode
     */
    public function setMode($a_mode)
    {

        $this->mode = $a_mode;
        $this->ctrl->setParameter($this, "mode", $a_mode);
    }



    /**
     * Execute command
     */
    public function executeCommand()
    {

        $this->tpl->setTitle($this->lng->txt("adn_cp") . " - " . $this->lng->txt("adn_cp_pdm"));
        
        $next_class = $this->ctrl->getNextClass();
        // forward command to next gui class in control flow
        switch ($next_class) {
            // no next class:
            // this class is responsible to process the command
            default:
                $cmd = $this->ctrl->getCmd("listPersonalData");

                switch ($cmd) {
                    // commands that need read permission
                    case "listPersonalData":
                    case "applyFilter":
                    case "resetFilter":
                    case "showPersonalDataDetails":
                    case "jumpToList":
                        if (adnPerm::check(adnPerm::CP, adnPerm::READ)) {
                            $this->$cmd();
                        }
                        break;
                    
                    // commands that need write permission
                    case "delete":
                    case "confirmDeletion":
                        if (adnPerm::check(adnPerm::CP, adnPerm::WRITE)) {
                            $this->$cmd();
                        }
                        break;
                    
                }
                break;
        }
    }

    //
    // All personal data
    //

    /**
     * List personal data
     */
    protected function listPersonalData()
    {

        $this->setTabs();

        // table of countries
        include_once("./Services/ADN/AD/classes/class.adnPersonalDataTableGUI.php");
        $table = new adnPersonalDataTableGUI($this, "listPersonalData", $this->mode);
        
        // output table
        $this->tpl->setContent($table->getHTML());
    }

    /**
     * Jump to list (called by goto procedure)
     */
    public function jumpToList()
    {
        if ($_GET["target"] == "candd") {
            $this->setMode(self::MODE_CAND);
        }
        if ($_GET["target"] == "certd") {
            $this->setMode(self::MODE_CERT);
        }
        $_POST["registered_by"] = (int) $_GET["wmo_id"];
        $this->applyFilter();
    }


    /**
     * Apply filter settings (from table gui)
     */
    protected function applyFilter()
    {
        include_once("./Services/ADN/AD/classes/class.adnPersonalDataTableGUI.php");
        $table = new adnPersonalDataTableGUI($this, "listPersonalData", $this->mode);
        $table->resetOffset();
        $table->writeFilterToSession();

        $this->listPersonalData();
    }

    /**
     * Reset filter settings (from table gui)
     */
    protected function resetFilter()
    {
        include_once("./Services/ADN/AD/classes/class.adnPersonalDataTableGUI.php");
        $table = new adnPersonalDataTableGUI($this, "listPersonalData", $this->mode);
        $table->resetOffset();
        $table->resetFilter();

        $this->listPersonalData();
    }
    
    /**
     * Get action for current mode
     *
     * @return string action
     */
    /*
    function getActionForMode()
    {
        if ($this->mode == self::MODE_CAND)
        {
            return "listExamCandidates";
        }
        else if ($this->mode == self::MODE_CERT)
        {
            return "listExamCertifiedProfessionals";
        }
        return "listPersonalData";
    }*/
    

    //
    // Exam Candidates
    //

    /**
     * List personal data
     */
    protected function listExamCandidates()
    {

        $this->setMode(self::MODE_CAND);

        $this->setTabs();

        // table of countries
        include_once("./Services/ADN/AD/classes/class.adnPersonalDataTableGUI.php");
        $table = new adnPersonalDataTableGUI($this, "listPersonalData", $this->mode);

        // output table
        $this->tpl->setContent($table->getHTML());
    }

    //
    // Details
    //

    /**
     * Show personal data details
     */
    public function showPersonalDataDetails()
    {

        $this->tabs->setBackTarget($this->lng->txt("back"), $this->ctrl->getLinkTarget($this, "listPersonalData"));


        $dtpl = new ilTemplate("tpl.pd_details.html", true, true, "Services/ADN/AD");

        include_once("./Services/ADN/ES/classes/class.adnCertifiedProfessional.php");
        $p = new adnCertifiedProfessional($this->pid);
        $dtpl->setVariable("TXT_PERSONAL_DATA", $this->lng->txt("adn_ad_personal_data"));
        $dtpl->setVariable("NAME", $p->getFirstName() . " " . $p->getLastName());
        $dtpl->setVariable("ID", $this->pid);

        // certificates

        //- adn_ep_cand_sheet -> adn_ep_answer_sheet (Prüfungsbögen) -> adn_ep_exam_event (Prüfungstermin)
        //- adn_ep_exam_invitation (Prüfungseinladung) -> adn_ep_exam_event (Prüfungstermin) (gibt es inc
        //- adn_ep_assignment (Prüfungskandidat) -> adn_ep_exam_event
        //- adn_cp_invoice (Kostenbescheide)
        //- adn_es_certificate (Bescheinigungen)

        include_once("./Services/UIComponent/GroupedList/classes/class.ilGroupedListGUI.php");

        // exam invitations (stored in report/inv)
        $items = array();
        include_once("./Services/ADN/EP/classes/class.adnAssignment.php");
        include_once("./Services/ADN/EP/classes/class.adnExaminationEvent.php");
        foreach (adnAssignment::getAllAssignments(array("user_id" => $this->pid)) as $ass) {
            if ($ass["invited_on"] != "") {
                $items[] = adnExaminationEvent::lookupName($ass["ep_exam_event_id"]);
            }
        }
        $this->outputBlock($dtpl, $this->lng->txt("adn_ep_ins"), $items);

        // exam candidates
        $items = array();
        include_once("./Services/ADN/EP/classes/class.adnAssignment.php");
        include_once("./Services/ADN/EP/classes/class.adnExaminationEvent.php");
        foreach (adnAssignment::getAllAssignments(array("user_id" => $this->pid)) as $ass) {
            $items[] = adnExaminationEvent::lookupName($ass["ep_exam_event_id"]);
        }
        $this->outputBlock($dtpl, $this->lng->txt("adn_exam_candidate"), $items);

        // answer sheets
        $items = array();
        include_once("./Services/ADN/EP/classes/class.adnAnswerSheetAssignment.php");
        include_once("./Services/ADN/EP/classes/class.adnAnswerSheet.php");
        foreach (adnAnswerSheetAssignment::getAllSheets($this->pid) as $s) {
            $items[] = adnAnswerSheet::lookupName($s["ep_answer_sheet_id"]) .
                ", " . adnExaminationEvent::lookupName(adnAnswerSheet::lookupEvent($s["ep_answer_sheet_id"])) .
                ", " . $this->lng->txt("adn_generated_on") . ": " . ilDatePresentation::formatDate(new ilDateTime($s["generated_on"], IL_CAL_DATETIME));
        }
        $this->outputBlock($dtpl, $this->lng->txt("adn_answer_sheets"), $items);

        // certificates
        $items = array();
        include_once("./Services/ADN/ES/classes/class.adnCertificate.php");
        foreach (adnCertificate::getAllCertificates(array("cp_professional_id" => $this->pid), true, true) as $cert) {
            $c = new adnCertificate($cert["id"]);
            $items[] = $c->getFullCertificateNumber() . ", " . $this->lng->txt("adn_valid_until") . ": " . ilDatePresentation::formatDate($c->getValidUntil());
        }
        $this->outputBlock($dtpl, $this->lng->txt("adn_certificates"), $items);

        // score notifications
        $items = array();
        include_once("./Services/ADN/EP/classes/class.adnAssignment.php");
        include_once("./Services/ADN/EP/classes/class.adnExaminationEvent.php");
        foreach (adnAssignment::getAllAssignments(array("user_id" => $this->pid)) as $ass) {
            include_once './Services/ADN/Report/classes/class.adnReportScoreNotificationLetter.php';
            if (adnReportScoreNotificationLetter::hasFile($ass["ep_exam_event_id"], $ass["id"])) {
                $items[] = adnExaminationEvent::lookupName($ass["ep_exam_event_id"]);
            }
        }
        $this->outputBlock($dtpl, $this->lng->txt("adn_es_sns"), $items);

        // invoices
        $items = array();
        include_once("./Services/ADN/ES/classes/class.adnCertificate.php");
        include_once './Services/ADN/Report/classes/class.adnReportInvoice.php';
        foreach (adnCertificate::getAllCertificates(array("cp_professional_id" => $this->pid), true, true) as $cert) {
            if (adnReportInvoice::hasInvoice($cert["id"])) {
                $c = new adnCertificate($cert["id"]);
                $items[] = $c->getFullCertificateNumber() . ", " . $this->lng->txt("adn_valid_until") . ": " . ilDatePresentation::formatDate($c->getValidUntil());
            }
        }
        $this->outputBlock($dtpl, $this->lng->txt("adn_invoices"), $items);

        $this->tpl->setContent($dtpl->get());
    }

    /**
     * Output list
     *
     * @param ilTemplate $a_tpl
     * @param ilGroupedListGUI $a_li
     */
    protected function outputBlock(ilTemplate $a_tpl, $a_txt, $a_items)
    {

        $a_tpl->setCurrentBlock("dblock");
        $a_tpl->setVariable("HEAD_TITLE", $a_txt);

        if (count($a_items) > 0) {
            $li = new ilGroupedListGUI();
            foreach ($a_items as $i) {
                $li->addEntry($i);
            }
            $html = $li->getHTML();
        } else {
            $html = "<i>" . $this->lng->txt("adn_no_entries") . "</i>";
        }

        $a_tpl->setVariable("LIST", $html);
        $a_tpl->parseCurrentBlock();
    }

    //
    // Deletion
    //

    /**
     * Confirm
     */
    public function delete()
    {

        if (!is_array($_POST["id"]) || count($_POST["id"]) == 0) {
            ilUtil::sendInfo($this->lng->txt("no_checkbox"), true);
            $this->ctrl->redirect($this, "listPersonalData");
        } else {
            include_once("./Services/Utilities/classes/class.ilConfirmationGUI.php");
            $cgui = new ilConfirmationGUI();
            $cgui->setFormAction($this->ctrl->getFormAction($this));
            $cgui->setHeaderText($this->lng->txt("adn_really_delete_pd"));
            $cgui->setCancel($this->lng->txt("cancel"), "listPersonalData");
            $cgui->setConfirm($this->lng->txt("delete"), "confirmDeletion");

            include_once("./Services/ADN/ES/classes/class.adnCertifiedProfessional.php");

            foreach ($_POST["id"] as $i) {
                $p = new adnCertifiedProfessional($i);
                $cgui->addItem("id[]", $i, $p->getFirstName() . " " . $p->getLastName() . " [" . $p->getId() . "]");
            }

            $this->tpl->setContent($cgui->getHTML());
        }
    }


    /**
     * Confirmed deletion
     */
    public function confirmDeletion()
    {

        include_once("./Services/ADN/ES/classes/class.adnCertifiedProfessional.php");

        if (is_array($_POST["id"])) {
            foreach ($_POST["id"] as $id) {
                $p = new adnCertifiedProfessional($id);
                $p->delete();
            }
            ilUtil::sendSuccess($this->lng->txt("msg_obj_modified"));
        }

        $this->ctrl->redirect($this, "listPersonalData");
    }

    /**
     * Set tabs
     */
    public function setTabs()
    {

        $this->ctrl->setParameter($this, "mode", self::MODE_ALL);
        $this->tabs->addTab(self::MODE_ALL, $this->lng->txt("adn_ad_pd_all"), $this->ctrl->getLinkTarget($this, "listPersonalData"));
        $this->ctrl->setParameter($this, "mode", self::MODE_CAND);
        $this->tabs->addTab(self::MODE_CAND, $this->lng->txt("adn_ad_pd_cand"), $this->ctrl->getLinkTarget($this, "listPersonalData"));
        $this->ctrl->setParameter($this, "mode", self::MODE_CERT);
        $this->tabs->addTab(self::MODE_CERT, $this->lng->txt("adn_ad_pd_cert"), $this->ctrl->getLinkTarget($this, "listPersonalData"));
        $this->tabs->activateTab($this->mode);
        $this->ctrl->setParameter($this, "mode", $this->mode);
    }
}
// cr-008 end
