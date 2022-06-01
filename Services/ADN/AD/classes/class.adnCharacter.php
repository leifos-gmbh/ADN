<?php
/* Copyright (c) 2010 Leifos, GPL, see docs/LICENSE */

/**
 * Special character application class
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.de>
 * @version $Id: class.adnCharacter.php 27871 2011-02-25 15:29:26Z jluetzen $
 *
 * @ingroup ServicesADN
 */
class adnCharacter
{
    protected int $id;
    protected string $name;
    protected int $code;

    protected ilDBInterface $db;
    /**
     * Constructor
     *
     * @param int $a_id instance id
     */
    public function __construct($a_id = null)
    {
        global $DIC;

        $this->db = $DIC->database();
        
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
     * Set code
     *
     * @param int $a_code
     */
    public function setCode($a_code)
    {
        $this->code = (int) $a_code;
    }

    /**
     * Get code
     *
     * @return int
     */
    public function getCode()
    {
        return $this->code;
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

        $res = $this->db->query("SELECT charact FROM adn_ad_character" .
            " WHERE id = " . $this->db->quote($id, "integer"));
        $set = $this->db->fetchAssoc($res);
        $this->setName($set["charact"]);
    }

    /**
     * Convert properties to DB fields
     *
     * @return array (charact)
     */
    protected function propertiesToFields()
    {
        // build character from code if given
        $code = $this->getCode();
        if ($code) {
            $this->setName(mb_convert_encoding('&#' . $code . ';', 'UTF-8', 'HTML-ENTITIES'));
        }

        if ($this->getName()) {
            $fields = array("charact" => array("text", $this->getName()));

            return $fields;
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
        $this->setId($this->db->nextId("adn_ad_character"));
        $id = $this->getId();

        $fields = $this->propertiesToFields();
        if ($fields) {
            $fields["id"] = array("integer", $id);

            $this->db->insert("adn_ad_character", $fields);

            return $id;
        }
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
        if ($fields) {
            $this->db->update("adn_ad_character", $fields, array("id" => array("integer", $id)));

            return true;
        }
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
            // U.AD.2.3: no archive here
            $this->db->manipulate("DELETE FROM adn_ad_character" .
                " WHERE id = " . $this->db->quote($id, "integer"));
            $this->setId(null);
            return true;
        }
    }

    /**
     * Get all characters
     *
     * @return array (id, name)
     */
    public static function getAllCharacters()
    {
        global $DIC;

        $ilDB = $DIC->database();

        $sql = "SELECT id,charact AS name" .
            " FROM adn_ad_character";
        $res = $ilDB->query($sql);
        $all = array();
        while ($row = $ilDB->fetchAssoc($res)) {
            $all[] = $row;
        }
        return $all;
    }

    /**
     * Lookup property
     *
     * @param int $a_id character id
     * @param string $a_prop property
     * @return mixed property value
     */
    protected static function lookupProperty($a_id, $a_prop)
    {
        global $DIC;

        $ilDB = $DIC->database();

        $set = $ilDB->query("SELECT " . $a_prop .
            " FROM adn_ad_character" .
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
        return self::lookupProperty($a_id, "charact");
    }
}
