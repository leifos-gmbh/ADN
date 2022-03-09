<?php
/* Copyright (c) 2010 Leifos, GPL, see docs/LICENSE */

include_once("./Services/Table/classes/class.ilTable2GUI.php");

/**
 * Candidate table GUI class. This table class lists all candidates of an exam
 * and offers actions to edit the scoring for and to download
 * the score notification letter for each candidate.
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.de>
 * @version $Id: class.adnCandidateTableGUI.php 59660 2015-06-30 08:50:17Z jluetzen $
 *
 * @ingroup ServicesADN
 */
class adnCandidateTableGUI extends ilTable2GUI
{
    protected $event_id; // [int] id of examination event
    protected $notification_mode; // [bool]
    protected $has_case_part; // [bool]

    /**
     * Constructor
     *
     * @param object $a_parent_obj parent gui object
     * @param string $a_parent_cmd parent default command
     * @param int $a_event_id id of examination event
     * @param bool $a_notification_mode id of examination event
     */
    public function __construct($a_parent_obj, $a_parent_cmd, $a_event_id, $a_notification_mode = false)
    {
        global $ilCtrl, $lng;

        $this->event_id = $a_event_id;

        include_once './Services/ADN/EP/classes/class.adnExaminationEvent.php';
        $this->event = new adnExaminationEvent($a_event_id);

        $this->notification_mode = (bool) $a_notification_mode;
        
        parent::__construct($a_parent_obj, $a_parent_cmd);

        $this->id = "adn_tbl_ecd";

        include_once("./Services/ADN/EP/classes/class.adnExaminationEvent.php");
        $this->setTitle($lng->txt("adn_registered_candidates") . ": " .
            adnExaminationEvent::lookupName($this->event_id));

        if ($this->notification_mode) {
            if (adnPerm::check(adnPerm::EP, adnPerm::WRITE)) {
                $this->addMultiCommand("createLetters", $lng->txt("adn_create_notification_letters"));
            }
            $this->addColumn("", "", 1);
        }

        // column header
        $this->addColumn($this->lng->txt("adn_last_name"), "last_name");
        $this->addColumn($this->lng->txt("adn_first_name"), "first_name");
        $this->addColumn($this->lng->txt("adn_birthdate"), "birthdate");
        $this->addColumn($this->lng->txt("adn_participated"), "has_participated");
        $this->addColumn($this->lng->txt("adn_result"), "total_result");

        if (!$this->notification_mode) {
            $this->addColumn($this->lng->txt("adn_holdback"), "blocked_until");
        } else {
            include_once "Services/ADN/ED/classes/class.adnSubjectArea.php";
            $event = new adnExaminationEvent($this->event_id);
            if (adnSubjectArea::hasCasePart($event->getType())) {
                $this->has_case_part = true;
                $this->addColumn($this->lng->txt("adn_points"), "points");
            } else {
                $this->addColumn($this->lng->txt("adn_points"), "answers");
            }
        }
        
        $this->addColumn($this->lng->txt("actions"));
        
        $this->setDefaultOrderField("last_name");
        $this->setDefaultOrderDirection("asc");

        $this->setFormAction($ilCtrl->getFormAction($a_parent_obj));
        $this->setRowTemplate("tpl.candidate_row.html", "Services/ADN/ES");

        // get data
        $this->importData();
    }


    /**
     * Get examination event
     * @return adnExaminationEvent
     */
    public function getEvent()
    {
        return $this->event;
    }

    /**
     * Import data from DB
     */
    protected function importData()
    {
        // get candidates of event
        include_once("./Services/ADN/EP/classes/class.adnAssignment.php");
        $assignments = adnAssignment::getAllAssignments(
            array("event_id" => $this->event_id),
            array("first_name", "last_name", "birthdate",
                "blocked_until")
        );

        // calculate result
        if ($assignments) {
            foreach ($assignments as $idx => $item) {
                $assignments[$idx]["result_details"] = $item["score_mc"] + $item["score_case"];
            }
        }

        $this->setData($assignments);
    }

    /**
     * Fill table row
     *
     * @param array $a_set data array
     */
    protected function fillRow($a_set)
    {
        global $lng, $ilCtrl;

        // actions...
        $ilCtrl->setParameter($this->parent_obj, "ass_id", $a_set["id"]);

        if (!$this->notification_mode) {
            if (adnPerm::check(adnPerm::ES, adnPerm::WRITE)) {
                // edit scoring
                $this->tpl->setCurrentBlock("action");
                $this->tpl->setVariable("TXT_CMD", $lng->txt("adn_edit_scoring"));
                $this->tpl->setVariable(
                    "HREF_CMD",
                    $ilCtrl->getLinkTarget($this->parent_obj, "editScoring")
                );
                $this->tpl->parseCurrentBlock();
            }
        } else {
            if (
                adnPerm::check(adnPerm::ES, adnPerm::READ) and
                $this->isNotificationPossible($a_set['result_total'])
            ) {
                include_once './Services/ADN/Report/classes/class.adnReportScoreNotificationLetter.php';
                if (adnReportScoreNotificationLetter::hasFile($this->event_id, $a_set['id'])) {
                    // download notification letter
                    $this->tpl->setCurrentBlock("action");
                    $this->tpl->setVariable("TXT_CMD", $lng->txt("adn_download_notification_letter"));
                    $this->tpl->setVariable(
                        "HREF_CMD",
                        $ilCtrl->getLinkTarget($this->parent_obj, "downloadLetter")
                    );
                    $this->tpl->parseCurrentBlock();
                }
            }
        }

        $ilCtrl->setParameter($this->parent_obj, "ass_id", "");
        
        
        // name
        $this->tpl->setVariable("VAL_LAST_NAME", $a_set["last_name"]);
        $this->tpl->setVariable("VAL_FIRST_NAME", $a_set["first_name"]);

        // birthdate
        if (trim($a_set["birthdate"]) != "") {
            $this->tpl->setVariable(
                "VAL_BIRTHDATE",
                ilDatePresentation::formatDate(
                    new ilDate($a_set["birthdate"], IL_CAL_DATE)
                )
            );
        } else {
            $this->tpl->setVariable("VAL_BIRTHDATE", "-");
        }

        // participated?
        $this->tpl->setVariable(
            "VAL_PARTICIPATED",
            $a_set["has_participated"] ? $lng->txt("yes") : $lng->txt("no")
        );

        // score
        /* moved to adnAssignment::getAllAssignments() - because of report
        include_once './Services/ADN/ED/classes/class.adnSubjectArea.php';
        if(adnSubjectArea::hasCasePart($a_set['subject_area']) and $a_set['result_total'] == adnAssignment::SCORE_PASSED)
        {
            if($a_set['result_details'] < adnAssignment::TOTAL_SCORE_REQUIRED)
            {
                $this->tpl->setVariable("VAL_RESULT",
                    adnAssignment::getScoreText(adnAssignment::SCORE_FAILED_SUM));
            }
            else
            {
                $this->tpl->setVariable(
                    'VAL_RESULT',
                    adnAssignment::getScoreText(adnAssignment::SCORE_PASSED)
                );
            }
        }
        else
        {
        */
        $this->tpl->setVariable(
            "VAL_RESULT",
            adnAssignment::getScoreText($a_set["result_total"])
        );

        if (!$this->notification_mode) {
            // holdback
            $this->tpl->setCurrentBlock("blocked");
            if ($a_set["blocked_until"]) {
                $this->tpl->setVariable("VAL_BLOCKED_UNTIL", ilDatePresentation::formatDate(
                    new ilDate($a_set["blocked_until"], IL_CAL_DATE)
                ));
            } else {
                $this->tpl->setVariable("VAL_BLOCKED_UNTIL", "");
            }
            $this->tpl->parseCurrentBlock();
        } else {
            // checkbox
            if ($this->isNotificationPossible($a_set['result_total'])) {
                $this->tpl->setCurrentBlock("box");
                $this->tpl->setVariable("VAL_ID", $a_set["id"]);
                $this->tpl->parseCurrentBlock();
            } else {
                $this->tpl->touchBlock('cbox');
            }

            // result
            $this->tpl->setCurrentBlock("result_details");
            $this->tpl->setVariable("VAL_RESULT_DETAILS", $a_set["result_details"]);
            $this->tpl->parseCurrentBlock();
        }
    }

    /**
     * Check if notification is possible
     * @param int $a_ass_id
     * @return bool
     */
    public function isNotificationPossible($a_res)
    {
        switch ($this->getEvent()->getType()) {
            case 'gas':
            case 'chem':
                return true;

            default:
                return $a_res == adnAssignment::SCORE_FAILED;
        }
        return false;
    }
}
