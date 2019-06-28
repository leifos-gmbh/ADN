<?php
/* Copyright (c) 2010 Leifos, GPL, see docs/LICENSE */

/**
 * Area of expertise application class
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.de>
 * @version $Id: class.adnAreaOfExpertise.php 27891 2011-02-28 11:46:53Z jluetzen $
 *
 * @ingroup ServicesADN
 */
class adnAreaOfExpertise extends adnDBBase
{
	protected $id; // [int]
	protected $name; // [string]

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

		$res = $ilDB->query("SELECT title".
			" FROM adn_ta_expertise".
			" WHERE id = ".$ilDB->quote($this->getId(), "integer"));
		$set = $ilDB->fetchAssoc($res);
		$this->setName($set["title"]);

		parent::_read($id, "adn_ta_expertise");
	}

	/**
	 * Convert properties to DB fields
	 *
	 * @return array (title)
	 */
	protected function propertiesToFields()
	{
		$fields = array("title" => array("text", $this->getName()));
			
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
		$this->setId($ilDB->nextId("adn_ta_expertise"));
		$id = $this->getId();

		$fields = $this->propertiesToFields();
		$fields["id"] = array("integer", $id);
			
		$ilDB->insert("adn_ta_expertise", $fields);

		parent::_save($id, "adn_ta_expertise");
		
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
		
		$ilDB->update("adn_ta_expertise", $fields, array("id"=>array("integer", $id)));

		parent::_update($id, "adn_ta_expertise");

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
			// U.SV.7.4: archived flag is not used here!
			$ilDB->manipulate("DELETE FROM adn_ta_instructor_exp".
				" WHERE ta_expertise_id = ".$ilDB->quote($id, "integer"));
			$ilDB->manipulate("DELETE FROM adn_ta_expertise".
				" WHERE id = ".$ilDB->quote($id, "integer"));
			$this->setId(null);
			return true;
		}
	}

	/**
	 * Get all areas
	 *
	 * @return array
	 */
	public static function getAllAreasOfExpertise()
	{
		global $ilDB;

		$sql = "SELECT id,title AS name".
			" FROM adn_ta_expertise";
		
		$res = $ilDB->query($sql);
		$all = array();
		while($row = $ilDB->fetchAssoc($res))
		{
			$all[] = $row;
		}

		return $all;
	}

	/**
	 * Get area ids and names
	 *
	 * @return array (id => caption)
	 */
	public static function getAreasOfExpertiseSelect()
	{
		global $ilDB;

		$sql = "SELECT id,title".
			" FROM adn_ta_expertise".
			" ORDER BY title";
		
		$res = $ilDB->query($sql);
		$all = array();
		while($row = $ilDB->fetchAssoc($res))
		{
			$all[$row["id"]] = $row["title"];
		}

		return $all;
	}

	/**
	 * Lookup property
	 *
	 * @param integer $a_id expertise id
	 * @param string $a_prop property
	 * @return	mixed property value
	 */
	protected static function lookupProperty($a_id, $a_prop)
	{
		global $ilDB;

		$set = $ilDB->query("SELECT ".$a_prop.
			" FROM adn_ta_expertise".
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
}

?>