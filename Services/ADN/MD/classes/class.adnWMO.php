<?php
/* Copyright (c) 2010 Leifos, GPL, see docs/LICENSE */

/**
 * WMO application class
 *
 * Please mind: the code is handled by a db update step and entries are created by default
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.de>
 * @version $Id: class.adnWMO.php 34286 2012-04-18 15:09:37Z jluetzen $
 *
 * @ingroup ServicesADN
 */
class adnWMO extends adnDBBase
{
	protected $id; // [int]
	protected $name; // [string]
	protected $subtitle; // [string]
	protected $code; // [string]
	protected $postal_street; // [string]
	protected $postal_street_no; // [int]
	protected $postal_zip; // [string]
	protected $postal_city; // [string]
	protected $visitor_street; // [string]
	protected $visitor_street_no; // [string]
	protected $visitor_zip; // [string]
	protected $visitor_city; // [string]
	protected $bank_institution; // [string]
	protected $bank_code; // [string]
	protected $bank_account; // [string]
	protected $bank_iban; // [string]
	protected $bank_bic; // [string]
	protected $phone; // [string]
	protected $fax; // [string]
	protected $email; // [string]
	protected $notification_email; // [string]
	protected $url; // [string]
	protected $cost; // [array]

	const COST_CERTIFICATE = 1;
	const COST_DUPLICATE = 2;
	const COST_EXTENSION = 3;
	const COST_EXAM = 4;
    const COST_EXAM_GAS_CHEM = 5;

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
	 * Set country code
	 *
	 * @param string $a_code
	 */
	public function setCode($a_code)
	{
		$this->code = (string)$a_code;
	}

	/**
	 * Get country code
	 *
	 * @return string
	 */
	public function getCode()
	{
		return $this->code;
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
	 * Set subtitle
	 * @param string $a_subtitle
	 */
	public function setSubtitle($a_subtitle)
	{
		$this->subtitle = $a_subtitle;
	}
	
	/**
	 * get subtitle
	 * @return string
	 */
	public function getSubtitle()
	{
		return $this->subtitle;
	}

	/**
	 * Set phone
	 *
	 * @param string $a_phone
	 */
	public function setPhone($a_phone)
	{
		$this->phone = (string)$a_phone;
	}

	/**
	 * Get phone
	 *
	 * @return string
	 */
	public function getPhone()
	{
		return $this->phone;
	}

	/**
	 * Set fax
	 *
	 * @param string $a_fax
	 */
	public function setFax($a_fax)
	{
		$this->fax = (string)$a_fax;
	}

	/**
	 * Get fax
	 *
	 * @return string
	 */
	public function getFax()
	{
		return $this->fax;
	}

	/**
	 * Set email
	 *
	 * @param string $a_email
	 */
	public function setEmail($a_email)
	{
		$this->email = (string)$a_email;
	}

	/**
	 * Get email
	 *
	 * @return string
	 */
	public function getEmail()
	{
		return $this->email;
	}

	/**
	 * Set notification email
	 *
	 * @param string $a_email
	 */
	public function setNotificationEmail($a_email)
	{
		$this->notification_email = (string)$a_email;
	}

	/**
	 * Get notification email
	 *
	 * @return string
	 */
	public function getNotificationEmail()
	{
		return $this->notification_email;
	}

	/**
	 * Set URL
	 *
	 * @param string $a_url
	 */
	public function setURL($a_url)
	{
		$this->url = (string)$a_url;
	}

	/**
	 * Get URL
	 *
	 * @return string
	 */
	public function getURL()
	{
		return $this->url;
	}

	/**
	 * Set postal street
	 *
	 * @param string $a_postal_street
	 */
	public function setPostalStreet($a_postal_street)
	{
		$this->postal_street = (string)$a_postal_street;
	}

	/**
	 * Get postal street
	 *
	 * @return string
	 */
	public function getPostalStreet()
	{
		return $this->postal_street;
	}

	/**
	 * Set postal street number
	 *
	 * @param string $a_postal_street_no
	 */
	public function setPostalStreetNumber($a_postal_street_no)
	{
		$this->postal_street_no = (string)$a_postal_street_no;
	}

	/**
	 * Get postal street number
	 *
	 * @return string
	 */
	public function getPostalStreetNumber()
	{
		return $this->postal_street_no;
	}

	/**
	 * Set postal zip
	 *
	 * @param string $a_postal_zip
	 */
	public function setPostalZip($a_postal_zip)
	{
		$this->postal_zip = (string)$a_postal_zip;
	}

	/**
	 * Get postal zip
	 *
	 * @return string
	 */
	public function getPostalZip()
	{
		return $this->postal_zip;
	}

	/**
	 * Set postal city
	 *
	 * @param string $a_postal_city
	 */
	public function setPostalCity($a_postal_city)
	{
		$this->postal_city = (string)$a_postal_city;
	}

	/**
	 * Get postal city
	 *
	 * @return string
	 */
	public function getPostalCity()
	{
		return $this->postal_city;
	}

	/**
	 * Set visitor street
	 *
	 * @param string $a_visitor_street
	 */
	public function setVisitorStreet($a_visitor_street)
	{
		$this->visitor_street = (string)$a_visitor_street;
	}

	/**
	 * Get postal street
	 *
	 * @return string
	 */
	public function getVisitorStreet()
	{
		return $this->visitor_street;
	}

	/**
	 * Set visitor street number
	 *
	 * @param string $a_visitor_street_no
	 */
	public function setVisitorStreetNumber($a_visitor_street_no)
	{
		$this->visitor_street_no = (string)$a_visitor_street_no;
	}

	/**
	 * Get visitor street number
	 *
	 * @return string
	 */
	public function getVisitorStreetNumber()
	{
		return $this->visitor_street_no;
	}

	/**
	 * Set visitor zip
	 *
	 * @param string $a_visitor_zip
	 */
	public function setVisitorZip($a_visitor_zip)
	{
		$this->visitor_zip = (string)$a_visitor_zip;
	}

	/**
	 * Get visitor zip
	 *
	 * @return string
	 */
	public function getVisitorZip()
	{
		return $this->visitor_zip;
	}

	/**
	 * Set visitor city
	 *
	 * @param string $a_visitor_city
	 */
	public function setVisitorCity($a_visitor_city)
	{
		$this->visitor_city = (string)$a_visitor_city;
	}

	/**
	 * Get visitor city
	 *
	 * @return string
	 */
	public function getVisitorCity()
	{
		return $this->visitor_city;
	}

	/**
	 * Set bank institute
	 *
	 * @param string $a_bank_institute
	 */
	public function setBankInstitute($a_bank_institute)
	{
		$this->bank_institute = (string)$a_bank_institute;
	}

	/**
	 * Get bank institute
	 *
	 * @return string
	 */
	public function getBankInstitute()
	{
		return $this->bank_institute;
	}

	/**
	 * Set bank code
	 *
	 * @param int $a_bank_code
	 */
	public function setBankCode($a_bank_code)
	{
		$this->bank_code = (int)$a_bank_code;
	}

	/**
	 * Get bank code
	 *
	 * @return int
	 */
	public function getBankCode()
	{
		return $this->bank_code;
	}

	/**
	 * Set bank account
	 *
	 * @param int $a_bank_account
	 */
	public function setBankAccount($a_bank_account)
	{
		$this->bank_account = (int)$a_bank_account;
	}

	/**
	 * Get bank account
	 *
	 * @return int
	 */
	public function getBankAccount()
	{
		return $this->bank_account;
	}

	/**
	 * Set bank iban
	 *
	 * @param string $a_bank_iban
	 */
	public function setBankIBAN($a_bank_iban)
	{
		$this->bank_iban = (string)$a_bank_iban;
	}

	/**
	 * Get bank iban
	 *
	 * @return string
	 */
	public function getBankIBAN()
	{
		return $this->bank_iban;
	}

	/**
	 * Set bank bic
	 *
	 * @param string $a_bank_bic
	 */
	public function setBankBIC($a_bank_bic)
	{
		$this->bank_bic = (string)$a_bank_bic;
	}

	/**
	 * Get bank bic
	 *
	 * @return string
	 */
	public function getBankBIC()
	{
		return $this->bank_bic;
	}

	/**
	 * Set certificate cost
	 *
	 * @param int $a_no
	 * @param string $a_desc
	 * @param float $a_value
	 */
	public function setCostCertificate($a_no, $a_desc, $a_value)
	{
		$this->setCost(self::COST_CERTIFICATE, $a_no, $a_desc, $a_value);
	}

	/**
	 * get certificate cost
	 * @return array("no", "desc", "value");
	 */
	public function getCostCertificate()
	{
		return $this->getCost(self::COST_CERTIFICATE);
	}

	/**
	 * Set duplicate cost
	 *
	 * @param int $a_no
	 * @param string $a_desc
	 * @param float $a_value
	 */
	public function setCostDuplicate($a_no, $a_desc, $a_value)
	{
		$this->setCost(self::COST_DUPLICATE, $a_no, $a_desc, $a_value);
	}

	/**
	 * get duplicate cost
	 * @return array("no", "desc", "value");
	 */
	public function getCostDuplicate()
	{
		return $this->getCost(self::COST_DUPLICATE);
	}

	/**
	 * Set extension cost
	 *
	 * @param int $a_no
	 * @param string $a_desc
	 * @param float $a_value
	 */
	public function setCostExtension($a_no, $a_desc, $a_value)
	{
		$this->setCost(self::COST_EXTENSION, $a_no, $a_desc, $a_value);
	}

	/**
	 * get extension cost
	 * @return array("no", "desc", "value");
	 */
	public function getCostExtension()
	{
		return $this->getCost(self::COST_EXTENSION);
	}

	/**
	 * Set exam cost
	 *
	 * @param int $a_no
	 * @param string $a_desc
	 * @param float $a_value
	 */
	public function setCostExam($a_no, $a_desc, $a_value)
	{
		$this->setCost(self::COST_EXAM, $a_no, $a_desc, $a_value);
	}

	/**
	 * get exam cost
	 * @return array("no", "desc", "value");
	 */
	public function getCostExam()
	{
		return $this->getCost(self::COST_EXAM);
	}

    /**
     * Set exam cost for gas/chemistry
     *
     * @param int $a_no
     * @param string $a_desc
     * @param float $a_value
     */
    public function setCostExamGasChem($a_no, $a_desc, $a_value)
    {
        $this->setCost(self::COST_EXAM_GAS_CHEM, $a_no, $a_desc, $a_value);
    }

    /**
     * get exam cost for gas/chemistry
     * @return array("no", "desc", "value");
     */
    public function getCostExamGasChem()
    {
        return $this->getCost(self::COST_EXAM_GAS_CHEM);
    }

	/**
	 * Set cost
	 *
	 * @param int $a_type
	 * @param int $a_no
	 * @param string $a_desc
	 * @param float $a_value
	 */
	protected function setCost($a_type, $a_no, $a_desc, $a_value)
	{
		if($this->isValidCostType($a_type))
		{
			if(stristr($a_value, ","))
			{
				$a_value = str_replace(",", ".", $a_value);
			}
			$a_value = round((float)$a_value, 2);

			// do not force decimals
			// $a_value = number_format($a_value, 2, ".", "");

			$this->cost[$a_type] = array("no" => $a_no,
				"desc" => (string)$a_desc,
				"value" => $a_value);
		}
	}

	/**
	 * Get Cost
	 *
	 * @param int $a_type
	 * @return array
	 */
	public function getCost($a_type)
	{
		if(isset($this->cost[$a_type]))
		{
			return $this->cost[$a_type];
		}
	}

	/**
	 * Check if given cost type is valid
	 *
	 * @param int $a_type
	 * @return bool
	 */
	protected function isValidCostType($a_type)
	{
		$valid = array(self::COST_CERTIFICATE, self::COST_DUPLICATE, self::COST_EXTENSION,
			self::COST_EXAM,self::COST_EXAM_GAS_CHEM);
		if(in_array($a_type, $valid))
		{
			return true;
		}
		return false;
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

		$res = $ilDB->query("SELECT code_nr,name,subtitle,street,street_no,postal_code,city,visit_street,".
			"visit_street_no,visit_postal_code,visit_city,bank,bank_id,account_id,bic,iban,phone,".
			"fax,email,internet,cert_nr,cert_description,cert_cost,duplicate_nr,duplicate_description,".
			"duplicate_cost,ext_nr,ext_description,ext_cost,exam_nr,exam_description,exam_cost,notification_email,".
            "exam_gas_chem_nr,exam_gas_chem_description,exam_gas_chem_cost" .
			" FROM adn_md_wmo".
			" WHERE id = ".$ilDB->quote($id, "integer"));
		$set = $ilDB->fetchAssoc($res);
		
		$this->setCode($set["code_nr"]);
		$this->setName($set["name"]);
		$this->setSubtitle($set['subtitle']);
		$this->setPostalStreet($set["street"]);
		$this->setPostalStreetNumber($set["street_no"]);
		$this->setPostalZip($set["postal_code"]);
		$this->setPostalCity($set["city"]);
		$this->setVisitorStreet($set["visit_street"]);
		$this->setVisitorStreetNumber($set["visit_street_no"]);
		$this->setVisitorZip($set["visit_postal_code"]);
		$this->setVisitorCity($set["visit_city"]);
		$this->setBankInstitute($set["bank"]);
		$this->setBankCode($set["bank_id"]);
		$this->setBankAccount($set["account_id"]);
		$this->setBankBIC($set["bic"]);
		$this->setBankIBAN($set["iban"]);
		$this->setPhone($set["phone"]);
		$this->setFax($set["fax"]);
		$this->setEmail($set["email"]);
		$this->setNotificationEmail($set['notification_email']);
		$this->setURL($set["internet"]);

		$this->setCostCertificate($set["cert_nr"], $set["cert_description"], $set["cert_cost"]/100);
		$this->setCostDuplicate($set["duplicate_nr"], $set["duplicate_description"],
			$set["duplicate_cost"]/100);
		$this->setCostExtension($set["ext_nr"], $set["ext_description"], $set["ext_cost"]/100);
		$this->setCostExam($set["exam_nr"], $set["exam_description"], $set["exam_cost"]/100);
		$this->setCostExamGasChem($set["exam_gas_chem_nr"], $set["exam_gas_chem_description"], $set["exam_gas_chem_cost"]/100);

		parent::_read($id, "adn_md_wmo");
	}

	/**
	 * Convert properties to DB fields
	 *
	 * @return array
	 */
	protected function propertiesToFields()
	{
		$fields = array("code_nr" => array("text", $this->getCode()),
			"name" => array("text", $this->getName()),
			'subtitle' => array('text', $this->getSubtitle()), 
			"street" => array("text", $this->getPostalStreet()),
			"street_no" => array("text", $this->getPostalStreetNumber()),
			"postal_code" => array("text", $this->getPostalZip()),
			"city" => array("text", $this->getPostalCity()),
			"visit_street" => array("text", $this->getVisitorStreet()),
			"visit_street_no" => array("text", $this->getVisitorStreetNumber()),
			"visit_postal_code" => array("text", $this->getVisitorZip()),
			"visit_city" => array("text", $this->getVisitorCity()),
			"bank" => array("text", $this->getBankInstitute()),
			"bank_id" => array("integer", $this->getBankCode()),
			"account_id" => array("integer", $this->getBankAccount()),
			"bic" => array("text", $this->getBankBIC()),
			"iban" => array("text", $this->getBankIBAN()),
			"phone" => array("text", $this->getPhone()),
			"fax" => array("text", $this->getFax()),
			"email" => array("text", $this->getEmail()),
			'notification_email' => array('text', $this->getNotificationEmail()),
			"internet" => array("text", $this->getURL()));

		$costs = array(self::COST_CERTIFICATE => "cert",
			self::COST_DUPLICATE => "duplicate",
			self::COST_EXTENSION => "ext",
			self::COST_EXAM => "exam",
            self::COST_EXAM_GAS_CHEM => "exam_gas_chem");
		foreach($costs as $type => $id)
		{
			$cost = $this->getCost($type);
			$fields[$id."_nr"] = array("text", $cost["no"]);
			$fields[$id."_description"] = array("text", $cost["desc"]);
			$fields[$id."_cost"] = array("integer", $cost["value"]*100);
		}

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
		$this->setId($ilDB->nextId("adn_md_wmo"));
		$id = $this->getId();

		$fields = $this->propertiesToFields();
		$fields["id"] = array("integer", $id);
		
		$ilDB->insert("adn_md_wmo", $fields);

		parent::_save($id, "adn_md_wmo");

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

		$ilDB->update("adn_md_wmo", $fields, array("id"=>array("integer", $id)));

		parent::_update($id, "adn_md_wmo");

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
		if($id)
		{
			// co-chairs
			include_once "Services/ADN/MD/classes/class.adnCoChair.php";
			$all = adnCoChair::getAllCoChairs($id);
			if(sizeof($all))
			{				
				foreach($all as $item)
				{
					$cchair = new adnCoChair($item["id"]);
					$cchair->delete();
				}				
			}		
			
			// exam facilities
			include_once "Services/ADN/MD/classes/class.adnExamFacility.php";
			$all = adnExamFacility::getAllExamFacilities($id);
			if(sizeof($all))
			{				
				foreach($all as $item)
				{
					$facility = new adnExamFacility($item["id"]);
					$facility->delete();
				}				
			}										
			
			// U.SD.1.4: always set "archived" flag
			$this->setArchived(true);
			return $this->update();
		}
	}

	/**
	 * Get all countries
	 *
	 * @param bool $a_with_archived
	 * @return array
	 */
	public static function getAllWMOs($a_with_archived = false)
	{
		global $ilDB;

		$sql = "SELECT id,code_nr,name,subtitle".
			" FROM adn_md_wmo";
		if(!$a_with_archived)
		{
			$sql .= " WHERE archived < ".$ilDB->quote(1, "integer");
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
	 * Get country ids and names
	 *
	 * @param array $a_old_values
	 * @return array (id => caption)
	 */
	public static function getWMOsSelect(array $a_old_values = null)
	{
		global $ilDB;

		$sql = "SELECT id,name,archived".
			" FROM adn_md_wmo";
		if(!$a_old_values)
		{
			$sql .= " WHERE archived < ".$ilDB->quote(1, "integer");
		}
		else
		{
			$sql .= " WHERE (archived < ".$ilDB->quote(1, "integer").
				" OR ".$ilDB->in("id", $a_old_values, false, "integer").")";
		}
		
		$res = $ilDB->query($sql);
		$all = array();
		while($row = $ilDB->fetchAssoc($res))
		{
			$all[$row["id"]] = self::handleName($row["name"], $row["archived"]);
		}
		return $all;
	}

	/**
	 * Lookup property
	 *
	 * @param integer $a_id wmo id
	 * @param string $a_prop property
	 * @return mixed property value
	 */
	protected static function lookupProperty($a_id, $a_prop)
	{
		global $ilDB;

		$set = $ilDB->query("SELECT ".$a_prop.
			" FROM adn_md_wmo".
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
		$name = self::lookupProperty($a_id, "name");
		$archived = self::lookupProperty($a_id, "archived");
		return self::handleName($name, $archived);
	}

	/**
	 * Lookup code
	 *
	 * @param int $a_id
	 * @return string
	 */
	public static function lookupCode($a_id)
	{
		return self::lookupProperty($a_id, "code_nr");
	}

	/**
	 * Lookup id for code
	 *
	 * @param string $a_id code
	 * @return int ID
	 */
	public static function lookupIdForCode($a_code)
	{
		global $ilDB;

		$set = $ilDB->query("SELECT id".
			" FROM adn_md_wmo".
			" WHERE code_nr = ".$ilDB->quote($a_code, "text").
			" AND archived < ".$ilDB->quote(1, "integer"));
		$rec = $ilDB->fetchAssoc($set);
		return (int) $rec["id"];
	}
}

?>