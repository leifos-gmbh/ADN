<?php
/* Copyright (c) 2010 Leifos, GPL, see docs/LICENSE */

/**
 * Candidate answer sheet assignment application class
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.de>
 * @version $Id: class.adnAnswerSheetAssignment.php 28255 2011-03-29 08:35:18Z jluetzen $
 *
 * @ingroup ServicesADN
 */
class adnAnswerSheetAssignment extends adnDBBase
{
    protected int $id = 0;
    protected int $user_id = 0;
    protected int $sheet_id = 0;
    protected ilDateTime $generated_on;

    /**
     * Constructor
     *
     * @param int $a_id instance id
     * @param int $a_user_id
     * @param int $a_sheet_id
     */
    public function __construct($a_id = null, $a_user_id = null, $a_sheet_id = null)
    {

        if (!$a_id && $a_user_id && $a_sheet_id) {
            $this->setUser($a_user_id);
            $this->setSheet($a_sheet_id);
            $a_id = $this->find($a_user_id, $a_sheet_id);
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
     * Set sheet id
     *
     * @param int $a_id
     */
    public function setSheet($a_id)
    {
        $this->sheet_id = (int) $a_id;
    }

    /**
     * Get sheet id
     *
     * @return int
     */
    public function getSheet()
    {
        return $this->sheet_id;
    }

    /**
     * Set generated on date
     *
     * @param	ilDateTime	$a_val	Generated on date
     */
    public function setGeneratedOn(ilDateTime $a_val)
    {
        $this->generated_on = $a_val;
    }

    /**
     * Get generated on date
     *
     * @return	ilDateTime	Generated on date
     */
    public function getGeneratedOn()
    {
        return $this->generated_on;
    }

    /**
     * Read db entry
     */
    public function read()
    {

        $id = $this->getId();

        if (!$id) {
            return;
        }

        $res = $this->db->query("SELECT ep_answer_sheet_id, cp_professional_id, generated_on" .
            " FROM adn_ep_cand_sheet" .
            " WHERE id = " . $this->db->quote($id, "integer"));
        $set = $this->db->fetchAssoc($res);
        $this->setSheet($set["ep_answer_sheet_id"]);
        $this->setUser($set["cp_professional_id"]);
        $this->setGeneratedOn(new ilDateTime($set["generated_on"], IL_CAL_DATE, ilTimeZone::UTC));
        
        parent::_read($id, "adn_ep_cand_sheet");
    }

    /**
     * Convert properties to DB fields
     *
     * @return array (ep_answer_sheet_id, cp_professional_id)
     */
    protected function propertiesToFields()
    {
        $fields = array(
            "ep_answer_sheet_id" => array("integer", $this->getSheet()),
            "cp_professional_id" => array("integer", $this->getUser())
        );

        $date = $this->getGeneratedOn();
        if ($date && !$date->isNull()) {
            $fields["generated_on"] = array("timestamp", $date->get(
                IL_CAL_DATETIME,
                "",
                ilTimeZone::UTC
            ));
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

        // sequence
        $this->setId($this->db->nextId("adn_ep_cand_sheet"));
        $id = $this->getId();

        $fields = $this->propertiesToFields();
        $fields["id"] = array("integer", $id);
            
        $this->db->insert("adn_ep_cand_sheet", $fields);

        parent::_save($id, "adn_ep_cand_sheet");

        return $id;
    }

    /**
     * Update db entry
     *
     * @return bool
     */
    public function update()
    {

        $id = $this->getId();
        if (!$id) {
            return;
        }
        
        $fields = $this->propertiesToFields();

        $this->db->update("adn_ep_cand_sheet", $fields, array("id" => array("integer", $id)));
        parent::_update($id, "adn_ep_cand_sheet");

        return true;
    }

    /**
     * Delete from DB
     *
     * @return bool
     */
    public function delete()
    {

        $id = $this->getId();
        if ($id) {
            $this->db->manipulate("DELETE FROM adn_ep_cand_sheet" .
                " WHERE id = " . $this->db->quote($id, "integer"));
            $this->setId(null);
            return true;
        }
    }

    /**
     * Get all assignments (for user [and optional event])
     *
     * @param int $a_user_id
     * @param int $a_event_id
     * @return array
     */
    public static function getAllSheets($a_user_id, $a_event_id = false)
    {
        global $DIC;
        $ilDB = $DIC->database();

        $sql = "SELECT id,ep_answer_sheet_id,generated_on" .
            " FROM adn_ep_cand_sheet" .
            " WHERE cp_professional_id = " . $ilDB->quote($a_user_id, "integer");

        if ($a_event_id) {
            $event_sheets = self::getEventSheets($a_event_id);
            if ($event_sheets) {
                $sql .= " AND " . $ilDB->in("ep_answer_sheet_id", $event_sheets, "", "integer");
            } else {
                return array();
            }
        }

        $set = $ilDB->query($sql);
        $all = array();
        while ($row = $ilDB->fetchAssoc($set)) {
            $all[] = $row;
        }

        return $all;
    }

    /**
     * Get assignment and answer sheet ids (for user [and optional event])
     *
     * @param int $a_user_id
     * @param int $a_event_id
     * @param int $a_sheet_id
     * @return array (id => caption)
     */
    public static function getSheetsSelect(
        $a_user_id = false,
        $a_event_id = false,
        $a_sheet_id = false
    )
    {
        global $DIC;
        $ilDB = $DIC->database();

        $sql = "SELECT cs.id,cs.ep_answer_sheet_id" .
            " FROM adn_ep_cand_sheet cs" .
            " JOIN adn_ep_answer_sheet sh ON (cs.ep_answer_sheet_id = sh.id)";

        $where = array();
        if ($a_user_id) {
            $where[] = "cs.cp_professional_id = " . $ilDB->quote($a_user_id, "integer");
        }
        if ($a_event_id) {
            $event_sheets = self::getEventSheets($a_event_id);
            if ($event_sheets) {
                $where[] = $ilDB->in("cs.ep_answer_sheet_id", $event_sheets, "", "integer");
            } else {
                return array();
            }
        }
        if ($a_sheet_id) {
            $where[] = "cs.ep_answer_sheet_id = " . $ilDB->quote($a_sheet_id, "integer");
        }
        if (sizeof($where)) {
            $sql .= " WHERE " . implode(" AND ", $where);
        }
        $sql .= " ORDER BY sh.type";

        $set = $ilDB->query($sql);
        $all = array();
        while ($row = $ilDB->fetchAssoc($set)) {
            $all[$row["id"]] = $row["ep_answer_sheet_id"];
        }

        return $all;
    }

    /**
     * Get all sheets (for exam event)
     *
     * @return array (ids)
     */
    public static function getEventSheets($a_event_id)
    {
        if ($a_event_id) {
            include_once "Services/ADN/EP/classes/class.adnAnswerSheet.php";
            $all_sheets = adnAnswerSheet::getSheetsSelect($a_event_id);
            if (sizeof($all_sheets)) {
                return array_keys($all_sheets);
            }
        }
    }
    
    /**
     * Check if sheet is assigned (to any candidate)
     * @param object $a_sheet_id
     * @return bool
     */
    public static function isAssigned($a_sheet_id)
    {
        global $DIC;
        $ilDB = $DIC->database();
        
        $query = "SELECT * FROM adn_ep_cand_sheet " .
            "WHERE ep_answer_sheet_id = " . $ilDB->quote($a_sheet_id, 'integer');
        $res = $ilDB->query($query);
        while ($row = $res->fetchObject()) {
            return true;
        }
        return false;
    }

    /**
     * Check if user is already assigned to specific sheet
     *
     * @param int $a_user_id
     * @param int $a_sheet_id
     * @return int sheet assignment id
     */
    public static function find($a_user_id, $a_sheet_id)
    {
        global $DIC;
        $ilDB = $DIC->database();

        $sql = "SELECT id FROM adn_ep_cand_sheet" .
            " WHERE cp_professional_id = " . $ilDB->quote((int) $a_user_id, "integer") .
            " AND ep_answer_sheet_id = " . $ilDB->quote((int) $a_sheet_id, "integer");

        $set = $ilDB->query($sql);
        if ($ilDB->numRows($set)) {
            $row = $ilDB->fetchAssoc($set);
            return $row["id"];
        }
    }

    /**
     * Check if an already created report is deprecated
     *
     * Deprecated means that an included question or the sheet itself have been modified after the
     * report has been generated
     *
     * @return bool
     */
    public function isReportDeprecated()
    {

        // Check for modified questions
        $query = 'SELECT * FROM adn_ep_sheet_question ' .
        'JOIN adn_ed_question ON ed_question_id = id ' .
        'WHERE ep_answer_sheet_id = ' . $this->db->quote($this->getSheet(), 'integer') . ' ' .
        'AND last_update > ' . $this->db->quote(
            $this->getGeneratedOn()->get(IL_CAL_DATETIME, '', ilTimeZone::UTC),
            'timestamp'
        );

        $res = $this->db->query($query);
        if ($res->numRows()) {
            return true;
        }

        // Check for modified question sheets
        $query = "SELECT * FROM adn_ep_answer_sheet " .
        'WHERE last_update > ' . $this->db->quote(
            $this->getGeneratedOn()->get(IL_CAL_DATETIME, '', ilTimeZone::UTC),
            'timestamp'
        );
        $res = $this->db->query($query);

        return $res->numRows() ? true : false;
    }
}
