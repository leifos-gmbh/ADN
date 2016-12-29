<?php
/* Copyright (c) 2010 Leifos, GPL, see docs/LICENSE */

/**
 * ADN subobjective GUI class
 *
 * Subobjective list, forms and persistence (objective is mandatory)
 *
 * @author Alex Killing <killing@leifos.de>
 * @version $Id: class.adnSubobjectiveGUI.php 27874 2011-02-25 16:36:28Z jluetzen $
 *
 * @ingroup ServicesADN
 */
class adnSubobjectiveGUI
{
	// current subobjective object
	protected $subobjective = null;

	/**
	 * Constructor
	 */
	public function __construct()
	{
		global $ilCtrl;

		$this->objective_id = (int)$_REQUEST["ob_id"];

		// save subobjective ID through requests
		$ilCtrl->saveParameter($this, array("sob_id"));
		
		$this->readSubobjective();
	}

	/**
	 * Execute command
	 */
	public function executeCommand()
	{
		global $ilCtrl;

		$next_class = $ilCtrl->getNextClass();

		// forward command to next gui class in control flow
		switch ($next_class)
		{
			// no next class:
			// this class is responsible to process the command
			default:
				$cmd = $ilCtrl->getCmd("listSubobjectives");

				switch ($cmd)
				{
					// commands that need read permission
					case "listSubobjectives":
						if(adnPerm::check(adnPerm::ED, adnPerm::READ))
						{
							$this->$cmd();
						}
						break;

					// commands that need write permission
					case "addSubobjective":
					case "saveSubobjective":
					case "editSubobjective":
					case "updateSubobjective":
					case "confirmSubobjectiveDeletion":
					case "deleteSubobjective":
						if(adnPerm::check(adnPerm::ED, adnPerm::WRITE))
						{
							$this->$cmd();
						}
						break;

				}
				break;
		}
	}

	/**
	 * Read subobjective
	 */
	protected function readSubobjective()
	{
		if ((int)$_GET["sob_id"] > 0)
		{
			include_once("./Services/ADN/ED/classes/class.adnSubobjective.php");
			$this->subobjective = new adnSubobjective((int)$_GET["sob_id"]);
		}
	}

	/**
	 * List all subobjectives
	 */
	protected function listSubobjectives()
	{
		global $tpl, $ilToolbar, $ilCtrl, $lng, $ilTabs;

		$ilTabs->setBackTarget($lng->txt("back"),
			$ilCtrl->getLinkTargetByClass("adnObjectiveGUI", "listObjectives"));

		if(adnPerm::check(adnPerm::ED, adnPerm::WRITE))
		{
			$ilToolbar->addButton($lng->txt("adn_add_subobjective"),
				$ilCtrl->getLinkTarget($this, "addSubobjective"));
		}

		// table of objectives
		include_once("./Services/ADN/ED/classes/class.adnSubobjectiveTableGUI.php");
		$table = new adnSubobjectiveTableGUI($this, "listSubobjectives", $this->objective_id);

		// output table
		$tpl->setContent($table->getHTML());
	}

	/**
	 * Add new subobjective form
	 *
	 * @param ilPropertyFormGUI $a_form
	 */
	protected function addSubobjective(ilPropertyFormGUI $a_form = null)
	{
		global $tpl;

		if(!$a_form)
		{
			$a_form = $this->initSubobjectiveForm("create");
		}
		$tpl->setContent($a_form->getHTML());
	}

	/**
	 * Edit subobjective form
	 *
	 * @param ilPropertyFormGUI $a_form
	 */
	protected function editSubobjective(ilPropertyFormGUI $a_form = null)
	{
		global $tpl;

		if(!$a_form)
		{
			$a_form = $this->initSubobjectiveForm("edit");
		}
		$tpl->setContent($a_form->getHTML());
	}

	/**
	 * Init subobjective form.
	 *
	 * @param string $a_mode form mode ("create" | "edit")
	 * @return ilPropertyFormGUI $form
	 */
	protected function initSubobjectiveForm($a_mode = "edit")
	{
		global $lng, $ilCtrl, $ilTabs;

		$ilTabs->setBackTarget($lng->txt("back"),
				$ilCtrl->getLinkTarget($this, "listSubobjectives"));

		// get form object and add input fields
		include_once("Services/Form/classes/class.ilPropertyFormGUI.php");
		$form = new ilPropertyFormGUI();

		include_once "Services/ADN/ED/classes/class.adnObjective.php";
		$obj = new adnObjective($this->objective_id);
		if($obj->getType() != adnObjective::TYPE_CASE)
		{
			$number = new ilNumberInputGUI($lng->txt("adn_number"), "number");
			$number->setRequired(true);
			$number->setSize(10);
			$number->setMaxLength(50);
			$form->addItem($number);
		}
		else
		{
			$number = new ilTextInputGUI($lng->txt("adn_number"), "number");
			$number->setRequired(true);
			$number->setSize(5);
			$number->setMaxLength(5);
			$form->addItem($number);
		}

		$name = new ilTextInputGUI($lng->txt("adn_title"), "name");
		$name->setRequired(true);
		$name->setMaxLength(100);
		$form->addItem($name);

		$topic = new ilTextInputGUI($lng->txt("adn_topic"), "topic");
		$topic->setMaxLength(200);
		$form->addItem($topic);

		include_once "Services/ADN/ED/classes/class.adnObjective.php";
		$objective = new adnObjective($this->objective_id);
		$objective = $objective->buildADNNumber()." ".$objective->getName();

		if ($a_mode == "create")
		{
			// creation: save/cancel buttons and title
			$form->addCommandButton("saveSubobjective", $lng->txt("save"));
			$form->addCommandButton("listSubobjectives", $lng->txt("cancel"));
			$form->setTitle($lng->txt("adn_add_subobjective").": ".$objective);
		}
		else
		{
			$name->setValue($this->subobjective->getName());
			$topic->setValue($this->subobjective->getTopic());
			$number->setValue($this->subobjective->getNumber());

			// editing: update/cancel buttons and title
			$form->addCommandButton("updateSubobjective", $lng->txt("save"));
			$form->addCommandButton("listSubobjectives", $lng->txt("cancel"));
			$form->setTitle($lng->txt("adn_edit_subobjective").": ".$objective);
		}

		$form->setFormAction($ilCtrl->getFormAction($this));

		return $form;
	}

	/**
	 * Create subobjective
	 */
	protected function saveSubobjective()
	{
		global $tpl, $lng, $ilCtrl;

		$form = $this->initSubobjectiveForm("create");

		// check input
		if ($form->checkInput())
		{
			// input ok: create new subobjective
			include_once("./Services/ADN/ED/classes/class.adnSubobjective.php");
			$subobjective = new adnSubobjective();
			$subobjective->setObjective($this->objective_id);
			$subobjective->setNumber($form->getInput("number"));
			$subobjective->setName($form->getInput("name"));
			$subobjective->setTopic($form->getInput("topic"));

			if($subobjective->isUniqueNumber())
			{
				if($subobjective->save())
				{
					// show success message and return to list
					ilUtil::sendSuccess($lng->txt("adn_subobjective_created"), true);
					$ilCtrl->redirect($this, "listSubobjectives");
				}
			}
			else
			{
				$form->getItemByPostVar("number")->setAlert($lng->txt("adn_unique_number"));
			}
		}

		// input not valid: show form again
		$form->setValuesByPost();
		$this->addSubobjective($form);
	}

	/**
	 * Update subobjective
	 */
	protected function updateSubobjective()
	{
		global $lng, $ilCtrl, $tpl;

		$form = $this->initSubobjectiveForm("edit");

		// check input
		if ($form->checkInput())
		{
			// perform update
			$this->subobjective->setNumber($form->getInput("number"));
			$this->subobjective->setName($form->getInput("name"));
			$this->subobjective->setTopic($form->getInput("topic"));

			if($this->subobjective->isUniqueNumber())
			{
				if($this->subobjective->update())
				{
					// show success message and return to list
					ilUtil::sendSuccess($lng->txt("adn_subobjective_updated"), true);
					$ilCtrl->redirect($this, "listSubobjectives");
				}
			}
			else
			{
				$form->getItemByPostVar("number")->setAlert($lng->txt("adn_unique_number"));
			}
		}

		// input not valid: show form again
		$form->setValuesByPost();
		$this->editSubobjective($form);
	}

	/**
	 * Confirm subobjective deletion
	 */
	protected function confirmSubobjectiveDeletion()
	{
		global $ilCtrl, $tpl, $lng, $ilTabs;

		// check whether at least one item has been seleced
		if (!is_array($_POST["subobjective_id"]) || count($_POST["subobjective_id"]) == 0)
		{
			ilUtil::sendFailure($lng->txt("no_checkbox"), true);
			$ilCtrl->redirect($this, "listSubobjectives");
		}
		else
		{
			$ilTabs->setBackTarget($lng->txt("back"),
				$ilCtrl->getLinkTarget($this, "listSubobjectives"));

			// display confirmation message
			include_once("./Services/Utilities/classes/class.ilConfirmationGUI.php");
			$cgui = new ilConfirmationGUI();
			$cgui->setFormAction($ilCtrl->getFormAction($this));
			$cgui->setHeaderText($lng->txt("adn_sure_delete_subobjectives"));
			$cgui->setCancel($lng->txt("cancel"), "listSubobjectives");
			$cgui->setConfirm($lng->txt("delete"), "deleteSubobjective");

			// list objects that should be deleted
			foreach ($_POST["subobjective_id"] as $i)
			{
				include_once("./Services/ADN/ED/classes/class.adnSubobjective.php");
				$cgui->addItem("subobjective_id[]", $i, adnSubobjective::lookupName($i));
			}

			$tpl->setContent($cgui->getHTML());
		}
	}

	/**
	 * Delete subobjective
	 */
	protected function deleteSubobjective()
	{
		global $ilCtrl, $lng;

		include_once("./Services/ADN/ED/classes/class.adnSubobjective.php");

		$has_failed = false;
		if (is_array($_POST["subobjective_id"]))
		{
			foreach ($_POST["subobjective_id"] as $i)
			{
				$subobjective = new adnSubobjective($i);
				if(!$subobjective->delete())
				{
					$has_failed = true;
				}
			}
		}
		if(!$has_failed)
		{
			ilUtil::sendSuccess($lng->txt("adn_subobjective_deleted"), true);
		}
		else
		{
			ilUtil::sendFailure($lng->txt("adn_subobjective_not_deleted"), true);
		}
		$ilCtrl->redirect($this, "listSubobjectives");
	}
}

?>