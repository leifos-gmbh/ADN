<?php
/* Copyright (c) 2010 Leifos, GPL, see docs/LICENSE */

/**
 * Question base application class
 *
 * This is the base class for mc and case questions, but can be used independently (non-abstract)
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.de>
 * @version $Id: class.adnExaminationQuestion.php 31877 2011-11-29 16:23:33Z jluetzen $
 *
 * @ingroup ServicesADN
 */
class adnExaminationQuestion extends adnDBBase
{
	protected $id; // [int]
	protected $objective_id; // [int]
	protected $subobjective_id; // [int]
	protected $backup_of; // [int]
	protected $number; // [string]
	protected $name; // [string]
	protected $question; // [string]
	protected $status; // [int]
	protected $status_date; // [ilDateTime]
	protected $comment; // [string]

	/**
	 * Constructor
	 *
	 * @param int $a_id instance id
	 */
	public function __construct($a_id = null)
	{
		global $ilCtrl;

		$this->setFileDirectory("ed_question");

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
	 * Set backup id
	 *
	 * @param int $a_id
	 */
	public function setBackupOf($a_id)
	{
		$this->backup_of = (int)$a_id;
	}

	/**
	 * Get backup id
	 *
	 * @return int
	 */
	public function getBackupOf()
	{
		return $this->backup_of;
	}

	/**
	 * Set parent objective id (will reset subobjective id)
	 *
	 * @param int $a_id
	 */
	public function setObjective($a_id)
	{
		$this->objective_id = (int)$a_id;
		$this->subobjective_id = null;
	}

	/**
	 * Get parent objective id
	 *
	 * @return int
	 */
	public function getObjective()
	{
		return $this->objective_id;
	}

	/**
	 * Set parent subobjective id
	 *
	 * @param int $a_id
	 */
	public function setSubobjective($a_id)
	{
		if((int)$a_id)
		{
			$this->subobjective_id = (int)$a_id;

			// set (matching) parent objective (current objective does not have to match)
			include_once "./Services/ADN/ED/classes/class.adnSubobjective.php";
			$sub = new adnSubobjective($this->subobjective_id);
			$this->objective_id = $sub->getObjective();
		}
		else
		{
			$this->subobjective_id = null;
		}
	}

	/**
	 * Get parent subobjective id
	 *
	 * @return int
	 */
	public function getSubobjective()
	{
		return $this->subobjective_id;
	}

	/**
	 * Get catalog area
	 *
	 * @return int
	 */
	public function getCatalogArea()
	{
		$obj_id = $this->getObjective();
		if($obj_id)
		{
			include_once "Services/ADN/ED/classes/class.adnObjective.php";
			$obj = new adnObjective($obj_id);
			return $obj->getCatalogArea();
		}
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
	 * Set number
	 *
	 * @param string $a_number
	 */
	public function setNumber($a_number)
	{
		$this->number = (string)$a_number;
	}

	/**
	 * Get number
	 *
	 * @return string
	 */
	public function getNumber()
	{
		return $this->number;
	}

	/**
	 * Set question
	 *
	 * @param string $a_question
	 */
	public function setQuestion($a_question)
	{
		$this->question = (string)$a_question;
	}

	/**
	 * Get question
	 *
	 * @return string
	 */
	public function getQuestion()
	{
		return $this->question;
	}

	/**
	 * Set status
	 *
	 * @param bool $a_status
	 */
	public function setStatus($a_status)
	{
		// status is changed: set change time
		if($this->getStatus() !== null && (bool)$a_status != $this->getStatus())
		{
			$this->setStatusDate(new ilDateTime(time(), IL_CAL_UNIX));
		}
		$this->status = (bool)$a_status;
	}

	/**
	 * Get status
	 *
	 * @return bool
	 */
	public function getStatus()
	{
		return $this->status;
	}

	/**
	 * Set status date
	 *
	 * @param ilDateTime $a_date
	 */
	protected function setStatusDate(ilDateTime $a_date)
	{
		$this->status_date = $a_date;
	}

	/**
	 * Get status date
	 *
	 * @return ilDateTime
	 */
	public function getStatusDate()
	{
		return $this->status_date;
	}

	/**
	 * Set comment
	 *
	 * @param string $a_comment
	 */
	public function setComment($a_comment)
	{
		$this->comment = (string)$a_comment;
	}

	/**
	 * Get comment
	 *
	 * @return string
	 */
	public function getComment()
	{
		return $this->comment;
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

		$res = $ilDB->query("SELECT ed_objective_id,ed_subobjective_id,nr,title,question,status,".
			"status_date,qfile,last_change_comment".
			" FROM adn_ed_question".
			" WHERE id = ".$ilDB->quote($this->getId(), "integer"));
		$set = $ilDB->fetchAssoc($res);
		$this->setBackupOf($set["backup_of"]);
		$this->setObjective($set["ed_objective_id"]);
		$this->setSubobjective($set["ed_subobjective_id"]);
		$this->setName($set["title"]);
		$this->setNumber($set["nr"]);
		$this->setQuestion($set["question"]);
		$this->setStatus($set["status"]);
		$this->setStatusDate(new ilDateTime($set["status_date"], IL_CAL_DATETIME, ilTimeZone::UTC));
		$this->setComment($set["last_change_comment"]);
		$this->setFileName($set["qfile"], 1);
		
		parent::read($id, "adn_ed_question");
	}

	/**
	 * Convert properties to DB fields
	 *
	 * @return array (backup_of, ed_objective_id, ed_subobjective_id, title, nr, padded_nr,
	 * question, status, qfile, last_change_comment, status_date)
	 */
	protected function propertiesToFields()
	{
		$fields = array("backup_of" => array("integer", $this->getBackupOf()),
			"ed_objective_id" => array("integer", $this->getObjective()),
			"ed_subobjective_id" => array("integer", $this->getSubobjective()),
			"title" => array("text", $this->getName()),
			"nr" => array("text", $this->getNumber()),
			"padded_nr" => array("text", $this->handleCaseNumber($this->getNumber())),
			"question" => array("text", $this->getQuestion()),
			"status" => array("integer", $this->getStatus()),
			"qfile" => array("text", $this->getFileName(1)),
			"last_change_comment" => array("text", $this->getComment()));

		$date = $this->getStatusDate();
		if($date)
		{
			$fields["status_date"] = array("text", $date->get(IL_CAL_DATETIME, "", ilTimeZone::UTC));
		}
			
		return $fields;
	}

	/**
	 * Get numeric part of case question number and ensure at least 2 digits
	 *
	 * @param string $a_nr
	 * @return string
	 */
	protected function handleCaseNumber($a_nr)
	{
		if(preg_match("/^([0-9]+)/", $a_nr, $clean))
		{
			$digit = str_pad($clean[1], 2, "0", STR_PAD_LEFT);
			return $digit.substr($a_nr, strlen($clean[1]));
		}
		return $a_nr;
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
		$this->setId($ilDB->nextId("adn_ed_question"));
		$id = $this->getId();

		$fields = $this->propertiesToFields();
		$fields["id"] = array("integer", $id);

		if($this->getUploadedFile(1))
		{
			if(!$this->saveFile($this->getUploadedFile(1), $id."_1"))
			{
				$fields["qfile"] = array("text", "");
			}
		}
			
		$ilDB->insert("adn_ed_question", $fields);

		parent::save($id, "adn_ed_question");
		
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

		if($this->getUploadedFile(1))
		{
			if(!$this->saveFile($this->getUploadedFile(1), $id."_1"))
			{
				$fields["qfile"] = array("text", "");
			}
		}
		
		$ilDB->update("adn_ed_question", $fields, array("id"=>array("integer", $id)));

		parent::update($id, "adn_ed_question");

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
			$this->removeFile($id."_1");
						
			// online tests
			$ilDB->manipulate("DELETE FROM adn_ec_given_answer".
				" WHERE ed_question_id = ".$ilDB->quote($id, "integer"));
			
			// answer sheet
			$ilDB->manipulate("DELETE FROM adn_ep_sheet_question".
				" WHERE ed_question_id = ".$ilDB->quote($id, "integer"));			
			
			// archived flag is not used here ?!
			$ilDB->manipulate("DELETE FROM adn_ed_question".
				" WHERE id = ".$ilDB->quote($id, "integer"));
			$this->setId(null);
			return true;
		}
	}

	/**
	 * Get all questions
	 *
	 * When limit is given, the method will return an array with: data, overall, overall, active
	 * or just the data if not
	 *
	 * This uses a really complicated sql query because of performance and filter issues
	 * We try to "build" all values which are needed for the filters in the database, only in 1
	 * case (padded_nr) we use a precalculated one. But the speed-up has been significant, there
	 * is no way to do this on the php-side.
	 *
	 * @param array $a_filter
	 * @param bool $a_case_questions
	 * @param bool $a_full_export
	 * @param int $a_offset
	 * @param int $a_limit
	 * @param string $a_order
	 * @param string $a_order_direction
	 * @return array
	 */
	public static function getAllQuestions(array $a_filter = null, $a_case_questions = false,
		$a_full_export = false, $a_offset = null, $a_limit = null, $a_order = null,
		$a_order_direction = null, $a_no_backups_or_archived = true)
	{
		global $ilDB;

		if(!$a_full_export)
		{
			$sql = "SELECT q.id,q.title AS name,q.nr,q.question,q.status,q.ed_subobjective_id,";
		}
		else
		{
			$sql = "SELECT q.*,";
		}

		// build adn number for mc question with subobjective
		$fields = array(
			array("o.catalog_area", "dummy"),
			array($ilDB->quote(" ", "text"), "dummy"),
			array("LPAD(o.nr,2,".$ilDB->quote("0", "text").")", "dummy"),
			array($ilDB->quote(".", "text"), "dummy"),
			array("s.nr", "dummy"),
			array($ilDB->quote("-", "text"), "dummy"),
			array("q.padded_nr", "dummy")
		);
		$nr_mc_with_sub = $ilDB->concat($fields, false);

		// build adn number for mc question without subobjective
		$fields = array(
			array("o.catalog_area", "dummy"),
			array($ilDB->quote(" ", "text"), "dummy"),
			array("LPAD(o.nr,2,".$ilDB->quote("0", "text").")", "dummy"),
			array($ilDB->quote(".0-", "text"), "dummy"),
			array("q.padded_nr", "dummy")
		);
		$nr_mc_without_sub = $ilDB->concat($fields, false);

		// build adn number for case question (no catalog area, no subobjective)
		$fields = array(
			array("o.nr", "dummy"),
			array($ilDB->quote("-", "text"), "dummy"),
			array("q.padded_nr", "dummy")
		);
		$nr_case = $ilDB->concat($fields, false);

		// data request including (sub-)objective data
		$sql .= "o.catalog_area,o.title AS objective_title,s.title AS subobjective_title,".
			"CASE WHEN o.type=1 AND s.id > 0 THEN ".$nr_mc_with_sub.
			" WHEN o.type=1 AND s.id IS NULL THEN ".$nr_mc_without_sub.
			" ELSE ".$nr_case." END AS adn_number".
			" FROM adn_ed_question q".
			" LEFT JOIN adn_ed_objective o ON (q.ed_objective_id = o.id)".
			" LEFT JOIN adn_ed_subobjective s ON (q.ed_subobjective_id = s.id)";

		// count request to get overall number
		$sql_full = "SELECT COUNT(*) AS overall,status".
			" FROM adn_ed_question q".
			" LEFT JOIN adn_ed_objective o ON (q.ed_objective_id = o.id)".
			" LEFT JOIN adn_ed_subobjective s ON (q.ed_subobjective_id = s.id)";

		$where = array();
		
		// only used in import to remove all old data
		if($a_no_backups_or_archived)
		{
			$where[] = "(q.backup_of < ".$ilDB->quote(1, "integer")." OR q.backup_of IS NULL)";
			$where[] = "q.archived < ".$ilDB->quote(1, "integer");
		}

		include_once "Services/ADN/ED/classes/class.adnObjective.php";
		if(!$a_case_questions)
		{
			$where[] = "o.type =".$ilDB->quote(adnObjective::TYPE_MC);
		}
		else
		{
			$where[] = "o.type =".$ilDB->quote(adnObjective::TYPE_CASE);
		}
		
		if(is_array($a_filter))
		{
			// question
			if(isset($a_filter["question_nr"]))
			{
				self::handleAlphaNumericFilter($where, "q.nr", $a_filter["question_nr"]);
			}
			if(isset($a_filter["status"]) && $a_filter["status"])
			{
				$where[] = "q.status = ".$ilDB->quote($a_filter["status"]-1, "integer");
			}
			if(isset($a_filter["question_title"]) && $a_filter["question_title"])
			{
				$where[] = $ilDB->like("q.title", "text", "%".$a_filter["question_title"]."%");
			}

			// objective
			if(isset($a_filter["catalog_area"]) && $a_filter["catalog_area"])
			{
				$where[] = "o.catalog_area = ".$ilDB->quote($a_filter["catalog_area"], "integer");
			}
			if(isset($a_filter["objective_title"]) && $a_filter["objective_title"])
			{
				$where[] = $ilDB->like("o.title", "text", "%".$a_filter["objective_title"]."%");
			}
			if(isset($a_filter["objective_nr"]))
			{
				self::handleAlphaNumericFilter($where, "o.nr", $a_filter["objective_nr"]);
			}

			// subobjective
			if(isset($a_filter["subobjective_title"]) && $a_filter["subobjective_title"])
			{
				$where[] = $ilDB->like("s.title", "text", "%".$a_filter["subobjective_title"]."%");
			}
			if(isset($a_filter["subobjective_nr"]))
			{
				self::handleAlphaNumericFilter($where, "s.nr", $a_filter["subobjective_nr"]);
			}
		}

		$where = " WHERE ".implode(" AND ", $where);

		$overall = $overall_active = 0;
		if($a_limit !== null)
		{
			// we need number of active questions 
			$sql_full .= $where." GROUP BY status";
			$res = $ilDB->query($sql_full);
			while($row = $ilDB->fetchAssoc($res))
			{
				$overall += $row["overall"];
				if($row["status"] == 1)
				{
					$overall_active = $row["overall"];
				}
			}
			
			$ilDB->setLimit((int)$a_limit, (int)$a_offset);
		}

		$sql .= $where;

		// "translate" order field to sql
		if($a_order)
		{
			$field = "";

			switch($a_order)
			{
				case "nr":
					$field = "adn_number";
					break;

				case "name":
					$field = "q.title";
					break;

				case "question":
					if(get_class($ilDB) == "ilDBMySQL")
					{
						$field = "q.question";
					}
					// order by clob will fail
					else
					{
						$field = "CAST(q.question as VARCHAR2(100))";
					}
					break;

				case "status":
					$field = "q.status";

					// sorting by value != sorting by caption (will work for german only)
					if($a_order_direction == "asc")
					{
						$a_order_direction = "DESC";
					}
					else
					{
						$a_order_direction = "ASC";
					}
					break;
			}
			if($field)
			{
				$sql .= " ORDER BY ".$field." ".$a_order_direction;
			}
		}

		$res = $ilDB->query($sql);
		$all = array();
		while($row = $ilDB->fetchAssoc($res))
		{
			$all[] = $row;
		}

		if($a_limit === null)
		{
			return $all;
		}
		else
		{
			return array("data"=>$all, "overall"=>$overall, "active"=>$overall_active);
		}
	}

	/**
	 * Get questions by objective
	 *
	 * @param int $a_objective_id
	 * @param int $a_number
	 * @param bool $a_active_only
	 * @return array (ids)
	 */
	public static function getByObjective($a_objective_id, $a_number = null, $a_active_only = false)
	{
		global $ilDB;

		$sql = "SELECT id".
			" FROM adn_ed_question".
			" WHERE ed_objective_id = ".$ilDB->quote($a_objective_id, "integer");

		if($a_number)
		{
			$sql .= " AND nr = ".$ilDB->quote($a_number, "text");
		}
		if($a_active_only)
		{
			$sql .= " AND status = ".$ilDB->quote(1, "integer");
		}

		// no backups and archived
		$sql .= " AND (backup_of < ".$ilDB->quote(1, "integer")." OR backup_of IS NULL)";
		$sql .= " AND archived < ".$ilDB->quote(1, "integer");

		$res = $ilDB->query($sql);
		$all = array();
		while($row = $ilDB->fetchAssoc($res))
		{
			$all[] = $row["id"];
		}
		return $all;
	}

	/**
	 * Get questions by subobjective
	 *
	 * @param int $a_subobjective_id
	 * @param int $a_number
	 * @param bool $a_active_only
	 * @return array (ids)
	 */
	public static function getBySubobjective($a_subobjective_id, $a_number = null,
		$a_active_only = false)
	{
		global $ilDB;

		$sql = "SELECT id".
			" FROM adn_ed_question".
			" WHERE ed_subobjective_id = ".$ilDB->quote($a_subobjective_id, "integer");

		if($a_number)
		{
			$sql .= " AND nr = ".$ilDB->quote($a_number, "text");
		}
		if($a_active_only)
		{
			$sql .= " AND status = ".$ilDB->quote(1, "integer");
		}

		// no backups and archived
		$sql .= " AND (backup_of < ".$ilDB->quote(1, "integer")." OR backup_of IS NULL)";
		$sql .= " AND archived < ".$ilDB->quote(1, "integer");

		$res = $ilDB->query($sql);
		$all = array();
		while($row = $ilDB->fetchAssoc($res))
		{
			$all[] = $row["id"];
		}
		return $all;
	}

	/**
	 * Lookup property
	 *
	 * @param integer $a_id question id
	 * @param string $a_prop property
	 * @return mixed property value
	 */
	protected static function lookupProperty($a_id, $a_prop)
	{
		global $ilDB;

		$set = $ilDB->query("SELECT ".$a_prop." FROM adn_ed_question WHERE ".
			" id = ".$ilDB->quote($a_id, "integer"));
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
		$quest = new self($a_id);
		return "(".$quest->buildADNNumber().") ".$quest->getName();
	}

	/**
	 * Lookup question
	 *
	 * @param int $a_id
	 * @return string
	 */
	public static function lookupQuestion($a_id)
	{
		return self::lookupProperty($a_id, "question");
	}

	/**
	 * Is current number unique for (sub-)objective
	 *
	 * @param int $a_nr
	 * @return bool
	 */
	public function isNumberUnique($a_nr)
	{
		global $ilDB;

		$nr = $a_nr;
		$id = $this->getId();
		$obj = $this->getObjective();
		$sub = $this->getSubobjective();

		// only case questions use alpha-numeric numbers
		if(get_class($this) == "adnMCQuestion")
		{
			$nr = (int)$nr;
		}
		
		if($sub)
		{
			$sql = "SELECT id".
				" FROM adn_ed_question".
				" WHERE ed_subobjective_id = ".$ilDB->quote($sub, "integer").
				" AND nr = ".$ilDB->quote($nr, "text").
				" AND archived < ".$ilDB->quote(1, "integer").
				" AND (backup_of < ".$ilDB->quote(1, "integer")." OR backup_of IS NULL)";

			if($id)
			{
				$sql .= " AND id <> ".$ilDB->quote($id, "integer");
			}

			$set = $ilDB->query($sql);
			if($ilDB->numRows($set))
			{
				return false;
			}
		}
        else if ($obj)
		{
			$sql = "SELECT id".
				" FROM adn_ed_question".
				" WHERE ed_objective_id = ".$ilDB->quote($obj, "integer").
				" AND (ed_subobjective_id < ".$ilDB->quote(1, "integer").
				" OR ed_subobjective_id IS NULL)".
				" AND nr = ".$ilDB->quote($nr, "text").
				" AND archived < ".$ilDB->quote(1, "integer").
				" AND (backup_of < ".$ilDB->quote(1, "integer")." OR backup_of IS NULL)";

			if($id)
			{
				$sql .= " AND id <> ".$ilDB->quote($id, "integer");
			}

			$set = $ilDB->query($sql);
			if($ilDB->numRows($set))
			{
				return false;
			}
		}
		return true;
	}

	/**
	 * Build adn number
	 *
	 * This should not be used in bulk operations, costly because of OOP
	 *
	 * @param adnObjective $a_objective
	 * @param adnSubobjective $a_subobjective
	 * @return string
	 */
	public function buildADNNumber($a_objective = null, $a_subobjective = null)
	{
		$obj = $a_objective;
		if(!$obj)
		{
			if($this->getObjective())
			{
				include_once "Services/ADN/ED/classes/class.adnObjective.php";
				$obj = new adnObjective($this->getObjective());
			}
			else
			{
				return false;
			}
		}

		// mc
		if($obj->getType() == adnObjective::TYPE_MC)
		{
			if($this->getSubobjective())
			{
				$sobj = $a_subobjective;
				if(!$sobj)
				{
					include_once "Services/ADN/ED/classes/class.adnSubobjective.php";
					$sobj = new adnSubobjective($this->getSubobjective());
				}
				$nr = $sobj->buildADNNumber();
			}
			else
			{
				$nr = $obj->buildADNNumber().".0";
			}

			return $nr."-".str_pad($this->getNumber(), 2, "0", STR_PAD_LEFT);
		}
		// case
		else
		{
			return $obj->buildADNNumber()."-".$this->handleCaseNumber($this->getNumber());
		}
	}

	/**
	 * Translate placeholders for given answer sheet
	 *
	 * @param adnAnswerSheet $a_sheet
	 * @return string
	 */
	public function getTranslatedQuestion(adnAnswerSheet $a_sheet)
	{
		$text = $this->getQuestion();

		$good_id = $a_sheet->getNewGood();
		if($good_id)
		{
			include_once "Services/ADN/ED/classes/class.adnGoodInTransit.php";
			$good = new adnGoodInTransit($good_id);

			// german only for now
			$text = str_replace("[UN-Nr]", "UN ".$good->getNumber(), $text);
			$text = str_replace("[Bezeichnung]", $good->getName(), $text);
		}

		return $text;
	}

	
	//
	// BACKUPS
	//

	/**
	 * Get all question ids with backup
	 *
	 * @return array (ids)
	 */
	public static function getAllQuestionsWithBackup()
	{
		global $ilDB;

		$set = $ilDB->query("SELECT DISTINCT(backup_of)".
			" FROM adn_ed_question".
			" WHERE backup_of >= ".$ilDB->quote(1, "integer").
			" AND archived < ".$ilDB->quote(1, "integer"));
		$all = array();
		while($row = $ilDB->fetchAssoc($set))
		{
			$all[] = $row["backup_of"];
		}
		return $all;
	}

	/**
	 * Get id of backup item
	 *
	 * @return int
	 */
	public function getBackupId()
	{
		global $ilDB;
		
		$res = $ilDB->query("SELECT id".
			" FROM adn_ed_question".
			" WHERE backup_of = ".$ilDB->quote($this->getId(), "integer"));
		$set = $ilDB->fetchAssoc($res);
		if($set["id"])
		{
			return $set["id"];
		}
	}

	/**
	 * Read backup data
	 *
	 * This will overwrite all current properties!
	 */
	public function readBackup()
	{
		global $ilDB;

		$backup_id = $this->getBackupId();
		if($backup_id)
		{
			$this->setId($backup_id);
			$this->read();
		}
	}

	/**
	 * Remove all backups
	 */
	public function removeBackups()
	{
		global $ilDB;

		$id = $this->getId();
		if($id)
		{
			// there can only be 1 backup
			$backup = $this->getBackupId();
			if($backup)
			{
				$class = get_class($this);
				$question = new $class($backup);
				$question->delete(true);
			}
		}
	}
}

?>