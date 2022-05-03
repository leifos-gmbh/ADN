<?php
/* Copyright (c) 2010 Leifos, GPL, see docs/LICENSE */

/**
 * Exam information letter application class
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.de>
 * @version $Id: class.adnExamInfoLetter.php 27888 2011-02-28 11:09:28Z jluetzen $
 *
 * @ingroup ServicesADN
 */
class adnExamInfoLetter extends adnDBBase
{
    protected int $id;
    protected string $file = '';

    /**
     * Constructor
     *
     * @param int $a_id instance id
     */
    public function __construct($a_id = 0)
    {
        global $ilCtrl;

        $this->setFileDirectory("ep_information_letter");

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
     * Read db entry
     */
    public function read()
    {
        global $ilDB;

        $id = $this->getId();
        if (!$id) {
            return;
        }

        $res = $ilDB->query("SELECT ifile" .
            " FROM adn_ep_information" .
            " WHERE id = " . $ilDB->quote($this->getId(), "integer"));
        $set = $ilDB->fetchAssoc($res);
        $this->setFileName($set["ifile"]);
        
        parent::_read($id, "adn_ep_information");
    }

    /**
     * Convert properties to DB fields
     *
     * @return array
     */
    protected function propertiesToFields()
    {
        $fields = array();
            
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
        $this->setId($ilDB->nextId("adn_ep_information"));
        $id = $this->getId();

        $fields = $this->propertiesToFields();
        $fields["id"] = array("integer", $id);

        if ($this->getUploadedFile()) {
            if (!$this->saveFile($this->getUploadedFile(), $id)) {
                return false;
            }

            $fields["ifile"] = array("text", $this->getFileName());
        }
            
        $ilDB->insert("adn_ep_information", $fields);

        parent::_save($id, "adn_ep_information");
        
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

            $fields["ifile"] = array("text", $this->getFileName());
        }

        $ilDB->update("adn_ep_information", $fields, array("id" => array("integer", $id)));

        parent::_update($id, "adn_ep_information");

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
            $this->removeFile($id);

            // U.PVB.1.4: archived flag is not used here!
            $ilDB->manipulate("DELETE FROM adn_ep_information" .
                " WHERE id = " . $ilDB->quote($id, "integer"));
            $this->setId(null);
            return true;
        }
    }

    /**
     * Get all letters
     *
     * @return array
     */
    public static function getAllLetters()
    {
        global $ilDB;

        $sql = "SELECT id,ifile" .
            " FROM adn_ep_information";
        
        $res = $ilDB->query($sql);
        $all = array();
        while ($row = $ilDB->fetchAssoc($res)) {
            // oracle: reserved word file
            $row["file"] = $row["ifile"];
            unset($row["ifile"]);

            $all[] = $row;
        }

        return $all;
    }

    /**
     * Get letter ids and names
     *
     * @return array (id => caption)
     */
    public static function getLettersSelect()
    {
        global $ilDB;

        $sql = "SELECT id,ifile" .
            " FROM adn_ep_information" .
            " ORDER BY ifile";
        
        $res = $ilDB->query($sql);
        $all = array();
        while ($row = $ilDB->fetchAssoc($res)) {
            $all[$row["id"]] = $row["ifile"];
        }

        return $all;
    }

    /**
     * Lookup property
     *
     * @param integer $a_id letter id
     * @param string $a_prop property
     * @return mixed property value
     */
    protected static function lookupProperty($a_id, $a_prop)
    {
        global $ilDB;

        $set = $ilDB->query("SELECT " . $a_prop .
            " FROM adn_ep_information" .
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
        return self::lookupProperty($a_id, "ifile");
    }
}
