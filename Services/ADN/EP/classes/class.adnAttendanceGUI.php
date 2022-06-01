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
    protected ilCtrl $ctrl;
    protected ilLanguage $lng;
    protected ilGlobalTemplateInterface $tpl;

    public function __construct()
    {
        global $DIC;
        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();
        $this->tpl = $DIC->ui()->mainTemplate();
    }
    /**
     * Execute command
     */
    public function executeCommand()
    {

        $this->tpl->setTitle($this->lng->txt("adn_ep") . " - " . $this->lng->txt("adn_ep_als"));
        
        $next_class = $this->ctrl->getNextClass();
        
        // forward command to next gui class in control flow
        switch ($next_class) {
            // no next class:
            // this class is responsible to process the command
            default:
                $cmd = $this->ctrl->getCmd("listEvents");
                
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

        // table of examination events
        include_once("./Services/ADN/ES/classes/class.adnExaminationEventTableGUI.php");
        $table = new adnExaminationEventTableGUI(
            $this,
            "listEvents",
            adnExaminationEventTableGUI::MODE_ATTENDANCE,
            false
        );
        
        // output table
        $this->tpl->setContent($table->getHTML());
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
        
        $event_id = (int) $_REQUEST['ev_id'];

        // create report
        include_once './Services/ADN/Report/exceptions/class.adnReportException.php';
        try {
            include_once './Services/ADN/EP/classes/class.adnExaminationEvent.php';
            include_once("./Services/ADN/Report/classes/class.adnReportAttendanceList.php");
            $report = new adnReportAttendanceList(new adnExaminationEvent($event_id));
            $report->create();
        
            // @todo: set creation date of report

            ilUtil::sendSuccess($this->lng->txt('adn_report_created_attendance_list'), true);
            $this->ctrl->redirect($this, 'listEvents');
        } catch (adnReportException $e) {
            ilUtil::sendFailure($e->getMessage(), true);
            $this->ctrl->redirect($this, 'listEvents');
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
