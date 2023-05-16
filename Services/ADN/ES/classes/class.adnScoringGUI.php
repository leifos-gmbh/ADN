<?php
/* Copyright (c) 2010 Leifos, GPL, see docs/LICENSE */

/**
 * Scoring GUI class. This class mainly provides the form that allows to edit the core
 * of a candidate of an online exam.
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.de>
 * @version $Id: class.adnScoringGUI.php 32253 2011-12-21 12:49:35Z jluetzen $
 *
 * @ilCtrl_Calls adnScoringGUI:
 *
 * @ingroup ServicesADN
 */
class adnScoringGUI
{
    // current certificate object
    protected $scoring = null;
    
    // current form object
    protected $form = null;
    
    /**
     * Constructor
     */
    public function __construct()
    {
        global $ilCtrl;
        
        // save scoring ID through requests
        $ilCtrl->saveParameter($this, array("sc_id"));
        $ilCtrl->saveParameter($this, array("ev_id"));
        
        $this->readScoring();
    }
    
    /**
     * Execute command
     */
    public function executeCommand()
    {
        global $ilCtrl, $tpl, $lng;

        $tpl->setTitle($lng->txt("adn_es") . " - " .
            $lng->txt("adn_es_scs"));
        adnIcon::setTitleIcon("es_scs");

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
                    case "listCandidates":
                    case "applyFilter":
                    case "resetFilter":
                        if (adnPerm::check(adnPerm::ES, adnPerm::READ)) {
                            $this->$cmd();
                        }
                        break;
                    
                    // commands that need write permission
                    case "editScoring":
                    case "saveScoring":
                        if (adnPerm::check(adnPerm::ES, adnPerm::WRITE)) {
                            $this->$cmd();
                        }
                        break;
                    
                }
                break;
        }
    }
    
    /**
     * Read scoring
     */
    protected function readScoring()
    {
        if ((int) $_GET["sc_id"] > 0) {
            include_once("./Services/ADN/ES/classes/class.adnScoring.php");
            $this->scoring = new adnScoring((int) $_GET["ct_id"]);
        }
    }
    
    /**
     * List all examination events
     */
    protected function listEvents()
    {
        global $tpl;

        // table of examination events
        include_once("./Services/ADN/ES/classes/class.adnExaminationEventTableGUI.php");
        $table = new adnExaminationEventTableGUI(
            $this,
            "listEvents",
            adnExaminationEventTableGUI::MODE_SCORING,
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
            adnExaminationEventTableGUI::MODE_SCORING,
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
            adnExaminationEventTableGUI::MODE_SCORING,
            true
        );
        $table->resetOffset();
        $table->resetFilter();

        $this->listEvents();
    }

    /**
     * List all candiates for event
     */
    protected function listCandidates()
    {
        global $tpl, $ilCtrl, $ilTabs, $lng;

        $ilTabs->setBackTarget($lng->txt("back"), $ilCtrl->getLinkTarget($this, "listEvents"));

        $event_id = (int) $_GET["ev_id"];

        $ilCtrl->setParameter($this, "ev_id", $event_id);

        // table of candidates
        include_once("./Services/ADN/ES/classes/class.adnCandidateTableGUI.php");
        $table = new adnCandidateTableGUI($this, "listCandidates", $event_id);

        // output table
        $tpl->setContent($table->getHTML());
    }

    /**
     * Edit scoring for candidate and event
     *
     * @param ilPropertyFormGUI $a_form
     */
    protected function editScoring(ilPropertyFormGUI $a_form = null)
    {
        global $tpl, $lng, $ilTabs, $ilCtrl;

        $ilTabs->setBackTarget($lng->txt("back"), $ilCtrl->getLinkTarget($this, "listCandidates"));

        if (!$a_form) {
            $a_form = $this->initScoringForm();
        }

        // given answers
        $assignment_id = (int) $_GET["ass_id"];
        include_once("./Services/ADN/EP/classes/class.adnAssignment.php");
        $assignment = new adnAssignment($assignment_id);
        $event_id = (int) $_GET["ev_id"];
        $candidate_id = $assignment->getUser();

        include_once("./Services/ADN/ES/classes/class.adnOnlineAnswersTableGUI.php");
        $online_answers_table = new adnOnlineAnswersTableGUI(
            $this,
            "editScoring",
            $event_id,
            $candidate_id
        );

        $tpl->setContent($a_form->getHTML() . "<br/>" .
            $online_answers_table->getHTML());
    }

    /**
     * Build scoring form
     *
     * @return ilPropertyFormGUI $a_form
     */
    protected function initScoringForm()
    {
        global  $lng, $ilCtrl;

        // assignment
        $assignment_id = (int) $_GET["ass_id"];
        include_once("./Services/ADN/EP/classes/class.adnAssignment.php");
        $assignment = new adnAssignment($assignment_id);
        $ilCtrl->setParameter($this, "ass_id", $assignment_id);

        // event
        $event_id = (int) $_GET["ev_id"];
        include_once("./Services/ADN/EP/classes/class.adnExaminationEvent.php");
        $event = new adnExaminationEvent($event_id);
        $ilCtrl->setParameter($this, "ev_id", $event_id);

        // candidate
        $candidate_id = $assignment->getUser();
        include_once("./Services/ADN/ES/classes/class.adnCertifiedProfessional.php");
        $candidate = new adnCertifiedProfessional($candidate_id);

        // init form...
        include_once "Services/Form/classes/class.ilPropertyFormGUI.php";
        $form = new ilPropertyFormGUI();
        $form->setTitle("<div class=\"small\" style=\"font-weight:normal; margin-bottom:5px;\">" .
            adnExaminationEvent::lookupName($event_id) . "</div>" .
            $lng->txt("adn_edit_scoring") . ": " .
            $candidate->getLastname() . ", " . $candidate->getFirstname());
        $form->setFormAction($ilCtrl->getFormAction($this, "listCandidates"));
        
        // has participated
        $participated = new ilCheckboxInputGUI($lng->txt("adn_participated"), "has_participated");
        $participated->setRequired(true);
        $participated->setChecked($assignment->getHasParticipated());
        $form->addItem($participated);

        // score mc
        $mc = new ilNumberInputGUI($lng->txt("adn_scoring_mc"), "score_mc");
        $mc->setDecimals(1);
        $mc->setRequired(true);
        $mc->setMinValue(0);
        $mc->setSize(5);
        $mc->setMaxLength(5);
        if ($assignment->getScoreMc() != null) {
            $mc->setValue($assignment->getScoreMc());
        } else {
            // get score from online test
            include_once("./Services/ADN/EC/classes/class.adnTest.php");
            $mc_result = adnTest::lookupMCResult($event_id, $candidate_id);
            if ($mc_result > 0) {
                $mc->setValue($mc_result);
            }
        }
        $form->addItem($mc);

        // score case
        include_once("./Services/ADN/ED/classes/class.adnSubjectArea.php");
        if (adnSubjectArea::hasCasePart($event->getType())) {
            $case = new ilNumberInputGUI($lng->txt("adn_scoring_case"), "score_case");
            $case->setDecimals(1);
            $case->setRequired(true);
            $case->setMinValue(0);
            $case->setSize(5);
            $case->setMaxLength(5);
            $case->setValue($assignment->getScoreCase());
            $form->addItem($case);
        }

        // result mc
        include_once("./Services/ADN/EP/classes/class.adnAssignment.php");
        $mc_result = new ilSelectInputGUI($lng->txt("adn_result_mc"), "result_mc");
        $mc_result->setOptions(adnAssignment::getAllScores());
        $mc_result->setRequired(true);
        $mc_result->setValue($assignment->getResultMc());
        $form->addItem($mc_result);

        // result case
        if (adnSubjectArea::hasCasePart($event->getType())) {
            $case_result = new ilSelectInputGUI($lng->txt("adn_result_case"), "result_case");
            $case_result->setOptions(adnAssignment::getAllScores());
            $case_result->setRequired(true);
            $case_result->setValue($assignment->getResultCase());
            $form->addItem($case_result);
        }

        $holdback_toggle = new ilCheckboxInputGUI($lng->txt("adn_holdback"), "blocked");
        $form->addItem($holdback_toggle);

        $holdback = new ilDateTimeInputGUI($lng->txt("adn_holdback_until"), "blocked_until");
        $holdback->setRequired(true);
        $holdback_toggle->addSubItem($holdback);

        include_once "Services/ADN/MD/classes/class.adnWMO.php";
        $wmos = adnWMO::getWMOsSelect();

        $holdback_by = new ilSelectInputGUI($lng->txt("adn_holdback_by"), "blocked_by");
        $holdback_by->setOptions($wmos);
        $holdback_by->setRequired(true);
        include_once("./Services/ADN/AD/classes/class.adnUser.php");
        $holdback_by->setValue(adnUser::lookupWmoId());
        $holdback_toggle->addSubItem($holdback_by);

        // set holdback values
        if ($candidate->getBlockedUntil() != null) {
            $holdback_toggle->setChecked(true);
            $holdback_by->setValue($candidate->getBlockedBy());
            $holdback->setDate($candidate->getBlockedUntil());
        }
        
        $update_date = $assignment->getLastScoringUpdate();
        if (!$update_date->isNull()) {
            $sh = new ilFormSectionHeaderGUI();
            $sh->setTitle($lng->txt("adn_last_change"));
            $form->addItem($sh);

            $last_update = new ilNonEditableValueGUI($lng->txt("adn_last_change_date"));
            $last_update->setValue(ilDatePresentation::formatDate($update_date));
            $form->addItem($last_update);

            $user = $assignment->getLastScoringUpdateUser();
            if ($user) {
                $user = ilObjUser::_lookupName($user);
                $user = $user["lastname"] . ", " . $user["firstname"] . " [" . $user["login"] . "]";
            }
            if ($user) {
                $last_update_user = new ilNonEditableValueGUI($lng->txt("adn_last_change_user"));
                $last_update_user->setValue($user);
                $form->addItem($last_update_user);
            }
        }
        
        $form->addCommandButton("saveScoring", $lng->txt("save"));
        $form->addCommandButton("listCandidates", $lng->txt("cancel"));

        return $form;
    }

    /**
     * Save scoring
     */
    protected function saveScoring()
    {
        global $tpl, $lng, $ilCtrl, $ilUser;

        $form = $this->initScoringForm();

        include_once("./Services/ADN/ED/classes/class.adnSubjectArea.php");

        // get event
        include_once("./Services/ADN/EP/classes/class.adnExaminationEvent.php");
        $event = new adnExaminationEvent((int) $_GET["ev_id"]);

        // get assignment
        $assignment_id = (int) $_GET["ass_id"];
        include_once("./Services/ADN/EP/classes/class.adnAssignment.php");
        $assignment = new adnAssignment($assignment_id);

        // get candidate
        $candidate_id = $assignment->getUser();
        include_once("./Services/ADN/ES/classes/class.adnCertifiedProfessional.php");
        $candidate = new adnCertifiedProfessional($candidate_id);

        // check input
        if ($form->checkInput()) {
            // extra checks
            $extra_checks_ok = true;

            // holdback period set, even if result is passed
            if ($form->getInput("result_mc") == adnAssignment::SCORE_PASSED &&
                (!adnSubjectArea::hasCasePart($event->getType()) ||
                $form->getInput("result_case") == adnAssignment::SCORE_PASSED) &&
                $form->getInput("blocked")) {
                // @todo: Currently disabled
                // Example: chem (case passed, mc passed) but total points <= 44) => then FAILED
                #ilUtil::sendFailure($lng->txt("adn_blocked_and_passed_not_allowed"), true);
                #$extra_checks_ok = false;
            }
            if (!$form->getInput('has_participated')) {
                $extra_checks_ok = false;
            }

            if ($extra_checks_ok) {

                // update assignment...
                $assignment->setHasParticipated($form->getInput("has_participated"));
                $assignment->setScoreMc($form->getInput("score_mc"));
                $assignment->setResultMc($form->getInput("result_mc"));

                // check wheter result has been set
                $result_set = false;
                if ($form->getInput("result_mc") > 0) {
                    $result_set = true;
                }

                if (adnSubjectArea::hasCasePart($event->getType())) {
                    $assignment->setScoreCase($form->getInput("score_case"));
                    $assignment->setResultCase($form->getInput("result_case"));
                    if ($form->getInput("result_case") > 0) {
                        $result_set = true;
                    }
                }
                
                $assignment->setLastScoringUpdate(new ilDateTime(time(), IL_CAL_UNIX));
                $assignment->setLastScoringUpdateUser($ilUser->getId());
                $assignment->update();

                // update candidate
                if ($form->getInput("blocked")) {
                    $candidate->setBlockedBy($form->getInput("blocked_by"));
                    $date = $form->getInput("blocked_until");
                    $candidate->setBlockedUntil(new ilDate($date, IL_CAL_DATE));
                } else {
                    $candidate->setBlockedBy(null);
                    $candidate->setBlockedUntil(null);
                }

                // if result set, reset registered for exam and subject area
                if ($result_set) {
                    $candidate->setSubjectArea(null);
                    $candidate->setRegisteredForExam(false);
                }

                $candidate->update();


                // show success message and return to list
                ilUtil::sendSuccess($lng->txt("adn_score_saved"), true);
                $ilCtrl->redirect($this, "listCandidates");
            }
        }

        // input not valid: show form again
        ilUtil::sendFailure($lng->txt('err_check_input'));
        $form->setValuesByPost();
        $this->editScoring($form);
    }
}
