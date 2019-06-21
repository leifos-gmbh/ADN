<?php
/* Copyright (c) 2010 Leifos, GPL, see docs/LICENSE */

include_once "Services/ADN/ED/classes/class.adnObjective.php";
include_once "./Services/ADN/ED/classes/class.adnSubobjective.php";
include_once "Services/ADN/ED/classes/class.adnExaminationQuestion.php";
include_once "Services/ADN/ED/classes/class.adnCaseQuestion.php";
include_once "Services/ADN/ED/classes/class.adnGoodRelatedAnswer.php";
include_once "Services/ADN/ED/classes/class.adnQuestionTargetNumbers.php";
include_once "Services/ADN/ED/classes/class.adnMCQuestion.php";
include_once "Services/ADN/ED/classes/class.adnCatalogNumbering.php";
include_once "Services/ADN/ED/classes/class.adnGoodInTransit.php";
include_once "Services/ADN/ED/classes/class.adnGoodInTransitCategory.php";

/**
 * (MC) question export application class
 *
 * Builds xml structure from question data
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.de>
 * @version $Id: class.adnQuestionExport.php 27883 2011-02-27 19:30:41Z akill $
 *
 * @ingroup ServicesADN
 */
class adnQuestionExport
{
	protected $objectives; // [array]
	protected $subobjectives; // [array]
	protected $goods; // [array]
	protected $good_categories; // [array]
	protected $targets; // [array]
	protected $files; // [array]
	protected $log; // [array]
	
	/**
	 * Build export data
	 *
	 * We always build the full export, which can be filtered/selected on import
	 */
	public function buildExport()
	{
		$xml =
		new SimpleXMLElement("<?xml version='1.0' encoding='utf-8' standalone='yes'?><adn></adn>");

		$categories = $xml->addChild("good_categories");
		$this->GoodCategoriesToXML($categories);

		$goods = $xml->addChild("goods");
		$this->GoodsToXML($goods);

		$objectives = $xml->addChild("objectives");
		$subs_data = $this->ObjectivesToXML($objectives);

		$subobjectives = $xml->addChild("subobjectives");
		$this->SubobjectivesToXML($subobjectives, $subs_data);

		$questions_mc = $xml->addChild("questions_mc");
		$this->MCQuestionsToXML($questions_mc);

		$questions_case = $xml->addChild("questions_case");
		$this->CaseQuestionsToXML($questions_case);

		$targets = $xml->addChild("target_numbers");
		$this->TargetNumbersToXML($targets);

		$this->saveFile($xml);
	}

	/**
	 * Convert objective data to xml
	 * 
	 * @param SimpleXMLElement $a_xml
	 * @return array
	 */
	protected function ObjectivesToXML(SimpleXMLElement $a_xml)
	{
		$data = adnObjective::getAllObjectives();
		$all_subs = array();
		foreach($data as $row)
		{
			$objective = $a_xml->addChild("objective");
			$objective->addChild("number", $this->buildNumber($row["catalog_area"], $row["nr"]));
			$objective->addChild("title", $row["name"]);
			$objective->addChild("topic", $row["topic"]);
			$objective->addChild("type", $row["type"]);

			// internal mapping
			$this->objectives[$row["id"]] = array("catalog_area" => $row["catalog_area"],
				"nr" => $row["nr"]);

			// gather subobjectives
			$subs = adnSubobjective::getAllSubobjectives($row["id"]);
			if($subs)
			{
				foreach($subs as $sub)
				{
					$sub["ed_objective_id"] = $row["id"];
					$all_subs[] = $sub;
					
					// internal mapping
					$this->subobjectives[$sub["id"]] = array("objective" => $row["id"],
						"nr" => $sub["nr"]);
				}
			}
		}
		return $all_subs;
	}

	/**
	 * Convert subobjective data to xml
	 *
	 * @param SimpleXMLElement $a_xml
	 * @param array $a_data
	 */
	protected function SubobjectivesToXML(SimpleXMLElement $a_xml, array $a_data)
	{
		if(sizeof($a_data))
		{
			foreach($a_data as $row)
			{
				// get from internal mapping
				$obj = $this->objectives[$row["ed_objective_id"]];

				if(!$obj)
				{
					$this->log("subobjective objective lookup");
				}

				$number = $this->buildNumber($obj["catalog_area"], $obj["nr"], $row["nr"]);
				
				$subobjective = $a_xml->addChild("subobjective");
				$subobjective->addChild("number", $number);
				$subobjective->addChild("title", $row["name"]);
				$subobjective->addChild("topic", $row["topic"]);
			}
		}
	}

	/**
	 * Convert good category data to xml
	 *
	 * @param SimpleXMLElement $a_xml
	 * @return array
	 */
	protected function GoodCategoriesToXML(SimpleXMLElement $a_xml)
	{
		$data = adnGoodInTransitCategory::getAllCategories();
		foreach($data as $item)
		{
			$category = $a_xml->addChild("good_category");
			$category->addChild("name", $item["name"]);
			$category->addChild("type", $item["type"]);

			// internal mapping
			$this->good_categories[$item["id"]] = $item["name"];
		}
	}

	/**
	 * Convert goods data to xml
	 *
	 * @param SimpleXMLElement $a_xml
	 * @return array
	 */
	protected function GoodsToXML(SimpleXMLElement $a_xml)
	{
		$data = adnGoodInTransit::getAllGoods();
		foreach($data as $row)
		{
			if(!$row["ed_good_category_id"] ||
				isset($this->good_categories[$row["ed_good_category_id"]]))
			{
				// get from internal mapping (category is optional)
				if(isset($this->good_categories[$row["ed_good_category_id"]]))
				{
					$row["ed_good_category_id"] =
						$this->good_categories[$row["ed_good_category_id"]];
				}
				else if($row["ed_good_category_id"])
				{
					$this->log("good category lookup");
				}

				// internal mapping
				$this->goods[$row["id"]] = $row["un_nr"];

				$good = $a_xml->addChild("good");
				$good->addChild("type", $row["type"]);
				$good->addChild("category", $row["ed_good_category_id"]);
				$good->addChild("number", $row["un_nr"]);
				$good->addChild("name", $row["name"]);
				$good->addChild("class", $row["class"]);
				$good->addChild("class_code", $row["class_code"]);
				$good->addChild("packing_group", $row["packing_group"]);
			}
		}
	}

	/**
	 * Convert mc question data to xml
	 * 
	 * @param SimpleXMLElement $a_xml
	 */
	protected function MCQuestionsToXML(SimpleXMLElement $a_xml)
	{
		$data = adnExaminationQuestion::getAllQuestions(null, false, true);
		foreach($data as $row)
		{
			// generic question data
			$question = $this->QuestionToXML($a_xml, $row);
			
			$question_obj = new adnMCQuestion($row["id"]);
			$question->addChild("correct", $question_obj->getCorrectAnswer());

			$answers = $question->addChild("answers");
			$answer = $question_obj->getAnswerA();
			$a = $answers->addChild("A");
			$a->addChild("text", trim($answer["text"]));
			$a->addChild("image", $answer["image"]);
			if($answer["image"])
			{
				$this->files[] = $row["id"]."_2";
			}
			$answer = $question_obj->getAnswerB();
			$b = $answers->addChild("B");
			$b->addChild("text", trim($answer["text"]));
			$b->addChild("image", $answer["image"]);
			if($answer["image"])
			{
				$this->files[] = $row["id"]."_3";
			}
			$answer = $question_obj->getAnswerC();
			$c = $answers->addChild("C");
			$c->addChild("text", trim($answer["text"]));
			$c->addChild("image", $answer["image"]);
			if($answer["image"])
			{
				$this->files[] = $row["id"]."_4";
			}
			$answer = $question_obj->getAnswerD();
			$d = $answers->addChild("D");
			$d->addChild("text", trim($answer["text"]));
			$d->addChild("image", $answer["image"]);
			if($answer["image"])
			{
				$this->files[] = $row["id"]."_5";
			}
		}
	}

	/**
	 * Convert case question data to xml
	 *
	 * @param SimpleXMLElement $a_xml
	 */
	protected function CaseQuestionsToXML(SimpleXMLElement $a_xml)
	{
		$data = adnExaminationQuestion::getAllQuestions(null, true, true);
		foreach($data as $row)
		{
			// generic question data
			$question = $this->QuestionToXML($a_xml, $row);

			$question_obj = new adnCaseQuestion($row["id"]);
			$question->addChild("default_answer", trim($question_obj->getDefaultAnswer()));
			$question->addChild("good_specific", (int)$question_obj->isGoodSpecific());

			// goods specific
			if($question_obj->isGoodSpecific())
			{
				$goods = array();
				foreach($question_obj->getGoods() as $good_id)
				{
					// use internal map
					if(isset($this->goods[$good_id]))
					{
						$goods[] = $this->goods[$good_id];
					}
					else
					{
						$this->log("case question good lookup");
					}
				}
				$question->addChild("goods", implode(";", $goods));
			}

			// good related anwers
			$answers = adnGoodRelatedAnswer::getAllAnswers($question_obj->getId());
			if($answers)
			{
				$answers_node = $question->addChild("answers");

				foreach($answers as $answer)
				{
					$answer_node = $answers_node->addChild("answer");
					$answer_node->addChild("butan_or_empty", (int)$answer["butan_or_empty"]);
					$answer_node->addChild("text", trim($answer["answer"]));

					$goods = array();
					foreach($answer["goods"] as $good_id)
					{
						// use internal map
						if(isset($this->goods[$good_id]))
						{
							$goods[] = $this->goods[$good_id];
						}
						else
						{
							$this->log("case question good related answer good lookup");
						}
					}
					$answer_node->addChild("goods", implode(";", $goods));
				}
			}
		}
	}

	/**
	 * Convert question base data to xml
	 * 
	 * @param SimpleXMLElement $a_xml
	 * @param array $a_item
	 * @return SimpleXMLElement
	 */
	protected function QuestionToXML(SimpleXMLElement $a_xml, array $a_item)
	{
		if($a_item["ed_subobjective_id"])
		{
			// use internal map
			$sobj = $this->subobjectives[$a_item["ed_subobjective_id"]];
			if(!$sobj)
			{
				$this->log("question subobjective lookup");
			}
			$obj = $this->objectives[$sobj["objective"]];
		}
		else
		{
			// use internal map
			$sobj = null;
			$obj = $this->objectives[$a_item["ed_objective_id"]];
			if(!$obj)
			{
				$this->log("question objective lookup");
			}
		}

		$number = $this->buildNumber($obj["catalog_area"], $obj["nr"], $sobj["nr"], $a_item["nr"]);

		$question = $a_xml->addChild("question");
		$question->addChild("number", $number);
		$question->addChild("title", trim($a_item["title"]));
		$question->addChild("text", trim($a_item["question"]));
		
		$question->addChild("status", (int)$a_item["status"]);
		if($a_item["status_date"] === null || $a_item["status_date"] === "1970-01-01 00:00:00")
		{
			$a_item["status_date"] = null;
		}
		$question->addChild("status_date", $a_item["status_date"]);

		$question->addChild("image_id", $a_item["id"]);
		$question->addChild("image", $a_item["qfile"]);
		if($a_item["qfile"])
		{
			$this->files[] = $a_item["id"]."_1";
		}

		return $question;
	}

	/**
	 * Convert question target numbers data to xml
	 *
	 * @param SimpleXMLElement $a_xml
	 */
	protected function TargetNumbersToXML(SimpleXMLElement $a_xml)
	{
		// gather data
		$data = array();
		foreach(adnQuestionTargetNumbers::getAllTargets() as $item)
		{
			$data[$item["subject_area"]][$item["mc_case"]][] = array(
				"nr" => (int)$item["nr_of_questions"],
				"max_one" => (bool)$item["max_one_per_objective"],
				"objectives" => $item["objectives"],
				"subobjectives" => $item["subobjectives"]);
		}

		// data to xml
		foreach($data as $area => $types)
		{
			$area_node = $a_xml->addChild("area");
			$area_node->addAttribute("id", $area);
			foreach($types as $type => $targets)
			{
				$type_node = $area_node->addChild("type");
				$type_node->addAttribute("id", (int)$type);
				$type_node->addAttribute("overall",
					adnQuestionTargetNumbers::readOverall($area, $type));
			
				foreach($targets as $item)
				{
					$node = $type_node->addChild("target");
					$node->addChild("nr", $item["nr"]);
					$node->addChild("max_one", (int)$item["max_one"]);

					if($item["objectives"])
					{
						$objectives = array();
						foreach($item["objectives"] as $obj_id)
						{
							// use internal map
							if(isset($this->objectives[$obj_id]))
							{
								$obj = $this->objectives[$obj_id];
								$objectives[] =
									$this->buildNumber($obj["catalog_area"], $obj["nr"]);
							}
							else
							{
								$this->log("target objective lookup");
							}
						}
						$node->addChild("objectives", implode(";", $objectives));
					}
					if($item["subobjectives"])
					{
						$subobjectives = array();
						foreach($item["subobjectives"] as $sobj_id)
						{
							// use internal map
							if(isset($this->subobjectives[$sobj_id]))
							{
								$sobj = $this->subobjectives[$sobj_id];
								$obj = $this->objectives[$sobj["objective"]];
								$subobjectives[] =
									$this->buildNumber($obj["catalog_area"], $obj["nr"],
									$sobj["nr"]);
							}
							else
							{
								$this->log("target subobjective lookup");
							}
						}
						$node->addChild("subobjectives", implode(";", $subobjectives));
					}
				}

				if($area == "chem" || $area == "gas")
				{
					$type_node = $area_node->addChild("type");
					$type_node->addAttribute("id", 2);
					$type_node->addAttribute("overall",
						adnQuestionTargetNumbers::readOverall($area, 2));
				}
			}
		}
	}

	/**
	 * Debug helper
	 *
	 * @param mixed $a_text
	 */
	protected function log($a_text)
	{
		if(DEVMODE)
		{
			var_dump($a_text);
		}
	}

	/**
	 * Write xml data to zip file
	 *
	 * @param SimpleXMLElement $xml
	 * @return string
	 */
	protected function saveFile(SimpleXMLElement $xml)
	{
		$zip = new ZipArchive();
		$filename = $this->getFilePath()."/adn_export_".time().".zip";
		if ($zip->open($filename, ZIPARCHIVE::CREATE) !== true)
		{
			return false;
		}
		$zip->addFromString("adn_export.xml", $xml->asXML());

		// file handling
		if(sizeof($this->files))
		{
			$path = new adnMCQuestion();
			$path = $path->getFilePath();
			foreach($this->files as $file)
			{
				$file = $path.$file;
				if(file_exists($file))
				{
					$zip->addFile($file, "images/".basename($file));
				}
			}
		}

		$zip->close();
		return true;
	}


	//
	// GUI calls
	//

	public static function getAllFiles()
	{
		$files = array();
		foreach(glob(self::getFilePath()."/adn_export_*.zip") as $file)
		{
			$date = substr(basename($file), 11, -4);
			$date = new ilDateTime($date, IL_CAL_UNIX, ilTimeZone::UTC);

			$files[] = array("id" => basename($file),
				"name" => "ADN Export",
				"date" => $date);
		}
		return $files;
	}

	/**
	 * Translate file name to human-readable
	 *
	 * @param string $a_file
	 * @param bool $a_real_file_name
	 * @return string
	 */
	public static function lookupName($a_file, $a_real_file_name = false)
	{
		$file = basename($a_file);
		$date = substr($file, 11, -4);
		if(!$a_real_file_name)
		{
			$date = new ilDateTime($date, IL_CAL_UNIX, ilTimeZone::UTC);
			return "ADN Export, ".ilDatePresentation::formatDate($date);
		}
		else
		{
			return "ADN_Export_".date("Y-m-d_H-i", $date).".zip";
		}
	}

	/**
	 * delete file
	 *
	 * @param string $a_file
	 * @return bool
	 */
	public static function delete($a_file)
	{
		$file = self::getFilePath()."/".basename($a_file);
		if(file_exists($file))
		{
			unlink($file);
			return true;
		}
		return false;
	}

	/**
	 * Get path to export files
	 *
	 * @return string
	 */
	public static function getFilePath()
	{
		$target = ilUtil::getDataDir()."/adn/ad_export";
		if(!is_dir($target))
		{
			// create target directory
			$path = ilUtil::getDataDir();
			ilUtil::createDirectory($path."/adn");
			ilUtil::createDirectory($path."/adn/ad_export");
		}
		return $target;
	}

	
	//
	// the following method is shared between import and export
	//

	/**
	 * Build adn number from parts
	 *
	 * @param int $a_area
	 * @param int $a_objective
	 * @param int $a_subobjective
	 * @param int $a_question
	 * @return string
	 */
	public static function buildNumber($a_area, $a_objective, $a_subobjective = null,
		$a_question = null)
	{
		// no padding here, we want minimized data (and no double-padding)

		// mc
		if(is_numeric($a_objective))
		{
			$adn_number = (int)$a_area." ".(int)$a_objective;
		}
		// case
		else
		{
			$adn_number = (int)$a_area." ".trim($a_objective);
		}
		if($a_subobjective || $a_question)
		{
			// mc
			if(is_numeric($a_objective))
			{
				$adn_number .= ".".(int)$a_subobjective;
			}
			// case (optional?)
			else if(trim($a_subobjective))
			{
				$adn_number .= ".".trim($a_subobjective);
			}
		}
		if($a_question)
		{
			// mc
			if(is_numeric($a_objective))
			{
				$adn_number .= "-".(int)$a_question;
			}
			// case
			else
			{
				$adn_number .= "-".trim($a_question);
			}
		}
		return $adn_number;
	}
}

?>