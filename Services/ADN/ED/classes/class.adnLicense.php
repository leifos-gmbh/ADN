<?php
/* Copyright (c) 2010 Leifos, GPL, see docs/LICENSE */

/**
 * License application class
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.de>
 * @version $Id: class.adnLicense.php 27873 2011-02-25 16:21:55Z jluetzen $
 *
 * @ingroup ServicesADN
 */
class adnLicense extends adnDBBase
{
    protected int $id = 0;
    protected int $type = 0;
    protected string $name = '';
    /**
     * @var int[]
     */
    protected array $goods = [];

    public const TYPE_CHEMICALS = 1;
    public const TYPE_GAS = 2;

    /**
     * Constructor
     *
     * @param int $a_id instance id
     */
    public function __construct($a_id = 0)
    {

        $this->setFileDirectory("ed_license");

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
        if (in_array((int) $a_type, array(self::TYPE_GAS, self::TYPE_CHEMICALS))) {
            return true;
        }
        return false;
    }

    /**
     * Set goods
     *
     * @param array $a_goods
     */
    public function setGoods(array $goods = null)
    {
        $this->goods = $goods;
    }

    /**
     * Get goods
     *
     * @return array
     */
    public function getGoods()
    {
        return $this->goods;
    }

    /**
     * Is license valid for given good?
     *
     * @param int $a_id
     * @return bool
     */
    public function hasGood($a_id)
    {
        if (is_array($this->goods) && in_array($a_id, $this->goods)) {
            return true;
        }
        return false;
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

        $res = $this->db->query("SELECT title,lfile,type" .
            " FROM adn_ed_license" .
            " WHERE id = " . $this->db->quote($this->getId(), "integer"));
        $set = $this->db->fetchAssoc($res);
        $this->setName($set["title"]);
        $this->setFileName($set["lfile"]);
        $this->setType($set["type"]);

        // get goods
        $goods = array();
        $res = $this->db->query("SELECT ed_good_id" .
            " FROM adn_ed_license_good" .
            " WHERE ed_license_id = " . $this->db->quote($this->getId(), "integer"));
        while ($row = $this->db->fetchAssoc($res)) {
            $goods[] = $row["ed_good_id"];
        }
        $this->setGoods($goods);

        parent::_read($id, "adn_ed_license");
    }

    /**
     * Convert properties to DB fields
     *
     * @return array (title, type)
     */
    protected function propertiesToFields()
    {
        $fields = array("title" => array("text", $this->getName()),
            "type" => array("integer", $this->getType()),
            "lfile" => array("text", $this->getFileName()));
            
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
        $this->setId($this->db->nextId("adn_ed_license"));
        $id = $this->getId();

        $fields = $this->propertiesToFields();
        $fields["id"] = array("integer", $id);

        if ($this->getUploadedFile()) {
            if (!$this->saveFile($this->getUploadedFile(), $id)) {
                return false;
            }

            $fields["lfile"] = array("text", $this->getFileName());
        }
            
        $this->db->insert("adn_ed_license", $fields);

        $this->saveGoods();

        parent::_save($id, "adn_ed_license");
        
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

        if ($this->getUploadedFile()) {
            if (!$this->saveFile($this->getUploadedFile(), $id)) {
                return false;
            }

            $fields["lfile"] = array("text", $this->getFileName());
        }
        
        $this->db->update("adn_ed_license", $fields, array("id" => array("integer", $id)));

        $this->saveGoods();

        parent::_update($id, "adn_ed_license");

        return true;
    }

    /**
     * Save goods (in separate table)
     */
    protected function saveGoods()
    {
        $id = $this->getId();
        if ($id) {
            // remove old entries first (so we do not have to sync)
            $this->db->manipulate("DELETE FROM adn_ed_license_good" .
                " WHERE ed_license_id = " . $this->db->quote($id, "integer"));
            if (is_array($this->goods) && count($this->goods) > 0) {
                foreach ($this->goods as $good_id) {
                    $fields = array("ed_license_id" => array("integer", $id),
                        "ed_good_id" => array("integer", $good_id));

                    $this->db->insert("adn_ed_license_good", $fields);
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

        $id = $this->getId();
        if ($id) {
            $this->removeFile($id);

            // U.PV.10.4: archived flag is not used here!
            $this->db->manipulate("DELETE FROM adn_ed_license_good" .
                " WHERE ed_license_id = " . $this->db->quote($id, "integer"));
            $this->db->manipulate("DELETE FROM adn_ed_license" .
                " WHERE id = " . $this->db->quote($id, "integer"));
            $this->setId(null);
            return true;
        }
    }

    /**
     * Get all licenses
     *
     * @param int $a_type
     * @return array
     */
    public static function getAllLicenses($a_type = false)
    {
        global $DIC;
        $ilDB = $DIC->database();

        $sql = "SELECT id,title as name,lfile" .
            " FROM adn_ed_license";

        if ($a_type) {
            $sql .= " WHERE type = " . $ilDB->quote((int) $a_type, "integer");
        }

        $res = $ilDB->query($sql);
        $all = array();
        while ($row = $ilDB->fetchAssoc($res)) {
            $row["file"] = $row["lfile"];
            unset($row["lfile"]);
            $all[] = $row;
        }

        return $all;
    }

    /**
     * Get license ids and names
     *
     * @param int $a_type
     * @return array (id => caption)
     */
    public static function getLicensesSelect($a_type = false)
    {
        global $DIC;
        $ilDB = $DIC->database();

        $sql = "SELECT id,title" .
            " FROM adn_ed_license";

        if ($a_type) {
            $sql .= " WHERE type = " . $ilDB->quote((int) $a_type, "integer");
        }

        $res = $ilDB->query($sql);
        $all = array();
        while ($row = $ilDB->fetchAssoc($res)) {
            $all[$row["id"]] = $row["title"];
        }

        return $all;
    }

    /**
     * Lookup property
     *
     * @param integer $a_id license id
     * @param string $a_prop property
     * @return mixed property value
     */
    protected static function lookupProperty($a_id, $a_prop)
    {
        global $DIC;
        $ilDB = $DIC->database();

        $set = $ilDB->query("SELECT " . $a_prop .
            " FROM adn_ed_license" .
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
     * Check if good is used by any license (used when deleting goods)
     *
     * @param int $a_id
     * @return bool
     */
    public static function findByGood($a_id)
    {
        global $DIC;
        $ilDB = $DIC->database();

        $res = $ilDB->query("SELECT ed_license_id" .
            " FROM adn_ed_license_good" .
            " WHERE ed_good_id = " . $ilDB->quote((int) $a_id, "integer"));
        return (bool) $ilDB->numRows($res);
    }
}
