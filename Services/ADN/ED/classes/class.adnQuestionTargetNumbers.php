<?php
/* Copyright (c) 2010 Leifos, GPL, see docs/LICENSE */

/**
 * Question target numbers application class
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.de>
 * @version $Id: class.adnQuestionTargetNumbers.php 27874 2011-02-25 16:36:28Z jluetzen $
 *
 * @ingroup ServicesADN
 */
class adnQuestionTargetNumbers extends adnDBBase
{
    protected $id; // [int]
    protected $area_id; // [string]
    protected $type; // [int]
    protected $number; // [int]
    protected $single; // [bool]
    protected $objectives; // [array]

    const TYPE_MC = 1;
    const TYPE_CASE = 2;

    /**
     * Constructor
     *
     * @param int $a_id instance id
     */
    public function __construct($a_id = null)
    {
        global $ilCtrl;

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
     * Set area id
     *
     * @param string $a_id
     */
    public function setArea($a_id)
    {
        $this->area_id = (string) $a_id;
    }

    /**
     * Get area id
     *
     * @return string
     */
    public function getArea()
    {
        return $this->area_id;
    }

    /**
     * Set number
     *
     * @param int $a_number
     */
    public function setNumber($a_number)
    {
        $this->number = (int) $a_number;
    }

    /**
     * Get number
     *
     * @return int
     */
    public function getNumber()
    {
        return $this->number;
    }

    /**
     * Set type
     *
     * @param int $a_type
     */
    public function setType($a_type)
    {
        if ($this->isValidType($a_type)) {
            $this->type = (int) $a_type;
        }
    }

    /**
     * Get type
     *
     * @return int
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Check if given is valid
     *
     * @param int $a_type
     * @return bool
     */
    public static function isValidType($a_type)
    {
        if (in_array((int) $a_type, array(self::TYPE_MC, self::TYPE_CASE))) {
            return true;
        }
        return false;
    }

    /**
     * Set single
     *
     * @param bool $a_single
     */
    public function setSingle($a_single)
    {
        $this->single = (bool) $a_single;
    }

    /**
     * Get single
     *
     * @return bool
     */
    public function isSingle()
    {
        return $this->single;
    }

    /**
     * Set objectives
     *
     * @param array $a_objectives
     */
    public function setObjectives($a_objectives)
    {
        $this->objectives = $a_objectives;
    }

    /**
     * Get objectives
     *
     * @return array
     */
    public function getObjectives()
    {
        return $this->objectives;
    }

    /**
     * Has objective been assigned?
     *
     * @return array
     */
    public function hasObjective()
    {
        if (is_array($this->objectives) && in_array($a_id, $this->objectives)) {
            return true;
        }
        return false;
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

        $res = $ilDB->query("SELECT subject_area,mc_case,nr_of_questions,max_one_per_objective" .
            " FROM adn_ed_quest_target_nr" .
            " WHERE id = " . $ilDB->quote($this->getId(), "integer"));
        $set = $ilDB->fetchAssoc($res);
        $this->setArea($set["subject_area"]);
        $this->setType($set["mc_case"]);
        $this->setNumber($set["nr_of_questions"]);
        $this->setSingle($set["max_one_per_objective"]);

        // get (sub-)objectives from subj-table
        $res = $ilDB->query("SELECT ed_objective_id,ed_subobjective_id" .
            " FROM adn_ed_target_nr_obj" .
            " WHERE ed_question_target_nr_id = " . $ilDB->quote($this->getId(), "integer"));
        $obj = array();
        while ($row = $ilDB->fetchAssoc($res)) {
            $obj[] = $row;
        }
        $this->setObjectives($obj);
        
        parent::_read($id, "adn_ed_quest_target_nr");
    }

    /**
     * Convert properties to DB fields
     *
     * @return array (subject_area, mc_case, nr_of_questions, max_one_per_objective)
     */
    protected function propertiesToFields()
    {
        $fields = array("subject_area" => array("text", $this->getArea()),
            "mc_case" => array("integer", $this->getType()),
            "nr_of_questions" => array("integer", $this->getNumber()),
            "max_one_per_objective" => array("integer", $this->isSingle()));
            
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

        $this->setId($ilDB->nextId("adn_ed_quest_target_nr"));
        $id = $this->getId();

        $fields = $this->propertiesToFields();
        $fields["id"] = array("integer", $id);
            
        $ilDB->insert("adn_ed_quest_target_nr", $fields);

        $this->saveObjectives();

        parent::_save($id, "adn_ed_quest_target_nr");
        
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
        
        $ilDB->update("adn_ed_quest_target_nr", $fields, array("id" => array("integer", $id)));

        $this->saveObjectives();

        parent::_update($id, "adn_ed_quest_target_nr");

        return true;
    }

    /**
     * Save objectives
     */
    protected function saveObjectives()
    {
        global $ilDB;

        $id = $this->getId();
        if ($id) {
            $ilDB->manipulate("DELETE FROM adn_ed_target_nr_obj" .
                " WHERE ed_question_target_nr_id = " . $ilDB->quote($id, "integer"));

            if (!empty($this->objectives)) {
                foreach ($this->objectives as $item) {
                    $fields = array("ed_question_target_nr_id" => array("integer", $id),
                        "ed_objective_id" => array("integer", $item["ed_objective_id"]),
                        "ed_subobjective_id" => array("integer", $item["ed_subobjective_id"]));

                    $ilDB->insert("adn_ed_target_nr_obj", $fields);
                }
            }
        }
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
            // U.PV.7.4: archived flag is not used here!
            $ilDB->manipulate("DELETE FROM adn_ed_target_nr_obj" .
                " WHERE ed_question_target_nr_id = " . $ilDB->quote($id, "integer"));
            $ilDB->manipulate("DELETE FROM adn_ed_quest_target_nr" .
                " WHERE id = " . $ilDB->quote($id, "integer"));
            $this->setId(null);
            return true;
        }
    }

    /**
     * Get all targets
     *
     * @param string $a_area
     * @param int $a_type
     * @return array
     */
    public static function getAllTargets($a_area = false, $a_type = false)
    {
        global $ilDB;

        $sql = "SELECT id,nr_of_questions,max_one_per_objective,subject_area,mc_case" .
            " FROM adn_ed_quest_target_nr";

        $where = array();
        if ($a_area) {
            $where[] = "subject_area = " . $ilDB->quote($a_area, "text");
        }
        if ($a_type && self::isValidType($a_type)) {
            $where[] = "mc_case = " . $ilDB->quote($a_type, "integer");
        }
        if (!empty($where)) {
            $sql .= " WHERE " . implode(" AND ", $where);
        }
        
        $res = $ilDB->query($sql);
        $all = array();
        $ids = array();
        while ($row = $ilDB->fetchAssoc($res)) {
            $all[$row["id"]] = $row;
            $ids[] = $row["id"];
        }

        if (!empty($all)) {
            $sql = "SELECT ed_question_target_nr_id, ed_objective_id, ed_subobjective_id" .
                " FROM adn_ed_target_nr_obj" .
                " WHERE " . $ilDB->in("ed_question_target_nr_id", $ids, false, "integer");
            $res = $ilDB->query($sql);
            while ($row = $ilDB->fetchAssoc($res)) {
                if ($row["ed_subobjective_id"]) {
                    $all[$row["ed_question_target_nr_id"]]["subobjectives"][] = $row["ed_subobjective_id"];
                } else {
                    $all[$row["ed_question_target_nr_id"]]["objectives"][] = $row["ed_objective_id"];
                }
            }
        }

        return $all;
    }

    /**
     * Lookup property
     *
     * @param integer $a_id target id
     * @param string $a_prop property
     * @return mixed property value
     */
    protected static function lookupProperty($a_id, $a_prop)
    {
        global $ilDB;

        $set = $ilDB->query("SELECT " . $a_prop .
            " FROM adn_ed_quest_target_nr" .
            " WHERE id = " . $ilDB->quote($a_id, "integer"));
        $rec = $ilDB->fetchAssoc($set);
        return $rec[$a_prop];
    }

    /**
     * Lookup name
     *
     * @param int $a_id
     * @return string
     */
    public static function lookupName($a_id)
    {
        global $ilDB;

        // for a useful identifier we need (sub-)objective data

        include_once "Services/ADN/ED/classes/class.adnObjective.php";
        include_once "./Services/ADN/ED/classes/class.adnSubobjective.php";
        include_once "Services/ADN/ED/classes/class.adnCatalogNumbering.php";

        $sql = "SELECT ed_objective_id, ed_subobjective_id" .
            " FROM adn_ed_target_nr_obj" .
            " WHERE ed_question_target_nr_id = " . $ilDB->quote($a_id, "integer");
        $res = $ilDB->query($sql);
        $all = array();
        while ($row = $ilDB->fetchAssoc($res)) {
            if ($row["ed_subobjective_id"]) {
                $all["subobjectives"][] = $row["ed_subobjective_id"];
            } else {
                $all["objectives"][] = $row["ed_objective_id"];
            }
        }

        $all_obj = array();
        if ($all["objectives"]) {
            foreach ($all["objectives"] as $obj_id) {
                $obj = new adnObjective($obj_id);
                $all_obj[] = adnCatalogNumbering::getAreaTextRepresentation($obj->getCatalogArea()) .
                    " / " . $obj->getNumber() . " " . $obj->getName();
            }
        }
        if ($all["subobjectives"]) {
            foreach ($all["subobjectives"] as $sobj_id) {
                $sobj = new adnSubobjective($sobj_id);
                $obj = new adnObjective($sobj->getObjective());
                $all_obj[] = adnCatalogNumbering::getAreaTextRepresentation($obj->getCatalogArea()) .
                    " / " . $obj->getNumber() . " " . $obj->getName() .
                    " / " . $sobj->getNumber() . " " . $sobj->getName();
            }
        }

        return implode("<br />", $all_obj);
    }

    /**
     * Save overall value
     *
     * @param string $a_area_id
     * @param int $a_type
     * @param int $a_value
     */
    public static function saveOverall($a_area_id, $a_type, $a_value)
    {
        global $ilDB, $ilUser;

        $ilDB->manipulate("DELETE FROM adn_ed_question_total" .
            " WHERE subject_area = " . $ilDB->quote($a_area_id, "text") .
            " AND mc_case = " . $ilDB->quote((int) $a_type, "integer"));

        $date = new ilDateTime(time(), IL_CAL_UNIX, ilTimeZone::UTC);

        $fields = array("subject_area" => array("text", $a_area_id),
            "mc_case" => array("integer", (int) $a_type),
            "total" => array("integer", $a_value),
            "last_update" => array("timestamp", $date->get(IL_CAL_DATETIME, "", ilTimeZone::UTC)),
            "last_update_user" => array("integer", $ilUser->getId()));

        return $ilDB->insert("adn_ed_question_total", $fields);
    }

    /**
     * Get overall value
     *
     * @param string $a_area_id
     * @param int $a_type
     */
    public static function readOverall($a_area_id, $a_type)
    {
        global $ilDB;

        $set = $ilDB->query("SELECT total" .
            " FROM adn_ed_question_total" .
            " WHERE subject_area = " . $ilDB->quote($a_area_id, "text") .
            " AND mc_case = " . $ilDB->quote((int) $a_type, "integer"));
        $row = $ilDB->fetchAssoc($set);
        return $row["total"];
    }

    /**
     * Process target numbers for given subject area, pick randomized questions
     *
     * This will not save anything, it just picks the questions ids according to the "rules"
     *
     * @param string $a_type
     * @return array question-ids
     */
    public static function generateMCSheet($a_type)
    {
        include_once "Services/ADN/ED/classes/class.adnMCQuestion.php";
        $targets = self::getAllTargets($a_type);
        $sheet_questions = array();
        foreach ($targets as $target) {
            // gather questions
            $questions = array();
            if (!empty($target["objectives"])) {
                foreach ($target["objectives"] as $obj_id) {
                    $questions["obj_" . $obj_id] = adnMCQuestion::getByObjective($obj_id, null, true);
                }
            }
            if (!empty($target["subobjectives"])) {
                foreach ($target["subobjectives"] as $sobj_id) {
                    $questions["sobj_" . $sobj_id] = adnMCQuestion::getBySubobjective($sobj_id, null, true);
                }
            }

            $all_questions = array();
            foreach ($questions as $obj_id => $qids) {
                // if only 1 per objective
                if ($target["max_one_per_objective"]) {
                    $one = array_rand($qids);
                    $qids = array($qids[$one]);
                }
                foreach ($qids as $qid) {
                    $all_questions[] = $qid;
                }
            }

            // choose random entries
            if ($target["nr_of_questions"] < count($all_questions)) {
                $picks = array_rand($all_questions, $target["nr_of_questions"]);
                if ($target["nr_of_questions"] == 1) {
                    $picks = array($picks);
                }
            }
            // not enough, us all anyways
            else {
                $picks = array_keys($all_questions);
            }
            if ($picks) {
                $questions_pick = array();
                foreach ($picks as $idx) {
                    $questions_pick[] = $all_questions[$idx];
                }
                $sheet_questions = array_merge($sheet_questions, $questions_pick);
            }
        }
        return $sheet_questions;
    }

    /**
     * Get relevant objectives for subject area / type
     *
     * @param string $a_type
     * @return array
     */
    public static function getObjectivesForType($a_type)
    {
        include_once "./Services/ADN/ED/classes/class.adnSubobjective.php";
        $targets = self::getAllTargets($a_type);
        foreach ($targets as $target) {
            if (!empty($target["objectives"])) {
                foreach ($target["objectives"] as $obj_id) {
                    $objectives[] = $obj_id;
                }
            }
            if (!empty($target["subobjectives"])) {
                foreach ($target["subobjectives"] as $sobj_id) {
                    $sobj = new adnSubobjective($sobj_id);
                    $objectives[] = $sobj->getObjective();
                }
            }
        }
        return array_unique($objectives);
    }

    /**
     * Validate set of questions against target numbers
     *
     * This will return an array with all invalid (sub-)objectives - if any
     *
     * @param string $a_type
     * @param array $a_questions
     * @return array
     */
    public static function validateMCSheet($a_type, array $a_questions)
    {
        // map questions to (sub-)objectives
        include_once "Services/ADN/ED/classes/class.adnExaminationQuestion.php";
        include_once "Services/ADN/ED/classes/class.adnObjective.php";
        include_once "./Services/ADN/ED/classes/class.adnSubobjective.php";
        $objectives = $subobjectives = array();
        foreach ($a_questions as $question_id) {
            $question = new adnExaminationQuestion($question_id);
            $obj_id = $question->getObjective();
            $sobj_id = $question->getSubobjective();

            // only active questions
            if ($question->getStatus()) {
                if ($sobj_id) {
                    $subobjectives[$sobj_id][] = $question_id;
                } else {
                    $objectives[$obj_id][] = $question_id;
                }
            }
        }

        $objectives_add = $subobjectives_add = array();
        $objectives_invalid = $subobjectives_invalid = array();

        // validate against targets
        $targets = self::getAllTargets($a_type);
        foreach ($targets as $target) {
            $max_nr = $target["nr_of_questions"];
            $max_one = $target["max_one_per_objective"];
            
            // get the current number of questions
            $counter = 0;
            if (!empty($target["objectives"])) {
                foreach ($target["objectives"] as $obj_id) {
                    if (isset($objectives[$obj_id])) {
                        $counter += count($objectives[$obj_id]);

                        if ($max_one && count($objectives[$obj_id]) > 1) {
                            $objectives_invalid[] = $obj_id;
                        }
                    }
                }
            }
            if (!empty($target["subobjectives"])) {
                foreach ($target["subobjectives"] as $sobj_id) {
                    if (isset($subobjectives[$sobj_id])) {
                        $counter += count($subobjectives[$sobj_id]);

                        if ($max_one && count($subobjectives[$sobj_id]) > 1) {
                            $subobjectives_invalid[] = $sobj_id;
                        }
                    }
                }
            }

            // process based on current number
            if (!empty($target["objectives"])) {
                foreach ($target["objectives"] as $obj_id) {
                    // if too few, but not (if max_one and at least 1)
                    if ($counter < $max_nr && !(isset($objectives[$obj_id]) && $max_one)) {
                        $objectives_invalid[] = $obj_id;
                        $objectives_add[] = $obj_id;
                    }
                    // if too many, but not if not set yet or (currently 1 and max one)
                    elseif ($counter > $max_nr && isset($objectives[$obj_id]) &&
                        !(count($objectives[$obj_id]) == 1 && $max_one)) {
                        $objectives_invalid[] = $obj_id;
                    }
                }
            }
            if (!empty($target["subobjectives"])) {
                foreach ($target["subobjectives"] as $sobj_id) {
                    // if too few, but not (if max_one and at least 1)
                    if ($counter < $max_nr && !(isset($subobjectives[$sobj_id]) && $max_one)) {
                        $subobjectives_invalid[] = $sobj_id;
                        $subobjectives_add[] = $sobj_id;
                    }
                    // if too many, but not if not set yet or (currently 1 and max one)
                    elseif ($counter > $max_nr && isset($subobjectives[$sobj_id]) &&
                        !(count($subobjectives[$sobj_id]) == 1 && $max_one)) {
                        $subobjectives_invalid[] = $sobj_id;
                    }
                }
            }
        }

        return array("objectives_add" => array_unique($objectives_add),
            "subobjectives_add" => array_unique($subobjectives_add),
            "objectives_invalid" => array_unique($objectives_invalid),
            "subobjectives_invalid" => array_unique($subobjectives_invalid));
    }

    /**
     * Get target ids by objective
     *
     * @param int $a_id
     * @return array
     */
    public static function getByObjective($a_id)
    {
        global $ilDB;
        
        $res = $ilDB->query("SELECT ed_question_target_nr_id" .
            " FROM adn_ed_target_nr_obj" .
            " WHERE ed_objective_id = " . $ilDB->quote($a_id, "integer"));
        $all = array();
        while ($row = $ilDB->fetchAssoc($res)) {
            $all[] = $row["ed_question_target_nr_id"];
        }
        return $all;
    }

    /**
     * Get target ids by subobjective
     *
     * @param int $a_id
     * @return array
     */
    public static function getBySubobjective($a_id)
    {
        global $ilDB;

        $res = $ilDB->query("SELECT ed_question_target_nr_id" .
            " FROM adn_ed_target_nr_obj" .
            " WHERE ed_subobjective_id = " . $ilDB->quote($a_id, "integer"));
        $all = array();
        while ($row = $ilDB->fetchAssoc($res)) {
            $all[] = $row["ed_question_target_nr_id"];
        }
        return $all;
    }
}
