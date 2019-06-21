<?php
/* Copyright (c) 2010 Leifos, GPL, see docs/LICENSE */

/**
 * Examination event application class
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.de>
 * @version $Id: class.adnExaminationEvent.php 27883 2011-02-27 19:30:41Z akill $
 *
 * @ingroup ServicesADN
 */
class adnExaminationEvent extends adnDBBase
{
	protected $id; // [int]
	protected $type; // [string]
	protected $date_from; // [ilDateTime]
	protected $date_to; // [ilDateTime]
	protected $facility; // [int]
	protected $chairman; // [int]
	protected $co_chair1; // [int]
	protected $co_chair2; // [int]
	protected $costs; // [float]

	/**
	 * Constructor
	 *
	 * @param int $a_id instance id
	 */
	public function __construct($a_id = null)
	{
		global $ilCtrl;

		if($a_id)
		{
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
		$this->id = (int)$a_id;
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
	 * Set type
	 *
	 * @param string $a_type
	 */
	public function setType($a_type)
	{
		$this->type = (string)$a_type;
	}

	/**
	 * Get type
	 *
	 * @return string
	 */
	public function getType()
	{
		return $this->type;
	}

	/**
	 * Set facility
	 *
	 * @param int $a_id
	 */
	public function setFacility($a_id)
	{
		$this->facility = (int)$a_id;
	}

	/**
	 * Get facility
	 *
	 * @return int
	 */
	public function getFacility()
	{
		return $this->facility;
	}

	/**
	 * Set from date
	 *
	 * @param ilDateTime $a_date
	 */
	public function setDateFrom(ilDateTime $a_date)
	{
		$this->date_from = $a_date;
	}

	/**
	 * Get from date
	 *
	 * @return ilDateTime
	 */
	public function getDateFrom()
	{
		return $this->date_from;
	}

	/**
	 * Set to date
	 *
	 * @param ilDateTime $a_date
	 */
	public function setDateTo(ilDateTime $a_date)
	{
		$this->date_to = $a_date;
	}

	/**
	 * Get to date
	 *
	 * @return ilDateTime
	 */
	public function getDateTo()
	{
		return $this->date_to;
	}

	/**
	 * Set chairman
	 *
	 * @param int $a_id
	 */
	public function setChairman($a_id)
	{
		$this->chairman = (int)$a_id;
	}

	/**
	 * Get chairman
	 *
	 * @return int
	 */
	public function getChairman()
	{
		return $this->chairman;
	}

	/**
	 * Set co-chair 1
	 *
	 * @param int $a_id
	 */
	public function setCoChair1($a_id)
	{
		$this->co_chair1 = (int)$a_id;
	}

	/**
	 * Get co chair 1
	 *
	 * @return int
	 */
	public function getCoChair1()
	{
		return $this->co_chair1;
	}

	/**
	 * Set co-chair 2
	 *
	 * @param int $a_id
	 */
	public function setCoChair2($a_id)
	{
		$this->co_chair2 = (int)$a_id;
	}

	/**
	 * Get co chair 2
	 *
	 * @return int
	 */
	public function getCoChair2()
	{
		return $this->co_chair2;
	}

	/**
	 * Set additional costs
	 *
	 * @param float $a_value
	 */
	public function setCosts($a_value)
	{
		if(stristr($a_value, ","))
		{
			$a_value = str_replace(",", ".", $a_value);
		}
		$a_value = round((float)$a_value, 2);

		$this->costs = $a_value;
	}

	/**
	 * Get co chair 1
	 *
	 * @return int
	 */
	public function getCosts()
	{
		return $this->costs;
	}
	
	/**
	 * Read db entry
	 */
	public function read()
	{
		global $ilDB;

		$id = $this->getId();
		if(!$id)
		{
			return;
		}

		$res = $ilDB->query("SELECT subject_area,date_from,date_to,md_exam_facility_id,chairman_id,".
			"co_chair_1_id,co_chair_2_id,additional_costs".
			" FROM adn_ep_exam_event".
			" WHERE id = ".$ilDB->quote($this->getId(), "integer"));
		$set = $ilDB->fetchAssoc($res);
		$this->setType($set["subject_area"]);
		$this->setDateFrom(new ilDateTime($set["date_from"], IL_CAL_DATETIME, ilTimeZone::UTC));
		$this->setDateTo(new ilDateTime($set["date_to"], IL_CAL_DATETIME, ilTimeZone::UTC));
		$this->setFacility($set["md_exam_facility_id"]);
		$this->setChairman($set["chairman_id"]);
		$this->setCoChair1($set["co_chair_1_id"]);
		$this->setCoChair2($set["co_chair_2_id"]);
		$this->setCosts($set["additional_costs"]/100);
		
		parent::read($id, "adn_ep_exam_event");
	}

	/**
	 * Convert properties to DB fields
	 *
	 * @return array (subject_area, date_from, date_to, md_exam_facility_id, chairman_id,
	 * co_chair_id_1, co_chair_id_2, additional_costs)
	 */
	protected function propertiesToFields()
	{
		$fields = array("subject_area" => array("text", $this->getType()),
			"date_from" => array("timestamp", $this->getDateFrom()->get(IL_CAL_DATETIME, "",
				ilTimeZone::UTC)),
			"date_to" => array("timestamp", $this->getDateTo()->get(IL_CAL_DATETIME, "",
				ilTimeZone::UTC)),
			"md_exam_facility_id" => array("integer", $this->getFacility()),
			"chairman_id" => array("integer", $this->getChairman()),
			"co_chair_1_id" => array("integer", $this->getCoChair1()),
			"co_chair_2_id" => array("integer", $this->getCoChair2()),
			"additional_costs" => array("integer", $this->getCosts()*100));

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
		$this->setId($ilDB->nextId("adn_ep_exam_event"));
		$id = $this->getId();

		$fields = $this->propertiesToFields();
		$fields["id"] = array("integer", $id);

		$ilDB->insert("adn_ep_exam_event", $fields);

		parent::save($id, "adn_ep_exam_event");
		
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
		if(!$id)
		{
			return;
		}

		$fields = $this->propertiesToFields();
		
		$ilDB->update("adn_ep_exam_event", $fields, array("id"=>array("integer", $id)));

		parent::update($id, "adn_ep_exam_event");

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
		if($id)
		{
			// archived flag is not used here!

			// delete sheets incl. sub-tables
			include_once "Services/ADN/EP/classes/class.adnAnswerSheet.php";
			$all = adnAnswerSheet::getSheetsSelect($id);
			if($all)
			{
				foreach($all as $sheet_id => $sheet_name)
				{
					$sheet = new adnAnswerSheet($sheet_id);
					$sheet->delete();
				}
			}

			// $ilDB->manipulate("DELETE FROM adn_es_certificate".
			//		" WHERE ep_exam_id = ".$ilDB->quote($id, "integer"));
			$ilDB->manipulate("DELETE FROM adn_ep_exam_invitation".
				" WHERE ep_exam_event_id = ".$ilDB->quote($id, "integer"));
			$ilDB->manipulate("DELETE FROM adn_ep_assignment".
				" WHERE ep_exam_event_id = ".$ilDB->quote($id, "integer"));
			$ilDB->manipulate("DELETE FROM adn_ep_exam_event".
				" WHERE id = ".$ilDB->quote($id, "integer"));
			$this->setId(null);
			return true;
		}
	}

	/**
	 * Get all events
	 *
	 * @param array $a_filter
	 * @param bool $a_archived show current or past events
	 * @return array
	 */
	public static function getAllEvents(array $a_filter = null, $a_archived = false)
	{
		global $ilDB;

		$sql = "SELECT id,subject_area,date_from,date_to,md_exam_facility_id".
			" FROM adn_ep_exam_event";

		$where = array();
		if(!(bool)$a_archived)
		{
			// get all events of today or later
			$now = substr(ilUtil::now(), 0, 10)." 00:00:00";
			$where[] = "date_to >= ".$ilDB->quote($now, "timestamp");
		}
		else
		{
			// get all events of today or earlier
			$now = substr(ilUtil::now(), 0, 10)." 23:59:59";
			$where[] = "date_to < ".$ilDB->quote($now, "timestamp");
		}

		if(isset($a_filter["type"]) && $a_filter["type"])
		{
			$where[] = "subject_area = ".$ilDB->quote($a_filter["type"], "text");
		}
		if(isset($a_filter["facility"]) && $a_filter["facility"])
		{
			$where[] = "md_exam_facility_id = ".$ilDB->quote($a_filter["facility"], "integer");
		}
		if(isset($a_filter["date"]))
		{
			if(isset($a_filter["date"]["from"]) && $a_filter["date"]["from"])
			{
				if(is_object($a_filter["date"]["from"]))
				{
					$a_filter["date"]["from"] = $a_filter["date"]["from"]->get(IL_CAL_DATE).
						" 00:00:00";
				}
				$where[] = "date_from >= ".$ilDB->quote($a_filter["date"]["from"], "timestamp");
			}
			if(isset($a_filter["date"]["to"]) && $a_filter["date"]["to"])
			{
				if(is_object($a_filter["date"]["to"]))
				{
					$a_filter["date"]["to"] = $a_filter["date"]["to"]->get(IL_CAL_DATE)." 23:59:59";
				}
				$where[] = "date_to <= ".$ilDB->quote($a_filter["date"]["to"], "timestamp");
			}
		}
		if(sizeof($where))
		{
			$sql .= " WHERE ".implode(" AND ", $where);
		}
		
		$sql .= " ORDER BY date_from";
		
		$res = $ilDB->query($sql);
		$all = array();
		while($row = $ilDB->fetchAssoc($res))
		{
			$row["date_from"] = new ilDateTime($row["date_from"], IL_CAL_DATETIME, ilTimeZone::UTC);
			$row["date_to"] = new ilDateTime($row["date_to"], IL_CAL_DATETIME, ilTimeZone::UTC);
			$all[] = $row;
		}

		return $all;
	}

	/**
	 * Get event ids and names
	 *
	 * @return array (id => caption)
	 */
	public static function getEventsSelect()
	{
		global $ilDB;

		include_once "Services/ADN/MD/classes/class.adnExamFacility.php";
		include_once "Services/ADN/ED/classes/class.adnSubjectArea.php";

		$sql = "SELECT id,subject_area,date_from,md_exam_facility_id".
			" FROM adn_ep_exam_event".
			" ORDER BY date_from";
		
		$res = $ilDB->query($sql);
		$all = array();
		while($row = $ilDB->fetchAssoc($res))
		{
			$date = new ilDateTime($row["date_from"], IL_CAL_DATETIME, ilTimeZone::UTC);
			$all[$row["id"]] = ilDatePresentation::formatDate($date).", ".
				adnSubjectArea::getTextRepresentation($row["subject_area"]).", ".
				adnExamFacility::lookupName($row["md_exam_facility_id"]);
		}

		return $all;
	}

	/**
	 * Lookup property
	 *
	 * @param integer $a_id event id
	 * @param string $a_prop property
	 * @return mixed property value
	 */
	protected static function lookupProperty($a_id, $a_prop)
	{
		global $ilDB;

		$set = $ilDB->query("SELECT ".$a_prop.
			" FROM adn_ep_exam_event".
			" WHERE id = ".$ilDB->quote($a_id, "integer"));
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
		global $ilDB;
		
		include_once "Services/ADN/MD/classes/class.adnExamFacility.php";
		include_once "Services/ADN/ED/classes/class.adnSubjectArea.php";

		$sql = "SELECT id,subject_area,date_from,md_exam_facility_id".
			" FROM adn_ep_exam_event".
			" WHERE id = ".$ilDB->quote($a_id, "integer");
		$res = $ilDB->query($sql);
		if($ilDB->numRows($res))
		{
			$row = $ilDB->fetchAssoc($res);
			$date = new ilDateTime($row["date_from"], IL_CAL_DATETIME, ilTimeZone::UTC);
			return ilDatePresentation::formatDate($date).", ".
				adnSubjectArea::getTextRepresentation($row["subject_area"]).", ".
				adnExamFacility::lookupCity($row["md_exam_facility_id"]);
		}
	}

	/**
	 * Lookup event type
	 *
	 * @param int $a_id
	 * @return string
	 */
	public static function lookupType($a_id)
	{
		return adnExaminationEvent::lookupProperty($a_id, "type");
	}

	/**
	 * Check if any event has user as co-chair or chairman
	 *
	 * We are using a separate method because co-chair deletion depends on it
	 * 
	 * @param int $a_id
	 * @return bool
	 */
	public static function hasChair($a_id)
	{
		global $ilDB;
		
		$id = $ilDB->quote($a_id, "integer");

		$set = $ilDB->query("SELECT id FROM adn_ep_exam_event".
			" WHERE chairman_id = ".$id." OR co_chair_1_id = ".$id." OR co_chair_2_id = ".$id);
		if($ilDB->numRows($set))
		{
			return true;
		}
		return false;
	}

	/**
	 * Check if any event has exam facility
	 *
	 * We are using a separate method because exam facility deletion depends on it
	 *
	 * @param int $a_id
	 * @return bool
	 */
	public static function hasExamFacility($a_id)
	{
		global $ilDB;

		$id = $ilDB->quote($a_id, "integer");

		$set = $ilDB->query("SELECT id FROM adn_ep_exam_event".
			" WHERE md_exam_facility_id = ".$id);
		if($ilDB->numRows($set))
		{
			return true;
		}
		return false;
	}
}

?>