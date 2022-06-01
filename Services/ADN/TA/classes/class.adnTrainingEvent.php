<?php
/* Copyright (c) 2010 Leifos, GPL, see docs/LICENSE */

/**
 * Training event application class
 *
 * Every event has a mandatory parent provider
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.de>
 * @version $Id: class.adnTrainingEvent.php 27891 2011-02-28 11:46:53Z jluetzen $
 *
 * @ingroup ServicesADN
 */
class adnTrainingEvent extends adnDBBase
{
    protected int $id = 0;
    protected int $provider_id = 0;
    protected string $type = '';
    protected ?ilDate $date_from = null;
    protected ?ilDate $date_to = null;

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
     * Set type of training
     *
     * @param string $a_last_name
     */
    public function setType($a_type)
    {
        $this->type = (string) $a_type;
    }

    /**
     * Get type of training
     *
     * @return int
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set from date
     *
     * @param ilDate $a_date
     */
    public function setDateFrom(ilDate $a_date)
    {
        $this->date_from = $a_date;
    }

    /**
     * Get from date
     *
     * @return ilDate
     */
    public function getDateFrom()
    {
        return $this->date_from;
    }

    /**
     * Set to date
     *
     * @param ilDate $a_date
     */
    public function setDateTo(ilDate $a_date)
    {
        $this->date_to = $a_date;
    }

    /**
     * Get to date
     *
     * @return ilDate
     */
    public function getDateTo()
    {
        return $this->date_to;
    }

    /**
     * Set training facility
     *
     * @param int $a_facility
     */
    public function setFacility($a_facility)
    {
        $this->facility = (int) $a_facility;
    }

    /**
     * Get training facility
     *
     * @return int
     */
    public function getFacility()
    {
        return $this->facility;
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

        $res = $this->db->query("SELECT ta_provider_id,type,date_from,date_to,ta_facility_id" .
            " FROM adn_ta_event" .
            " WHERE id = " . $this->db->quote($this->getId(), "integer"));
        $set = $this->db->fetchAssoc($res);
        $this->setProvider($set["ta_provider_id"]);
        $this->setType($set["type"]);
        $this->setDateFrom(new ilDate($set["date_from"], IL_CAL_DATE, ilTimeZone::UTC));
        $this->setDateTo(new ilDate($set["date_to"], IL_CAL_DATE, ilTimeZone::UTC));
        $this->setFacility($set["ta_facility_id"]);

        parent::_read($id, "adn_ta_event");
    }

    /**
     * Convert properties to DB fields
     *
     * @return array (ta_provider_id, type, date_from, date_to, ta_facility_id)
     */
    protected function propertiesToFields()
    {
        $fields = array("ta_provider_id" => array("integer", $this->getProvider()),
            "type" => array("text", $this->getType()),
            "date_from" => array("text", $this->getDateFrom()->get(
                IL_CAL_DATE,
                "",
                ilTimeZone::UTC
            )),
            "date_to" => array("text", $this->getDateTo()->get(IL_CAL_DATE, "", ilTimeZone::UTC)),
            "ta_facility_id" => array("integer", $this->getFacility()));
            
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
        $this->setId($this->db->nextId("adn_ta_event"));
        $id = $this->getId();

        $fields = $this->propertiesToFields();
        $fields["id"] = array("integer", $id);
            
        $this->db->insert("adn_ta_event", $fields);

        parent::_save($id, "adn_ta_event");
        
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
        
        $this->db->update("adn_ta_event", $fields, array("id" => array("integer", $id)));

        parent::_update($id, "adn_ta_event");

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
            // date in the past: archive
            if ($this->getDateTo()->get(
                IL_CAL_DATETIME,
                "",
                ilTimeZone::UTC
            ) < date("Y-m-d 00:00:00")) {
                $this->setArchived(true);
                return $this->update();
            }
            // upcoming: full delete
            else {
                $this->db->manipulate("DELETE FROM adn_ta_event" .
                    " WHERE id = " . $this->db->quote($id, "integer"));
                $this->setId(null);
                return true;
            }
        }
    }

    /**
     * Get all instructors
     *
     * @param int $a_provider_id
     * @param bool $a_current
     * @param bool $a_with_archived
     * @return array
     */
    public static function getAllTrainingEvents(
        $a_provider_id = false,
        $a_current = true,
        $a_filter = null,
        $a_with_archived = false
    )
    {
        global $DIC;
        $ilDB = $DIC->database();

        $sql = "SELECT id,ta_facility_id,ta_provider_id,type,date_from,date_to" .
            " FROM adn_ta_event";

        $where = array();
        if ($a_provider_id) {
            $where[] = "ta_provider_id = " . $ilDB->quote($a_provider_id, "integer");
        }
        if ($a_current !== null) {
            if ($a_current) {
                $where[] = "date_to >= " . $ilDB->quote(date("Y-m-d 00:00:00"), "text");
            } else {
                $where[] = "date_to < " . $ilDB->quote(date("Y-m-d 00:00:00"), "text");
            }
        }
        if (!$a_with_archived) {
            $where[] = "archived < " . $ilDB->quote(1, "integer");
        }
        if (isset($a_filter["type_of_training"]) && $a_filter["type_of_training"]) {
            $where[] = "type = " . $ilDB->quote($a_filter["type_of_training"], "text");
        }
        if (isset($a_filter["date"]) && $a_filter["date"]) {
            if (isset($a_filter["date"]["from"])) {
                $date = $a_filter["date"]["from"]->get(IL_CAL_DATE);
                $where[] = "date_from >= " . $ilDB->quote($date, "text");
            }
            if (isset($a_filter["date"]["to"])) {
                $date = $a_filter["date"]["to"]->get(IL_CAL_DATE);
                $where[] = "date_to <= " . $ilDB->quote($date, "text");
            }
        }
        if (isset($a_filter["training_facility"]) && $a_filter["training_facility"]) {
            $where[] = "ta_facility_id = " . $ilDB->quote($a_filter["training_facility"], "integer");
        }
        if (isset($a_filter["provider"]) && $a_filter["provider"]) {
            $where[] = "ta_provider_id = " . $ilDB->quote($a_filter["provider"], "integer");
        }
        if (sizeof($where)) {
            $sql .= " WHERE " . implode(" AND ", $where);
        }

        $res = $ilDB->query($sql);
        $all = array();
        while ($row = $ilDB->fetchAssoc($res)) {
            $row["date_from"] = new ilDate($row["date_from"], IL_CAL_DATE, ilTimeZone::UTC);
            $row["date_to"] = new ilDate($row["date_to"], IL_CAL_DATE, ilTimeZone::UTC);
            $all[] = $row;
        }
        return $all;
    }

    /**
     * Lookup property
     *
     * @param	integer	$a_id	instructor id
     * @param	string	$a_prop	property
     *
     * @return	mixed	property value
     */
    protected static function lookupProperty($a_id, $a_prop)
    {
        global $DIC;
        $ilDB = $DIC->database();

        $set = $ilDB->query("SELECT " . $a_prop .
            " FROM adn_ta_event" .
            " WHERE id = " . $ilDB->quote($a_id, "integer"));
        $rec = $ilDB->fetchAssoc($set);
        return $rec[$a_prop];
    }

    /**
     * Lookup name
     *
     * Because we add the training type and facility data this is more than a simple property lookup
     *
     * @param int $a_id
     * @return string
     */
    public static function lookupName($a_id)
    {
        global $DIC;
        $ilDB = $DIC->database();
        
        $set = $ilDB->query("SELECT ta_facility_id,type,date_from,date_to" .
            " FROM adn_ta_event" .
            " WHERE id = " . $ilDB->quote($a_id, "integer"));
        $rec = $ilDB->fetchAssoc($set);

        include_once("./Services/ADN/TA/classes/class.adnTypesOfTraining.php");
        include_once "Services/ADN/TA/classes/class.adnTrainingFacility.php";

        $from = new ilDate($rec["date_from"], IL_CAL_DATE, ilTimeZone::UTC);
        $to = new ilDate($rec["date_to"], IL_CAL_DATE, ilTimeZone::UTC);

        $parts = array();
        $parts[] = adnTypesOfTraining::getTextRepresentation($rec["type"]);
        $parts[] = ilDatePresentation::formatDate($from) . " - " .
            ilDatePresentation::formatDate($to);
        $parts[] = adnTrainingFacility::lookupName($rec["ta_facility_id"]);

        return implode(", ", $parts);
    }
}
