<?php
/* Copyright (c) 2010 Leifos, GPL, see docs/LICENSE */

/**
 * Online answer sheet GUI class. This class handles the user interface for
 * providing the download features for online exam answer sheets after the exam
 * has been taken.
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.de>
 * @version $Id: class.adnOnlineAnswerSheetGUI.php 27884 2011-02-27 21:01:07Z akill $
 *
 * @ilCtrl_Calls adnOnlineAnswerSheetGUI:
 *
 * @ingroup ServicesADN
 */
class adnOnlineAnswerSheetGUI
{
    /**
     * Constructor
     */
    public function __construct()
    {
        global $ilCtrl;

        // keep event
        $ilCtrl->saveParameter($this, "ev_id");
    }
    
    /**
     * Execute command
     */
    public function executeCommand()
    {
        global $ilCtrl, $lng, $tpl;

        $tpl->setTitle($lng->txt("adn_es") . " - " . $lng->txt("adn_es_oas"));
        adnIcon::setTitleIcon("es_oas");
        
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
                    case "downloadSheets":
                        if (adnPerm::check(adnPerm::ES, adnPerm::READ)) {
                            $this->$cmd();
                        }
                        break;
                }
                break;
        }
    }

    /**
     * List all events
     */
    protected function listEvents()
    {
        global $tpl;

        // table of examination events
        include_once("./Services/ADN/ES/classes/class.adnExaminationEventTableGUI.php");
        $table = new adnExaminationEventTableGUI(
            $this,
            "listEvents",
            adnExaminationEventTableGUI::MODE_ONLINE,
            true
        );
        
        // output table
        $tpl->setContent($table->getHTML());
    }

    /**
     * Apply filter settings
     */
    protected function applyFilter()
    {
        include_once("./Services/ADN/ES/classes/class.adnExaminationEventTableGUI.php");
        $table = new adnExaminationEventTableGUI(
            $this,
            "listEvents",
            adnExaminationEventTableGUI::MODE_ONLINE,
            true
        );
        $table->resetOffset();
        $table->writeFilterToSession();

        $this->listEvents();
    }

    /**
     * Reset filter settings
     */
    protected function resetFilter()
    {
        include_once("./Services/ADN/ES/classes/class.adnExaminationEventTableGUI.php");
        $table = new adnExaminationEventTableGUI(
            $this,
            "listEvents",
            adnExaminationEventTableGUI::MODE_ONLINE,
            true
        );
        $table->resetOffset();
        $table->resetFilter();

        $this->listEvents();
    }

    /**
     * Download sheets
     */
    protected function downloadSheets()
    {
        global $lng,$ilCtrl;

        include_once './Services/ADN/Report/exceptions/class.adnReportException.php';
        try {
            include_once("./Services/ADN/Report/classes/class.adnReportOnlineExam.php");
            $report = new adnReportOnlineExam((int) $_REQUEST['ev_id']);
            $report->createSheets();
            ilUtil::deliverFile(
                $report->getOutfile(),
                'Antwortboegen.pdf',
                'application/pdf'
            );
        } catch (adnReportException $e) {
            ilUtil::sendFailure($e->getMessage(), true);
            $ilCtrl->redirect($this, 'listEvents');
        }
        
        $event_id = $_REQUEST['ev_id'];
        $ass_id = $_REQUEST['ass_id'];
        if (!$ass_id or !$event_id) {
            ilUtil::sendFailure($lng->txt('select_one'), true);
            $ilCtrl->redirect($this, 'listParticipants');
        }
    }
}
