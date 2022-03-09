<?php
/* Copyright (c) 2010 Leifos, GPL, see docs/LICENSE */

include_once("./Services/ADN/Base/classes/class.adnDBBase.php");

/**
 * Candidate assignment application class
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.de>
 * @version $Id: class.adnAssignment.php 32253 2011-12-21 12:49:35Z jluetzen $
 *
 * @ingroup ServicesADN
 */
class adnAssignment extends adnDBBase
{
    protected $id; // [int]
    protected $user_id; // [int]
    protected $event_id; // [int]
    protected $invited_on; // [ilDate]
    protected $scoring_update; // [ilDate]
    protected $scoring_update_user; // [int]

    const SCORE_NOT_SCORED = 0;
    const SCORE_FAILED = 1;
    const SCORE_PASSED = 2;
    const SCORE_FAILED_SUM = 3;

    const TOTAL_SCORE_REQUIRED = 44;

    /**
     * Constructor
     *
     * @param int $a_id instance id
     * @param int $a_user_id
     * @param int $a_event_id
     */
    public function __construct($a_id = null, $a_user_id = null, $a_event_id = null)
    {
        global $ilCtrl;

        if (!$a_id && $a_user_id && $a_event_id) {
            $this->setUser($a_user_id);
            $this->setEvent($a_event_id);
            $a_id = $this->find($a_user_id, $a_event_id);
        }
        if ($a_id) {
            $this->setId($a_id);
            $this->read();
        }
    }

    /**
     * Set id
     *
     * @param int $a_id
     */
    public function setId($a_id)
    {
        $this->id = (int) $a_id;
    }

    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set user id
     *
     * @param int $a_id
     */
    public function setUser($a_id)
    {
        $this->user_id = (int) $a_id;
    }

    /**
     * Get user id
     *
     * @return int
     */
    public function getUser()
    {
        return $this->user_id;
    }

    /**
     * Set event id
     *
     * @param int $a_id
     */
    public function setEvent($a_id)
    {
        $this->event_id = (int) $a_id;
    }

    /**
     * Get event id
     *
     * @return int
     */
    public function getEvent()
    {
        return $this->event_id;
    }

    /**
     * Set invited on date
     *
     * @param	ilDate	$a_val	invited on date
     */
    public function setInvitedOn(ilDate $a_val)
    {
        $this->invited_on = $a_val;
    }

    /**
     * Get invited on date
     *
     * @return	ilDate	invited on date
     */
    public function getInvitedOn()
    {
        return $this->invited_on;
    }

    /**
     * Set has participated
     *
     * @param	boolean	$a_val	has participated
     */
    public function setHasParticipated($a_val)
    {
        $this->has_participated = $a_val;
    }
    
    /**
     * Get has participated
     *
     * @return	boolean	has participated
     */
    public function getHasParticipated()
    {
        return $this->has_participated;
    }

    /**
     * Set score mc
     *
     * @param	int $a_val	score mc
     */
    public function setScoreMc($a_val)
    {
        $this->score_mc = $a_val;
    }

    /**
     * Get score mc
     *
     * @return	float	score mc
     */
    public function getScoreMc()
    {
        return $this->score_mc;
    }

    /**
     * Set score case
     *
     * @param	float	$a_val	score case
     */
    public function setScoreCase($a_val)
    {
        $this->score_case = $a_val;
    }

    /**
     * Get score case
     *
     * @return	float	score case
     */
    public function getScoreCase()
    {
        return $this->score_case;
    }

    /**
     * Set result mc
     *
     * @param	int	$a_val	result mc
     */
    public function setResultMc($a_val)
    {
        $this->result_mc = $a_val;
    }

    /**
     * Get result mc
     *
     * @return	int	result mc
     */
    public function getResultMc()
    {
        return $this->result_mc;
    }

    /**
     * Get result sum of mc and case questions
     * @return float
     */
    public function getScoreSum()
    {
        return (float) $this->score_mc + $this->score_case;
    }

    /**
     * Set result case
     *
     * @param	int	$a_val	result case
     */
    public function setResultCase($a_val)
    {
        $this->result_case = $a_val;
    }

    /**
     * Get result case
     *
     * @return	int	result case
     */
    public function getResultCase()
    {
        return $this->result_case;
    }

    /**
     * Set notified on
     *
     * @param	timestamp	$a_val	notified on
     */
    public function setNotifiedOn($a_val)
    {
        $this->notified_on = $a_val;
    }

    /**
     * Get notified on
     *
     * @return	timestamp	notified on
     */
    public function getNotifiedOn()
    {
        return $this->notified_on;
    }

    /**
     * Set access code
     *
     * @param	string	$a_val	access code
     */
    public function setAccessCode($a_val)
    {
        $this->access_code = $a_val;
    }

    /**
     * Get access code
     *
     * @return	string	access code
     */
    public function getAccessCode()
    {
        return $this->access_code;
    }
    
    /**
     * Set last scoring update date
     *
     * @param	ilDateTime	$a_val	scoring update date
     */
    public function setLastScoringUpdate(ilDateTime $a_val)
    {
        $this->scoring_update = $a_val;
    }

    /**
     * Get last scoring update date
     *
     * @return	ilDate	scoring update date
     */
    public function getLastScoringUpdate()
    {
        return $this->scoring_update;
    }
    
    /**
     * Set last scoring update user
     *
     * @param	integer	$a_val	user id
     */
    public function setLastScoringUpdateUser($a_val)
    {
        $this->scoring_update_user = (int) $a_val;
    }

    /**
     * Get last scoring update user
     *
     * @return	integer	user id
     */
    public function getLastScoringUpdateUser()
    {
        return $this->scoring_update_user;
    }

    /**
     * Read db entry
     */
    public function read()
    {
        global $ilDB;

        $id = $this->getId();

        if (!$id) {
            return;
        }

        $res = $ilDB->query("SELECT ep_exam_event_id, cp_professional_id, " .
            " invited_on, has_participated, score_mc, score_case, result_mc, " .
            " result_case, notified_on, access_code, scoring_update, scoring_update_user " .
            " FROM adn_ep_assignment" .
            " WHERE id = " . $ilDB->quote($id, "integer"));
        $set = $ilDB->fetchAssoc($res);
        $this->setEvent($set["ep_exam_event_id"]);
        $this->setUser($set["cp_professional_id"]);
        $this->setInvitedOn(new ilDate($set["invited_on"], IL_CAL_DATE, ilTimeZone::UTC));
        $this->setHasParticipated($set["has_participated"]);
        $this->setScoreMc($set["score_mc"] / 10);
        $this->setScoreCase($set["score_case"] / 10);
        $this->setResultMc($set["result_mc"]);
        $this->setResultCase($set["result_case"]);
        $this->setNotifiedOn($set["notified_on"]);
        $this->setAccessCode($set["access_code"]);
        $this->setLastScoringUpdate(new ilDateTime($set["scoring_update"], IL_CAL_DATETIME, ilTimeZone::UTC));
        $this->setLastScoringUpdateUser($set["scoring_update_user"]);

        parent::_read($id, "adn_ep_assignment");
    }

    /**
     * Convert properties to DB fields
     *
     * @return array (ep_exam_event_id, cp_professional_id, has_participated, score_mc, score_case,
     * result_mc, result_case, notified_on, access_code, invited_on)
     */
    protected function propertiesToFields()
    {
        $fields = array(
            "ep_exam_event_id" => array("integer", $this->getEvent()),
            "cp_professional_id" => array("integer", $this->getUser()),
            "has_participated" => array("integer", $this->getHasParticipated()),
            "score_mc" => array("integer", $this->getScoreMc() * 10),
            "score_case" => array("integer", $this->getScoreCase() * 10),
            "result_mc" => array("integer", $this->getResultMc()),
            "result_case" => array("integer", $this->getResultCase()),
            "notified_on" => array("timestamp", $this->getNotifiedOn()),
            "access_code" => array("text", $this->getAccessCode()),
            "scoring_update_user" => array("integer", $this->getLastScoringUpdateUser())
        );

        $date = $this->getInvitedOn();
        if ($date && !$date->isNull()) {
            $fields["invited_on"] = array("timestamp", $date->get(IL_CAL_DATE, "", ilTimeZone::UTC));
        }
        
        $date = $this->getLastScoringUpdate();
        if ($date && !$date->isNull()) {
            $fields["scoring_update"] = array("timestamp", $date->get(IL_CAL_DATETIME, "", ilTimeZone::UTC));
        }
        
        return $fields;
    }

    /**
     * Create new db entry
     *
     * @return int new id
     */
    public function save()
    {
        global $ilDB;

        // sequence
        $this->setId($ilDB->nextId("adn_ep_assignment"));
        $id = $this->getId();

        $fields = $this->propertiesToFields();
        $fields["id"] = array("integer", $id);
            
        $ilDB->insert("adn_ep_assignment", $fields);

        parent::_save($id, "adn_ep_assignment");

        return $id;
    }

    /**
     * Update db entry
     *
     * @return bool
     */
    public function update()
    {
        global $ilDB;

        $id = $this->getId();
        if (!$id) {
            return;
        }
        
        $fields = $this->propertiesToFields();

        $ilDB->update("adn_ep_assignment", $fields, array("id" => array("integer", $id)));
        parent::_update($id, "adn_ep_assignment");

        return true;
    }

    /**
     * Delete from DB
     *
     * @return bool
     */
    public function delete()
    {
        global $ilDB;

        $id = $this->getId();
        if ($id) {
            // archived flag not used here
            include_once "Services/ADN/EP/classes/class.adnAnswerSheetAssignment.php";
            $sheets = adnAnswerSheetAssignment::getSheetsSelect($this->getUser(), $this->getEvent());
            if ($sheets) {
                $sheets = array_keys($sheets);

                // if there are answers for sheet from candidate: cancel
                include_once "Services/ADN/EC/classes/class.adnTest.php";
                foreach ($sheets as $cand_sheet_id) {
                    if (adnTest::hasAnswered($cand_sheet_id)) {
                        return false;
                    }
                }

                $ilDB->manipulate("DELETE FROM adn_ep_cand_sheet" .
                    " WHERE " . $ilDB->in("id", $sheets, "", "integer"));
            }
            $ilDB->manipulate("DELETE FROM adn_ep_assignment" .
                " WHERE id = " . $ilDB->quote($id, "integer"));
            $this->setId(null);
            return true;
        }
    }

    /**
     * Get all assignments (optional: various filters)
     *
     * @param array $a_filter
     * @return array
     */
    public static function getAllAssignments(array $a_filter = null, array $a_user_fields = null)
    {
        global $ilDB;

        $sql = "SELECT a.id,ep_exam_event_id,cp_professional_id,invited_on,has_participated," .
            "score_mc,score_case,result_mc,result_case,notified_on,access_code, e.subject_area" .
            " FROM adn_ep_assignment a" .
            " JOIN adn_ep_exam_event e ON (a.ep_exam_event_id = e.id) ";

        $where = array();
        if (isset($a_filter["event_id"]) && $a_filter["event_id"]) {
            $where[] = "ep_exam_event_id = " . $ilDB->quote($a_filter["event_id"], "integer");
        }
        if (isset($a_filter["user_id"]) && $a_filter["user_id"]) {
            $where[] = "cp_professional_id = " . $ilDB->quote($a_filter["user_id"], "integer");
        }
        if (sizeof($where)) {
            $sql .= " WHERE " . implode(" AND ", $where);
        }

        $res = $ilDB->query($sql);
        $all = array();

        // add user fields
        if (is_array($a_user_fields)) {
            include_once("./Services/ADN/ES/classes/class.adnCertifiedProfessional.php");
        }

        while ($row = $ilDB->fetchAssoc($res)) {
            $row['score_mc'] /= 10;
            $row['score_case'] /= 10;
            
            // add user fields
            if (is_array($a_user_fields)) {
                $cand = adnCertifiedProfessional::getAllCandidates(
                    array("id" => $row["cp_professional_id"]),
                    true
                );
                foreach ($a_user_fields as $f) {
                    $row[$f] = $cand[$row["cp_professional_id"]][$f];
                }
            }

            // calculate total result
            // for gas/chem use both results, otherwise only mc
            include_once("./Services/ADN/ED/classes/class.adnSubjectArea.php");
            if (adnSubjectArea::hasCasePart($row["subject_area"])) {
                $row["result_total"] =
                     min(array((int) $row["result_mc"], (int) $row["result_case"]));
                
                // if total score is below required value result is set to failed
                if (
                    $row["result_total"] == self::SCORE_PASSED &&
                    ($row["score_mc"] + $row["score_case"]) < self::TOTAL_SCORE_REQUIRED) {
                    $row["result_total"] = self::SCORE_FAILED_SUM;
                }
            } else {
                $row["result_total"] = (int) $row["result_mc"];
            }

            // check filter for total result
            $filter_ok = true;
            if (isset($a_filter["result_total"]) &&
                ($row["result_total"] != $a_filter["result_total"])) {
                $filter_ok = false;
            }

            if ($filter_ok) {
                $all[] = $row;
            }
        }

        return $all;
    }

    /**
     * Get assignments for current examination events and specific user
     *
     * @param int $a_user_id
     * @param bool $a_archived show current or past events
     * @return array
     */
    public static function getAllCurrentUserAssignments($a_user_id, $a_archived = false)
    {
        global $ilDB;

        // get all current events
        include_once "Services/ADN/EP/classes/class.adnExaminationEvent.php";
        $events = array();
        foreach (adnExaminationEvent::getAllEvents(null, $a_archived) as $item) {
            $events[] = $item["id"];
        }

        $all = array();
        if (sizeof($events)) {
            $set = $ilDB->query("SELECT ep_exam_event_id" .
                " FROM adn_ep_assignment" .
                " WHERE cp_professional_id = " . $ilDB->quote($a_user_id, "integer") .
                " AND " . $ilDB->in("ep_exam_event_id", $events, "", "integer"));
            while ($row = $ilDB->fetchAssoc($set)) {
                $all[] = $row["ep_exam_event_id"];
            }
        }
        return $all;
    }

    /**
     * Get all invitations for event
     *
     * @param int $a_event_id
     * @return array
     */
    public static function getAllInvitations($a_event_id)
    {
        global $ilDB;

        $set = $ilDB->query("SELECT cp_professional_id,invited_on" .
            " FROM adn_ep_assignment" .
            " WHERE ep_exam_event_id = " . $ilDB->quote($a_event_id, "integer") .
            " AND invited_on > " . $ilDB->quote("1970-01-01", "timestamp"));
        $all = array();
        while ($row = $ilDB->fetchAssoc($set)) {
            $all[$row["cp_professional_id"]] = new ilDate($row["invited_on"], IL_CAL_DATE, ilTimeZone::UTC);
        }

        return $all;
    }

    /**
     * Check if user is already assigned to event
     *
     * @param int $a_user_id
     * @param int $a_event_id
     * @return int
     */
    public static function find($a_user_id, $a_event_id)
    {
        global $ilDB;

        $sql = "SELECT id FROM adn_ep_assignment" .
            " WHERE cp_professional_id = " . $ilDB->quote((int) $a_user_id, "integer") .
            " AND ep_exam_event_id = " . $ilDB->quote((int) $a_event_id, "integer");

        $set = $ilDB->query($sql);
        if ($ilDB->numRows($set)) {
            $row = $ilDB->fetchAssoc($set);
            return $row["id"];
        }
    }

    /**
     * Get score text for const value
     *
     * @param int score
     * @return string score text
     */
    public static function getScoreText($a_score)
    {
        global $lng;

        $t = "";
        switch ($a_score) {
            case self::SCORE_NOT_SCORED:
                $t = $lng->txt("adn_not_scored");
                break;
            case self::SCORE_FAILED:
                $t = $lng->txt("adn_failed");
                break;
            case self::SCORE_PASSED:
                $t = $lng->txt("adn_passed");
                break;
            case self::SCORE_FAILED_SUM:
                $t = $lng->txt('adn_failed_sum');
                break;
        }
        return $t;
    }

    /**
     * Get all valid scores and score texts
     *
     * @return array
     */
    public static function getAllScores()
    {
        return array(
            self::SCORE_NOT_SCORED =>
                self::getScoreText(
                    self::SCORE_NOT_SCORED
                ),
            self::SCORE_FAILED =>
                self::getScoreText(
                    self::SCORE_FAILED
                ),
            self::SCORE_PASSED =>
                self::getScoreText(
                    self::SCORE_PASSED
                )
        );
    }

    /**
     * Prepare online test for event. This function ensures that
     * a) All participants have a user account with login
     * b) All participants have an access code for the online test
     *
     * @param int $a_event_id event id
     */
    public static function prepareOnlineTest($a_event_id)
    {
        global $ilDB;

        $assignments =
            adnAssignment::getAllAssignments(array("event_id" => $a_event_id));
        foreach ($assignments as $ass) {
            include_once("./Services/ADN/ES/classes/class.adnCertifiedProfessional.php");
            adnCertifiedProfessional::prepareUser($ass["cp_professional_id"]);
            if (trim($ass["access_code"]) == "") {
                adnAssignment::createAccessCode($ass["id"]);
            }
        }
    }
    
    /**
     * Create access code for assignment (event & user)
     *
     * @param int $a_ass_id assignment id
     */
    protected static function createAccessCode($a_ass_id)
    {
        global $ilDB;

        $cp_id = self::lookupCertifiedProfessional($a_ass_id);
        $code = substr(ilUtil::randomhash(), 0, 6);
        while (adnAssignment::codeUsed($cp_id, $code)) {
            $code = substr(ilUtil::randomhash(), 0, 6);
        }

        $ilDB->manipulate("UPDATE adn_ep_assignment" .
            " SET access_code = " . $ilDB->quote($code, "text") .
            " WHERE id = " . $ilDB->quote($a_ass_id, "integer"));
    }

    /**
     * Check whether an access code has already been used for a candidate
     *
     * @param int $a_cp_id certified professional id
     * @param string $a_code access code
     * @return boolean
     */
    public static function codeUsed($a_cp_id, $a_code)
    {
        global $ilDB;

        $set = $ilDB->query(
            "SELECT id" .
            " FROM adn_ep_assignment" .
            " WHERE cp_professional_id = " . $ilDB->quote($a_cp_id, "integer") .
            " AND access_code = " . $ilDB->quote($a_code, "text")
        );
        if ($rec = $ilDB->fetchAssoc($set)) {
            return true;
        }
        return false;
    }

    /**
     * Lookup property
     *
     * @param integer $a_id certificate id
     * @param string $a_prop property
     * @return mixed property value
     */
    protected static function lookupProperty($a_id, $a_prop)
    {
        global $ilDB;

        $set = $ilDB->query("SELECT " . $a_prop .
            " FROM adn_ep_assignment" .
            " WHERE id = " . $ilDB->quote($a_id, "integer"));
        $rec = $ilDB->fetchAssoc($set);
        return $rec[$a_prop];
    }

    /**
     * Lookup certified professional (from assignment)
     *
     * @param int $a_ass_id assignment id
     * @return int certified professional is
     */
    public static function lookupCertifiedProfessional($a_ass_id)
    {
        return self::lookupProperty($a_ass_id, "cp_professional_id");
    }

    /**
     * Get assignment id for certified professional id and access code
     *
     * @param int $a_cp_id certified professional id
     * @param string $a_code access code
     * @return int assignment id (0 if no assignment has been found)
     */
    public static function getAssignmentIdForCodeAndCP($a_cp_id, $a_code)
    {
        global $ilDB;

        $set = $ilDB->query(
            "SELECT id" .
            " FROM adn_ep_assignment" .
            " WHERE cp_professional_id = " . $ilDB->quote($a_cp_id, "integer") .
            " AND access_code = " . $ilDB->quote($a_code, "text")
        );
        if ($rec = $ilDB->fetchAssoc($set)) {
            return $rec["id"];
        }
        return 0;
    }

    /**
     * Check whether login/code is valid access code combination
     *
     * @param string $a_login
     * @param string $a_access_code
     * @return bool
     */
    public static function isValidAccessCode($a_login, $a_access_code)
    {
        include_once("./Services/ADN/ES/classes/class.adnCertifiedProfessional.php");
        $cp_id = adnCertifiedProfessional::getCPIdForUserLogin($a_login);
        $ass_id = adnAssignment::getAssignmentIdForCodeAndCP($cp_id, $a_access_code);
        if ($ass_id > 0) {
            // get event
            $ass = new adnAssignment($ass_id);
            include_once("./Services/ADN/EP/classes/class.adnExaminationEvent.php");
            $ev = new adnExaminationEvent($ass->getEvent());

            // check if examination is today
            $from = $ev->getDateFrom();
            $from_str = $from->get(IL_CAL_DATE);
            if (substr($from_str, 0, 10) == substr(ilUtil::now(), 0, 10)) {
                return true;
            }
        }
        return false;
    }
}
