<?php
/* Copyright (c) 2010 Leifos, GPL, see docs/LICENSE */

/**
 * DB access base class
 *
 * This class handles the data which should appended to all object write update/create queries
 * - create_data and create_user
 * - last_update and last_update_user
 * - archived flag
 *
 * The upload/file handling is also centralized here
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.de>
 * @version $Id: class.adnDBBase.php 29461 2011-06-09 12:07:54Z jluetzen $
 *
 * @ingroup ServicesADN
 */
class adnDBBase
{
    protected $create_date; // [ilDateTime]
    protected $create_user; // [int]
    protected $last_update; // [ilDateTime]
    protected $last_update_user; // [int]
    protected $archived; // [bool]
    protected $file_name; // [string]
    protected $new_file; // [string]
    protected $file_dir; // [string]

    /**
     * Set creation date
     *
     * @param ilDateTime $a_date date
     */
    protected function setCreateDate(ilDateTime $a_date)
    {
        $this->create_date = $a_date;
    }

    /**
     * Get creation date
     *
     * @return ilDateTime
     */
    public function getCreateDate()
    {
        return $this->create_date;
    }

    /**
     * Set creation user
     *
     * @param int $a_user_id user id
     */
    protected function setCreateUser($a_user_id)
    {
        $this->create_user = (int) $a_user_id;
    }

    /**
     * Get creation user
     *
     * @return int
     */
    public function getCreateUser()
    {
        return $this->create_user;
    }

    /**
     * Set last update date
     *
     * @param ilDateTime $a_date date
     */
    protected function setLastUpdate(ilDateTime $a_date)
    {
        $this->last_update = $a_date;
    }

    /**
     * Get last update date
     *
     * @return ilDateTime
     */
    public function getLastUpdate()
    {
        return $this->last_update;
    }

    /**
     * Set last update user
     *
     * @param int $a_user_id user id
     */
    protected function setLastUpdateUser($a_user_id)
    {
        $this->last_update_user = (int) $a_user_id;
    }

    /**
     * Get last update user
     *
     * @return int
     */
    public function getLastUpdateUser()
    {
        return $this->last_update_user;
    }

    /**
     * Set archived status
     *
     * @param bool $a_status status
     */
    public function setArchived($a_status)
    {
        $this->archived = (bool) $a_status;
    }

    /**
     * Is item archived?
     *
     * @return bool
     */
    public function isArchived()
    {
        return $this->archived;
    }

    /**
     * Read db entry
     *
     * @param int $a_id
     * @param string $a_table
     * @param string $a_primary_key
     */
    protected function _read($a_id, $a_table, $a_primary_key = "id")
    {
        global $ilDB;

        $res = $ilDB->query("SELECT create_date,create_user,last_update,last_update_user,archived" .
            " FROM " . $a_table .
            " WHERE " . $a_primary_key . " = " . $ilDB->quote($a_id, "integer"));
        $set = $ilDB->fetchAssoc($res);

        $this->setArchived($set["archived"]);
        $this->setCreateDate(new ilDateTime($set["create_date"], IL_CAL_DATETIME, ilTimeZone::UTC));
        $this->setCreateUser($set["create_user"]);

        if ($set["last_update"]) {
            $this->setLastUpdate(new ilDateTime($set["last_update"], IL_CAL_DATETIME, ilTimeZone::UTC));
            $this->setLastUpdateUser($set["last_update_user"]);
        }
    }

    /**
     * Create new db entry
     *
     * @param int $a_id
     * @param string $a_table
     * @param string $a_primary_key
     * @return bool
     */
    public function _save($a_id, $a_table, $a_primary_key = "id")
    {
        global $ilDB, $ilUser;

        $this->setCreateDate(new ilDateTime(time(), IL_CAL_UNIX));
        $this->setCreateUser($ilUser->getId());
        $this->setLastUpdate(new ilDateTime(time(), IL_CAL_UNIX));
        $this->setLastUpdateUser($ilUser->getId());
    
        $fields = array("create_date" => array("timestamp",
                $this->getCreateDate()->get(IL_CAL_DATETIME, "", ilTimeZone::UTC)),
            "create_user" => array("integer", $this->getCreateUser()),
            "last_update" => array("timestamp",
                $this->getLastUpdate()->get(IL_CAL_DATETIME, "", ilTimeZone::UTC)),
            "last_update_user" => array("integer", $this->getLastUpdateUser()),
            "archived" => array("integer", (int) $this->isArchived()));

        return $ilDB->update($a_table, $fields, array($a_primary_key => array("integer", $a_id)));
    }

    /**
     * Update db entry
     *
     * @param int $a_id
     * @param string $a_table
     * @param string $a_primary_key
     * @return bool
     */
    public function _update($a_id, $a_table, $a_primary_key = "id")
    {
        global $ilDB, $ilUser;
        
        $this->setLastUpdate(new ilDateTime(time(), IL_CAL_UNIX));
        $this->setLastUpdateUser($ilUser->getId());

        $fields = array("last_update" => array("timestamp",
            $this->getLastUpdate()->get(IL_CAL_DATETIME, "", ilTimeZone::UTC)),
            "last_update_user" => array("integer", $this->getLastUpdateUser()),
            "archived" => array("integer", (int) $this->isArchived()));

        return $ilDB->update($a_table, $fields, array($a_primary_key => array("integer", $a_id)));
    }

    
    //
    // UPLOAD HANDLING
    //

    /**
     * Set upload target directory
     *
     * @param string $a_dir directory
     */
    protected function setFileDirectory($a_dir)
    {
        $this->file_dir = (string) $a_dir;
    }
    
    /**
     * Import uploaded file
     *
     * @param string $a_tmp_name temporary name
     * @param string $a_name original name
     * @param int $a_multi_index file index
     * @return bool
     */
    public function importFile($a_tmp_name, $a_name, $a_multi_index = null)
    {
        $a_tmp_name = (string) $a_tmp_name;

        if (is_uploaded_file($a_tmp_name)) {
            if (!(int) $a_multi_index) {
                $this->new_file = $a_tmp_name;
                $this->setFileName($a_name);
            } else {
                $this->new_file[(int) $a_multi_index] = $a_tmp_name;
                $this->setFileName($a_name, $a_multi_index);
            }
            return true;
        }
        return false;
    }

    /**
     * Get upload tmp name
     *
     * @param int $a_multi_index file index
     * @return string temporary filename
     */
    protected function getUploadedFile($a_multi_index = null)
    {
        if (!(int) $a_multi_index) {
            return $this->new_file;
        } else {
            return $this->new_file[(int) $a_multi_index];
        }
    }

    /**
     * Get original file name
     *
     * @param int $a_multi_index file index
     * @return string original filename
     */
    public function getFileName($a_multi_index = null)
    {
        if (!(int) $a_multi_index) {
            return $this->file_name;
        } else {
            return $this->file_name[(int) $a_multi_index];
        }
    }

    /**
     * Set original file name
     *
     * @param string $a_value file name
     * @param int $a_multi_index file index
     */
    public function setFileName($a_value, $a_multi_index = null)
    {
        if (!(int) $a_multi_index) {
            $this->file_name = (string) $a_value;
        } else {
            $this->file_name[(int) $a_multi_index] = (string) $a_value;
        }
    }

    /**
     * Remove existing file
     *
     * @param string $a_file_name file name
     */
    public function removeFile($a_file_name)
    {
        $path = $this->getFilePath();
        if ($path) {
            $file = $path . (string) $a_file_name;
            if (file_exists($file)) {
                unlink($file);
            }
        }
    }

    /**
     * Build path to file
     *
     * @return string file path
     */
    public function getFilePath()
    {
        if ($this->file_dir) {
            $path = ilUtil::getDataDir();
            return $path . "/adn/" . $this->file_dir . "/";
        }
    }

    /**
     * Import uploaded file into system
     *
     * @param string $a_upload_file temporary/source name
     * @param string $a_file_name target name
     * @return bool
     */
    protected function saveFile($a_upload_file, $a_file_name)
    {
        if ((string) $a_upload_file && $this->file_dir) {
            // create target directory
            if (!is_dir($this->getFilePath())) {
                $path = ilUtil::getDataDir();
                ilUtil::createDirectory($path . "/adn");
                ilUtil::createDirectory($path . "/adn/" . $this->file_dir);
            }
            
            $target = $this->getFilePath() . (string) $a_file_name;
            if (!move_uploaded_file((string) $a_upload_file, $target)) {
                return false;
            }
        }
        return true;
    }

    /**
     * Handle alpha-numeric filter values (case vs. mc, (sub-)objectives and questions)
     *
     * Centralized here because of Oracle/MySQL differences as a reference implementation
     *
     * @param array $a_where
     * @param string $a_column
     * @param mixed $a_value
     */
    protected static function handleAlphaNumericFilter(array &$a_where, $a_column, $a_value)
    {
        global $ilDB;

        if ($ilDB->getDBType() == "mysql" || $ilDB->getDBType() == "innodb") {
            $cast_value = $cast_column = "CAST(%s AS UNSIGNED)";
        } else {
            $cast_value = "TO_NUMBER(%s)";
            $cast_column = "TO_NUMBER(REGEXP_REPLACE(%s,'[^[:digit:]]',''))";
        }

        if (is_array($a_value)) {
            if (isset($a_value["from"]) && $a_value["from"]) {
                if (is_numeric($a_value["from"])) {
                    $a_where[] = sprintf($cast_column, $a_column) . " >= " .
                        sprintf($cast_value, $ilDB->quote((int) $a_value["from"], "text"));
                } else {
                    $a_where[] = $a_column . " >= " .
                        $ilDB->quote($a_value["from"], "text");
                }
            }
            if (isset($a_value["to"]) && $a_value["to"]) {
                if (is_numeric($a_value["to"])) {
                    $a_where[] = sprintf($cast_column, $a_column) . " <= " .
                        sprintf($cast_value, $ilDB->quote((int) $a_value["to"], "text"));
                } else {
                    $a_where[] = $a_column . " <= " .
                        $ilDB->quote($a_value["to"], "text");
                }
            }
        } elseif ($a_value) {
            $a_where[] = $ilDB->like($a_column, "text", "%" . $a_value . "%");
        }
    }

    protected static function handleName($a_name, $a_archived)
    {
        global $lng;
        
        if ($a_archived) {
            $a_name .= " " . $lng->txt("adn_archived_flag");
        }
        return $a_name;
    }
}
