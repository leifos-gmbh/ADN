<?php
/* Copyright (c) 2010 Leifos, GPL, see docs/LICENSE */

/**
 * Objective application class
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.de>
 * @version $Id: class.adnObjective.php 28314 2011-04-01 13:27:14Z jluetzen $
 *
 * @ingroup ServicesADN
 */
class adnObjective extends adnDBBase
{
	protected $id; // [int]
	protected $catalog_area_id; // [int]
	protected $type; // [int]
	protected $number; // [string]
	protected $name; // [string]
	protected $topic; // [string]
	protected $sheet_subjected; // [bool]

	const TYPE_MC = 1;
	const TYPE_CASE = 2;

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
	 * Set catalog area
	 *
	 * @param int $a_area
	 */
	public function setCatalogArea($a_area)
	{
		$this->catalog_area = (int)$a_area;
	}

	/**
	 * Get catalog area
	 *
	 * @return int
	 */
	public function getCatalogArea()
	{
		return $this->catalog_area;
	}

	/**
	 * Set type
	 *
	 * @param int $a_type
	 */
	public function setType($a_type)
	{
		if($this->isValidType($a_type))
		{
			$this->type = (int)$a_type;
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
	 * Check if given type is valid (mc | case)
	 *
	 * @param int $a_type
	 * @return bool
	 */
	public static function isValidType($a_type)
	{
		if(in_array((int)$a_type, array(self::TYPE_MC, self::TYPE_CASE)))
		{
			return true;
		}
		return false;
	}

	/**
	 * Set number
	 *
	 * @param string|integer $a_number
	 */
	public function setNumber($a_number)
	{
		if($this->type == self::TYPE_MC)
		{
			$this->number = (int)$a_number;
		}
		else
		{
			$this->number = (string)$a_number;
		}
	}

	/**
	 * Get number
	 *
	 * @return string|integer
	 */
	public function getNumber()
	{
		return $this->number;
	}

	/**
	 * Set topic
	 *
	 * @param string $a_number
	 */
	public function setTopic($a_topic)
	{
		$this->topic = (string)$a_topic;
	}

	/**
	 * Get topic
	 *
	 * @return string
	 */
	public function getTopic()
	{
		return $this->topic;
	}

	/**
	 * Set sheet subjected
	 *
	 * @param bool $a_value
	 */
	public function setSheetSubjected($a_value)
	{
		$this->sheet_subjected = (bool)$a_value;
	}

	/**
	 * Get sheet subjected
	 *
	 * @return bool
	 */
	public function isSheetSubjected()
	{
		return $this->sheet_subjected;
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

		$res = $ilDB->query("SELECT title,type,nr,topic,catalog_area,sheet_subjected".
			" FROM adn_ed_objective".
			" WHERE id = ".$ilDB->quote($this->getId(), "integer"));
		$set = $ilDB->fetchAssoc($res);
		$this->setName($set["title"]);
		$this->setTopic($set["topic"]);
		$this->setCatalogArea($set["catalog_area"]);
		$this->setType($set["type"]);
		$this->setNumber($set["nr"]);
		$this->setSheetSubjected($set["sheet_subjected"]);

		parent::read($a_id, "adn_ed_objective");
	}

	/**
	 * Convert properties to DB fields
	 *
	 * @return array (title, topic, nr, catalog_area, type, sheet_subjected)
	 */
	protected function propertiesToFields()
	{
		$fields = array("title" => array("text", $this->getName()),
			"topic" => array("text", $this->getTopic()),
			"nr" => array("text", $this->getNumber()),
			"catalog_area" => array("integer", $this->getCatalogArea()),
			"type" => array("integer", $this->getType()),
			"sheet_subjected" => array("integer", $this->isSheetSubjected()));
			
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

		$this->setId($ilDB->nextId("adn_ed_objective"));
		$id = $this->getId();

		$fields = $this->propertiesToFields();
		$fields["id"] = array("integer", $id);
			
		$ilDB->insert("adn_ed_objective", $fields);

		parent::save($id, "adn_ed_objective");
		
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
		
		$ilDB->update("adn_ed_objective", $fields, array("id"=>array("integer", $id)));

		parent::update($id, "adn_ed_objective");

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
			// U.PV.2.4: check if objective or its subojectives are used in any question

			$in_use = false;

			include_once "Services/ADN/ED/classes/class.adnExaminationQuestion.php";
			include_once "Services/ADN/ED/classes/class.adnQuestionTargetNumbers.php";
			if(
				sizeof(adnQuestionTargetNumbers::getByObjective($id)) ||
				sizeof(adnExaminationQuestion::getByObjective($id)))
			{
				$in_use = true;
			}

			if(!$in_use)
			{
				include_once "./Services/ADN/ED/classes/class.adnSubobjective.php";
				$all = adnSubobjective::lookupIdByObjective($id);
				if(sizeof($all))
				{
					foreach($all as $sobj_id)
					{
						ilLoggerFactory::getLogger('adn')->info('Validating: ' . $sobj_id);
						if(
							sizeof(adnQuestionTargetNumbers::getBySubobjective($sobj_id)) ||
							sizeof(adnExaminationQuestion::getBySubobjective($sobj_id)))
						{
							ilLoggerFactory::getLogger('adn')->info('Validating: ' . $sobj_id . ' is in use.');
							$in_use = true;
						}
						else
						{
							ilLoggerFactory::getLogger('adn')->info('Validating: ' . $sobj_id . ' is in not use.');
						}
					}
				}
			}

			if($in_use)
			{
				$this->setArchived(true);
				$this->update();
			}
			else
			{
				// delete subobjectives
				include_once './Services/ADN/ED/classes/class.adnSubobjective.php';
				ilLoggerFactory::getLogger('adn')->info('reading subobjectives for ' . $id);
				foreach((array) adnSubobjective::lookupIdByObjective($id) as $subobjective_id)
				{
					ilLoggerFactory::getLogger('adn')->info('---- deleting subobjective: ' . $subobjective_id);
					$subobjective = new adnSubobjective($subobjective_id);
					$subobjective->delete();
				}
				
				ilLoggerFactory::getLogger('adn')->info('-- deleting objectives for' . $id);
				$ilDB->manipulate("UPDATE adn_ep_sheet_question".
					" SET ed_objective_id = NULL".
					" WHERE ed_objective_id = ".$ilDB->quote($id, "integer"));
				$ilDB->manipulate("DELETE FROM adn_ed_target_nr_obj".
					" WHERE ed_objective_id = ".$ilDB->quote($id, "integer"));
				$ilDB->manipulate("DELETE FROM adn_ed_subobjective".
					" WHERE ed_objective_id = ".$ilDB->quote($id, "integer").' '.
					' AND archived < 1 ');
				$ilDB->manipulate("DELETE FROM adn_ed_objective".
					" WHERE id = ".$ilDB->quote($id, "integer").' '.
					' AND archived < 1 ');
				$this->setId(null);
			}
			return true;
		}
	}

	/**
	 * Get all objectives
	 *
	 * @param array $a_filter
	 * @param bool $a_with_archived
	 * @return array
	 */
	public static function getAllObjectives(array $a_filter = null, $a_with_archived = false)
	{
		global $ilDB;

		$sql = "SELECT id,title AS name,catalog_area,type,nr,topic".
			" FROM adn_ed_objective";

		$where = array();

		if(!$a_with_archived)
		{
			$where[] = "archived < ".$ilDB->quote(1, "integer");
		}

		if(is_array($a_filter))
		{
			if(isset($a_filter["type"]) && $a_filter["type"])
			{
				$where[] = "type = ".$ilDB->quote($a_filter["type"], "integer");
			}
			if(isset($a_filter["catalog_area"]) && $a_filter["catalog_area"])
			{
				if(!is_array($a_filter["catalog_area"]))
				{
					$where[] = "catalog_area = ".$ilDB->quote($a_filter["catalog_area"], "integer");
				}
				else
				{
					$where[] = $ilDB->in("catalog_area", $a_filter["catalog_area"], false, "integer");
				}
			}
			if(isset($a_filter["title"]) && $a_filter["title"])
			{
				$where[] = $ilDB->like("title", "text", "%".$a_filter["title"]."%");
			}
			if(isset($a_filter["nr"]))
			{
				self::handleAlphaNumericFilter($where, "nr", $a_filter["nr"]);
			}
		}

		if(sizeof($where))
		{
			$sql .= " WHERE ".implode(" AND ", $where);
		}

		$sql .= " ORDER BY type,nr";
		
		$res = $ilDB->query($sql);
		$all = array();
		while($row = $ilDB->fetchAssoc($res))
		{
			if($row["type"] == self::TYPE_MC)
			{
				$row["adn_number"] = $row["catalog_area"]." ".
					str_pad($row["nr"], 2, "0", STR_PAD_LEFT);
			}
			else
			{
				$row["adn_number"] = $row["nr"];
			}

			$all[] = $row;
		}

		return $all;
	}

	/**
	 * Get objective ids and names
	 *
	 * @param int|array $a_catalog_area
	 * @param int $a_type
	 * @param bool $a_exclude_subjected
	 * @param int $a_old_value
	 * @return array (id => caption)
	 */
	public static function getObjectivesSelect($a_catalog_area = null, $a_type = null,
		$a_exclude_subjected = false, $a_old_value = null)
	{
		global $ilDB;

		$sql = "SELECT id,nr,title".
			" FROM adn_ed_objective";

		$where = array();
		if(!$a_old_value)
		{
			$where[] = "archived < ".$ilDB->quote(1, "integer");
		}
		else
		{
			$where[] = "(archived < ".$ilDB->quote(1, "integer").
				" OR id = ".$ilDB->quote($a_old_value, "integer").")";
		}

		if($a_catalog_area)
		{
			if(!is_array($a_catalog_area))
			{
				$where[] = "catalog_area = ".$ilDB->quote($a_catalog_area, "integer");
			}
			else
			{
				$where[] = $ilDB->in("catalog_area", $a_catalog_area, false, "integer");
			}
		}
		if($a_type && self::isValidType($a_type))
		{
			$where[] = "type = ".$ilDB->quote($a_type, "integer");
		}
		if($a_exclude_subjected)
		{
			$where[] = "(sheet_subjected < ".$ilDB->quote(1, "integer").
				" OR sheet_subjected IS NULL)";
		}
		
		if(sizeof($where))
		{
			$sql .= " WHERE ".implode(" AND ", $where);
		}

		$sql .= " ORDER BY nr,title";

		$res = $ilDB->query($sql);
		$all = array();
		while($row = $ilDB->fetchAssoc($res))
		{
			$all[$row["id"]] = $row["nr"]." ".$row["title"];
		}

		return $all;
	}

	/**
	 * Lookup property
	 *
	 * @param integer $a_id objective id
	 * @param string $a_prop property
	 * @return mixed property value
	 */
	protected static function lookupProperty($a_id, $a_prop)
	{
		global $ilDB;

		$set = $ilDB->query("SELECT ".$a_prop.
			" FROM adn_ed_objective".
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
		return self::lookupProperty($a_id, "title");
	}

	/**
	 * Get caption for type
	 *
	 * @param int $a_type
	 * @return string
	 */
	public static function getTypeTextualRepresentation($a_type)
	{
		global $lng;
		
		if(self::isValidType($a_type))
		{
			if($a_type == self::TYPE_MC)
			{
				return $lng->txt("adn_type_mc");
			}
			else
			{
				return $lng->txt("adn_type_case");
			}
		}
	}

	/**
	 * Get all catalog areas with case objectives
	 *
	 * @return array
	 */
	public static function getAllCaseCatalogAreas()
	{
		return self::getAllCatalogAreas(self::TYPE_CASE);
	}

	/**
	 * Get all catalog areas with mc objectives
	 *
	 * @return array
	 */
	public static function getAllMCCatalogAreas()
	{
		return self::getAllCatalogAreas(self::TYPE_MC);
	}

	/**
	 * Get all catalog areas with objectives of certain type
	 *
	 * @param int $a_type
	 * @param bool $a_with_captions
	 * @return array
	 */
	protected static function getAllCatalogAreas($a_type, $a_with_captions = true)
	{
		global $ilDB;

		$set = $ilDB->query("SELECT DISTINCT(catalog_area)".
			" FROM adn_ed_objective".
			" WHERE type = ".$ilDB->quote($a_type, "integer"));
		$all = array();
		while($row = $ilDB->fetchAssoc($set))
		{
			$all[] = $row["catalog_area"];
		}

		if(sizeof($all))
		{
			if($a_with_captions)
			{
				$res = array();
				include_once "Services/ADN/ED/classes/class.adnCatalogNumbering.php";
				foreach($all as $id)
				{
					$res[$id] = adnCatalogNumbering::getAreaTextRepresentation($id);
				}
				asort($res);
				return $res;
			}
			else
			{
				return $all;
			}
		}
	}

	/**
	 * Check if given number is unique for current catalog area
	 *
	 * @return bool
	 */
	public function isUniqueNumber()
	{
		global $ilDB;

		$id = $this->getId();
		$area = $this->getCatalogArea();

		$sql = "SELECT id".
			" FROM adn_ed_objective".
			" WHERE catalog_area = ".$ilDB->quote($area, "integer").
			" AND nr = ".$ilDB->quote($this->getNumber(), "text");
			" AND archived < ".$ilDB->quote(1, "integer");

		if($id)
		{
			$sql .= " AND id <> ".$ilDB->quote($id, "integer");
		}

		$set = $ilDB->query($sql);
		return !(bool)$ilDB->numRows($set);
	}

	/**
	 * Build adn number
	 *
	 * @return string
	 */
	public function buildADNNumber()
	{
		if($this->getType() == self::TYPE_MC)
		{
			return $this->getCatalogArea()." ".str_pad($this->getNumber(), 2, "0", STR_PAD_LEFT);
		}
		else
		{
			return $this->getNumber();
		}
	}
}

?>