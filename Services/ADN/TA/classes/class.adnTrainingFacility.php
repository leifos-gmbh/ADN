<?php
/* Copyright (c) 2010 Leifos, GPL, see docs/LICENSE */

/**
 * Training facility application class
 *
 * Every facility has a mandatory parent provider
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.de>
 * @version $Id: class.adnTrainingFacility.php 32529 2012-01-05 10:31:40Z jluetzen $
 *
 * @ingroup ServicesADN
 */
class adnTrainingFacility extends adnDBBase
{
    protected $id; // [int]
    protected $provider_id; // [int]
    protected $name; // [string]

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
     * Read db entry
     */
    public function read()
    {
        global $ilDB;

        $id = $this->getId();
        if (!$id) {
            return;
        }

        $res = $ilDB->query("SELECT ta_provider_id,name" .
            " FROM adn_ta_facility" .
            " WHERE id = " . $ilDB->quote($this->getId(), "integer"));
        $set = $ilDB->fetchAssoc($res);
        $this->setProvider($set["ta_provider_id"]);
        $this->setName($set["name"]);

        parent::_read($id, "adn_ta_instructor");
    }

    /**
     * Convert properties to DB fields
     *
     * @return array (ta_provider_id, name)
     */
    protected function propertiesToFields()
    {
        $fields = array("ta_provider_id" => array("integer", $this->getProvider()),
            "name" => array("text", $this->getName()));
            
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
        $this->setId($ilDB->nextId("adn_ta_facility"));
        $id = $this->getId();

        $fields = $this->propertiesToFields();
        $fields["id"] = array("integer", $id);
            
        $ilDB->insert("adn_ta_facility", $fields);

        parent::_save($id, "adn_ta_facility");
        
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
        
        $ilDB->update("adn_ta_facility", $fields, array("id" => array("integer", $id)));

        parent::_update($id, "adn_ta_facility");

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
            // U.SV.3.4: check if facility is used in any training event which already has taken place
            include_once "Services/ADN/TA/classes/class.adnTrainingEvent.php";
            $all = adnTrainingEvent::getAllTrainingEvents(
                $this->getProvider(),
                false,
                array("training_facility" => $id),
                true
            );
            if (is_array($all) && sizeof($all)) {
                $this->setArchived(true);
                return $this->update();
            } else {
                $ilDB->manipulate("UPDATE adn_ta_event" .
                    " SET ta_facility_id = " . $ilDB->quote(null, "integer") .
                    " WHERE ta_facility_id = " . $ilDB->quote($id, "integer"));
                $ilDB->manipulate("DELETE FROM adn_ta_facility" .
                    " WHERE id = " . $ilDB->quote($id, "integer"));
                $this->setId(null);
                return true;
            }
        }
    }

    /**
     * Get all facilities for provider
     *
     * @param int $a_provider_id
     * @param bool $a_with_archived
     * @return array
     */
    public static function getAllTrainingFacilities($a_provider_id, $a_with_archived = false)
    {
        global $ilDB;

        $sql = "SELECT id,name" .
            " FROM adn_ta_facility" .
            " WHERE ta_provider_id = " . $ilDB->quote($a_provider_id, "integer");
        if (!$a_with_archived) {
            $sql .= " AND archived < " . $ilDB->quote(1, "integer");
        }
        $res = $ilDB->query($sql);
        $all = array();
        while ($row = $ilDB->fetchAssoc($res)) {
            $all[] = $row;
        }

        return $all;
    }

    /**
     * Get facility ids and names for provider
     *
     * @param int $a_provider_id
     * @param int $a_old_value
     * @return array (id => caption)
     */
    public static function getTrainingFacilitiesSelect($a_provider_id = null, $a_old_value = null)
    {
        global $ilDB;

        $sql = "SELECT id,name" .
            " FROM adn_ta_facility";
        if ($a_old_value) {
            $sql .= " WHERE (archived < " . $ilDB->quote(1, "integer") .
                " OR id = " . $ilDB->quote($a_old_value, "integer") . ")";
        } else {
            $sql .= " WHERE archived < " . $ilDB->quote(1, "integer");
        }
        if ($a_provider_id) {
            $sql .= " AND ta_provider_id = " . $ilDB->quote($a_provider_id, "integer");
        }
        $sql .= " ORDER BY name";
            
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
     * @param integer $a_id facility id
     * @param string $a_prop property
     * @return mixed property value
     */
    protected static function lookupProperty($a_id, $a_prop)
    {
        global $ilDB;

        $set = $ilDB->query("SELECT " . $a_prop .
            " FROM adn_ta_facility" .
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
