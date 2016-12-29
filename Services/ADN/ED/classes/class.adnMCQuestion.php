<?php
/* Copyright (c) 2010 Leifos, GPL, see docs/LICENSE */

include_once "Services/ADN/ED/classes/class.adnExaminationQuestion.php";

/**
 * MC question application class
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.de>
 * @version $Id: class.adnMCQuestion.php 28314 2011-04-01 13:27:14Z jluetzen $
 *
 * @ingroup ServicesADN
 */
class adnMCQuestion extends adnExaminationQuestion
{
	protected $correct; // [string]
	protected $answer_a_text; // [string]
	protected $answer_b_text; // [string]
	protected $answer_c_text; // [string]
	protected $answer_d_text; // [string]

	/**
	 * Set correct answer
	 *
	 * @param string $a_correct
	 */
	public function setCorrectAnswer($a_correct)
	{
		$this->correct = (string)$a_correct;
	}

	/**
	 * Get correct answer
	 *
	 * @return string
	 */
	public function getCorrectAnswer()
	{
		return $this->correct;
	}

	/**
	 * Set answer A
	 *
	 * @param string $a_text
	 * @param string $a_image
	 */
	public function setAnswerA($a_text, $a_image = false)
	{
		$this->setAnswer("A", $a_text, $a_image);
	}

	/**
	 * Get answer A
	 *
	 * @return array
	 */
	public function getAnswerA()
	{
		return $this->getAnswer("A");
	}

	/**
	 * Set answer B
	 *
	 * @param string $a_text
	 * @param string $a_image
	 */
	public function setAnswerB($a_text, $a_image = false)
	{
		$this->setAnswer("B", $a_text, $a_image);
	}

	/**
	 * Get answer B
	 *
	 * @return array
	 */
	public function getAnswerB()
	{
		return $this->getAnswer("B");
	}

	/**
	 * Set answer C
	 *
	 * @param string $a_text
	 * @param string $a_image
	 */
	public function setAnswerC($a_text, $a_image = false)
	{
		$this->setAnswer("C", $a_text, $a_image);
	}

	/**
	 * Get answer C
	 *
	 * @return array
	 */
	public function getAnswerC()
	{
		return $this->getAnswer("C");
	}

	/**
	 * Set answer D
	 *
	 * @param string $a_text
	 * @param string $a_image
	 */
	public function setAnswerD($a_text, $a_image = false)
	{
		$this->setAnswer("D", $a_text, $a_image);
	}

	/**
	 * Get answer D
	 *
	 * @return array
	 */
	public function getAnswerD()
	{
		return $this->getAnswer("D");
	}

	/**
	 * Set answer data
	 *
	 * @param string $a_id
	 * @param string $a_text
	 * @param string $a_image
	 */
	protected function setAnswer($a_id, $a_text, $a_image = false)
	{
		$this->{"answer_".strtolower($a_id)."_text"} = $a_text;
		if($a_image)
		{
			$map = array("A" => 2, "B" => 3, "C" => 4, "D" => 5);
			$this->setFileName($a_image, $map[$a_id]);
		}
	}

	/**
	 * Get answer data
	 *
	 * @param string $a_id
	 * @return array
	 */
	protected function getAnswer($a_id)
	{
		$map = array("A" => 2, "B" => 3, "C" => 4, "D" => 5);

		return array("text" => $this->{"answer_".strtolower($a_id)."_text"},
			"image" => $this->getFileName($map[$a_id]));
	}

	/**
	 * Set number
	 *
	 * @param int $a_number
	 */
	public function setNumber($a_number)
	{
		$this->number = (int)$a_number;
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

		$res = $ilDB->query("SELECT correct_answer,answer_1,answer_1_file,answer_2,answer_2_file,".
			"answer_3,answer_3_file,answer_4,answer_4_file".
			" FROM adn_ed_question_mc".
			" WHERE ed_question_id = ".$ilDB->quote($this->getId(), "integer"));
		$set = $ilDB->fetchAssoc($res);
		$this->setCorrectAnswer($set["correct_answer"]);
		$this->setAnswerA($set["answer_1"]);
		$this->setAnswerB($set["answer_2"]);
		$this->setAnswerC($set["answer_3"]);
		$this->setAnswerD($set["answer_4"]);
		$this->setFileName($set["answer_1_file"], 2);
		$this->setFileName($set["answer_2_file"], 3);
		$this->setFileName($set["answer_3_file"], 4);
		$this->setFileName($set["answer_4_file"], 5);

		parent::read();
	}

	/**
	 * Convert properties to DB fields
	 *
	 * @return array (ed_question_id, correct_answer, answer_1, answer_1_file, answer_2, answer_2_file,
	 * answer_3, answer_3_file, answer_4, answer_4_file)
	 */
	protected function MCPropertiesToFields()
	{
		$fields = array("ed_question_id" => array("integer", $this->getId()),
			"correct_answer" => array("text", $this->getCorrectAnswer()));

		$answer = $this->getAnswerA();
		$fields["answer_1"] = array("text", $answer["text"]);
		$fields["answer_1_file"] = array("text", $answer["image"]);
		$answer = $this->getAnswerB();
		$fields["answer_2"] = array("text", $answer["text"]);
		$fields["answer_2_file"] = array("text", $answer["image"]);
		$answer = $this->getAnswerC();
		$fields["answer_3"] = array("text", $answer["text"]);
		$fields["answer_3_file"] = array("text", $answer["image"]);
		$answer = $this->getAnswerD();
		$fields["answer_4"] = array("text", $answer["text"]);
		$fields["answer_4_file"] = array("text", $answer["image"]);
			
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

		parent::save();
	
		$fields = $this->MCPropertiesToFields();

		for($loop = 2; $loop < 6; $loop++)
		{
			if($this->getUploadedFile($loop))
			{
				if(!$this->saveFile($this->getUploadedFile($loop), $id."_".$loop))
				{
					$fields["answer_".($loop-1)."_file"] = array("text", "");
				}
			}
		}
		
		$ilDB->insert("adn_ed_question_mc", $fields);

		return $this->getId();
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

		$fields = $this->MCPropertiesToFields();

		for($loop = 2; $loop < 6; $loop++)
		{
			if($this->getUploadedFile($loop))
			{
				if(!$this->saveFile($this->getUploadedFile($loop), $id."_".$loop))
				{
					$fields["answer_".($loop-1)."_file"] = array("text", "");
				}
			}
		}

		$ilDB->update("adn_ed_question_mc", $fields, array("ed_question_id"=>array("integer", $id)));

		parent::update();

		return true;
	}

	/**
	 * Delete from DB
	 *
	 * @param bool $a_force do not use archived flag in any case
	 * @return bool
	 */
	public function delete($a_force = false)
	{
		global $ilDB;

		$id = $this->getId();
		if($id)
		{
			// U.PV.4.4: always set flag?
			if(!$a_force)
			{
				$this->setArchived(true);
				$this->update();
			}
			else
			{
				$this->removeFile($id."_2");
				$this->removeFile($id."_3");
				$this->removeFile($id."_4");
				$this->removeFile($id."_5");

				$ilDB->manipulate("DELETE FROM adn_ed_question_mc".
					" WHERE ed_question_id = ".$ilDB->quote($id, "integer"));
				
				parent::delete();
			}
			return true;
		}
	}

	/**
	 * Lookup correct answer
	 *
	 * @param int $a_q_id question id
	 * @return int correct answer
	 */
	function lookupCorrectAnswer($a_q_id)
	{
		global $ilDB;

		$res = $ilDB->query("SELECT correct_answer ".
			" FROM adn_ed_question_mc".
			" WHERE ed_question_id = ".$ilDB->quote($a_q_id, "integer"));

		if ($rec = $ilDB->fetchAssoc($res))
		{
			return $rec["correct_answer"];
		}
		return false;
	}
}

?>