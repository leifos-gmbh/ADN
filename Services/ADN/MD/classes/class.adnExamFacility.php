<?php
/* Copyright (c) 2010 Leifos, GPL, see docs/LICENSE */

/**
 * Exam facility application class
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.de>
 * @version $Id: class.adnExamFacility.php 27867 2011-02-25 09:35:47Z akill $
 *
 * @ingroup ServicesADN
 */
class adnExamFacility extends adnDBBase
{
	protected $id; // [int]
	protected $wmo_id; // [int]
	protected $company; // [string]
	protected $street; // [string]
	protected $street_no; // [string]
	protected $zip; // [string]
	protected $city; // [string]

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
	 * Set wmo id
	 *
	 * @param int $a_id
	 */
	public function setWMO($a_id)
	{
		$this->wmo_id = (int)$a_id;
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
	 * Set name
	 *
	 * @param string $a_name
	 */
	public function setName($a_name)
	{
		$this->name = (string)$a_name;
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
	 * Set street
	 *
	 * @param string $a_street
	 */
	public function setStreet($a_street)
	{
		$this->street = (string)$a_street;
	}

	/**
	 * Get street
	 *
	 * @return string
	 */
	public function getStreet()
	{
		return $this->street;
	}

	/**
	 * Set street number
	 *
	 * @param string $a_street_no
	 */
	public function setStreetNumber($a_street_no)
	{
		$this->street_no = (string)$a_street_no;
	}

	/**
	 * Get street number
	 *
	 * @return string
	 */
	public function getStreetNumber()
	{
		return $this->street_no;
	}

	/**
	 * Set zip
	 *
	 * @param string $a_zip
	 */
	public function setZip($a_zip)
	{
		$this->zip = (string)$a_zip;
	}

	/**
	 * Get zip
	 *
	 * @return string
	 */
	public function getZip()
	{
		return $this->zip;
	}

	/**
	 * Set city
	 *
	 * @param string $a_city
	 */
	public function setCity($a_city)
	{
		$this->city = (string)$a_city;
	}

	/**
	 * Get city
	 *
	 * @return string
	 */
	public function getCity()
	{
		return $this->city;
	}

	/**
	 * Read db entry
	 */
	public function read()
	{
		global $ilDB;

		$id = $this->getId();

		$res = $ilDB->query("SELECT md_wmo_id,name,street,street_no,postal_code,city".
			" FROM adn_md_exam_facility".
			" WHERE id = ".$ilDB->quote($id, "integer"));
		$set = $ilDB->fetchAssoc($res);
		$this->setWMO($set["md_wmo_id"]);
		$this->setName($set["name"]);
		$this->setStreet($set["street"]);
		$this->setStreetNumber($set["street_no"]);
		$this->setZip($set["postal_code"]);
		$this->setCity($set["city"]);

		parent::read($id, "adn_md_exam_facility");
	}

	/**
	 * Convert properties to DB fields
	 *
	 * @return array (md_wmo_id, name, street, street_no, postal_code, city)
	 */
	protected function propertiesToFields()
	{
		$fields = array("md_wmo_id" => array("integer", $this->getWMO()),
			"name" => array("text", $this->getName()),
			"street" => array("text", $this->getStreet()),
			"street_no" => array("text", $this->getStreetNumber()),
			"postal_code" => array("text", $this->getZip()),
			"city" => array("text", $this->getCity()));
			
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
		$this->setId($ilDB->nextId("adn_md_exam_facility"));
		$id = $this->getId();

		$fields = $this->propertiesToFields();
		$fields["id"] = array("integer", $id);
			
		$ilDB->insert("adn_md_exam_facility", $fields);

		parent::save($id, "adn_md_exam_facility");
		
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

		$ilDB->update("adn_md_exam_facility", $fields, array("id"=>array("integer", $id)));

		parent::update($id, "adn_md_exam_facility");

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
			// U.SD.3.4: check if exam facility is used in any examination event
			include_once "Services/ADN/EP/classes/class.adnExaminationEvent.php";
			if(adnExaminationEvent::hasExamFacility($id))
			{
				$this->setArchived(true);
				return $this->update();
			}
			else
			{
				$ilDB->manipulate("DELETE FROM adn_md_exam_facility".
					" WHERE id = ".$ilDB->quote($id, "integer"));
				$this->setId(null);
				return true;
			}
		}
	}

	/**
	 * Get all facilities
	 *
	 * @param int $a_wmo_id
	 * @param bool $a_with_archived
	 * @return array
	 */
	public static function getAllExamFacilities($a_wmo_id = false, $a_with_archived = false)
	{
		global $ilDB;

		$sql = "SELECT id,name,street,street_no,postal_code,city,md_wmo_id".
			" FROM adn_md_exam_facility";

		$where = array();
		if($a_wmo_id)
		{
			$where[] = "md_wmo_id = ".$ilDB->quote($a_wmo_id, "integer");
		}
		if(!$a_with_archived)
		{
			$where[] = "archived < ".$ilDB->quote(1, "integer");
		}
		if(sizeof($where))
		{
			$sql .= " WHERE ".implode(" AND ", $where);
		}

		$res = $ilDB->query($sql);
		$all = array();
		while($row = $ilDB->fetchAssoc($res))
		{
			$all[] = $row;
		}
		return $all;
	}

	/**
	 * Get facility ids and names
	 *
	 * @param int $a_wmo_id
	 * @param int $a_old_value
	 * @return array (id => caption)
	 */
	public static function getFacilitiesSelect($a_wmo_id = null, $a_old_value = null)
	{
		global $ilDB;

		$sql = "SELECT id,city".
			" FROM adn_md_exam_facility";
		if(!$a_old_value)
		{
			$sql .= " WHERE archived < ".$ilDB->quote(1, "integer");
		}
		else
		{
			$sql .= " WHERE (archived < ".$ilDB->quote(1, "integer").
				" OR id = ".$ilDB->quote($a_old_value, "integer").")";
		}
		if($a_wmo_id)
		{
			$sql .= " AND md_wmo_id = ".$ilDB->quote($a_wmo_id, "integer");
		}

		$res = $ilDB->query($sql);
		$all = array();
		while($row = $ilDB->fetchAssoc($res))
		{
			$all[$row["id"]] = $row["city"];
		}
		return $all;
	}

	/**
	 * Lookup property
	 *
	 * @param integer $a_id facility id
	 * @param string $a_prop property
	 * @return mixed property value
	 */
	protected static function lookupProperty($a_id, $a_prop)
	{
		global $ilDB;

		$set = $ilDB->query("SELECT ".$a_prop.
			" FROM adn_md_exam_facility".
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
		return self::lookupProperty($a_id, "name");
	}

	/**
	 * Lookup city
	 *
	 * @param int $a_id
	 * @return string
	 */
	public static function lookupCity($a_id)
	{
		return self::lookupProperty($a_id, "city");
	}
}

?>