<?php
/* Copyright (c) 2010 Leifos, GPL, see docs/LICENSE */

/**
 * Attendance GUI class
 *
 * Generates a pdf file listing all attended candidates for an event
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.de>
 * @version $Id: class.adnAttendanceGUI.php 27888 2011-02-28 11:09:28Z jluetzen $
 *
 * @ilCtrl_Calls adnAttendanceGUI:
 *
 * @ingroup ServicesADN
 */
class adnAttendanceGUI
{
    /**
     * Execute command
     */
    public function executeCommand()
    {
        global $ilCtrl, $lng, $tpl;

        $tpl->setTitle($lng->txt("adn_ep") . " - " . $lng->txt("adn_ep_als"));
        
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
                    case "downloadList":
                        if (adnPerm::check(adnPerm::EP, adnPerm::READ)) {
                            $this->$cmd();
                        }
                        break;
                    
                    // commands that need write permission
                    case "createList":
                        if (adnPerm::check(adnPerm::EP, adnPerm::WRITE)) {
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
            adnExaminationEventTableGUI::MODE_ATTENDANCE,
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
            adnExaminationEventTableGUI::MODE_ATTENDANCE,
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
            adnExaminationEventTableGUI::MODE_ATTENDANCE,
            false
        );
        $table->resetOffset();
        $table->resetFilter();

        $this->listEvents();
    }
    
    /**
     * Create attendance list (pdf)
     */
    protected function createList()
    {
        global $ilCtrl,$lng;
        
        $event_id = (int) $_REQUEST['ev_id'];

        // create report
        include_once './Services/ADN/Report/exceptions/class.adnReportException.php';
        try {
            include_once './Services/ADN/EP/classes/class.adnExaminationEvent.php';
            include_once("./Services/ADN/Report/classes/class.adnReportAttendanceList.php");
            $report = new adnReportAttendanceList(new adnExaminationEvent($event_id));
            $report->create();
        
            // @todo: set creation date of report

            ilUtil::sendSuccess($lng->txt('adn_report_created_attendance_list'), true);
            $ilCtrl->redirect($this, 'listEvents');
        } catch (adnReportException $e) {
            ilUtil::sendFailure($e->getMessage(), true);
            $ilCtrl->redirect($this, 'listEvents');
        }
    }
    
    /**
     * Download attendance list file (pdf)
     */
    protected function downloadList()
    {
        $event_id = (int) $_REQUEST['ev_id'];

        include_once("./Services/ADN/Report/classes/class.adnReportAttendanceList.php");
        $report = new adnReportAttendanceList(null);
        
        ilUtil::deliverFile(
            $report->getFile($event_id),
            "Teilnahmeliste_" . $event_id . '.pdf',
            'application/pdf'
        );
    }
}
