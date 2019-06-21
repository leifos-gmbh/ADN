<?php

/**
 * Import the complete question related data from intermediate files (which are made by
 * custom java-tool)
 *
 * Only execute this if you know what you're doing
 */

exit();
echo ("Start script");
error_reporting(E_ALL);
set_time_limit(0);

$path = "C:\\Users\\fwolf\\Desktop\\Test\\";

$db = mysql_connect("localhost" , "ilias", "55555");
mysql_select_db("iliadn");
mysql_query("SET NAMES 'utf8'");
mysql_query("SET CHARACTER SET utf8");

// we do NOT touch objectives and subobjectives!

// removing old questions
/*mysql_query("DELETE FROM adn_ed_case_answ_good");
mysql_query("DELETE FROM adn_ed_good_rel_answer");
mysql_query("DELETE FROM adn_ed_good_rel_answer_seq");
mysql_query("DELETE FROM adn_ed_quest_case_good");
mysql_query("DELETE FROM adn_ed_question_case");*/
mysql_query("DELETE FROM adn_ed_question_mc");
mysql_query("DELETE FROM adn_ed_question");
mysql_query("DELETE FROM adn_ed_question_seq");

$importer = new MCImporter("", $path);
//$importer->importFiles();
$importer->readFiles();

/*
$importer = new MCImporter($path."__ConverterMultipleChoice.jar", $path."Fragenkatalog\\");
$importer->importFiles();
$importer->readFiles();

$importer = new CaseImporter($path."__ConverterKasusGas.jar", $path."__ConverterKasusChemie.jar",
	$path."Fragenkatalog\\");
$importer->importFiles();
$importer->readFiles();/*/
echo("<br>End script.");

abstract class Importer
{
	protected function debug($message, $level = false)
	{
		if(!$level || in_array($level, $this->levels))
		{
			if($level && $this->filename)
			{
				echo "<i>[".$this->filename."]</i> ";
			}
			echo $message."<br />";
		}
	}

	protected function parseEntities($a_raw)
	{
		if(preg_match_all("/&#([0-9]+);/", $a_raw, $hits))
		{
			foreach($hits[0] as $idx => $entity)
			{
				$this->debug("Removing entity: &amp;#".$hits[1][$idx].";", "warning");
				$a_raw = str_replace($entity, "", $a_raw);
			}
		}
		return $a_raw;
	}

	protected function loadXMLFile($a_file)
	{
		$file = file_get_contents($a_file);
		$file = $this->parseEntities($file);

		$this->filename = basename($a_file);
		return simplexml_load_string($file);
	}

	protected function nextId($table)
	{
		$table .= "_seq";

		$res = mysql_query("SELECT sequence FROM ".$table);
		if(!mysql_num_rows($res))
		{
			mysql_query("INSERT INTO ".$table." (sequence) VALUES (1)");
			return 1;
		}

		$res = mysql_fetch_assoc($res);
		$id = $res["sequence"]+1;
		mysql_query("UPDATE ".$table." SET sequence = ".$id);
		return $id;
	}

	protected function insert($table, array $fields)
	{
		$sql = "INSERT INTO ".$table." (".implode(",", array_keys($fields)).")".
			" VALUES (";

		$values = array();
		foreach($fields as $value)
		{
			if($value[1] === NULL)
			{
				$values[] = "NULL";
			}
			else if($value[0] == "text")
			{
				$values[] = "'".mysql_real_escape_string((string)$value[1])."'";
			}
			else
			{
				$values[] = (int)$value[1];
			}
		}

		$sql .= implode(",", $values).")";
		mysql_query($sql);
		$this->debug($sql, "db");
		if(mysql_errno())
		{
			$this->debug($sql." --- ".mysql_error(), "error");
			return false;
		}
		return true;
	}
}

class CaseImporter extends Importer
{
	protected $converter_gas; // [string]
	protected $converter_chem; // [string]
	protected $catalog; // [string]
	protected $filename; // [string]
	protected $levels = array("error","warning"); // [array]
	protected $objectives = array(); // [array]
	protected $questions = array(); // [array]
	protected $goods = array(); // [array]

	public function __construct($a_converter_gas, $a_converter_chem, $a_catalog)
	{
		$this->converter_gas = $a_converter_gas;
		$this->converter_chem = $a_converter_chem;
		$this->catalog = $a_catalog;
	}

	public function importFiles()
	{
		// removing old
		foreach(glob($this->catalog."*.xml") as $file)
		{
			if(!in_array(basename($file), array("_KasusfragenGas.xml", "_KasusfragenChemie.xml")))
			{
				unlink($file);
			}
		}
		
		foreach(glob($this->catalog."Antworten Kasusfragen*.doc") as $file)
		{
			if(substr(basename($file), 0, 1) != "~")
			{
				$this->debug("Importing ".basename($file), "info");

				if(stristr(basename($file), "gas"))
				{
					$converter = $this->converter_gas;
				}
				else
				{
					$converter = $this->converter_chem;
				}

				$output = false;
				exec("java -jar \"".$converter."\" \"".$file."\"", $output);

				if(!file_exists(str_replace(".doc", ".xml", $file)))
				{
					$this->debug("Failed ".basename($file)." [<i>".implode(" - ", $output)."</i>]",
						"error");
				}
			}
		}
	}

	public function readFiles()
	{
		$files = glob($this->catalog."*.xml");
		foreach($files as $xml_file)
		{
			if(stristr($xml_file, "Antworten"))
			{
				if(stristr($xml_file, "gas"))
				{
					$xml = $this->loadXMLFile($this->catalog."_KasusfragenGas.xml");
					$this->importQuestions(210, $xml);

					$xml = $this->loadXMLFile($xml_file);
					$this->importGasAnswers($xml);
				}
				else if(stristr($xml_file, "chemie"))
				{
					$xml = $this->loadXMLFile($this->catalog."_KasusfragenChemie.xml");
					$this->importQuestions(310, $xml);

					$xml = $this->loadXMLFile($xml_file);
					$this->importChemAnswers($xml);
				}
				else
				{
					$this->debug("Unknown file type ".basename($xml_file)."", "error");
				}
			}
		}

		$this->minimizeAnswers();
	}

	protected function buildGoodsMap($a_type)
	{
		$this->goods = array();
		
		$res = mysql_query("SELECT id, ed_good_category_id, name FROM adn_ed_good".
			" WHERE type = ".(int)$a_type);
		while($row = mysql_fetch_assoc($res))
		{
			$this->goods[$row["name"]] = $row["id"];
		}
	}

	protected function buildObjectiveMap($a_area)
	{
		$res = mysql_query("SELECT id,nr FROM adn_ed_objective".
			" WHERE catalog_area = ".(int)$a_area);
		while($row = mysql_fetch_assoc($res))
		{
			$this->objectives[$row["nr"]] = $row["id"];
		}
	}

	protected function importQuestions($a_area, SimpleXMLElement $xml)
	{
		$this->objectives = array();
		$this->questions = array();
		
		$this->buildObjectiveMap($a_area);
		
		foreach($xml as $part)
		{
			$cat = (string)$part["name"];
			if(preg_match("/^.*Teil ([A-E]).+$/", $cat, $hits))
			{
				$obj_id = $this->objectives[$hits[1]];
				if($obj_id)
				{
					foreach($part->question as $question)
					{
						$number = $this->parseNumber(trim((string)$question["nr"]));
						if($number)
						{
							$text = trim((string)$question);

							$id = $this->nextId("adn_ed_question");

							$fields = array("id" => array("integer", $id),
								"ed_objective_id" => array("integer", $obj_id),
								// "ed_subobjective_id" => array("integer", $sobj_id),
								"create_date" => array("text", date("Y-m-d H:i:s")),
								"create_user" => array("integer", 1),
								"last_update" => array("text", date("Y-m-d H:i:s")),
								"last_update_user" => array("integer", 1),
								"nr" => array("text", $number["nr"]),
								"title" => array("text", trim((string)$question["nr"])),
								"question" => array("text", $text),
								"status" => array("integer", 1));

							if($this->insert("adn_ed_question", $fields))
							{
								$fields = array("ed_question_id" => array("integer", $id),
									// "default_answer" => array("text", ""), ???
									// "good_specific_question" => array("integer", 1) comes with
									// answer
									);

								if($this->insert("adn_ed_question_case", $fields))
								{
									$this->questions[implode(".", $number)] = $id;
								}
							}
						}
					}
				}
				else
				{
					$this->debug("Unknown objective [".$cat."]", "warning");
				}
			}
			else
			{
				$this->debug("Unknown question category [".$cat."]", "warning");
			}
		}
	}

	protected function importGasAnswers(SimpleXMLElement $xml)
	{
		$this->buildGoodsMap(1);
		
		foreach($xml as $question)
		{
			$number = $this->parseNumber((string)$question["nr"]);
			if($number)
			{
				$question_id = $this->questions[implode(".", $number)];
				if($question_id)
				{
					$all_goods = array();
					foreach($question->kasus as $kasus)
					{
						$checksums = array();
						$case = (string)$kasus["type"];
						if(is_numeric($case) && (int)$case == 1 || (int)$case == 2)
						{
							// butan or empty (== 3) will be done with minimizeAnswers()
							if((int)$case == 1)
							{
								$butan_or_empty = 1;
							}
							else
							{
								$butan_or_empty = 2;
							}
							
							foreach($kasus->answer as $answer)
							{
								$good = strtoupper((string)$answer["substance"]);
								if(isset($this->goods[$good]))
								{
									$text = trim((string)$answer);
									$good_id = $this->goods[$good];
									$checksum = md5($text);

									// create new answer
									if(!isset($checksums[$checksum]))
									{
										$id = $this->nextId("adn_ed_good_rel_answer");

										$fields = array("id" => array("integer", $id),
											"ed_question_id" => array("integer", $question_id),
											"butan_or_empty" => array("integer", $butan_or_empty),
											"create_date" => array("text", date("Y-m-d H:i:s")),
											"create_user" => array("integer", 1),
											"last_update" => array("text", date("Y-m-d H:i:s")),
											"last_update_user" => array("integer", 1),
											"answer" => array("text", $text));

										if($this->insert("adn_ed_good_rel_answer", $fields))
										{
											$checksums[$checksum] = $id;
										}
									}
									else
									{
										$this->debug("Re-using answer ".implode(".", $number)." # ".
											$case." # (".$good_id.") ".(string)$answer["substance"],
											"info");
									}
									
									// add good to answer
									if(isset($checksums[$checksum]))
									{
										$id = $checksums[$checksum];
										$all_goods[] = $good_id;

										$fields = array("ed_good_related_answer_id" =>
											array("integer", $id),
											"ed_good_id" => array("integer", $good_id));

										$this->insert("adn_ed_case_answ_good", $fields);
									}
								}
								else
								{
									$this->debug("Unknown answer good ".$good." [".
										implode(".", $number)."]", "warning");
								}
							}
						}
						else
						{
							$this->debug("Unknown answer case ".$case." [".implode(".", $number).
								"]", "warning");
						}
					}

					// goods for questions
					if(sizeof($all_goods))
					{
						mysql_query("UPDATE adn_ed_question_case SET good_specific_question = 1".
							" WHERE ed_question_id = ".$question_id);
						if(mysql_errno())
						{
							$this->debug(mysql_error(), "error");
						}

						foreach(array_unique($all_goods) as $good_id)
						{
							$fields = array("ed_question_id" => array("integer", $question_id),
											"ed_good_id" => array("integer", $good_id));

							$this->insert("adn_ed_quest_case_good", $fields);
						}
					}
				}
				else
				{
					$this->debug("Unknown question [".implode(".", $number)."]", "warning");
				}
			}
		}
	}

	protected function importChemAnswers(SimpleXMLElement $xml)
	{
		$this->buildGoodsMap(2);

		$question_goods = array();
		$checksums = array();
		foreach($xml as $good_cats)
		{
			// good categories can be ignored
			foreach($good_cats->substance as $good)
			{
				$type = explode(", UN", (string)$good["name"]);
				$type = $type[0];
				if(isset($this->goods[$type]))
				{
					$good_id = $this->goods[$type];
					
					foreach($good->part as $part)
					{
						// part can be ignored
						foreach($part->answer as $answer)
						{
							$number = (string)$answer["nr"];
							$text = trim((string)$answer);

							// hack for E-4a/b
							if($number == "E-4")
							{
								$number .= substr($text, 0, 1);
								$text = trim(substr($text, 1));
							}

							$number = $this->parseNumber($number);
							if($number)
							{
								$question_id = $this->questions[implode(".", $number)];
								if($question_id)
								{
									$checksum = md5($text);

									// create new answer
									if(!isset($checksums[$question_id][$checksum]))
									{
										$id = $this->nextId("adn_ed_good_rel_answer");

										$fields = array("id" => array("integer", $id),
											"ed_question_id" => array("integer", $question_id),
											"create_date" => array("text", date("Y-m-d H:i:s")),
											"create_user" => array("integer", 1),
											"last_update" => array("text", date("Y-m-d H:i:s")),
											"last_update_user" => array("integer", 1),
											"answer" => array("text", $text));

										if($this->insert("adn_ed_good_rel_answer", $fields))
										{
											$checksums[$question_id][$checksum] = $id;
										}
									}
									else
									{
										$this->debug("Re-using answer ".implode(".", $number).
											" # (".$good_id.") ".$type, "info");
									}

									if(isset($checksums[$question_id][$checksum]))
									{
										$id = $checksums[$question_id][$checksum];

										$fields = array("ed_good_related_answer_id" =>
											array("integer", $id),
											"ed_good_id" => array("integer", $good_id));

										$this->insert("adn_ed_case_answ_good", $fields);

										$question_goods[$question_id][$id][] = $good_id;
									}
								}
								else
								{
									$this->debug("Unknown question [".implode(".", $number)."]",
										"warning");
								}
							}
						}
					}
				}
				else
				{
					$this->debug("Unknown answer good ".$type, "warning");
				}
			}
		}

		// goods for questions
		if(sizeof($question_goods))
		{
			foreach($question_goods as $question_id => $answers)
			{
				foreach($answers as $answer_id => $all_goods)
				{
					// answer for all goods => not good specific
					if(sizeof($all_goods) == sizeof($this->goods))
					{
						$res = mysql_query("SELECT answer FROM adn_ed_good_rel_answer WHERE id = ".
							$answer_id);
						if(mysql_errno())
						{
							$this->debug(mysql_error(), "error");
						}
						$set = mysql_fetch_assoc($res);
						$text = $set["answer"];

						mysql_query("DELETE FROM adn_ed_case_answ_good".
								" WHERE ed_good_related_answer_id = ".$answer_id);
						if(mysql_errno())
						{
							$this->debug(mysql_error(), "error");
						}
						mysql_query("DELETE FROM adn_ed_good_rel_answer".
							" WHERE id = ".$answer_id);
						if(mysql_errno())
						{
							$this->debug(mysql_error(), "error");
						}

						mysql_query("UPDATE adn_ed_question_case".
							" SET default_answer = '".mysql_real_escape_string($text)."'".
							" WHERE ed_question_id = ".$question_id);
						if(mysql_errno())
						{
							$this->debug(mysql_error(), "error");
						}

						$this->debug("Question ".$question_id." is not good specific", "info");
					}
					// add goods to question
					else
					{
						mysql_query("UPDATE adn_ed_question_case SET good_specific_question = 1".
							" WHERE ed_question_id = ".$question_id);
						if(mysql_errno())
						{
							$this->debug($sql." --- ".mysql_error(), "error");
						}

						foreach($all_goods as $good_id)
						{
							$fields = array("ed_question_id" => array("integer", $question_id),
											"ed_good_id" => array("integer", $good_id));

							$this->insert("adn_ed_quest_case_good", $fields);
						}
					}
				}
			}
		}
	}

	protected function minimizeAnswers()
	{
		$answers = array();

		$res = mysql_query("SELECT * FROM adn_ed_case_answ_good");
		while($row = mysql_fetch_assoc($res))
		{
			$goods[$row["ed_good_related_answer_id"]][] = $row["ed_good_id"];
		}
		foreach($goods as $idx => $values)
		{
			$goods[$idx] = md5(implode(";", $values));
		}

		$res = mysql_query("SELECT id,ed_question_id,butan_or_empty,answer".
			" FROM adn_ed_good_rel_answer".
			" WHERE butan_or_empty IN (1,2)");
		while($row = mysql_fetch_assoc($res))
		{
			$mygoods = $goods[$row["id"]];
		$answers[$row["ed_question_id"]][md5($row["answer"])][$mygoods][$row["butan_or_empty"]][] =
				$row["id"];
		}

		foreach($answers as $question_id => $checksums)
		{
			foreach($checksums as $goodsums)
			{
				foreach($goodsums as $types)
				{
					if(isset($types[1]) && isset($types[2]))
					{
						if(sizeof($types[1]) == 1 && sizeof($types[2]) == 1)
						{
							$keep_id = $types[1][0];
							$remove_id = $types[2][0];

							mysql_query("UPDATE adn_ed_good_rel_answer SET butan_or_empty = 3".
								" WHERE id = ".$keep_id);
							if(mysql_errno())
							{
								$this->debug(mysql_error(), "error");
							}
							mysql_query("DELETE FROM adn_ed_case_answ_good".
								" WHERE ed_good_related_answer_id = ".$remove_id);
							if(mysql_errno())
							{
								$this->debug(mysql_error(), "error");
							}
							mysql_query("DELETE FROM adn_ed_good_rel_answer".
								" WHERE id = ".$remove_id);
							if(mysql_errno())
							{
								$this->debug(mysql_error(), "error");
							}
						}
						else
						{
							var_dump($types);
						}
					}
				}

			}
		}
	}

	protected function parseNumber($a_number)
	{
		$number = explode("-", $a_number);
		if(sizeof($number) == 2 && in_array($number[0], array("A", "B", "C", "D", "E")))
		{
			// chemie: C-6.
			if($number[1] === "6.")
			{
				$number[1] = "6";
			}
			return array("category"=>$number[0], "nr"=>$number[1]);	
		}
		$this->debug("Invalid number ".$a_number, "warning");
	}
}

class MCImporter extends Importer
{
	protected $converter; // [string]
	protected $catalog; // [string]
	protected $filename; // [string]
	protected $ids; // [array]
	protected $levels = array("error", "warning", "info"); // [array]

	public function __construct($a_converter, $a_catalog)
	{
		$this->converter = $a_converter;
		$this->catalog = $a_catalog;
	}

	public function importFiles()
	{
		// removing old
		foreach(glob($this->catalog."*.xml") as $file)
		{
			if(!in_array(basename($file), array("_KasusfragenGas.xml", "_KasusfragenChemie.xml")))
			{
				unlink($file);
			}
		}

		foreach(glob($this->catalog."*.doc") as $file)
		{
			if(substr(basename($file), 0, 1) != "~")
			{
				if(!stristr($file, "Kasus") && !stristr($file, "ZZ-Stoffe"))
				{
					$this->debug("Importing ".basename($file), "info");
					
					$output = false;
					exec("java -jar \"".$this->converter."\" \"".$file."\"", $output);

					if(!file_exists(str_replace(".doc", ".xml", $file)))
					{
						$this->debug("Failed ".basename($file)." [<i>".
							implode(" - ", $output)."</i>]", "error");
					}
				}
			}
		}
	}

	public function readFiles()
	{
		$files = glob($this->catalog."*.xml");
		$this->buildObjectiveMap();
		$this->buildSubobjectiveMap();

		foreach($files as $file)
		{
			if(!in_array(basename($file), array("_KasusfragenGas.xml", "_KasusfragenChemie.xml")))
			{
				$this->filename = basename($file);
				$this->debug("Parsing \"".basename($file)."\"", "info");
				$this->parseFile($file);
			}
		}

		// $this->showIds();
	}

	protected function buildObjectiveMap()
	{
		$this->objectives = array();
		$this->objectives_reverse = array();

		$res = mysql_query("SELECT id,catalog_area,nr FROM adn_ed_objective");
		while($row = mysql_fetch_assoc($res))
		{
			$id = $row["catalog_area"]." ".str_pad($row["nr"], 2, "0", STR_PAD_LEFT);
			$this->objectives[$id] = $row["id"];
			$this->objectives_reverse[$row["id"]] = array("catalog_area" => $row["catalog_area"],
				"nr" => $row["nr"]);
		}
	}

	protected function buildSubobjectiveMap()
	{
		$this->subobjectives = array();

		$res = mysql_query("SELECT id,ed_objective_id,nr FROM adn_ed_subobjective");
		while($row = mysql_fetch_assoc($res))
		{
			$obj = $this->objectives_reverse[$row["ed_objective_id"]];
			$id = $obj["catalog_area"]." ".str_pad($obj["nr"], 2, "0", STR_PAD_LEFT).
				".".$row["nr"];
			$this->subobjectives[$id] = $row["id"];
		}
	}

	protected function parseFile($a_file)
	{
		$xml = $this->loadXMLFile($a_file);
		foreach($xml as $node)
		{
			$question = $this->getMultiQuestion($node);

			$id = $this->nextId("adn_ed_question");

			$obj_id = $question["catalog_area"]." ".str_pad($question["objective"], 2, "0",
				STR_PAD_LEFT);
			if(!isset($this->objectives[$obj_id]))
			{
				$this->debug("missing objective: ".$obj_id, "error");
				continue;
			}
			else
			{
				$obj_id = $this->objectives[$obj_id];
			}
			$sobj_id = NULL;
			if($question["subobjective"])
			{
				$sobj_id = $question["catalog_area"]." ".
					str_pad($question["objective"], 2, "0", STR_PAD_LEFT).".".
					$question["subobjective"];
				if(!isset($this->subobjectives[$sobj_id]))
				{
					$this->debug("missing subobjective: ".$sobj_id, "error");
					continue;
				}
				else
				{
					$sobj_id = $this->subobjectives[$sobj_id];
				}
			}

			$fields = array("id" => array("integer", $id),
				"ed_objective_id" => array("integer", $obj_id),
				"ed_subobjective_id" => array("integer", $sobj_id),
				"create_date" => array("text", date("Y-m-d H:i:s")),
				"create_user" => array("integer", 1),
				"last_update" => array("text", date("Y-m-d H:i:s")),
				"last_update_user" => array("integer", 1),
				"nr" => array("integer", $question["number"]),
				"title" => array("text", $question["title"]),
				"question" => array("text", $question["question"]),
				"status" => array("integer", 1),
				"padded_nr" => array("text", str_pad($question["number"], 2, "0", STR_PAD_LEFT))
				);

			if($this->insert("adn_ed_question", $fields))
			{
				$fields = array("ed_question_id" => array("integer", $id),
					"correct_answer" => array("text", $question["correct"]),
					"answer_1" => array("text", $question["answers"]["a"]),
					"answer_2" => array("text", $question["answers"]["b"]),
					"answer_3" => array("text", $question["answers"]["c"]),
					"answer_4" => array("text", $question["answers"]["d"]));

				$this->insert("adn_ed_question_mc", $fields);
			}
		}
	}

	protected function showIds()
	{
		ksort($this->ids);
		$this->debug(implode(",", array_keys($this->ids)));
		foreach($this->ids as $catalog_area => $objectives)
		{
			$this->debug($catalog_area);
			ksort($objectives);
			foreach($objectives as $objective => $subobjectives)
			{
				
				sort($subobjectives);
				$this->debug("- ".$objective.": ".implode(", ", array_unique($subobjectives)));
			}
		}
	}

	protected function getMultiQuestion($node)
	{
		$ids = $this->parseNumber(trim((string)$node->number));
		if($ids)
		{
			$question = array("catalog_area" => $ids["catalog_area"],
					"objective" => $ids["objective"],
					"subobjective" => $ids["subobjective"],
					"number"=> $ids["number"],
					"title" => trim((string)$node->title),
					"correct" => strtolower(trim((string)$node->correct)),
					"question" => trim((string)$node->question));
			foreach($node->answers as $answer)
			{
				$myanswer = trim((string)$answer->A);
				if(substr($myanswer, 0, 1) == ".")
				{
					$myanswer = substr($myanswer, 1);
				}
				$question["answers"]["a"] = $myanswer;
				$myanswer = trim((string)$answer->B);
				if(substr($myanswer, 0, 1) == ".")
				{
					$myanswer = substr($myanswer, 1);
				}
				$question["answers"]["b"] = $myanswer;
				$myanswer = trim((string)$answer->C);
				if(substr($myanswer, 0, 1) == ".")
				{
					$myanswer = substr($myanswer, 1);
				}
				$question["answers"]["c"] = $myanswer;
				$myanswer = trim((string)$answer->D);
				if(substr($myanswer, 0, 1) == ".")
				{
					$myanswer = substr($myanswer, 1);
				}
				$question["answers"]["d"] = $myanswer;
			}
			return $question;
		}
	}

	protected function parseNumber($a_number)
	{
		if(substr($a_number, 0, 1) == "A")
		{
			$a_number = substr($a_number, 2);
			$this->debug("corrected number to: ".$a_number, "warning");
		}

		// known "bugs" in source files
		if($a_number == "CM 304")
		{
			$a_number = "333 03.0-4";
		}
		else if($a_number == "CM 305")
		{
			$a_number = "333 03.0-5";
		}

		if(preg_match("/^([0-9]+)[ \.\-]([0-9]+)[ \.\-]([0-9]+)[ \.\-]([0-9]+)$/",
			trim($a_number), $parts))
		{
			$res["catalog_area"] = (int)$parts[1];
			$res["objective"] = (int)$parts[2];
			$res["subobjective"] = (int)$parts[3];
			$res["number"] = (int)$parts[4];
		
			$this->ids[$res["catalog_area"]][$res["objective"]][] = $res["subobjective"];
			return $res;
		}

		$this->debug("Invalid number: ".$a_number , "error");
		return false;
	}
}

?>