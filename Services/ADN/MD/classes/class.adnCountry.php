<?php
/* Copyright (c) 2010 Leifos, GPL, see docs/LICENSE */

/**
 * Country application class
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.de>
 * @version $Id: class.adnCountry.php 28410 2011-04-07 13:43:03Z jluetzen $
 *
 * @ingroup ServicesADN
 */
class adnCountry extends adnDBBase
{
    protected int $id = 0;
    protected string $code = '';
    protected string $name = '';

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
     * Set country code
     *
     * @param string $a_code
     */
    public function setCode($a_code)
    {
        $this->code = (string) $a_code;
    }

    /**
     * Get country code
     *
     * @return string
     */
    public function getCode()
    {
        return $this->code;
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

        $id = $this->getId();
        if (!$id) {
            return;
        }

        $res = $this->db->query("SELECT code,name" .
            " FROM adn_md_country" .
            " WHERE id = " . $this->db->quote($id, "integer"));
        $set = $this->db->fetchAssoc($res);
        $this->setCode($set["code"]);
        $this->setName($set["name"]);

        parent::_read($id, "adn_md_country");
    }

    /**
     * Convert properties to DB fields
     *
     * @return array (code, name)
     */
    protected function propertiesToFields()
    {
        $fields = array("code" => array("text", $this->getCode()),
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

        // sequence
        $this->setId($this->db->nextId("adn_md_country"));
        $id = $this->getId();

        $fields = $this->propertiesToFields();
        $fields["id"] = array("integer", $id);
            
        $this->db->insert("adn_md_country", $fields);

        parent::_save($id, "adn_md_country");
        
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

        $this->db->update("adn_md_country", $fields, array("id" => array("integer", $id)));

        parent::_update($id, "adn_md_country");

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
            // U.SD.6.4: check if country is used with any candidate/professional
            include_once "Services/ADN/ES/classes/class.adnCertifiedProfessional.php";
            if (adnCertifiedProfessional::hasCountry($id)) {
                $this->setArchived(true);
                return $this->update();
            } else {
                $this->db->manipulate("DELETE FROM adn_md_country" .
                    " WHERE id = " . $this->db->quote($id, "integer"));
                $this->setId(null);
                return true;
            }
        }
    }

    /**
     * Get all countries
     *
     * @param bool $a_with_archived
     * @return array
     */
    public static function getAllCountries($a_with_archived = false)
    {
        global $DIC;
        $ilDB = $DIC->database();

        $sql = "SELECT id,code,name" .
            " FROM adn_md_country";
        if (!$a_with_archived) {
            $sql .= " WHERE archived < " . $ilDB->quote(1, "integer");
        }
        $res = $ilDB->query($sql);
        $all = array();
        while ($row = $ilDB->fetchAssoc($res)) {
            $all[] = $row;
        }
        return $all;
    }

    /**
     * Get country ids and names
     *
     * @param array $a_old_values
     * @return array (id => caption)
     */
    public static function getCountriesSelect(array $a_old_values = null)
    {
        global $DIC;
        $ilDB = $DIC->database();

        $sql = "SELECT id,name,code,archived" .
            " FROM adn_md_country";
        
        if (!$a_old_values) {
            $sql .= " WHERE archived < " . $ilDB->quote(1, "integer");
        } else {
            $sql .= " WHERE (archived < " . $ilDB->quote(1, "integer") . " OR " .
                $ilDB->in("id", $a_old_values, false, "integer") . ")";
        }

        $sql .= " ORDER BY name";
    
        $res = $ilDB->query($sql);
        $all = $first = array();
        while ($row = $ilDB->fetchAssoc($res)) {
            $caption = self::handleName($row["name"], $row["archived"]);

            if (strtoupper($row["code"]) != "DE") {
                $all[$row["id"]] = $caption;
            } else {
                $first = array($row["id"] => $caption);
            }
        }

        $all = $first + $all;
        return $all;
    }

    /**
     * Lookup property
     *
     * @param integer $a_id country id
     * @param string $a_prop property
     * @return mixed property value
     */
    protected static function lookupProperty($a_id, $a_prop)
    {
        global $DIC;
        $ilDB = $DIC->database();

        $set = $ilDB->query("SELECT " . $a_prop .
            " FROM adn_md_country" .
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
