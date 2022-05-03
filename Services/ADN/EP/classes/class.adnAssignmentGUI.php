<?php
/* Copyright (c) 2010 Leifos, GPL, see docs/LICENSE */

/**
 * Candidate assignment GUI class (preparation context)
 *
 * List candidates for assignment to an examination event, validate assignment against pre-conditions
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.de>
 * @version $Id: class.adnAssignmentGUI.php 53831 2014-09-25 12:37:08Z jluetzen $
 *
 * @ilCtrl_Calls adnAssignmentGUI:
 *
 * @ingroup ServicesADN
 */
class adnAssignmentGUI
{
    protected bool $archived = false;
    
    /**
     * Constructor
     */
    public function __construct()
    {
        global $ilCtrl;

        $ilCtrl->saveParameter($this, array("arc"));

        $this->archived = (bool) $_REQUEST["arc"];
    }
    
    /**
     * Execute command
     */
    public function executeCommand()
    {
        global $ilCtrl, $lng, $tpl;

        $tpl->setTitle($lng->txt("adn_ep") . " - " . $lng->txt("adn_ep_ces"));
        
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
                    case "applyAssignmentFilter":
                    case "resetAssignmentFilter":
                    case "assignCandidates":
                        if (adnPerm::check(adnPerm::EP, adnPerm::READ)) {
                            $this->$cmd();
                        }
                        break;
                    
                    // commands that need write permission
                    case "saveAssignment":
                        if (adnPerm::check(adnPerm::EP, adnPerm::WRITE)) {
                            $this->$cmd();
                        }
                        break;
                    
                }
                break;
        }

        $this->setTabs();
    }

    /**
     * List all events (has to be selected first)
     */
    protected function listEvents()
    {
        global $tpl, $ilToolbar, $lng, $ilCtrl;

        // table of examination events
        include_once("./Services/ADN/ES/classes/class.adnExaminationEventTableGUI.php");
        $table = new adnExaminationEventTableGUI(
            $this,
            "listEvents",
            adnExaminationEventTableGUI::MODE_ASSIGNMENT,
            $this->archived
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
            adnExaminationEventTableGUI::MODE_ASSIGNMENT,
            $this->archived
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
            adnExaminationEventTableGUI::MODE_ASSIGNMENT,
            $this->archived
        );
        $table->resetOffset();
        $table->resetFilter();

        $this->listEvents();
    }

    /**
     * List all candiates for event
     */
    protected function assignCandidates()
    {
        global $tpl, $ilCtrl, $ilTabs, $lng;

        $ilTabs->setBackTarget($lng->txt("back"), $ilCtrl->getLinkTarget($this, "listEvents"));

        $event_id = (int) $_REQUEST["ev_id"];
        if (!$event_id) {
            return;
        }

        $ilCtrl->setParameter($this, "ev_id", $event_id);

        // table of candidates
        include_once("./Services/ADN/EP/classes/class.adnAssignmentTableGUI.php");
        $table = new adnAssignmentTableGUI(
            $this,
            "assignCandidates",
            $event_id,
            adnAssignmentTableGUI::MODE_ASSIGNMENT,
            $this->archived
        );
        
        // #5157
        $table->addHiddenInput("all_cand_ids", "~~phadncdids~~");
        
        $html = $table->getHTML();
                
        $ids = $table->getDisplayedIds();
        $html = str_replace("~~phadncdids~~", implode(";", $ids), $html);
                
        // output table
        $tpl->setContent($html);
    }

    /**
     * Apply filter settings (from table gui)
     */
    protected function applyAssignmentFilter()
    {
        $event_id = (int) $_REQUEST["ev_id"];
        include_once("./Services/ADN/EP/classes/class.adnAssignmentTableGUI.php");
        $table = new adnAssignmentTableGUI(
            $this,
            "assignCandidates",
            $event_id,
            adnAssignmentTableGUI::MODE_ASSIGNMENT,
            $this->archived
        );
        $table->resetOffset();
        $table->writeFilterToSession();

        $this->assignCandidates();
    }

    /**
     * Reset filter settings (from table gui)
     */
    protected function resetAssignmentFilter()
    {
        $event_id = (int) $_REQUEST["ev_id"];
        include_once("./Services/ADN/EP/classes/class.adnAssignmentTableGUI.php");
        $table = new adnAssignmentTableGUI(
            $this,
            "assignCandidates",
            $event_id,
            adnAssignmentTableGUI::MODE_ASSIGNMENT,
            $this->archived
        );
        $table->resetOffset();
        $table->resetFilter();

        $this->assignCandidates();
    }

    /**
     * Save candidate assignments
     *
     * This will perform the needed verification for all pre-conditions (age, last training, etc.)
     */
    protected function saveAssignment()
    {
        global $ilCtrl, $lng, $tpl;

        $confirmed = (bool) $_REQUEST["confirmed"];
        $ids = explode(";", $_POST["all_cand_ids"]);
        $assignments = (array) $_POST["cand_id"];
        $event_id = (int) $_REQUEST["ev_id"];
        $ilCtrl->setParameter($this, "ev_id", $event_id);

        if (sizeof($ids) && $event_id) {
            include_once "Services/ADN/EP/classes/class.adnExaminationEvent.php";
            include_once "Services/ADN/EP/classes/class.adnAssignment.php";
            include_once "Services/ADN/ES/classes/class.adnCertifiedProfessional.php";
            include_once "Services/ADN/Base/classes/class.adnDateUtil.php";
            include_once "Services/ADN/TA/classes/class.adnTrainingEvent.php";
            include_once "Services/ADN/ED/classes/class.adnSubjectArea.php";

            $event = new adnExaminationEvent($event_id);
            $invalids = array();
            $to_save = array();
            $delete_failed = false;
            foreach ($ids as $id) {
                $assignment = new adnAssignment(null, $id, $event_id);

                // U.PVB.5.2: validation of user data
                $user = new adnCertifiedProfessional($id);
                $valid = true;
                $invalid = array();

                // age: at least 18 (on day of exam)
                if (adnDateUtil::getAge($user->getBirthdate(), $event->getDateFrom()) < 18) {
                    $valid = false;
                    $invalid[] = "age";
                }

                // applied max. 6 months after last training and correct type
                $last_training = $user->getLastEvent();
                if (!$last_training) {
                    $valid = false;
                    $invalid[] = "last_training";
                } else {
                    $last_training = new adnTrainingEvent($last_training);
                    $date = $last_training->getDateTo();
                    $date = $date->get(IL_CAL_UNIX, "", ilTimeZone::UTC);
                    $min = strtotime(
                        "-6months",
                        $event->getDateFrom()->get(IL_CAL_UNIX, "", ilTimeZone::UTC)
                    );
                    if ($date < $min || $last_training->getType() != $user->getSubjectArea()) {
                        $valid = false;
                        $invalid[] = "last_training";
                    }
                }
                
                // if gas/chem: valid base certificate
                if (in_array($event->getType(), array(adnSubjectArea::CHEMICAL, adnSubjectArea::GAS)) &&
                    !$user->hasValidBaseCertificate()) {
                    $valid = false;
                    $invalid[] = "certificate";
                }

                // remove assignments
                if (!in_array($id, $assignments)) {
                    if ($assignment->getId()) {
                        if (!$assignment->delete()) {
                            $delete_failed = true;
                        }
                    }
                }
                // new/existing assignments
                else {
                    if ($valid || $confirmed) {
                        // only create new ones
                        if (!$assignment->getId()) {
                            $to_save[] = $assignment;
                        }
                    } else {
                        $invalids[$id] = $invalid;
                    }
                }
            }

            if (!sizeof($invalids)) {
                foreach ($to_save as $assignment) {
                    $assignment->save();
                }
                ilUtil::sendSuccess($lng->txt("adn_assignment_saved"), true);
                if ($delete_failed) {
                    ilUtil::sendFailure($lng->txt("adn_assignment_delete_fail_answered"), true);
                }
            } else {
                if ($delete_failed) {
                    ilUtil::sendFailure($lng->txt("adn_assignment_delete_fail_answered"));
                }

                // display confirmation message
                include_once("./Services/Utilities/classes/class.ilConfirmationGUI.php");
                $cgui = new ilConfirmationGUI();
                $cgui->setFormAction($ilCtrl->getFormAction($this));
                $cgui->setHeaderText($lng->txt("adn_sure_save_invalid_assignments"));
                $cgui->setCancel($lng->txt("cancel"), "assignCandidates");
                $cgui->setConfirm($lng->txt("adn_sure_save_assignments"), "saveAssignment");
                $cgui->addHiddenItem("confirmed", 1);

                $captions = array("age" => $lng->txt("adn_assignment_invalid_age"),
                    "certificate" => $lng->txt("adn_assignment_invalid_certificate"),
                    "last_training" => $lng->txt("adn_assignment_invalid_last_training"));

                // keep values
                $cgui->addHiddenItem("all_cand_ids", implode(";", $ids));
                foreach ($assignments as $candidate_id) {
                    $cgui->addHiddenItem("cand_id[]", $candidate_id);
                }
                
                // list objects that are invalid
                foreach ($invalids as $id => $types) {
                    $caption = array();
                    foreach ($types as $type) {
                        $caption[] = $captions[$type];
                    }
                    $caption = adnCertifiedProfessional::lookupName($id) . "<br />- " . implode("<br />- ", $caption);
                    $cgui->addItem("cand_id[]", $id, $caption);
                }

                $tpl->setContent($cgui->getHTML());
                return;
            }
        }

        $ilCtrl->redirect($this, "assignCandidates");
    }

    /**
     * Set tabs
     */
    public function setTabs()
    {
        global $ilTabs, $lng, $txt, $ilCtrl;

        $ilCtrl->setParameter($this, "arc", "");

        $ilTabs->addTab(
            "current",
            $lng->txt("adn_current_examination_events"),
            $ilCtrl->getLinkTarget($this, "listEvents")
        );


        $ilCtrl->setParameter($this, "arc", "1");

        $ilTabs->addTab(
            "archived",
            $lng->txt("adn_archived_examination_events"),
            $ilCtrl->getLinkTarget($this, "listEvents")
        );

        $ilCtrl->setParameter($this, "arc", $this->archived);

        if ($this->archived) {
            $ilTabs->activateTab("archived");
        } else {
            $ilTabs->activateTab("current");
        }
    }
}
