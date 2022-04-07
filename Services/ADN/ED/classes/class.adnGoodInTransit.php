<?php
/* Copyright (c) 2010 Leifos, GPL, see docs/LICENSE */

/**
 * Good in transit application class
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.de>
 * @version $Id: class.adnGoodInTransit.php 31990 2011-12-05 14:18:24Z jluetzen $
 *
 * @ingroup ServicesADN
 */
class adnGoodInTransit extends adnDBBase
{
    protected int $id;
    protected int $number;
    protected string $name;
    protected int $type;
    protected int $category_id;
    protected string $class;
    protected string $class_code;
    protected string $packing_group;

    public const TYPE_GAS = 1;
    public const TYPE_CHEMICALS = 2;

    /**
     * Constructor
     *
     * @param int $a_id instance id
     */
    public function __construct($a_id = null)
    {
        global $ilCtrl;

        $this->setFileDirectory("ed_goods");

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
     * Set category
     *
     * @param int $a_category
     */
    public function setCategory($a_category)
    {
        $a_category = (int) $a_category;
        if (!$a_category) {
            $a_category = null;
        }
        $this->category_id = $a_category;
    }

    /**
     * Get category
     *
     * @return int
     */
    public function getCategory()
    {
        return $this->category_id;
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
     * Set class
     *
     * @param string $a_class
     */
    public function setClass($a_class)
    {
        $this->class = (string) $a_class;
    }

    /**
     * Get class
     *
     * @return string
     */
    public function getClass()
    {
        return $this->class;
    }

    /**
     * Set class code
     *
     * @param string $a_code
     */
    public function setClassCode($a_code)
    {
        $this->class_code = (string) $a_code;
    }

    /**
     * Get class code
     *
     * @return string
     */
    public function getClassCode()
    {
        return $this->class_code;
    }

    /**
     * Set packing group
     *
     * @param string $a_group
     */
    public function setPackingGroup($a_group)
    {
        $this->packing_group = (string) $a_group;
    }

    /**
     * Get packing group
     *
     * @return string
     */
    public function getPackingGroup()
    {
        return $this->packing_group;
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

        $res = $ilDB->query("SELECT name,type,un_nr,class,class_code,packing_group," .
            "ed_good_category_id,material_file,upload_date" .
            " FROM adn_ed_good" .
            " WHERE id = " . $ilDB->quote($this->getId(), "integer"));
        $set = $ilDB->fetchAssoc($res);
        $this->setName($set["name"]);
        $this->setType($set["type"]);
        $this->setNumber($set["un_nr"]);
        $this->setClass($set["class"]);
        $this->setClassCode($set["class_code"]);
        $this->setPackingGroup($set["packing_group"]);
        $this->setCategory($set["ed_good_category_id"]);
        $this->setFileName($set["material_file"]);
        // upload date?

        parent::_read($id, "adn_ed_good");
    }

    /**
     * Convert properties to DB fields
     *
     * @return array (name, type, un_nr, class, class_code, packing_group, ed_good_category_id)
     */
    protected function propertiesToFields()
    {
        $fields = array("name" => array("text", $this->getName()),
            "type" => array("integer", $this->getType()),
            "un_nr" => array("integer", $this->getNumber()),
            "class" => array("text", $this->getClass()),
            "class_code" => array("text", $this->getClassCode()),
            "packing_group" => array("text", $this->getPackingGroup()),
            "ed_good_category_id" => array("integer", $this->getCategory()),
            "material_file" => array("text", $this->getFileName()));
            
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

        $this->setId($ilDB->nextId("adn_ed_good"));
        $id = $this->getId();

        $fields = $this->propertiesToFields();
        $fields["id"] = array("integer", $id);

        if ($this->getUploadedFile()) {
            if (!$this->saveFile($this->getUploadedFile(), $id)) {
                return false;
            }

            $file_date = new ilDateTime(time(), IL_CAL_UNIX, ilTimeZone::UTC);
            $fields["material_file"] = array("text", $this->getFileName());
            $fields["upload_date"] = array("timestamp",
                $file_date->get(IL_CAL_DATETIME, "", ilTimeZone::UTC));
        }
            
        $ilDB->insert("adn_ed_good", $fields);

        parent::_save($id, "adn_ed_good");
        
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
        
        if ($this->getUploadedFile()) {
            if (!$this->saveFile($this->getUploadedFile(), $id)) {
                return false;
            }

            $file_date = new ilDateTime(time(), IL_CAL_UNIX, ilTimeZone::UTC);
            $fields["material_file"] = array("text", $this->getFileName());
            $fields["upload_date"] = array("timestamp",
                $file_date->get(IL_CAL_DATETIME, "", ilTimeZone::UTC));
        }

        $ilDB->update("adn_ed_good", $fields, array("id" => array("integer", $id)));

        parent::_update($id, "adn_ed_good");

        return true;
    }

    /**
     * Delete from DB
     *
     * @param bool $a_force
     * @return bool
     */
    public function delete($a_force = false)
    {
        global $ilDB;

        $id = $this->getId();
        if ($id) {
            $in_use = false;
        
            if (!$a_force) {
                // U.PV.11.4: check if good is used

                include_once "Services/ADN/ED/classes/class.adnLicense.php";
                if (adnLicense::findByGood($id)) {
                    $in_use = true;
                }
                if (!$in_use) {
                    include_once "Services/ADN/ED/classes/class.adnCaseQuestion.php";
                    if (adnCaseQuestion::findByGood($id)) {
                        $in_use = true;
                    }
                }
                if (!$in_use) {
                    include_once "Services/ADN/ED/classes/class.adnGoodRelatedAnswer.php";
                    if (adnGoodRelatedAnswer::findByGood($id)) {
                        $in_use = true;
                    }
                }
            }
            
            if ($in_use) {
                $this->setArchived(true);
                $this->update();
            } else {
                $this->removeFile($id);
                
                // answer sheets
                $ilDB->manipulate("UPDATE adn_ep_answer_sheet" .
                    " SET prev_ed_good_id = NULL" .
                    " WHERE prev_ed_good_id = " . $ilDB->quote($id, "integer"));
                $ilDB->manipulate("UPDATE adn_ep_answer_sheet" .
                    " SET new_ed_good_id = NULL" .
                    " WHERE new_ed_good_id = " . $ilDB->quote($id, "integer"));
                
                // license
                $ilDB->manipulate("DELETE FROM adn_ed_license_good" .
                " WHERE ed_good_id = " . $ilDB->quote($id, "integer"));
                
                // archived flag is not used here!
                $ilDB->manipulate("DELETE FROM adn_ed_good" .
                    " WHERE id = " . $ilDB->quote($id, "integer"));
                $this->setId(null);
            }
            return true;
        }
    }

    /**
     * Get all goods
     *
     * @param int $a_type
     * @param bool $a_with_archived
     * @return array
     */
    public static function getAllGoods($a_type = null, $a_with_archived = false)
    {
        global $ilDB;

        $sql = "SELECT id,name,un_nr,class,class_code,packing_group,ed_good_category_id," .
            "material_file,type" .
            " FROM adn_ed_good";

        $where = array();
        if (!$a_with_archived) {
            $where[] = "archived < " . $ilDB->quote(1, "integer");
        }

        if ($a_type && self::isValidType($a_type)) {
            $where[] = "type = " . $ilDB->quote($a_type, "integer");
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
     * @param int $a_category
     * @param array $a_old_values
     * @param bool $a_force_all
     * @return array (id => caption)
     */
    public static function getGoodsSelect(
        $a_type = null,
        $a_category = null,
        array $a_old_values = null,
        $a_force_all = false
    )
    {
        global $ilDB;

        $sql = "SELECT id,name,un_nr,archived" .
            " FROM adn_ed_good";

        $where = array();
        if (!$a_force_all) {
            if ($a_old_values) {
                $where[] = "(archived < " . $ilDB->quote(1, "integer") . " OR " .
                    $ilDB->in("id", $a_old_values, "", "integer") . ")";
            } else {
                $where[] = "archived < " . $ilDB->quote(1, "integer");
            }
        }
        if ($a_type && self::isValidType($a_type)) {
            $where[] = "type = " . $ilDB->quote($a_type, "integer");
        }
        if ($a_category) {
            $where[] = "ed_good_category_id = " . $ilDB->quote($a_category, "integer");
        }
        if (sizeof($where)) {
            $sql .= " WHERE " . implode(" AND ", $where);
        }

        $sql .= " ORDER BY name";
        
        $res = $ilDB->query($sql);
        $all = array();
        while ($row = $ilDB->fetchAssoc($res)) {
            $all[$row["id"]] = self::handleName(
                $row["name"] . " (" . $row["un_nr"] . ")",
                $row["archived"]
            );
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
            " FROM adn_ed_good" .
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
