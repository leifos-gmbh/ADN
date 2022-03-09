<?php
/* Copyright (c) 2010 Leifos, GPL, see docs/LICENSE */

/**
 * Answer sheet application class
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.de>
 * @version $Id: class.adnAnswerSheet.php 36170 2012-08-13 10:51:52Z jluetzen $
 *
 * @ingroup ServicesADN
 */
class adnAnswerSheet extends adnDBBase
{
	protected $id; // [int]
	protected $event_id; // [int]
	protected $type; // [string]
	protected $nr; // [int]
	protected $butan; // [bool]
	protected $license; // [int]
	protected $previous_good; // [int]
	protected $new_good; // [int]
	protected $generated_on; // [ilDate]
	protected $questions; // [array]
	protected $question_map; // [array]

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
	 * Set event id
	 *
	 * @param int $a_id
	 */
	public function setEvent($a_id)
	{
		$this->event_id = (int)$a_id;
	}

	/**
	 * Get event id
	 *
	 * @return int
	 */
	public function getEvent()
	{
		return $this->event_id;
	}
	
	/**
	 * Set type
	 *
	 * @param int $a_value
	 */
	public function setType($a_value)
	{
		if($this->isValidType($a_value))
		{
			$this->type = (int)$a_value;
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
	 * Is given type valid?
	 *
	 * @param int $a_value
	 * @return bool
	 */
	public function isValidType($a_value)
	{
		if(in_array((int)$a_value, array(self::TYPE_MC, self::TYPE_CASE)))
		{
			return true;
		}
		return false;
	}

	/**
	 * Set number
	 *
	 * @param int $a_value
	 */
	protected function setNumber($a_value)
	{
		$this->number = (int)$a_value;
	}

	/**
	 * Get number
	 *
	 * @return int
	 */
	public function getNumber()
	{
		return $this->number;
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
	 * Set license
	 *
	 * @param int $a_value
	 */
	public function setLicense($a_value)
	{
		if(!$a_value)
		{
			$this->license = null;
		}
		else
		{
			$this->license = (int)$a_value;
		}
	}

	/**
	 * Get license
	 *
	 * @return int
	 */
	public function getLicense()
	{
		return $this->license;
	}

	/**
	 * Set previous good
	 *
	 * @param int $a_value
	 */
	public function setPreviousGood($a_value)
	{
		if(!$a_value)
		{
			$this->previous_good = null;
		}
		else
		{
			$this->previous_good = (int)$a_value;
		}
	}

	/**
	 * Get previous good
	 *
	 * @return int
	 */
	public function getPreviousGood()
	{
		return $this->previous_good;
	}

	/**
	 * Set new good
	 *
	 * @param int $a_value
	 */
	public function setNewGood($a_value)
	{
		if(!$a_value)
		{
			$this->new_good = null;
		}
		else
		{
			$this->new_good = (int)$a_value;
		}
	}

	/**
	 * Get new good
	 *
	 * @return int
	 */
	public function getNewGood()
	{
		return $this->new_good;
	}

	/**
	 * Set generated on
	 *
	 * @param ilDate $a_value
	 */
	public function setGeneratedOn(ilDate $a_value)
	{
		$this->generated_on = $a_value;
	}

	/**
	 * Get generated on
	 *
	 * @return ilDate
	 */
	public function getGeneratedOn()
	{
		return $this->generated_on;
	}
	
	/**
	 * Set questions
	 *
	 * @param array $a_questions
	 */
	public function setQuestions(array $a_questions)
	{
		$this->questions = $a_questions;
	}

	/**
	 * Get questions
	 *
	 * @return array
	 */
	public function getQuestions()
	{
		return $this->questions;
	}

	/**
	 * Set question to objective map
	 *
	 * @param array $a_questions
	 */
	public function setQuestionMap(array $a_questions)
	{
		$this->question_map = $a_questions;
	}

	/**
	 * Get question to objective map
	 *
	 * @return array
	 */
	public function getQuestionMap()
	{
		return $this->question_map;
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

		$res = $ilDB->query("SELECT ep_exam_event_id,nr,type,butan,ed_license_id,prev_ed_good_id,".
			"new_ed_good_id,generated_on".
			" FROM adn_ep_answer_sheet".
			" WHERE id = ".$ilDB->quote($this->getId(), "integer"));
		$set = $ilDB->fetchAssoc($res);
		$this->setEvent($set["ep_exam_event_id"]);
		$this->setNumber($set["nr"]);
		$this->setType($set["type"]);
		$this->setButan($set["butan"]);
		$this->setLicense($set["ed_license_id"]);
		$this->setPreviousGood($set["prev_ed_good_id"]);
		$this->setNewGood($set["new_ed_good_id"]);
		$this->setGeneratedOn(new ilDate($set["generated_on"], IL_CAL_DATE, ilTimeZone::UTC));
		
		parent::_read($id, "adn_ep_answer_sheet");

		// get questions
		$res = $ilDB->query("SELECT ed_question_id,ed_objective_id".
			" FROM adn_ep_sheet_question".
			" WHERE ep_answer_sheet_id = ".$ilDB->quote($id, "integer").
			// #2605 - question order should be determined 
			" ORDER BY ed_question_id");
		$questions = $map = array();
		while($row = $ilDB->fetchAssoc($res))
		{
			$questions[] = $row["ed_question_id"];

			if($row["ed_objective_id"])
			{
				$map[$row["ed_question_id"]] = $row["ed_objective_id"];
			}
		}
		$this->setQuestions($questions);
		$this->setQuestionMap($map);
	}

	/**
	 * Convert properties to DB fields
	 *
	 * @return array (ep_exam_event_id, type, butan, ed_license_id, prev_ed_good_id, new_ed_good_id)
	 */
	protected function propertiesToFields()
	{
		$fields = array("ep_exam_event_id" => array("integer", $this->getEvent()),
			// "nr" => array("integer", $this->getNumber()),
			"type" => array("text", $this->getType()),
			"butan" => array("integer", $this->getButan()),
			"ed_license_id" => array("integer", $this->getLicense()),
			"prev_ed_good_id" => array("integer", $this->getPreviousGood()),
			"new_ed_good_id" => array("integer", $this->getNewGood())
			);

		$date = $this->getGeneratedOn();
		if($date && !$date->isNull())
		{
			$fields["generated_on"] = array("timestamp", $date->get(IL_CAL_DATE, "", ilTimeZone::UTC));
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
		$this->setId($ilDB->nextId("adn_ep_answer_sheet"));
		$id = $this->getId();

		$fields = $this->propertiesToFields();
		$fields["id"] = array("integer", $id);

		$fields["nr"] = array("integer", $this->getNextNumber());

		$ilDB->insert("adn_ep_answer_sheet", $fields);

		parent::_save($id, "adn_ep_answer_sheet");

		$this->saveQuestions();
		
		return $id;
	}

	/**
	 * Internal answer sheet sequence for each event
	 *
	 * @return int
	 */
	protected function getNextNumber()
	{
		global $ilDB;

		$event = $this->getEvent();
		if($event)
		{
			$set = $ilDB->query("SELECT MAX(nr) AS nr".
				" FROM adn_ep_answer_sheet".
				" WHERE ep_exam_event_id = ".$ilDB->quote($this->getEvent(), "integer"));
			$row = $ilDB->fetchAssoc($set);
			return $row["nr"]+1;
		}
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

		$ilDB->update("adn_ep_answer_sheet", $fields, array("id"=>array("integer", $id)));

		parent::_update($id, "adn_ep_answer_sheet");

		$this->saveQuestions();

		return true;
	}

	/**
	 * Save assigned questions (in sub-table)
	 */
	protected function saveQuestions()
	{
		global $ilDB;

		$id = $this->getId();
		if($id)
		{
			$ilDB->manipulate("DELETE FROM adn_ep_sheet_question".
				" WHERE ep_answer_sheet_id = ".$ilDB->quote($id, "integer"));

			if(!empty($this->questions))
			{
				$map = $this->getQuestionMap();
				foreach($this->questions as $question_id)
				{
					// add objective mapping data
					$obj = null;
					if(isset($map[$question_id]))
					{
						$obj = $map[$question_id];
					}

					$fields = array("ep_answer_sheet_id" => array("integer", $id),
						"ed_question_id" => array("integer", $question_id),
						"ed_objective_id" => array("integer", $obj));

					$ilDB->insert("adn_ep_sheet_question", $fields);
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
			// U.PVB.7.6: archived flag is not used here!
			$ilDB->manipulate("DELETE FROM adn_ep_cand_sheet".
				" WHERE ep_answer_sheet_id = ".$ilDB->quote($id, "integer"));
			$ilDB->manipulate("DELETE FROM adn_ep_sheet_question".
				" WHERE ep_answer_sheet_id = ".$ilDB->quote($id, "integer"));
			$ilDB->manipulate("DELETE FROM adn_ep_answer_sheet".
				" WHERE id = ".$ilDB->quote($id, "integer"));
			$this->setId(null);
			return true;
		}
	}

	/**
	 * Get all sheets (for event)
	 *
	 * @param int $a_event_id
	 * @return array
	 */
	public static function getAllSheets($a_event_id)
	{
		global $ilDB;

		$sql = "SELECT id,nr,type,generated_on".
			" FROM adn_ep_answer_sheet".
			" WHERE ep_exam_event_id = ".$ilDB->quote($a_event_id, "integer");
		
		$res = $ilDB->query($sql);
		$all = array();
		while($row = $ilDB->fetchAssoc($res))
		{
			if($row["generated_on"])
			{
				$row["generated_on"] = new ilDate($row["generated_on"], IL_CAL_DATE, ilTimeZone::UTC);
			}
			$all[] = $row;
		}

		return $all;
	}

	/**
	 * Get sheet ids and captions (for event)
	 *
	 * @param int $a_event_id
	 * @param bool $a_divide_by_type
	 * @return array (id => caption // type|id => caption)
	 */
	public static function getSheetsSelect($a_event_id, $a_divide_by_type = false)
	{
		global $ilDB, $lng;

		$sql = "SELECT id,nr,type".
			" FROM adn_ep_answer_sheet".
			" WHERE ep_exam_event_id = ".$ilDB->quote($a_event_id, "integer");

		$res = $ilDB->query($sql);
		$all = array();
		while($row = $ilDB->fetchAssoc($res))
		{
			$caption = $row["nr"]." (";

			if($row["type"] == self::TYPE_MC)
			{
				$caption .= $lng->txt("adn_type_mc").")";
			}
			else
			{
				$caption .= $lng->txt("adn_type_case").")";
			}

			if(!$a_divide_by_type)
			{
				$all[$row["id"]] = $caption;
			}
			else
			{
				$all[$row["type"]][$row["id"]] = $caption;
			}
		}
		
		return $all;
	}

	/**
	 * Lookup property
	 *
	 * @param integer $a_id letter id
	 * @param string $a_prop property
	 * @return mixed property value
	 */
	protected static function lookupProperty($a_id, $a_prop)
	{
		global $ilDB;

		$set = $ilDB->query("SELECT ".$a_prop.
			" FROM adn_ep_answer_sheet".
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
		return self::lookupProperty($a_id, "nr");
	}

	// cr-008 start
	/**
	 * Lookup event
	 *
	 * @param int $a_id
	 * @return string
	 */
	public static function lookupEvent($a_id)
	{
		return self::lookupProperty($a_id, "ep_exam_event_id");
	}
	// cr-008 end

	/**
	 * Build complete sheet data
	 *
	 * This will gather all the data needed for an answer sheet (event, questions, objectives, etc.)
	 * The question order will be correct and texts / placeholders will be translated
	 *
	 * @param bool $a_ids_only
	 * @return array 
	 */
	public function getQuestionsInObjectiveOrder($a_ids_only = false)
	{
		include_once "Services/ADN/EP/classes/class.adnExaminationEvent.php";
		include_once "Services/ADN/ED/classes/class.adnQuestionTargetNumbers.php";
		include_once "Services/ADN/ED/classes/class.adnSubjectArea.php";
		include_once "Services/ADN/ED/classes/class.adnObjective.php";
		include_once "Services/ADN/ED/classes/class.adnSubobjective.php";
		include_once "Services/ADN/ED/classes/class.adnExaminationQuestion.php";

		$event = new adnExaminationEvent($this->getEvent());

		// mc objectives have target numbers, case objectives do not
		if($this->getType() == adnAnswerSheet::TYPE_MC)
		{
			$all_objectives = adnQuestionTargetNumbers::getObjectivesForType($event->getType());

			//  validate given questions against target numbers rule set
			$validation = adnQuestionTargetNumbers::validateMCSheet($event->getType(),
				$this->getQuestions());
		}
		else
		{
			$validation = null;

			// use all objectives from matching areas
			if($event->getType() == adnSubjectArea::GAS)
			{
				$all_objectives = array_keys(adnObjective::getObjectivesSelect(210));
			}
			else
			{
				$all_objectives = array_keys(adnObjective::getObjectivesSelect(310));
			}
		}
		
		// build objectives map
		$objectives = $subobjectives = $obj_map = $sobj_map = array();
		foreach($all_objectives as $obj_id)
		{
			$item = new adnObjective($obj_id);
			$nr = $item->buildADNNumber();

			$obj_map[$obj_id] = $nr;
			$objectives[$nr] = array("title" => $item->getName(), 
				"id" => $obj_id,
				"valid" => true,
				"addable" => false);

			// add subobjectives
			$sobj = adnSubobjective::getAllSubobjectives($obj_id);
			if($sobj)
			{
				foreach($sobj as $sitem)
				{
					$sobj_id = $sitem["id"];
					$sitem = new adnSubobjective($sobj_id);
					$nr = $sitem->buildADNNumber();
					
					$sobj_map[$sobj_id] = $nr;
					$subobjectives[$nr] = array("title" => $sitem->getName(),
						"objective_id" => $obj_map[$sitem->getObjective()],
						"id" => $sobj_id,
						"valid" => true,
						"addable" => false);
				}
			}
		}

		// parse questions (into objective tree)
		$question_objective_map = $this->getQuestionMap();
		$questions = array();
		foreach($this->getQuestions() as $question_id)
		{
			$question = new adnExaminationQuestion($question_id);
			$nr = $question->buildADNNumber();
			$obj_id = $question->getObjective();
			$sobj_id = $question->getSubobjective();
			$text = $question->getTranslatedQuestion($this);
			$status = (bool)$question->getStatus();

			if($sobj_id)
			{
				$questions[$nr] = array("text" => $text,
					"subobjective_id" => $sobj_map[$sobj_id],
					"id" => $question_id,
					"valid" => $status);
			}
			else
			{
				// questions is subjected to other objective?
				$target_obj = $obj_id;
				if(isset($question_objective_map[$question_id]))
				{
					$target_obj = $question_objective_map[$question_id];
				}

				$questions[$nr] = array("text" => $text,
					"objective_id" => $obj_map[$target_obj],
					"id" => $question_id,
					"valid" => $status);
			}
		}

		ksort($objectives);
		ksort($subobjectives);
		ksort($questions);

		$oquestions = array();
		$oquestion_ids = array();
		foreach($objectives as $objective_number => $objective)
		{
			$id = $objective["id"];

			// is invalid?
			if($validation && in_array($id, $validation["objectives_invalid"]))
			{
				$objectives[$objective_number]["valid"] = false;
			}

			// can questions be added?
			if($validation === null || in_array($id, $validation["objectives_add"]))
			{
				$objectives[$objective_number]["addable"] = true;
			}

			// corresponding questions
			foreach($questions as $question_number => $question)
			{
				if(isset($question["objective_id"]) && $question["objective_id"] == $objective_number)
				{
					$oquestions[$question_number] = $question;
					$oquestion_ids[] = $question["id"];
				}
			}

			foreach($subobjectives as $subobjective_number => $subobjective)
			{
				$subobjective_id = $subobjective["id"];

				if($subobjective["objective_id"] == $objective_number)
				{
					// is invalid?
					if($validation && in_array($subobjective_id, $validation["subobjectives_invalid"]))
					{
						$subobjectives[$subobjective_number]["valid"] = false;
					}

					// can questions be added?
					if($validation === null || in_array($subobjective_id, $validation["subobjectives_add"]))
					{
						$subobjectives[$subobjective_number]["addable"] = true;
					}

					// corresponding questions
					foreach($questions as $question_number => $question)
					{
						if(isset($question["subobjective_id"]) &&
							$question["subobjective_id"] == $subobjective_number)
						{
							$oquestions[$question_number] = $question;
							$oquestion_ids[] = $question["id"];
						}
					}
				}
			}
		}

		// overall value
		$case_mc = null;
		include_once("./Services/ADN/ED/classes/class.adnSubjectArea.php");
		if(adnSubjectArea::hasCasePart($event->getType()))
		{
			$case_mc = $this->getType();
		}
		$target = adnQuestionTargetNumbers::readOverall($event->getType(), $case_mc);

		if(!$a_ids_only)
		{
			return array(
				"objectives" => $objectives,
				"subobjectives" => $subobjectives,
				"questions" => $questions,
				"target" => $target
				);
		}
		else
		{
			return $oquestion_ids;
		}
	}

	/**
	 * Validate sheet against rules
	 *
	 * Number of questions is checked against target (mc) and/or overall (case) numbers
	 * Only active questions are allowed.
	 *
	 * @return bool
	 */
	public function validate()
	{
		include_once "Services/ADN/EP/classes/class.adnExaminationEvent.php";
		include_once "Services/ADN/ED/classes/class.adnQuestionTargetNumbers.php";
		$event = new adnExaminationEvent($this->getEvent());

		if($this->getType() == self::TYPE_MC)
		{
			$res = adnQuestionTargetNumbers::validateMCSheet($event->getType(), $this->getQuestions());
			foreach($res as $type => $items)
			{
				if(!empty($items))
				{
					return false;
				}
			}
		}
		else
		{
			$target = adnQuestionTargetNumbers::readOverall($event->getType(), self::TYPE_CASE);

			include_once "Services/ADN/ED/classes/class.adnExaminationQuestion.php";
			$counter = 0;
			foreach($this->getQuestions() as $question_id)
			{
				$question = new adnExaminationQuestion($question_id);
				
				// only active questions
				if($question->getStatus())
				{
					$counter++;
				}
			}

			if($counter != $target)
			{
				return false;
			}
		}

		return true;
	}
}

?>