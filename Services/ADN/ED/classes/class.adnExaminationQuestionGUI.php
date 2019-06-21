<?php
/* Copyright (c) 2010 Leifos, GPL, see docs/LICENSE */

/**
 * ADN examination question GUI class
 *
 * This is just used as a base class and not called directly
 *
 * @author Alex Killing <killing@leifos.de>
 * @version $Id: class.adnExaminationQuestionGUI.php 28345 2011-04-04 09:53:10Z jluetzen $
 *
 * @ingroup ServicesADN
 */
class adnExaminationQuestionGUI
{
	/**
	 * Set tabs
	 *
	 * @param string $a_activate
	 */
	function setTabs($a_activate)
	{
		global $ilTabs, $lng, $txt, $ilCtrl;

		$ilTabs->addTab("mc_questions",
		$lng->txt("adn_mc_questions"),
			$ilCtrl->getLinkTargetByClass("adnmcquestiongui", "listMCQuestions"));

		$ilTabs->addTab("case_questions",
		$lng->txt("adn_case_questions"),
			$ilCtrl->getLinkTargetByClass("adncasequestiongui", "listCaseQuestions"));

		$ilTabs->activateTab($a_activate);
	}

	/**
	 * Activate question
	 */
	protected function activateQuestion()
	{
		$this->activateQuestionHelper(true);
	}

	/**
	 * Deactivate question
	 */
	protected function deactivateQuestion()
	{
		$this->activateQuestionHelper(false);
	}

	/**
	 * Toggle question status
	 *
	 * @param bool $a_status
	 */
	protected function activateQuestionHelper($a_status)
	{
		global $ilCtrl, $tpl, $lng;

		if(get_class($this) == "adnMCQuestionGUI")
		{
			$list_cmd = "listMCQuestions";
		}
		else
		{
			$list_cmd = "listCaseQuestions";
		}

		// check whether at least one item has been seleced
		if (!is_array($_POST["question_id"]) || count($_POST["question_id"]) == 0)
		{
			ilUtil::sendFailure($lng->txt("no_checkbox"), true);
			$ilCtrl->redirect($this, $list_cmd);
		}
		else
		{
			include_once("./Services/ADN/ED/classes/class.adnExaminationQuestion.php");
			
			// list objects that should be deleted
			foreach ($_POST["question_id"] as $id)
			{
				$question = new adnExaminationQuestion($id);
				$question->setStatus($a_status);
				$question->update();
			}

			if($a_status)
			{
				$mess = $lng->txt("adn_questions_activated");
			}
			else
			{
				$mess = $lng->txt("adn_questions_deactivated");
			}
			ilUtil::sendSuccess($mess, true);
			$ilCtrl->redirect($this, $list_cmd);
		}
	}

	/**
	 * Get form with basic fields
	 *
	 * @param int $a_catalog_area
	 * @param string $a_mode
	 * @return ilPropertyForm
	 */
	protected function initBaseForm($a_catalog_area, $a_mode = "edit")
	{
		global $lng, $ilCtrl;
		
		// get form object and add input fields
		include_once("Services/Form/classes/class.ilPropertyFormGUI.php");
		$form = new ilPropertyFormGUI();
		$form->setId("adn_form");

		// objective (foreign key)
		$options = array();
		include_once "Services/ADN/ED/classes/class.adnObjective.php";
		include_once "./Services/ADN/ED/classes/class.adnSubobjective.php";
		$obj = $sobj = null;
		if($this->question)
		{
			$obj = $this->question->getObjective();
			$sobj = $this->question->getSubobjective();
		}
		$objectives = adnObjective::getObjectivesSelect($a_catalog_area, null, false, $obj);
		if($objectives)
		{
			foreach($objectives as $id => $name)
			{
				$options["ob_".$id] = $name;

				$subobjectives = adnSubobjective::getSubobjectivesSelect($id, $sobj);
				if($subobjectives)
				{
					foreach($subobjectives as $sid => $sname)
					{
						$options["sob_".$sid] = " - ".$sname;
					}
				}
			}
		}
		$objective = new ilSelectInputGUI($lng->txt("adn_objective"), "objective");
		$objective->setOptions($options);
		$objective->setRequired(true);
		$form->addItem($objective);

		// mc: numeric
		if(get_class($this) == "adnMCQuestionGUI")
		{
			$nr = new ilNumberInputGUI($lng->txt("adn_nr"), "nr");
			$nr->setRequired(true);
			$nr->setSize(5);
			$nr->setMaxLength(3);
			$nr->setMaxValue(99);
			$form->addItem($nr);
		}
		// case: alpha-numeric
		else
		{
			$nr = new ilTextInputGUI($lng->txt("adn_nr"), "nr");
			$nr->setRequired(true);
			$nr->setSize(5);
			$nr->setMaxLength(5);
			$form->addItem($nr);
		}

		// title
		$title = new ilTextInputGUI($lng->txt("adn_title"), "title");
		$title->setMaxLength(200);
		if(get_class($this) == "adnMCQuestionGUI")
		{
			$title->setRequired(true);
		}
		$title->setSpecialCharacters(true, true);
		$title->setFormId($form->getId());
		$form->addItem($title);

		// question
		$question = new ilTextAreaInputGUI($lng->txt("adn_question"), "question");
		$question->setCols(80);
		$question->setRows(5);
		$question->setRequired(true);
		$question->setSpecialCharacters(true);
		$question->setFormId($form->getId());
		$form->addItem($question);

		// question image
		$image = new ilImageFileInputGUI($lng->txt("adn_image_for_question"), "quest_image");
		$form->addItem($image);

		// status
		$options = array(
			"1" => $lng->txt("adn_active"),
			"0" => $lng->txt("adn_inactive"),
		);
		$status = new ilSelectInputGUI($lng->txt("status"), "status");
		$status->setOptions($options);
		$status->setInfo($lng->txt(""));
		$form->addItem($status);

		if($a_mode != "create")
		{
			$obj = "sob_".$this->question->getSubobjective();
			if($obj == "sob_")
			{
				$obj = "ob_".$this->question->getObjective();
			}
			$objective->setValue($obj);
			$nr->setValue($this->question->getNumber());
			$title->setValue($this->question->getName());
			$question->setValue($this->question->getQuestion());
			$status->setValue((int)$this->question->getStatus());

			$file = $this->question->getFilePath().$this->question->getId()."_1";
			if(file_exists($file))
			{
				$ilCtrl->setParameter($this, "img", 1);
				$image->setImage($ilCtrl->getLinkTarget($this, "showImage"));
				$ilCtrl->setParameter($this, "img", "");

				$image->setAlt($this->question->getFileName(1));
			}
		}
		else
		{
			$area = new ilHiddenInputGUI("catalog_area");
			$area->setValue($a_catalog_area);
			$form->addItem($area);
		}

		return $form;
	}

	/**
	 * Add last change information to form
	 *
	 * @param ilPropertyFormGUI $a_form
	 * @param string $a_mode
	 */
	protected function addFormLastChange(ilPropertyFormGUI $a_form, $a_mode = "edit")
	{
		global $lng;
		
		$sh = new ilFormSectionHeaderGUI();
		$sh->setTitle($lng->txt("adn_last_change"));
		$a_form->addItem($sh);

		if($a_mode == "edit")
		{
			$comment = new ilTextAreaInputGUI($lng->txt("adn_last_change_comment"), "comment");
			$comment->setCols(80);
			$comment->setRows(5);
			$a_form->addItem($comment);
		}

		$date = $this->question->getLastUpdate();
		if(!$date->isNull())
		{
			$date = ilDatePresentation::formatDate($date);
			$last_update = new ilNonEditableValueGUI($lng->txt("adn_last_change_date"));
			$last_update->setValue($date);
			$a_form->addItem($last_update);
		}

		$user = $this->question->getLastUpdateUser();
		if($user)
		{
			$user = ilObjUser::_lookupName($user);
			$user = $user["lastname"].", ".$user["firstname"]." [".$user["login"]."]";
		}
		if($user)
		{
			$last_update_user = new ilNonEditableValueGUI($lng->txt("adn_last_change_user"));
			$last_update_user->setValue($user);
			$a_form->addItem($last_update_user);
		}

		$comment = $this->question->getComment();
		if($comment)
		{
			$last_comment = new ilNonEditableValueGUI($lng->txt("adn_last_change_comment"));
			$last_comment->setValue($comment);
			$a_form->addItem($last_comment);
		}

		$date = $this->question->getStatusDate();
		if(!$date->isNull())
		{
			$date = ilDatePresentation::formatDate($date);
			$last_status = new ilNonEditableValueGUI($lng->txt("adn_last_change_status"));
			$last_status->setValue($date);
			$a_form->addItem($last_status);
		}
	}

	/**
	 * Import form values to question
	 *
	 * @param adnExaminationQuestion $question
	 * @param ilPropertyFormGUI $a_form
	 * @return bool
	 */
	protected function setFormValues(adnExaminationQuestion $question, ilPropertyFormGUI $a_form)
	{
		global $lng;

		// objective or subobjective?
		$obj = $a_form->getInput("objective");
		if(substr($obj, 0, 3) == "ob_")
		{
			$question->setObjective(substr($obj, 3));
		}
		else
		{
			$question->setSubobjective(substr($obj, 4));
		}

		$nr = $a_form->getInput("nr");
		if($question->isNumberUnique($nr))
		{
			$question->setNumber($nr);
			$question->setName($a_form->getInput("title"));
			$question->setQuestion($a_form->getInput("question"));
			$question->setStatus($a_form->getInput("status"));
			$question->setComment($a_form->getInput("comment"));

			// image handling
			if($a_form->getInput("quest_image_delete"))
			{
				$id = $question->getId();
				if($id)
				{
					$question->removeFile($id."_1");
					$question->setFileName("", 1);
				}
			}
			else
			{
				$file = $a_form->getInput("quest_image");
				$question->importFile($file["tmp_name"], $file["name"], 1);
			}
			
			return true;
		}
		else
		{
			ilUtil::sendFailure($lng->txt("form_input_not_valid"));
			$a_form->getItemByPostVar("nr")->setAlert($lng->txt("adn_question_unique_number"));
		}

		return false;
	}

	/**
	 * Show/Deliver question images
	 */
	protected function showImage()
	{
		if($this->question)
		{
			$id = (string)$_REQUEST["img"];

			$file = $this->question->getFilePath().$this->question->getId()."_".$id;
			if(file_exists($file))
			{
				ilUtil::deliverFile($file, $this->question->getFileName(1));
			}
		}
	}
	
	/**
	 * Replace bb code with HTML in question/answer text
	 *
	 * @param string $a_text text
	 * @return string text with replaced tags
	 */
	static function replaceBBCode($a_text)
	{
		// markups to replace
		$markups = array("[u]", "[/u]", "[f]", "[/f]", "[h]", "[/h]", "[t]", "[/t]");
		$markups_html = array("<u>", "</u>", "<b>", "</b>", "<sup>", "</sup>", "<sub>", "</sub>");
		
		return str_replace($markups, $markups_html, $a_text);
	}
	
}

?>