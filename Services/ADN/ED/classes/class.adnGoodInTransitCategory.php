<?php
/* Copyright (c) 2010 Leifos, GPL, see docs/LICENSE */

/**
 * Good in transit category application class
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.de>
 * @version $Id: class.adnGoodInTransitCategory.php 27867 2011-02-25 09:35:47Z akill $
 *
 * @ingroup ServicesADN
 */
class adnGoodInTransitCategory extends adnDBBase
{
    protected $id; // [int]
    protected $name; // [string]
    protected $type; // [int]

    const TYPE_GAS = 1;
    const TYPE_CHEMICALS = 2;

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
     * Get type
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
        if (in_array((int) $a_type, array(self::TYPE_GAS, self::TYPE_CHEMICALS))) {
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

        $res = $ilDB->query("SELECT name,type" .
            " FROM adn_ed_good_category" .
            " WHERE id = " . $ilDB->quote($this->getId(), "integer"));
        $set = $ilDB->fetchAssoc($res);
        $this->setName($set["name"]);
        $this->setType($set["type"]);

        parent::_read($id, "adn_ed_good_category");
    }

    /**
     * Convert properties to DB fields
     *
     * @return array (name, type)
     */
    protected function propertiesToFields()
    {
        $fields = array("name" => array("text", $this->getName()),
            "type" => array("integer", $this->getType()));
            
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

        $this->setId($ilDB->nextId("adn_ed_good_category"));
        $id = $this->getId();

        $fields = $this->propertiesToFields();
        $fields["id"] = array("integer", $id);
            
        $ilDB->insert("adn_ed_good_category", $fields);

        parent::_save($id, "adn_ed_good_category");
        
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
        
        $ilDB->update("adn_ed_good_category", $fields, array("id" => array("integer", $id)));

        parent::_update($id, "adn_ed_good_category");

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
            // U.PV.11.8: check if category is used
            include_once "Services/ADN/ED/classes/class.adnGoodInTransit.php";
            if (sizeof(adnGoodInTransit::getGoodsSelect(false, $id))) {
                return false;
            }
            
            $ilDB->manipulate("DELETE FROM adn_ed_good_category" .
                " WHERE id = " . $ilDB->quote($id, "integer"));
            $this->setId(null);
            return true;
        }
    }

    /**
     * Get all categories
     *
     * @param int $a_type
     * @param int $a_with_archived
     * @return array
     */
    public static function getAllCategories($a_type = false, $a_with_archived = false)
    {
        global $ilDB;

        $sql = "SELECT id,name,type" .
            " FROM adn_ed_good_category";

        $where = array();
        if ($a_type && self::isValidType($a_type)) {
            $where[] = "type = " . $ilDB->quote($a_type, "integer");
        }
        if (!$a_with_archived) {
            $where[] = "archived < " . $ilDB->quote(1, "integer");
        }
        if (sizeof($where)) {
            $sql .= " WHERE " . implode(" AND ", $where);
        }
        
        $res = $ilDB->query($sql);
        $all = array();
        while ($row = $ilDB->fetchAssoc($res)) {
            $all[] = $row;
        }

        return $all;
    }

    /**
     * Get good ids and names
     *
     * @param int $a_type
     * @return array (id => caption)
     */
    public static function getCategoriesSelect($a_type = false)
    {
        global $ilDB;

        $sql = "SELECT id,name" .
            " FROM adn_ed_good_category" .
            " WHERE archived < " . $ilDB->quote(1, "integer");

        if ($a_type && self::isValidType($a_type)) {
            $sql .= " AND type = " . $ilDB->quote($a_type, "integer");
        }
        
        $res = $ilDB->query($sql);
        $all = array();
        while ($row = $ilDB->fetchAssoc($res)) {
            $all[$row["id"]] = $row["name"];
        }

        return $all;
    }

    /**
     * Lookup property
     *
     * @param integer $a_id good id
     * @param string $a_prop property
     * @return mixed property value
     */
    protected static function lookupProperty($a_id, $a_prop)
    {
        global $ilDB;

        $set = $ilDB->query("SELECT " . $a_prop .
            " FROM adn_ed_good_category" .
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
        return self::lookupProperty($a_id, "name");
    }
}
