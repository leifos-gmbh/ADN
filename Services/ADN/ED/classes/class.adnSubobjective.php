<?php
/* Copyright (c) 2010 Leifos, GPL, see docs/LICENSE */

/**
 * Subobjective application class
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.de>
 * @version $Id: class.adnSubobjective.php 28314 2011-04-01 13:27:14Z jluetzen $
 *
 * @ingroup ServicesADN
 */
class adnSubobjective extends adnDBBase
{
    protected int $id = 0;
    protected int $objective_id = 0;
    protected int $number = 0;
    protected string $name = '';
    protected string $topic = '';

    /**
     * Constructor
     *
     * @param int $a_id instance id
     */
    public function __construct($a_id = 0)
    {

        if ($a_id !== 0) {
            $this->setId($a_id);
            $this->read();
        }
    }
    
    /**
     * Get ids by objective
     * @param int $a_objective
     * @return int[]
     */
    public static function lookupIdByObjective($a_objective)
    {
        global $DIC;
        $ilDB = $DIC->database();
        
        $query = 'SELECT id from adn_ed_subobjective ' .
            'WHERE ed_objective_id = ' . $ilDB->quote($a_objective, 'integer');
        $res = $ilDB->query($query);
        
        $ids = array();
        while ($row = $res->fetchObject()) {
            $ids[] = $row->id;
        }
        return $ids;
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
     * Set parent objective id
     *
     * @param int $a_id
     */
    public function setObjective($a_id)
    {
        $this->objective_id = (int) $a_id;
    }

    /**
     * Get parent objective id
     *
     * @return int
     */
    public function getObjective()
    {
        return $this->objective_id;
    }

    /**
     * Set name
     *
     * @param string $a_name
     */
    public function setName($a_name)
    {
        $this->name = (string) $a_name;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
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
     * Set topic
     *
     * @param string $a_number
     */
    public function setTopic($a_topic)
    {
        $this->topic = (string) $a_topic;
    }

    /**
     * Get topic
     *
     * @return string
     */
    public function getTopic()
    {
        return $this->topic;
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

        $res = $this->db->query("SELECT title,nr,topic,ed_objective_id" .
            " FROM adn_ed_subobjective" .
            " WHERE id = " . $this->db->quote($this->getId(), "integer"));
        $set = $this->db->fetchAssoc($res);
        $this->setObjective($set["ed_objective_id"]);
        $this->setName($set["title"]);
        $this->setTopic($set["topic"]);
        $this->setNumber($set["nr"]);

        parent::_read($id, "adn_ed_subobjective");
    }

    /**
     * Convert properties to DB fields
     *
     * @return array (ed_objective_id, title, topic, nr)
     */
    protected function propertiesToFields()
    {
        $fields = array("ed_objective_id" => array("integer", $this->getObjective()),
            "title" => array("text", $this->getName()),
            "topic" => array("text", $this->getTopic()),
            "nr" => array("text", $this->getNumber()));
            
        return $fields;
    }

    /**
     * Create new db entry
     *
     * @return int new id
     */
    public function save()
    {

        $this->setId($this->db->nextId("adn_ed_subobjective"));
        $id = $this->getId();

        $fields = $this->propertiesToFields();
        $fields["id"] = array("integer", $id);
            
        $this->db->insert("adn_ed_subobjective", $fields);

        parent::_save($id, "adn_ed_subobjective");
        
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
        
        $this->db->update("adn_ed_subobjective", $fields, array("id" => array("integer", $id)));

        parent::_update($id, "adn_ed_subobjective");

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
            // U.PV.3.4: check if subobjective is used in any question

            $in_use = false;
            
            include_once "Services/ADN/ED/classes/class.adnExaminationQuestion.php";
            include_once "Services/ADN/ED/classes/class.adnQuestionTargetNumbers.php";
            if (sizeof(adnQuestionTargetNumbers::getBySubobjective($id)) ||
                sizeof(adnExaminationQuestion::getBySubobjective($id))) {
                ilLoggerFactory::getLogger('adn')->info('------ subobjective is in use.');
                $in_use = true;
            }

            if ($in_use) {
                $this->setArchived(true);
                $this->update();
            } else {
                ilLoggerFactory::getLogger('adn')->info('------ deleting subobjective.');
                $this->db->manipulate("DELETE FROM adn_ed_target_nr_obj" .
                    " WHERE ed_subobjective_id = " . $this->db->quote($id, "integer"));
                $this->db->manipulate("DELETE FROM adn_ed_subobjective" .
                    " WHERE id = " . $this->db->quote($id, "integer"));
                $this->setId(null);
            }
            return true;
        }
    }

    /**
     * Get all areas
     *
     * @param int $a_objective_id
     * @param int $a_number
     * @param bool $a_with_archived
     * @return array
     */
    public static function getAllSubobjectives(
        $a_objective_id,
        $a_number = null,
        $a_with_archived = false
    )
    {
        global $DIC;
        $ilDB = $DIC->database();

        $sql = "SELECT id,title AS name,nr,topic" .
            " FROM adn_ed_subobjective" .
            " WHERE ed_objective_id = " . $ilDB->quote($a_objective_id, "integer");

        if (!$a_with_archived) {
            $sql .= " AND archived < " . $ilDB->quote(1, "integer");
        }

        if ($a_number) {
            $sql .= " AND nr = " . $ilDB->quote($a_number, "integer");
        }

        $sql .= " ORDER BY nr";

        include_once "Services/ADN/ED/classes/class.adnObjective.php";
        $obj = new adnObjective($a_objective_id);
        $obj_nr = $obj->buildADNNumber();

        $res = $ilDB->query($sql);
        $all = array();
        while ($row = $ilDB->fetchAssoc($res)) {
            $row["adn_number"] = $obj_nr . "." . $row["nr"];
            $all[] = $row;
        }

        return $all;
    }

    /**
     * Get subobjective ids and names
     *
     * @param int $a_objective_id
     * @param int $a_old_value
     * @return array (id => caption)
     */
    public static function getSubobjectivesSelect($a_objective_id = null, $a_old_value = null)
    {
        global $DIC;
        $ilDB = $DIC->database();

        $sql = "SELECT id,nr,title" .
            " FROM adn_ed_subobjective";

        $where = array();
        if (!$a_old_value) {
            $where[] = "archived < " . $ilDB->quote(1, "integer");
        } else {
            $where[] = "(archived < " . $ilDB->quote(1, "integer") .
                " OR id = " . $ilDB->quote($a_old_value, "integer") . ")";
        }

        if ($a_objective_id) {
            $where[] = "ed_objective_id = " . $ilDB->quote($a_objective_id, "integer");
        }

        if (sizeof($where)) {
            $sql .= " WHERE " . implode(" AND ", $where);
        }

        $sql .= " ORDER BY nr,title";

        $res = $ilDB->query($sql);
        $all = array();
        while ($row = $ilDB->fetchAssoc($res)) {
            $all[$row["id"]] = $row["nr"] . " " . $row["title"];
        }

        return $all;
    }

    /**
     * Lookup property
     *
     * @param integer $a_id subobjective id
     * @param string $a_prop property
     * @return mixed property value
     */
    protected static function lookupProperty($a_id, $a_prop)
    {
        global $DIC;
        $ilDB = $DIC->database();

        $set = $ilDB->query("SELECT " . $a_prop .
            " FROM adn_ed_subobjective" .
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
        return self::lookupProperty($a_id, "title");
    }

    /**
     * Check if given number is unique for current objective
     *
     * @return bool
     */
    public function isUniqueNumber()
    {

        $id = $this->getId();
        $obj = $this->getObjective();

        $sql = "SELECT id FROM adn_ed_subobjective" .
            " WHERE ed_objective_id = " . $this->db->quote($obj, "integer") .
            " AND nr = " . $this->db->quote($this->getNumber(), "text");
        " AND archived < " . $this->db->quote(1, "integer");

        if ($id) {
            $sql .= " AND id <> " . $this->db->quote($id, "integer");
        }

        $set = $this->db->query($sql);
        return !(bool) $this->db->numRows($set);
    }

    /**
     * Build adn number
     *
     * @param adnObjective $a_objective
     * @return string
     */
    public function buildADNNumber($a_objective = null)
    {
        $obj = $a_objective;
        if (!$obj) {
            if ($this->getObjective()) {
                include_once "Services/ADN/ED/classes/class.adnObjective.php";
                $obj = new adnObjective($this->getObjective());
            } else {
                return false;
            }
        }

        // we do not have subobjectives for case objectives
        return $obj->buildADNNumber() . "." . (int) $this->getNumber();
    }
}
