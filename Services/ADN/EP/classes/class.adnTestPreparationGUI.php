<?php
/* Copyright (c) 2010 Leifos, GPL, see docs/LICENSE */

/**
 * Test preparation GUI class
 *
 * List access codes for event and generate report
 *
 * @author Alex Kiling <killing@leifos.de>
 * @version $Id: class.adnTestPreparationGUI.php 27888 2011-02-28 11:09:28Z jluetzen $
 *
 * @ilCtrl_Calls adnTestPreparationGUI:
 *
 * @ingroup ServicesADN
 */
class adnTestPreparationGUI
{
    /**
     * Execute command
     */
    public function executeCommand()
    {
        global $ilCtrl, $lng, $tpl;

        $tpl->setTitle($lng->txt("adn_ep") . " - " . $lng->txt("adn_ep_acs"));
        adnIcon::setTitleIcon("ep_acs");
        
        $next_class = $ilCtrl->getNextClass();

        // forward command to next gui class in control flow
        switch ($next_class) {
            // no next class:
            // this class is responsible to process the command
            default:
                $cmd = $ilCtrl->getCmd("listEvents");
                
                switch ($cmd) {
                    // commands that need read permission
                    case "listEvents":
                    case "applyFilter":
                    case "resetFilter":
                    case "listAccessCodes":
                    case "downloadAccessCodes":
                        if (adnPerm::check(adnPerm::EP, adnPerm::READ)) {
                            $this->$cmd();
                        }
                        break;
                }
                break;
        }
    }

    /**
     * List all events (has to be selected first)
     */
    protected function listEvents()
    {
        global $tpl;

        // table of examination events
        include_once("./Services/ADN/ES/classes/class.adnExaminationEventTableGUI.php");
        $table = new adnExaminationEventTableGUI(
            $this,
            "listEvents",
            adnExaminationEventTableGUI::MODE_TEST_PREP,
            false
        );
        
        // output table
        $tpl->setContent($table->getHTML());
    }

    /**
     * Apply filter settings (from table gui)
     */
    protected function applyFilter()
    {
        include_once("./Services/ADN/ES/classes/class.adnExaminationEventTableGUI.php");
        $table = new adnExaminationEventTableGUI(
            $this,
            "listEvents",
            adnExaminationEventTableGUI::MODE_TEST_PREP,
            false
        );
        $table->resetOffset();
        $table->writeFilterToSession();

        $this->listEvents();
    }

    /**
     * Reset filter settings (from table gui)
     */
    protected function resetFilter()
    {
        include_once("./Services/ADN/ES/classes/class.adnExaminationEventTableGUI.php");
        $table = new adnExaminationEventTableGUI(
            $this,
            "listEvents",
            adnExaminationEventTableGUI::MODE_TEST_PREP,
            false
        );
        $table->resetOffset();
        $table->resetFilter();

        $this->listEvents();
    }

    /**
     * List access codes for event
     */
    public function listAccessCodes()
    {
        global $tpl, $ilCtrl, $ilTabs, $lng, $ilToolbar;

        $ilTabs->setBackTarget($lng->txt("back"), $ilCtrl->getLinkTarget($this, "listEvents"));

        $event_id = (int) $_GET["ev_id"];

        $ilCtrl->setParameter($this, "ev_id", $event_id);

        // download button
        $ilToolbar->addButton(
            $lng->txt("adn_download_access_codes"),
            $ilCtrl->getLinkTarget($this, "downloadAccessCodes")
        );

        // ensure that all participants have login and password
        include_once("./Services/ADN/EP/classes/class.adnAssignment.php");
        adnAssignment::prepareOnlineTest($event_id);

        // table of access codes
        include_once("./Services/ADN/EP/classes/class.adnAccessCodesTableGUI.php");
        $table = new adnAccessCodesTableGUI($this, "listAccessCodes", $event_id);

        // output table
        $tpl->setContent($table->getHTML());
    }

    /**
     * Download access codes (pdf)
     */
    public function downloadAccessCodes()
    {
        global $ilCtrl;
        
        $event_id = (int) $_REQUEST['ev_id'];
        
        // create report
        include_once './Services/ADN/Report/exceptions/class.adnReportException.php';
        try {
            include_once("./Services/ADN/Report/classes/class.adnReportOnlineExam.php");
            $report = new adnReportOnlineExam($event_id);
            $report->create();
            
            ilUtil::deliverFile(
                $report->getOutfile(),
                'Benutzerzugaenge.pdf',
                'application/pdf'
            );
        } catch (adnReportException $e) {
            ilUtil::sendFailure($e->getMessage(), true);
            $ilCtrl->redirect($this, 'listCertificates');
        }
    }
}
