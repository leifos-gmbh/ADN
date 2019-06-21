<?php
/* Copyright (c) 2010 Leifos, GPL, see docs/LICENSE */

include_once("./Services/Table/classes/class.ilTable2GUI.php");

/**
 * ADN examination question table GUI class
 *
 * List all questions (not case/mc specific)
 *
 * @author Alex Killing <killing@leifos.de>
 * @version $Id: class.adnExaminationQuestionTableGUI.php 41742 2013-04-24 12:57:04Z jluetzen $
 *
 * @ingroup ServicesADN
 */
class adnExaminationQuestionTableGUI extends ilTable2GUI
{
	protected $case_questions; // [bool]
	protected $backups; // [array]

	/**
	 * Constructor
	 *
	 * @param object $a_parent_obj parent gui object
	 * @param string $a_parent_cmd parent default command
	 * @param bool $a_case_questions mc/case toggle
	 */
	function __construct($a_parent_obj, $a_parent_cmd, $a_case_questions = false)
	{
		global $ilCtrl, $lng;

		$this->case_questions = (bool)$a_case_questions;
		$this->setId("adn_ed_quest".(int)$this->case_questions);

		parent::__construct($a_parent_obj, $a_parent_cmd);

		if(adnPerm::check(adnPerm::ED, adnPerm::WRITE))
		{
			$this->addMultiCommand("activateQuestion", $lng->txt("adn_activate"));
			$this->addMultiCommand("deactivateQuestion", $lng->txt("adn_deactivate"));
			$this->addMultiCommand("confirmQuestionDeletion", $lng->txt("delete"));
			$this->addColumn("", "", "1");
		}

		$this->addColumn($this->lng->txt("adn_nr"), "nr");
		if(!$this->case_questions)
		{
			$this->addColumn($this->lng->txt("adn_catalog_area"));
		}
		else
		{
			$this->addColumn($this->lng->txt("adn_subject_area"));
		}
		$this->addColumn($this->lng->txt("adn_title"), "name");
		$this->addColumn($this->lng->txt("adn_question"), "question");
		$this->addColumn($this->lng->txt("adn_status"), "status");
		$this->addColumn($this->lng->txt("actions"));
		
		$this->setDefaultOrderField("nr");
		$this->setDefaultOrderDirection("asc");

		$this->setExternalSegmentation(true);
		$this->setExternalSorting(true);

		$this->initFilter();
		
		$this->setFormAction($ilCtrl->getFormAction($a_parent_obj));
		$this->setRowTemplate("tpl.examination_questions_row.html", "Services/ADN/ED");

		$size = $this->importData();

		if ($this->case_questions)
		{
			$this->setTitle($lng->txt("adn_case_questions").", ".
				$lng->txt("adn_total_active_questions").": ".$size);
		}
		else
		{
			$this->setTitle($lng->txt("adn_mc_questions").", ".
				$lng->txt("adn_total_active_questions").": ".$size);
		}
	}

	/**
	 * Import data from DB
	 *
	 * @return int number of active questions
	 */
	protected function importData()
	{
		global $lng, $ilDB, $ilCtrl;

		$this->determineOffsetAndOrder();
		
		include_once "Services/ADN/ED/classes/class.adnExaminationQuestion.php";
		$questions = adnExaminationQuestion::getAllQuestions($this->filter, $this->case_questions,
			false, $this->getOffset(), $this->getLimit(), $this->getOrderField(),
			$this->getOrderDirection());
		
		$this->setMaxCount($questions["overall"]);
		$active = $questions["active"];
		$questions = $questions["data"];

		// gather questions with backups
		include_once "Services/ADN/ED/classes/class.adnExaminationQuestion.php";
		$this->backups = adnExaminationQuestion::getAllQuestionsWithBackup();

		// markups to replace
		$markups = array("[u]", "[/u]", "[f]", "[/f]", "[h]", "[/h]", "[t]", "[/t]");
		$markups_html = array("<u>", "</u>", "<b>", "</b>", "<sup>", "</sup>", "<sub>", "</sub>");

		if(sizeof($questions))
		{
			// :TEMP: add mc answers
			if(!$this->case_questions)
			{
				$q_ids = array();
				foreach($questions as $item)
				{
					$q_ids[] = $item["id"];
				}
				$set = $ilDB->query("SELECT mc.*,q.qfile".
					" FROM adn_ed_question_mc mc".
					" JOIN adn_ed_question q ON (mc.ed_question_id = q.id)".
					" WHERE ".$ilDB->in("q.id", $q_ids, false, "integer"));
				$mc = array();
				while($row = $ilDB->fetchAssoc($set))
				{
					$mc[$row["ed_question_id"]] = $row;
				}
			}

			$img_path = ilUtil::getDataDir()."/adn/ed_question/";
				
			foreach($questions as $idx => $item)
			{
				$questions[$idx]["color"] =
					adnCatalogNumbering::getColorForArea($item["catalog_area"]);
				$questions[$idx]["status_caption"] = ($item["status"] == 0 ?
					$lng->txt("adn_inactive") : $lng->txt("adn_active"));
				$questions[$idx]["nr"] = $item["adn_number"];

				// catalog area and (sub-)objective to text
				$title = adnCatalogNumbering::getAreaTextRepresentation($item["catalog_area"]);
				if(!$this->case_questions)
				{
					$title = substr($title, 4);
				}
				$title .= "<br/>".$item["objective_title"];
				if($item["ed_subobjective_id"])
				{
					include_once "./Services/ADN/ED/classes/class.adnSubobjective.php";
					$title .= "<br />".$item["subobjective_title"];
				}
				$questions[$idx]["catalog_area"] = $title;

				$questions[$idx]["question"] = nl2br($questions[$idx]["question"]);

				// :TEMP: add mc answers
				if(!$this->case_questions)
				{
					$ilCtrl->setParameterByClass("adnmcquestiongui", "eq_id", $item["id"]);

					$mc_data = $mc[$item["id"]];
					$style_a = $style_b = $style_c = $style_d = "";

					// mark the correct answer
					${"style_".$mc_data["correct_answer"]} = " style=\"text-decoration:underline;\"";

					// answer images
					$img_1 = $img_2 = $img_3 = $img_4 = "";
					for($loop = 1; $loop < 5; $loop++)
					{
						if($mc_data["answer_".$loop."_file"])
						{
							$sizes = adnBaseGUI::resizeImage($img_path.$item["id"]."_".($loop+1));
							$ilCtrl->setParameterByClass("adnmcquestiongui", "img", $loop+1);
							${"img_".$loop} = "<img width=\"".$sizes["width"].
								"\" height=\"".$sizes["height"].
								"\" src=\"".$ilCtrl->getLinkTargetByClass("adnmcquestiongui",
								"showImage")."\" />";
						}
					}
					
					$text = "<ul>";
					$text .= "<li".$style_a.">A ".$img_1.$mc_data["answer_1"]."</li>";
					$text .= "<li".$style_b.">B ".$img_2.$mc_data["answer_2"]."</li>";
					$text .= "<li".$style_c.">C ".$img_3.$mc_data["answer_3"]."</li>";
					$text .= "<li".$style_d.">D ".$img_4.$mc_data["answer_4"]."</li>";
					$text .= "</ul>";

					$questions[$idx]["question"] .= $text;

					// question image
					if($mc_data["qfile"])
					{
						$sizes = adnBaseGUI::resizeImage($img_path.$item["id"]."_1");
						$ilCtrl->setParameterByClass("adnmcquestiongui", "img", 1);
						$questions[$idx]["question"] = "<img width=\"".$sizes["width"].
							"\" height=\"".$sizes["height"].
							"\" src=\"".$ilCtrl->getLinkTargetByClass("adnmcquestiongui",
							"showImage")."\" />".
							$questions[$idx]["question"];
					}
				}

				// translate mark-ups
				$questions[$idx]["question"] = str_replace($markups, $markups_html,
					$questions[$idx]["question"]);

				// translate mark-ups
				$questions[$idx]["name_rendered"] = str_replace($markups, $markups_html,
					$questions[$idx]["name"]);
			}
		}

		$this->setData($questions);

		return $active;
	}

	/**
	 * Init filter
	 */
	function initFilter()
	{
		global $lng;

		// catalog area
		include_once "Services/ADN/ED/classes/class.adnCatalogNumbering.php";
		if(!$this->case_questions)
		{
			$f = $this->addFilterItemByMetaType("catalog_area", self::FILTER_SELECT, false,
				$lng->txt("adn_catalog_area"));
			$options = array(""=>$lng->txt("adn_filter_all"))+adnCatalogNumbering::getMCAreas();
		}
		else
		{
			$f = $this->addFilterItemByMetaType("catalog_area", self::FILTER_SELECT, false,
				$lng->txt("adn_subject_area"));
			$options = array(""=>$lng->txt("adn_filter_all"))+adnCatalogNumbering::getCaseAreas();
		}
		$f->setOptions($options);
		$f->readFromSession();
		if($f->getValue() && !array_key_exists($f->getValue(), $options))
		{
			$f->setValue(null);
			$f->writeToSession();
		}
		$this->filter["catalog_area"] = $f->getValue();

		if(!$this->case_questions)
		{
			// nr objective from to
			$f = $this->addFilterItemByMetaType("objective_nr", self::FILTER_TEXT_RANGE, false,
				$lng->txt("adn_objective_nr"));
			$f->readFromSession();
			$this->filter["objective_nr"] = $f->getValue();

			// nr subobjective from to
			$f = $this->addFilterItemByMetaType("subobjective_nr", self::FILTER_TEXT_RANGE, false,
				$lng->txt("adn_subobjective_nr"));
			$f->readFromSession();
			$this->filter["subobjective_nr"] = $f->getValue();
		}
		else
		{
			// nr objective 
			$f = $this->addFilterItemByMetaType("objective_nr", self::FILTER_TEXT, false,
				$lng->txt("adn_objective_nr"));
			$f->readFromSession();
			$this->filter["objective_nr"] = $f->getValue();
		}

		// objective title
		$f = $this->addFilterItemByMetaType("objective_title", self::FILTER_TEXT, false,
			$lng->txt("adn_objective_title"));
		$f->readFromSession();
		$this->filter["objective_title"] = $f->getValue();

		// subobjective title
		$f = $this->addFilterItemByMetaType("subobjective_title", self::FILTER_TEXT, false,
			$lng->txt("adn_subobjective_title"));
		$f->readFromSession();
		$this->filter["subobjective_title"] = $f->getValue();

		if(!$this->case_questions)
		{
			// nr question from to
			$f = $this->addFilterItemByMetaType("question_nr", self::FILTER_TEXT_RANGE, false,
				$lng->txt("adn_question_nr"));
			$f->readFromSession();
			$this->filter["question_nr"] = $f->getValue();
		}
		else
		{
			// nr question
			$f = $this->addFilterItemByMetaType("question_nr", self::FILTER_TEXT, false,
				$lng->txt("adn_question_nr"));
			$f->readFromSession();
			$this->filter["question_nr"] = $f->getValue();
		}

		// question title
		$f = $this->addFilterItemByMetaType("question_title", self::FILTER_TEXT, false,
			$lng->txt("adn_question_title"));
		$f->readFromSession();
		$this->filter["question_title"] = $f->getValue();

		// status
		$f = $this->addFilterItemByMetaType("status", self::FILTER_SELECT, false,
			$lng->txt("adn_status"));
		$options = array(
			"2" => $lng->txt("adn_active"),
			"1" => $lng->txt("adn_inactive"),
			"" => $lng->txt("adn_filter_all")
			);
		$f->setOptions($options);
		$f->readFromSession();
		$this->filter["status"] = $f->getValue();
	}

	/**
	 * Fill table row
	 *
	 * @param array $a_set data array
	 */
	protected function fillRow($a_set)
	{
		global $lng, $ilCtrl;
		
		// actions...

		$ilCtrl->setParameter($this->parent_obj, "eq_id", $a_set["id"]);

		if(adnPerm::check(adnPerm::ED, adnPerm::WRITE))
		{
			if(!$a_set["status"])
			{
				// ...edit
				$this->tpl->setCurrentBlock("action");
				$this->tpl->setVariable("TXT_CMD", $lng->txt("adn_edit_examination_question"));
				if($this->case_questions)
				{
					$link = $ilCtrl->getLinkTarget($this->parent_obj, "editCaseQuestion");
				}
				else
				{
					$link = $ilCtrl->getLinkTarget($this->parent_obj, "editMCQuestion");
				}
				$this->tpl->setVariable("HREF_CMD", $link);
				$this->tpl->parseCurrentBlock();
			}

			// checkbox for deletion
			$this->tpl->setCurrentBlock("cbox");
			$this->tpl->setVariable("VAL_ID", $a_set["id"]);
			$this->tpl->parseCurrentBlock();
		}

		if(adnPerm::check(adnPerm::ED, adnPerm::READ))
		{
			// if inactive or read-only
			if(!adnPerm::check(adnPerm::ED, adnPerm::WRITE) || $a_set["status"])
			{
				// ... show
				$this->tpl->setCurrentBlock("action");
				$this->tpl->setVariable("TXT_CMD", $lng->txt("adn_show_details"));
				if($this->case_questions)
				{
					$link = $ilCtrl->getLinkTarget($this->parent_obj, "showCaseQuestion");
				}
				else
				{
					$link = $ilCtrl->getLinkTarget($this->parent_obj, "showMCQuestion");
				}
				$this->tpl->setVariable("HREF_CMD", $link);
				$this->tpl->parseCurrentBlock();
			}

			// ... backup
			if(in_array($a_set["id"], $this->backups))
			{
				$this->tpl->setCurrentBlock("action");
				$this->tpl->setVariable("TXT_CMD", $lng->txt("adn_show_backup"));
				if($this->case_questions)
				{
					$link = $ilCtrl->getLinkTarget($this->parent_obj, "showCaseBackup");
				}
				else
				{
					$link = $ilCtrl->getLinkTarget($this->parent_obj, "showMCBackup");
				}
				$this->tpl->setVariable("HREF_CMD", $link);
				$this->tpl->parseCurrentBlock();
			}
		}

		$ilCtrl->setParameter($this->parent_obj, "eq_id", "");

		// properties
		$this->tpl->setVariable("VAL_TITLE", $a_set["name_rendered"]);
		$this->tpl->setVariable("VAL_QUESTION", $a_set["question"]);
		$this->tpl->setVariable("VAL_NR", $a_set["nr"]);
		$this->tpl->setVariable("VAL_STATUS", $a_set["status_caption"]);
		$this->tpl->setVariable("VAL_CATALOG_AREA", $a_set["catalog_area"]);

		$this->tpl->setVariable("COLOR", $a_set["color"]);

		$this->legend["<span style=\"background-color:".$a_set["color"].
			"; border: 1px solid grey;\">&nbsp;&nbsp;&nbsp;</span>"] = $a_set["catalog_area"];

		// separation of objectives
		if ($this->last_objective_nr != "" &&
			$this->last_objective_nr != $a_set["ed_objective_id"])
		{
			$this->tpl->setVariable("CELL_STYLE",
				'style="border-top-width:4px;"');
			$this->tpl->setVariable("CELL_ST",
				'border-top-width:4px;');
		}
		$this->last_objective_nr = $a_set["ed_objective_id"];
	}
}

?>