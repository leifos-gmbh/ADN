<?php
/* Copyright (c) 2010 Leifos, GPL, see docs/LICENSE */

/**
 * Good related answer application class
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.de>
 * @version $Id: class.adnGoodRelatedAnswer.php 27883 2011-02-27 19:30:41Z akill $
 *
 * @ingroup ServicesADN
 */
class adnGoodRelatedAnswer extends adnDBBase
{
	protected $id; // [int]
	protected $question_id; // [int]
	protected $answer; // [string]
	protected $butan_or_empty; // [int]
	protected $goods; // [array]

	const TYPE_EMPTY = 1;
	const TYPE_BUTAN = 2;
	const TYPE_BUTAN_OR_EMPTY = 3;

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
	 * Set question id
	 *
	 * @param int $a_id
	 */
	public function setQuestionId($a_id)
	{
		$this->question_id = (int)$a_id;
	}

	/**
	 * Get question id
	 *
	 * @return int
	 */
	public function getQuestionId()
	{
		return $this->question_id;
	}

	/**
	 * Set answer
	 *
	 * @param string $a_answer
	 */
	public function setAnswer($a_answer)
	{
		$this->answer = (string)$a_answer;
	}

	/**
	 * Get answer
	 *
	 * @return string
	 */
	public function getAnswer()
	{
		return $this->answer;
	}

	/**
	 * Set butan or empty
	 *
	 * @param int $a_empty
	 */
	public function setButanOrEmpty($a_empty)
	{
		if($this->isValidType($a_empty))
		{
			$this->butan_or_empty = (int)$a_empty;
		}
	}

	/**
	 * Get butan or empty
	 *
	 * @return int
	 */
	public function GetButanOrEmpty()
	{
		return $this->butan_or_empty;
	}

	/**
	 * Is given type valid?
	 *
	 * @return bool
	 */
	protected function IsValidType($a_type)
	{
		if(in_array((int)$a_type, array(self::TYPE_EMPTY, self::TYPE_BUTAN,
			self::TYPE_BUTAN_OR_EMPTY)))
		{
			return true;
		}
		return false;
	}

	/**
	 * Set goods
	 *
	 * @param array $a_goods
	 */
	public function setGoods($a_goods)
	{
		$this->goods = $a_goods;
	}

	/**
	 * Get goods
	 *
	 * @return array
	 */
	public function getGoods()
	{
		return $this->goods;
	}

	/**
	 * Is answer active for given good?
	 *
	 * @param int $a_id
	 * @return bool
	 */
	public function hasGood($a_id)
	{
		if(is_array($this->goods) && in_array($a_id, $this->goods))
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

		$res = $ilDB->query("SELECT ed_question_id,answer,butan_or_empty".
			" FROM adn_ed_good_rel_answer".
			" WHERE id = ".$ilDB->quote($this->getId(), "integer"));
		$set = $ilDB->fetchAssoc($res);
		$this->setAnswer($set["answer"]);
		$this->setButanOrEmpty($set["butan_or_empty"]);
		$this->setQuestionId($set["ed_question_id"]);

		// add goods from sub-table
		$res = $ilDB->query("SELECT ed_good_id".
			" FROM adn_ed_case_answ_good".
			" WHERE ed_good_related_answer_id = ".$ilDB->quote($this->getId(), "integer"));
		$goods = array();
		while($row = $ilDB->fetchAssoc($res))
		{
			$goods[] = $row["ed_good_id"];
		}
		$this->setGoods($goods);

		parent::read($id, "adn_ed_good_rel_answer");
	}

	/**
	 * Convert properties to DB fields
	 *
	 * @return array (answer, butan_or_empty, ed_question_id)
	 */
	protected function propertiesToFields()
	{
		$fields = array("answer" => array("text", $this->getAnswer()),
			"butan_or_empty" => array("integer", (int)$this->getButanOrEmpty()),
			"ed_question_id" => array("integer", $this->getQuestionId()));
			
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

		$this->setId($ilDB->nextId("adn_ed_good_rel_answer"));
		$id = $this->getId();

		$fields = $this->propertiesToFields();
		$fields["id"] = array("integer", $id);
			
		$ilDB->insert("adn_ed_good_rel_answer", $fields);

		$this->saveGoods();

		parent::save($id, "adn_ed_good_rel_answer");
		
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
		
		$ilDB->update("adn_ed_good_rel_answer", $fields, array("id"=>array("integer", $id)));

		$this->saveGoods();

		parent::update($id, "adn_ed_good_rel_answer");

		return true;
	}

	/**
	 * Save goods
	 */
	protected function saveGoods()
	{
		global $ilDB;

		$id = $this->getId();
		if($id)
		{
			$ilDB->manipulate("DELETE FROM adn_ed_case_answ_good".
				" WHERE ed_good_related_answer_id = ".$ilDB->quote($id, "integer"));

			if(sizeof($this->goods))
			{
				foreach($this->goods as $good_id)
				{
					$fields = array("ed_good_related_answer_id" => array("integer", $id),
						"ed_good_id" => array("integer", $good_id));

					$ilDB->insert("adn_ed_case_answ_good", $fields);
				}
			}
		}
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
			// U.PV.5.10: archived flag is not used here!
			$ilDB->manipulate("DELETE FROM adn_ed_case_answ_good".
				" WHERE ed_good_related_answer_id = ".$ilDB->quote($id, "integer"));
			$ilDB->manipulate("DELETE FROM adn_ed_good_rel_answer".
				" WHERE id = ".$ilDB->quote($id, "integer"));
			$this->setId(null);
			return true;
		}
	}

	/**
	 * Get all answers
	 *
	 * @param int $a_question_id
	 * @return array
	 */
	public static function getAllAnswers($a_question_id)
	{
		global $ilDB;

		$sql = "SELECT id,answer,butan_or_empty".
			" FROM adn_ed_good_rel_answer".
			" WHERE ed_question_id = ".$ilDB->quote($a_question_id, "integer");
		
		$res = $ilDB->query($sql);
		$all = array();
		$ids = array();
		while($row = $ilDB->fetchAssoc($res))
		{
			$all[$row["id"]] = $row;
			$ids[] = $row["id"];
		}

		// add goods to result
		if(sizeof($all))
		{
			$res = $ilDB->query("SELECT ed_good_id,ed_good_related_answer_id".
				" FROM adn_ed_case_answ_good".
				" WHERE ".$ilDB->in("ed_good_related_answer_id", $ids, false, "integer"));
			$goods = array();
			while($row = $ilDB->fetchAssoc($res))
			{
				$all[$row["ed_good_related_answer_id"]]["goods"][] = $row["ed_good_id"];
			}
		}

		return $all;
	}

	/**
	 * Lookup property
	 *
	 * @param integer $a_id answer id
	 * @param string $a_prop property
	 * @return mixed property value
	 */
	protected static function lookupProperty($a_id, $a_prop)
	{
		global $ilDB;

		$set = $ilDB->query("SELECT ".$a_prop.
			" FROM adn_ed_good_rel_answer".
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
		return self::lookupProperty($a_id, "answer");
	}

	/**
	 * Check if good is used by any answer
	 *
	 * @param int $a_id
	 * @return bool
	 */
	public static function findByGood($a_id)
	{
		global $ilDB;

		$res = $ilDB->query("SELECT ed_good_related_answer_id".
			" FROM adn_ed_case_answ_good".
			" WHERE ed_good_id = ".$ilDB->quote((int)$a_id, "integer"));
		return (bool)$ilDB->numRows($res);
	}


	/**
	 * Get correct answer for given sheet
	 *
	 * @param adnAnswerSheet $a_sheet
	 * @param adnCaseQuestion $a_question
	 * @param adnExaminationEvent $a_event 
	 * @return string
	 */
	public static function getAnswerForSheet(adnAnswerSheet $a_sheet, adnCaseQuestion $a_question,
		adnExaminationEvent $a_event = null)
	{
		if(!$a_event)
		{
			include_once "Services/ADN/EP/classes/class.adnExaminationEvent.php";
			$a_event = new adnExaminationEvent($a_sheet->getEvent());
		}

		// butan or empty only relevant in gas exams
		include_once "Services/ADN/ED/classes/class.adnSubjectArea.php";
		$butan = null;
		if($a_event->getType() == adnSubjectArea::GAS)
		{
			$butan = $a_sheet->getButan();
		}

		$answers = adnGoodRelatedAnswer::getAllAnswers($a_question->getId());
		foreach($answers as $answer_id => $answer_data)
		{
			// match butan status to butan-or-empty property
			if($butan === null ||
				$answer_data["butan_or_empty"] == adnGoodRelatedAnswer::TYPE_BUTAN_OR_EMPTY ||
				($butan === true &&
					$answer_data["butan_or_empty"] == adnGoodRelatedAnswer::TYPE_BUTAN) ||
				($butan === false &&
					$answer_data["butan_or_empty"] == adnGoodRelatedAnswer::TYPE_EMPTY))
			{
				if(in_array($a_sheet->getNewGood(), $answer_data["goods"]))
				{
					$correct_answer = $answer_data["answer"];
					break;
				}
			}
		}
		if(!$correct_answer)
		{
			$correct_answer = $a_question->getDefaultAnswer();
		}

		return $correct_answer;
	}
}

?>