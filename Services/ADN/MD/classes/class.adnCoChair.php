<?php
/* Copyright (c) 2010 Leifos, GPL, see docs/LICENSE */

/**
 * Co-chair application class
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.de>
 * @version $Id: class.adnCoChair.php 28410 2011-04-07 13:43:03Z jluetzen $
 *
 * @ingroup ServicesADN
 */
class adnCoChair extends adnDBBase
{
    protected int $id = 0;
    protected int $wmo_id = 0;
    protected string $salutation = '';
    protected string $name = '';

    /**
     * Constructor
     *
     * @param int $a_id instance id
     */
    public function __construct($a_id = 0)
    {
        global $ilCtrl;

        if ($a_id !== 0) {
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
     * Set wmo id
     *
     * @param int $a_id
     */
    public function setWMO($a_id)
    {
        $this->wmo_id = (int) $a_id;
    }

    /**
     * Get wmo id
     *
     * @return int
     */
    public function getWMO()
    {
        return $this->wmo_id;
    }

    /**
     * Set salutation
     *
     * @param string $a_salutation
     */
    public function setSalutation($a_salutation)
    {
        if ($a_salutation === null) {
            $this->salutation = null;
        } elseif (in_array($a_salutation, array("m", "f"))) {
            $this->salutation = (string) $a_salutation;
        }
    }

    /**
     * Get salutation
     *
     * @return string
     */
    public function getSalutation()
    {
        return $this->salutation;
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

        $res = $ilDB->query("SELECT salutation,name,md_wmo_id" .
            " FROM adn_md_cochair" .
            " WHERE id = " . $ilDB->quote($this->getId(), "integer"));
        $set = $ilDB->fetchAssoc($res);
        $this->setWMO($set["md_wmo_id"]);
        $this->setSalutation($set["salutation"]);
        $this->setName($set["name"]);

        parent::_read($a_id, "adn_md_cochair");
    }

    /**
     * Convert properties to DB fields
     *
     * @return array (md_wmo_id, salutation, name)
     */
    protected function propertiesToFields()
    {
        $fields = array("md_wmo_id" => array("integer", $this->getWMO()),
            "salutation" => array("text", $this->getSalutation()),
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
        $this->setId($ilDB->nextId("adn_md_cochair"));
        $id = $this->getId();

        $fields = $this->propertiesToFields();
        $fields["id"] = array("integer", $id);
            
        $ilDB->insert("adn_md_cochair", $fields);

        parent::_save($id, "adn_md_cochair");
        
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
        
        $ilDB->update("adn_md_cochair", $fields, array("id" => array("integer", $id)));

        parent::_update($id, "adn_md_cochair");

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
            // U.SD.2.4: check if cochair is used in any examination event
            include_once "Services/ADN/EP/classes/class.adnExaminationEvent.php";
            if (adnExaminationEvent::hasChair($id)) {
                // cr-008 start
                $this->setName("xxx");
                $this->setSalutation(null);
                // cr-008 end
                $this->setArchived(true);
                return $this->update();
            } else {
                $ilDB->manipulate("DELETE FROM adn_md_cochair" .
                    " WHERE id = " . $ilDB->quote($id, "integer"));
                $this->setId(null);
                return true;
            }
        }
    }

    /**
     * Get all cochairs
     *
     * @param int $a_wmo_id
     * @param bool $a_with_archived
     * @return array
     */
    public static function getAllCoChairs($a_wmo_id, $a_with_archived = false)
    {
        global $ilDB;

        $sql = "SELECT id,salutation,name" .
            " FROM adn_md_cochair" .
            " WHERE md_wmo_id = " . $ilDB->quote($a_wmo_id, "integer");
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
     * Get cochair ids and names
     *
     * @param int $a_wmo_id
     * @param array $a_old_values
     * @return array (id => caption)
     */
    public static function getCoChairsSelect($a_wmo_id = null, array $a_old_values = null)
    {
        global $ilDB, $lng;

        $sql = "SELECT id,salutation,name,archived" .
            " FROM adn_md_cochair";
        if (!$a_old_values) {
            $sql .= " WHERE archived < " . $ilDB->quote(1, "integer");
        } else {
            $sql .= " WHERE (archived < " . $ilDB->quote(1, "integer") . " OR " .
                $ilDB->in("id", $a_old_values, false, "integer") . ")";
        }
        if ($a_wmo_id) {
            $sql .= " AND md_wmo_id = " . $ilDB->quote($a_wmo_id, "integer");
        }

        $sql .= " ORDER BY name";

        $res = $ilDB->query($sql);
        $all = array();
        while ($row = $ilDB->fetchAssoc($res)) {
            if ($row["salutation"] == "m") {
                $salutation = $lng->txt("adn_salutation_m");
            } else {
                $salutation = $lng->txt("adn_salutation_f");
            }
            $all[$row["id"]] = self::handleName(
                $salutation . " " . $row["name"],
                $row["archived"]
            );
        }
        return $all;
    }

    /**
     * Lookup property
     *
     * @param integer $a_id cochair id
     * @param string $a_prop property
     * @return mixed property value
     */
    protected static function lookupProperty($a_id, $a_prop)
    {
        global $ilDB;

        $set = $ilDB->query("SELECT " . $a_prop .
            " FROM adn_md_cochair" .
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
        $name = self::lookupProperty($a_id, "name");
        $archived = self::lookupProperty($a_id, "archived");
        return self::handleName($name, $archived);
    }
}
