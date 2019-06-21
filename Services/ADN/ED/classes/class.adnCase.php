<?php
/* Copyright (c) 2010 Leifos, GPL, see docs/LICENSE */

/**
 * Case application class
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.de>
 * @version $Id: class.adnCase.php 27867 2011-02-25 09:35:47Z akill $
 *
 * @ingroup ServicesADN
 */
class adnCase extends adnDBBase
{
	protected $id; // [int]
	protected $subject_area; // [string]
	protected $butan; // [bool]
	protected $text; // [string]

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
	 * Set subject area
	 *
	 * @param string $a_area
	 */
	public function setArea($a_area)
	{
		$this->subject_area = (string)$a_area;
	}

	/**
	 * Get subject area
	 *
	 * @return string
	 */
	public function getArea()
	{
		return $this->subject_area;
	}

	/**
	 * Set butan
	 *
	 * @param bool $a_value
	 */
	public function setButan($a_value)
	{
		$this->butan = (bool)$a_value;
	}

	/**
	 * Get butan
	 *
	 * @return bool
	 */
	public function getButan()
	{
		return $this->butan;
	}

	/**
	 * Set text
	 *
	 * @param string $a_text
	 */
	public function setText($a_text)
	{
		$this->text = (string)$a_text;
	}

	/**
	 * Get text
	 *
	 * @return string
	 */
	public function getText()
	{
		return $this->text;
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

		$res = $ilDB->query("SELECT subject_area,butan,text".
			" FROM adn_ed_case".
			" WHERE id = ".$ilDB->quote($this->getId(), "integer"));
		$set = $ilDB->fetchAssoc($res);
		$this->setArea($set["subject_area"]);
		$this->setButan($set["butan"]);
		$this->setText($set["text"]);

		parent::read($id, "adn_ed_case");
	}

	/**
	 * Convert properties to DB fields
	 *
	 * @return array (subject_area, butan, text)
	 */
	protected function propertiesToFields()
	{
		$fields = array("subject_area" => array("text", $this->getArea()),
			"butan" => array("integer", $this->getButan()),
			"text" => array("text", $this->getText()));
			
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
		$this->setId($ilDB->nextId("adn_ed_case"));
		$id = $this->getId();

		$fields = $this->propertiesToFields();
		$fields["id"] = array("integer", $id);
			
		$ilDB->insert("adn_ed_case", $fields);

		parent::save($id, "adn_ed_case");
		
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
		
		$ilDB->update("adn_ed_case", $fields, array("id"=>array("integer", $id)));

		parent::update($id, "adn_ed_case");

		return true;
	}

	/**
	 * Get case id by subject area
	 *
	 * @param string $a_area
	 * @param int $a_butan
	 * @return int
	 */
	public static function getIdByArea($a_area, $a_butan = false)
	{
		global $ilDB;
		
		$res = $ilDB->query("SELECT id".
			" FROM adn_ed_case".
			" WHERE subject_area = ".$ilDB->quote($a_area, "text").
			" AND butan = ".$ilDB->quote($a_butan, "integer"));
		$row = $ilDB->fetchAssoc($res);
		return $row["id"];
	}

	/**
	 * Translate placeholders for given answer sheet
	 *
	 * @param adnAnswerSheet $a_sheet
	 * @param adnExaminationEvent $a_event
	 * @return string
	 */
	public function getTranslatedText(adnAnswerSheet $a_sheet, adnExaminationEvent $a_event = null)
	{
		global $lng;
		
		$text = $this->getText();

		if(!$a_event)
		{
			include_once "Services/ADN/EP/classes/class.adnExaminationEvent.php";
			include_once "Services/ADN/ED/classes/class.adnSubjectArea.php";
			$a_event = new adnExaminationEvent($a_sheet->getEvent());
		}

		// gas
		if($a_event->getType() == adnSubjectArea::GAS)
		{
			$good_id = $a_sheet->getNewGood();
			if($good_id)
			{
				include_once "Services/ADN/ED/classes/class.adnGoodInTransit.php";
				$good = new adnGoodInTransit($good_id);

				// german only for now
				$text = str_replace("[UN-Nr]", "UN ".$good->getNumber(), $text);
				$text = str_replace("[Bezeichnung]", $good->getName(), $text);
			}
		}
		// chemicals
		else
		{
			$goods = array(1 => $a_sheet->getPreviousGood(),
				2 => $a_sheet->getNewGood());

			foreach($goods as $idx => $good_id)
			{
				if($good_id)
				{
					include_once "Services/ADN/ED/classes/class.adnGoodInTransit.php";
					$good = new adnGoodInTransit($good_id);

					// german only for now
					$text = str_replace("[UN-Nr".$idx."]", "UN ".$good->getNumber(), $text);
					$text = str_replace("[Bezeichnung".$idx."]", $good->getName(), $text);
					$text = str_replace("[Klasse".$idx."]", $good->getClass(), $text);
					$text = str_replace("[Klassifizierungscode".$idx."]", $good->getClassCode(), $text);
					$text = str_replace("[Verpackungsgruppe".$idx."]", $good->getPackingGroup(), $text);
				}
				// no previous good
				else
				{
					// german only for now
					$text = str_replace("[UN-Nr".$idx."]", "", $text);
					$text = str_replace("[Bezeichnung".$idx."]", $lng->txt("adn_no_previous_good"), $text);
					$text = str_replace("[Klasse".$idx."]", "", $text);
					$text = str_replace("[Klassifizierungscode".$idx."]", "", $text);
					$text = str_replace("[Verpackungsgruppe".$idx."]", "", $text);
				}
			}
		}

		return $text;
	}
}

?>