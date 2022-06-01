<?php
/* Copyright (c) 2010 Leifos, GPL, see docs/LICENSE */

include_once("./Services/Database/classes/class.ilDBUpdate.php");

/**
 * ADN Database Update class
 *
 * Enables the custom ADN DB update steps, which add ADN specific tables to the database
 *
 * @author Alex Killing <killing@leifos.com>
 * @version $Id: class.adnDBUpdate.php 27871 2011-02-25 15:29:26Z jluetzen $
 *
 * @ingroup ServicesADN
 */
class adnDBUpdate extends ilDBUpdate
{
    /**
     * Constructor
     *
     * @param	object		$a_db_handler	database handler
     */
    public function __construct($a_db_handler)
    {
        $this->PATH = "./";
        $this->db = $a_db_handler;
        
        $this->getCurrentVersion();
        
        // get update file for current version
        $updatefile = $this->getFileForStep($this->currentVersion + 1);

        $this->current_file = $updatefile;
        $this->DB_UPDATE_FILE = $this->PATH . "Services/ADN/Base/classes/" . $updatefile;
        
        $this->LAST_UPDATE_FILE = $this->PATH . "Services/ADN/Base/classes/adnDBUpdateSteps.php";
        $this->readDBUpdateFile();
        $this->readLastUpdateFile();
        $this->readFileVersion();
    }
    
    /**
     * Get db update file name for db step
     *
     * @param	int		$a_version		version
     */
    public function getFileForStep($a_version)
    {
        return "adnDBUpdateSteps.php";
    }
        

    /**
     * Get current adn DB version
     *
     * @return	int		version
     */
    public function getCurrentVersion()
    {
        $GLOBALS["ilDB"] = $this->db;
        include_once './Services/Administration/classes/class.ilSetting.php';
        $set = new ilSetting();
        $this->currentVersion = (integer) $set->get("adn_db_version");
        return $this->currentVersion;
    }

    /**
     * Set current adn DB version
     *
     * @param	int		$a_version		version
     */
    public function setCurrentVersion($a_version)
    {
        include_once './Services/Administration/classes/class.ilSetting.php';
        $set = new ilSetting();
        $set->set("adn_db_version", $a_version);
        $this->currentVersion = $a_version;
        
        return true;
    }
    
    /**
     * Set running status for a step
     *
     * @param	int		step number
     */
    public function setRunningStatus($a_nr)
    {
        include_once './Services/Administration/classes/class.ilSetting.php';
        $set = new ilSetting();
        $set->set("adn_db_update_running", $a_nr);
        $this->db_update_running = $a_nr;
    }
    
    /**
     * Get running status
     *
     * @return	int		current runnning db step
     */
    public function getRunningStatus()
    {
        include_once './Services/Administration/classes/class.ilSetting.php';
        $set = new ilSetting();
        $this->db_update_running = (integer) $set->get("adn_db_update_running");

        return $this->db_update_running;
    }
    
    /**
     * Clear running status
     */
    public function clearRunningStatus()
    {
        include_once './Services/Administration/classes/class.ilSetting.php';
        $set = new ilSetting();
        $set->set("adn_db_update_running", 0);
        $this->db_update_running = 0;
    }

    
    /**
     * Apply update
     *
     * @param	int		$a_break	number of next update step, where a break should
     *								be performed
     */
    public function applyUpdate($a_break = 0)
    {
        
        $f = $this->fileVersion;
        $c = $this->currentVersion;
        
        if ($a_break > $this->currentVersion &&
            $a_break < $this->fileVersion) {
            $f = $a_break;
        }

        if ($c < $f) {
            $msg = array();
            for ($i = ($c + 1); $i <= $f; $i++) {
                // check wether next update file must be loaded
                if ($this->current_file != $this->getFileForStep($i)) {
                    $this->DB_UPDATE_FILE = $this->PATH . "Services/ADN/Base/classes/" . $this->getFileForStep($i);
                    $this->readDBUpdateFile();
                }
                
                $this->initStep($i);
                
                if ($this->applyUpdateNr($i) == false) {
                    $msg[] = array(
                        "msg" => "update_error: " . $this->error,
                        "nr" => $i
                    );
                    $this->updateMsg = $msg;
                    return false;
                } else {
                    $msg[] = array(
                        "msg" => "update_applied",
                        "nr" => $i
                    );
                }
            }

            $this->updateMsg = $msg;
        } else {
            $this->updateMsg = "no_changes";
        }

        if ($f < $this->fileVersion) {
            return true;
        } else {
            return $this->loadXMLInfo();
        }
    }
}
