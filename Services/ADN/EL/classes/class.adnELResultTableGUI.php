<?php
/* Copyright (c) 2010 Leifos, GPL, see docs/LICENSE */

include_once("./Services/Table/classes/class.ilTable2GUI.php");
include_once("./Services/ADN/ED/classes/class.adnMCQuestion.php");

/**
 * ADN E-Learning result table GUI class. This table lists all answers given by the user
 * and the correct answer for each question.
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id: class.adnELResultTableGUI.php 27884 2011-02-27 21:01:07Z akill $
 *
 * @ingroup ServicesADN
 */
class adnELResultTableGUI extends ilTable2GUI
{
	/**
	 * Constructor
	 *
	 * @param object $a_parent_obj parent gui object
	 * @param string $a_parent_cmd parent default command
	 * @param array $a_questions questions
	 * @param array $a_answers answers
	 */
	function __construct($a_parent_obj, $a_parent_cmd, $a_questions, $a_answers)
	{
		global $ilCtrl, $lng;

		$this->questions = array();
		$cnt = 1;
		foreach ($a_questions as $q)
		{
			$this->questions[] = array("nr" => $cnt++,
				"q_id" => $q);
		}
		$this->answers = $a_answers;

		parent::__construct($a_parent_obj, $a_parent_cmd);

		$this->setData($this->questions);

		$this->setTitle($lng->txt("adn_questions"));
		$this->addColumn($this->lng->txt("adn_nr"));
		$this->addColumn($this->lng->txt("adn_question"));
		$this->addColumn($this->lng->txt("adn_correct"));
		
		$this->setLimit(100);

		$this->setFormAction($ilCtrl->getFormAction($a_parent_obj));
		$this->setRowTemplate("tpl.test_questions_row.html", "Services/ADN/EL");
	}
	
	/**
	 * Fill table row
	 *
	 * @param array $a_set data array
	 */
	protected function fillRow($a_set)
	{
		global $lng, $ilCtrl;

		$markups = array("[u]", "[/u]", "[f]", "[/f]", "[h]", "[/h]", "[t]", "[/t]");
		$markups_html = array("<u>", "</u>", "<b>", "</b>", "<sup>", "</sup>", "<sub>", "</sub>");

		$question = new adnMCQuestion($a_set["q_id"]);

		$map = array(1 => "A", 2 => "B", 3 => "C", 4 => "D");

		// check if answer is given
		if ($this->answers[$a_set["q_id"]] > 0)
		{
			$g = $map[$this->answers[$a_set["q_id"]]];
			$m = "getAnswer".$g;
			$q = $question->$m();
			$this->tpl->setVariable("YOUR_ANSWER", $g.": ".
				$q["text"]);
		}
		else
		{
			$this->tpl->setVariable("YOUR_ANSWER", "-");
		}
		
		// properties
		$this->tpl->setVariable("VAL_NR", $a_set["nr"]);
		$this->tpl->setVariable("VAL_QUESTION",
			str_replace($markups, $markups_html, $question->getQuestion()));

		$a = strtoupper($question->getCorrectAnswer());
		$m = "getAnswer".$a;
		$q = $question->$m();
		$this->tpl->setVariable("CORRECT_ANSWER", $a.": ".
			$q["text"]);

		$this->tpl->setVariable("TXT_YOUR_ANSWER",
			str_replace($markups, $markups_html, $lng->txt("adn_your_answer")));
		$this->tpl->setVariable("TXT_CORRECT_ANSWER",
			str_replace($markups, $markups_html, $lng->txt("adn_correct_answer")));
		if ($a == $g)
		{
			$this->tpl->setVariable("VAL_CORRECT", "X");
		}
	}
}

?>