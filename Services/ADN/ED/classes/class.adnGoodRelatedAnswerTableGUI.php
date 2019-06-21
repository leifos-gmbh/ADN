<?php
/* Copyright (c) 2010 Leifos, GPL, see docs/LICENSE */

include_once("./Services/Table/classes/class.ilTable2GUI.php");

/**
 * ADN good related answer table GUI class
 *
 * List all good related answers (for question)
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @version $Id: class.adnGoodRelatedAnswerTableGUI.php 27873 2011-02-25 16:21:55Z jluetzen $
 *
 * @ingroup ServicesADN
 */
class adnGoodRelatedAnswerTableGUI extends ilTable2GUI
{
	protected $question_id; // [int]
	protected $show_butan_or_empty; // [bool]

	/**
	 * Constructor
	 *
	 * @param object $a_parent_obj parent gui object
	 * @param string $a_parent_cmd parent default command
	 * @param int $a_question_id parent question
	 */
	function __construct($a_parent_obj, $a_parent_cmd, $a_question_id)
	{
		global $ilCtrl, $lng;

		$this->question_id = (int)$a_question_id;

		// depending on question type
		include_once "Services/ADN/ED/classes/class.adnCaseQuestion.php";
		include_once "Services/ADN/ED/classes/class.adnCatalogNumbering.php";
		$question = new adnCaseQuestion($this->question_id);
		if(adnCatalogNumbering::isGasArea($question->getCatalogArea()))
		{
			$this->show_butan_or_empty = true;
		}

		$this->setId("adn_ed_gra");
		
		parent::__construct($a_parent_obj, $a_parent_cmd);

		$this->setTitle($lng->txt("adn_good_related_answers").": ".
			adnCaseQuestion::lookupName($this->question_id));
		
		$this->addColumn("", "", "1");
		$this->addColumn($this->lng->txt("adn_answer"), "answer");

		// depending on question type
		if($this->show_butan_or_empty)
		{
			$this->addColumn($this->lng->txt("adn_butan_or_empty"), "empty");
		}
		
		$this->addColumn($this->lng->txt("adn_goods_in_transit"), "goods");
		$this->addColumn($this->lng->txt("actions"));
		
		$this->setDefaultOrderField("answer");
		$this->setDefaultOrderDirection("asc");
		
		$this->setFormAction($ilCtrl->getFormAction($a_parent_obj));
		$this->setRowTemplate("tpl.answer_good_row.html", "Services/ADN/ED");
		
		$this->addMultiCommand("confirmAnswersDeletion", $lng->txt("delete"));

		$this->importData();
	}

	/**
	 * Import data from DB
	 */
	protected function importData()
	{
		global $lng;
		
		include_once "Services/ADN/ED/classes/class.adnGoodRelatedAnswer.php";
		$answers = adnGoodRelatedAnswer::getAllAnswers($this->question_id);

		// value mapping (to have correct sorting)
		if(sizeof($answers))
		{
			include_once "Services/ADN/ED/classes/class.adnGoodInTransit.php";

			foreach($answers as $idx => $item)
			{
				// depending on question type
				if($this->show_butan_or_empty)
				{
					switch($item["butan_or_empty"])
					{
						case adnGoodRelatedAnswer::TYPE_EMPTY:
							$type = $lng->txt("adn_empty");
							break;

						case adnGoodRelatedAnswer::TYPE_BUTAN:
							$type = $lng->txt("adn_butan");
							break;

						case adnGoodRelatedAnswer::TYPE_BUTAN_OR_EMPTY:
							$type = $lng->txt("adn_butan_or_empty");
							break;
					}
					$answers[$idx]["butan_or_empty"] = $type;
				}
				
				$goods = array();
				if($item["goods"])
				{
					foreach($item["goods"] as $good_id)
					{
						$goods[] = adnGoodInTransit::lookupName($good_id);
					}
				}
				$answers[$idx]["goods"] = implode("; ", $goods);

				$answers[$idx]["answer"] = nl2br($answers[$idx]["answer"]);
			}
		}
		
		$this->setData($answers);
		$this->setMaxCount(sizeof($answers));
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

		if(adnPerm::check(adnPerm::ED, adnPerm::WRITE))
		{
			$ilCtrl->setParameter($this->parent_obj, "cqa_id", $a_set["id"]);

			// ...edit
			$this->tpl->setCurrentBlock("action");
			$this->tpl->setVariable("TXT_CMD",
				$lng->txt("edit"));
			$this->tpl->setVariable("HREF_CMD",
				$ilCtrl->getLinkTarget($this->parent_obj, "editAnswer"));
			$this->tpl->parseCurrentBlock();

			$ilCtrl->setParameter($this->parent_obj, "cqa_id", "");
		}

		// properties
		$this->tpl->setVariable("VAL_ID", $a_set["id"]);
		$this->tpl->setVariable("VAL_ANSWER", $a_set["answer"]);
		$this->tpl->setVariable("VAL_GOODS", $a_set["goods"]);

		// depending on question type
		if($this->show_butan_or_empty)
		{
			$this->tpl->setCurrentBlock("butan");
			$this->tpl->setVariable("VAL_BUTAN_OR_EMPTY", $a_set["butan_or_empty"]);
			$this->tpl->parseCurrentBlock();
		}
	}
	
}
?>
