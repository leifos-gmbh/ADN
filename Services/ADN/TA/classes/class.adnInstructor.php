<?php
/* Copyright (c) 2010 Leifos, GPL, see docs/LICENSE */

/**
 * Instructor application class
 *
 * All instructors have a parent provider
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.de>
 * @version $Id: class.adnInstructor.php 27891 2011-02-28 11:46:53Z jluetzen $
 *
 * @ingroup ServicesADN
 */
class adnInstructor extends adnDBBase
{
    protected int $id = 0;
    protected int $provider_id = 0;
    protected string $last_name = '';
    protected string $first_name = '';
    /**
     * @var string[]
     */
    protected array $training_types = [];
    /**
     * @var int[]
     */
    protected array $expertise = [];

    /**
     * Constructor
     *
     * @param int $a_id instance id
     */
    public function __construct($a_id = null)
    {

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
     * Set provider
     *
     * @param int $a_id
     */
    public function setProvider($a_id)
    {
        $this->provider_id = (int) $a_id;
    }

    /**
     * Get provider
     *
     * @return int
     */
    public function getProvider()
    {
        return $this->provider_id;
    }

    /**
     * Set last name
     *
     * @param string $a_last_name
     */
    public function setLastName($a_last_name)
    {
        $this->last_name = (string) $a_last_name;
    }

    /**
     * Get last name
     *
     * @return string
     */
    public function getLastName()
    {
        return $this->last_name;
    }

    /**
     * Set first name
     *
     * @param string $a_first_name
     */
    public function setFirstName($a_first_name)
    {
        $this->first_name = (string) $a_first_name;
    }

    /**
     * Get first name
     *
     * @return string
     */
    public function getFirstName()
    {
        return $this->first_name;
    }

    /**
     * Set training types
     *
     * @param array $a_types
     */
    public function setTypesOfTraining(array $a_types)
    {
        $this->training_types = $a_types;
    }

    /**
     * Get training types
     *
     * @param array
     */
    public function getTypesOfTraining()
    {
        return $this->training_types;
    }

    /**
     * Has given type of training?
     *
     * @param string $a_type
     * @return bool
     */
    public function hasTypeOfTraining($a_type)
    {
        return is_array($this->training_types) && in_array($a_type, $this->training_types);
    }

    /**
     * Set areas of expertise
     *
     * @param array $a_areas
     */
    public function setAreasOfExpertise(array $a_areas)
    {
        $this->expertise = $a_areas;
    }

    /**
     * Get areas of expertise
     *
     * @param array
     */
    public function getAreasOfExpertise()
    {
        return $this->expertise;
    }

    /**
     * Has given area of expertise?
     *
     * @param string $a_area
     * @return bool
     */
    public function hasAreaOfExpertise($a_area)
    {
        return is_array($this->expertise) && in_array($a_area, $this->expertise);
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

        $res = $this->db->query("SELECT ta_provider_id,last_name,first_name" .
            " FROM adn_ta_instructor" .
            " WHERE id = " . $this->db->quote($this->getId(), "integer"));
        $set = $this->db->fetchAssoc($res);
        $this->setProvider($set["ta_provider_id"]);
        $this->setLastName($set["last_name"]);
        $this->setFirstName($set["first_name"]);

        parent::_read($id, "adn_ta_instructor");

        // get instructor training types
        $set = $this->db->query("SELECT *" .
            " FROM adn_ta_instr_ttype" .
            " WHERE ta_instructor_id = " . $id);
        $types = array();
        while ($rec = $this->db->fetchAssoc($set)) {
            $types[] = $rec["training_type"];
        }
        $this->setTypesOfTraining($types);

        // get instructor areas of expertise
        $set = $this->db->query("SELECT *" .
            " FROM adn_ta_instructor_exp" .
            " WHERE ta_instructor_id = " . $id);
        $areas = array();
        while ($rec = $this->db->fetchAssoc($set)) {
            $areas[] = $rec["ta_expertise_id"];
        }
        $this->setAreasOfExpertise($areas);
    }

    /**
     * Convert properties to DB fields
     *
     * @return array (ta_provider_id, last_name, first_name)
     */
    protected function propertiesToFields()
    {
        $fields = array("ta_provider_id" => array("integer", $this->getProvider()),
            "last_name" => array("text", $this->getLastName()),
            "first_name" => array("text", $this->getFirstName()));
            
        return $fields;
    }

    /**
     * Save training types (in sub-table)
     */
    protected function saveTrainingTypes()
    {
        
        $id = $this->getId();
        if ($id) {
            $this->db->manipulate("DELETE FROM adn_ta_instr_ttype" .
                " WHERE ta_instructor_id = " . $this->db->quote($id, "integer"));

            if (is_array($this->training_types)) {
                foreach ($this->training_types as $type) {
                    $fields = array("ta_instructor_id" => array("integer", $id),
                        "training_type" => array("text", $type));

                    $this->db->insert("adn_ta_instr_ttype", $fields);
                }
            }
        }
    }

    /**
     * Save areas of expertise (in sub-table)
     */
    protected function saveAreasOfExpertise()
    {

        $id = $this->getId();
        if ($id) {
            $this->db->manipulate("DELETE FROM adn_ta_instructor_exp" .
                " WHERE ta_instructor_id = " . $this->db->quote($id, "integer"));

            if (is_array($this->expertise)) {
                foreach ($this->expertise as $area) {
                    $fields = array("ta_instructor_id" => array("integer", $id),
                        "ta_expertise_id" => array("integer", $area));

                    $this->db->insert("adn_ta_instructor_exp", $fields);
                }
            }
        }
    }

    /**
     * Create new db entry
     *
     * @return int new id
     */
    public function save()
    {

        // sequence
        $this->setId($this->db->nextId("adn_ta_instructor"));
        $id = $this->getId();

        $fields = $this->propertiesToFields();
        $fields["id"] = array("integer", $id);
            
        $this->db->insert("adn_ta_instructor", $fields);

        $this->saveTrainingTypes();
        $this->saveAreasOfExpertise();

        parent::_save($id, "adn_ta_instructor");
        
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
        
        $this->db->update("adn_ta_instructor", $fields, array("id" => array("integer", $id)));

        $this->saveTrainingTypes();
        $this->saveAreasOfExpertise();

        parent::_update($id, "adn_ta_instructor");

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
            // U.SV.2.4: archived flag is not used as instructors are no foreign items
            $this->db->manipulate("DELETE FROM adn_ta_instructor_exp" .
                " WHERE ta_instructor_id = " . $this->db->quote($id, "integer"));
            $this->db->manipulate("DELETE FROM adn_ta_instr_ttype" .
                " WHERE ta_instructor_id = " . $this->db->quote($id, "integer"));
            $this->db->manipulate("DELETE FROM adn_ta_instructor" .
                " WHERE id = " . $this->db->quote($id, "integer"));
            $this->setId(null);
            return true;
        }
    }

    /**
     * Get all instructors (for provider)
     *
     * @param int $a_provider_id
     * @param bool $a_with_archived
     * @return array
     */
    public static function getAllInstructors($a_provider_id, $a_with_archived = false)
    {
        global $DIC;
        $ilDB = $DIC->database();

        $sql = "SELECT id,last_name,first_name" .
            " FROM adn_ta_instructor" .
            " WHERE ta_provider_id = " . $ilDB->quote($a_provider_id, "integer");
        if (!$a_with_archived) {
            $sql .= " AND archived < " . $ilDB->quote(1, "integer");
        }
        $res = $ilDB->query($sql);
        $all = array();
        $ids = array();
        while ($row = $ilDB->fetchAssoc($res)) {
            $ids[] = $row["id"];
            $all[$row["id"]] = $row;
        }

        // add instructor training types
        $set = $ilDB->query("SELECT ta_instructor_id,training_type" .
            " FROM adn_ta_instr_ttype" .
            " WHERE " . $ilDB->in("ta_instructor_id", $ids, false, "integer"));
        while ($rec = $ilDB->fetchAssoc($set)) {
            $all[$rec["ta_instructor_id"]]["type_of_training"][] = $rec["training_type"];
        }

        // add instructor expertise
        $set = $ilDB->query("SELECT ta_instructor_id,ta_expertise_id" .
            " FROM adn_ta_instructor_exp" .
            " WHERE " . $ilDB->in("ta_instructor_id", $ids, false, "integer"));
        while ($rec = $ilDB->fetchAssoc($set)) {
            $all[$rec["ta_instructor_id"]]["area_of_expertise"][] = $rec["ta_expertise_id"];
        }

        return $all;
    }

    /**
     * Get instructor ids and names (for provider)
     *
     * @param int $a_provider_id
     * @return array (id => caption)
     */
    public static function getInstructorsSelect($a_provider_id)
    {
        global $DIC;
        $ilDB = $DIC->database();

        $sql = "SELECT id,last_name,first_name" .
            " FROM adn_ta_instructor" .
            " WHERE ta_provider_id = " . $ilDB->quote($a_provider_id, "integer") .
            " AND archived < " . $ilDB->quote(1, "integer");
    
        $res = $ilDB->query($sql);
        $all = array();
        while ($row = $ilDB->fetchAssoc($res)) {
            $all[$row["id"]] = $row["last_name"] . ", " . $row["first_name"];
        }

        return $all;
    }

    /**
     * Lookup property
     *
     * @param integer $a_id instructor id
     * @param string $a_prop property
     * @return	mixed property value
     */
    protected static function lookupProperty($a_id, $a_prop)
    {
        global $DIC;
        $ilDB = $DIC->database();

        $set = $ilDB->query("SELECT " . $a_prop .
            " FROM adn_ta_instructor" .
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
        return self::lookupProperty($a_id, "last_name");
    }

    // cr-008 start
    /**
     * Archive
     */
    public function archive()
    {
        $this->setFirstName("xxx");
        $this->setLastName("xxx");
        $this->expertise = array();
        $this->training_types = array();
        $this->setArchived(true);
        $this->update();
    }

    // cr-008 end
}
