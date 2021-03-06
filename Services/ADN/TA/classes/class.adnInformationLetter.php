<?php
/* Copyright (c) 2010 Leifos, GPL, see docs/LICENSE */

/**
 * Information letter application class
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.de>
 * @version $Id: class.adnInformationLetter.php 27891 2011-02-28 11:46:53Z jluetzen $
 *
 * @ingroup ServicesADN
 */
class adnInformationLetter extends adnDBBase
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

		$this->setFileDirectory("ta_information");

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

		$res = $ilDB->query("SELECT name,ifile".
			" FROM adn_ta_information".
			" WHERE id = ".$ilDB->quote($this->getId(), "integer"));
		$set = $ilDB->fetchAssoc($res);
		$this->setName($set["name"]);
		$this->setFileName($set["ifile"]);

		parent::read($id, "adn_ta_information");
	}

	/**
	 * Convert properties to DB fields
	 *
	 * @return array (name, ifile)
	 */
	protected function propertiesToFields()
	{
		$fields = array("name" => array("text", $this->getName()),
			"ifile" =>  array("text", $this->getFileName()));
			
		return $fields;
	}

	/**
	 * Create new db entry
	 *
	 * @return int
	 */
	public function save()
	{
		global $ilDB;

		$this->setId($ilDB->nextId("adn_ta_information"));
		$id = $this->getId();

		$this->saveFile($this->getUploadedFile(), $id);

		$fields = $this->propertiesToFields();
		$fields["id"] = array("integer", $id);
			
		$ilDB->insert("adn_ta_information", $fields);

		parent::save($id, "adn_ta_information");
		
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
		
		$ilDB->update("adn_ta_information", $fields, array("id"=>array("integer", $id)));

		parent::update($id, "adn_ta_information");

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
			$this->removeFile($id);

			// U.SV.6.4: archived flag is not used here!
			$ilDB->manipulate("DELETE FROM adn_ta_information".
				" WHERE id = ".$ilDB->quote($id, "integer"));
			$this->setId(null);
			return true;
		}
	}

	/**
	 * Get all letters
	 *
	 * @return array
	 */
	public static function getAllInformationLetters()
	{
		global $ilDB;

		$sql = "SELECT id,name".
			" FROM adn_ta_information";
	
		$res = $ilDB->query($sql);
		$all = array();
		while($row = $ilDB->fetchAssoc($res))
		{
			$all[] = $row;
		}

		return $all;
	}

	/**
	 * Get letter ids and names
	 *
	 * @return array (id => caption)
	 */
	public static function getInformationLettersSelect()
	{
		global $ilDB;

		$sql = "SELECT id,name".
			" FROM adn_ta_information";
		
		$res = $ilDB->query($sql);
		$all = array();
		while($row = $ilDB->fetchAssoc($res))
		{
			$all[$row["id"]] = $row["name"];
		}

		return $all;
	}

	/**
	 * Lookup property
	 *
	 * @param	integer	$a_id	letter id
	 * @param	string	$a_prop	property
	 *
	 * @return	mixed	property value
	 */
	protected static function lookupProperty($a_id, $a_prop)
	{
		global $ilDB;

		$set = $ilDB->query("SELECT ".$a_prop.
			" FROM adn_ta_information".
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
}

?>