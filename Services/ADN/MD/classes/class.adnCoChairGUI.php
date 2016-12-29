<?php
/* Copyright (c) 2010 Leifos, GPL, see docs/LICENSE */

/**
 * Co-chair GUI class
 *
 * Co-chair list, forms and persistence (parent wmo is mandatory)
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.de>
 * @version $Id: class.adnCoChairGUI.php 28757 2011-05-02 14:38:22Z jluetzen $
 *
 * @ilCtrl_Calls adnCoChairGUI:
 *
 * @ingroup ServicesADN
 */
class adnCoChairGUI
{
	// current wmo id
	protected $wmo_id = null;

	// current cochair object
	protected $cochair = null;
	
	/**
	 * Constructor
	 */
	public function __construct()
	{
		global $ilCtrl;

		$this->wmo_id = (int)$_REQUEST["wmo_id"];
		
		// save office and cochair ID through requests
		$ilCtrl->saveParameter($this, array("wmo_id"));
		$ilCtrl->saveParameter($this, array("cch_id"));
		
		$this->readCoChair();
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
				$cmd = $ilCtrl->getCmd("listCoChairs");

				switch($cmd)
				{
					// commands that need read permission
					case "listCoChairs":
						if(adnPerm::check(adnPerm::MD, adnPerm::READ))
						{
							$this->$cmd();
						}
						break;
					
					// commands that need write permission
					case "editCoChair":
					case "addCoChair":
					case "saveCoChair":
					case "updateCoChair":
					case "confirmDeleteCoChairs":
					case "deleteCoChairs":
						if(adnPerm::check(adnPerm::MD, adnPerm::WRITE))
						{
							$this->$cmd();
						}
						break;
					
				}
				break;
		}
	}
	
	/**
	 * Read cochair
	 */
	protected function readCoChair()
	{
		if ((int)$_GET["cch_id"] > 0)
		{
			include_once("./Services/ADN/MD/classes/class.adnCoChair.php");
			$this->cochair = new adnCoChair((int)$_GET["cch_id"]);
		}
	}
	
	/**
	 * List all cochairs
	 */
	protected function listCoChairs()
	{
		global $tpl, $ilCtrl, $ilToolbar, $lng, $ilTabs;

		$ilTabs->setBackTarget($lng->txt("back"), $ilCtrl->getLinkTargetByClass("adnWMOGUI", "listWMOs"));

		if(adnPerm::check(adnPerm::MD, adnPerm::WRITE))
		{
			$ilToolbar->addButton($lng->txt("adn_add_cochair"),
				$ilCtrl->getLinkTarget($this, "addCoChair"));
		}

		// table of countries
		include_once("./Services/ADN/MD/classes/class.adnCoChairTableGUI.php");
		$table = new adnCoChairTableGUI($this, "listCoChairs", $this->wmo_id);
		
		// output table
		$tpl->setContent($table->getHTML());
	}

	/**
	 * Add cochair form
	 *
	 * @param ilPropertyFormGUI $a_form
	 */
	protected function addCoChair(ilPropertyFormGUI $a_form = null)
	{
		global $tpl, $lng, $ilTabs, $ilCtrl;

		$ilTabs->setBackTarget($lng->txt("back"), $ilCtrl->getLinkTarget($this, "listCoChairs"));

		if(!$a_form)
		{
			$a_form = $this->initCoChairForm(true);
		}
		$tpl->setContent($a_form->getHTML());
	}

	/**
	 * Create new cochair
	 */
	protected function saveCoChair()
	{
		global $tpl, $lng, $ilCtrl;

		$form = $this->initCoChairForm(true);
		if($form->checkInput())
		{
			include_once("./Services/ADN/MD/classes/class.adnCoChair.php");
			$cochair = new adnCoChair();
			$cochair->setWMO($this->wmo_id);
			$cochair->setSalutation($form->getInput("salutation"));
			$cochair->setName($form->getInput("name"));
			if($cochair->save())
			{
				ilUtil::sendSuccess($lng->txt("adn_cochair_created"), true);
				$ilCtrl->redirect($this, "listCoChairs");
			}
		}
		
		$form->setValuesByPost();
		$this->addCoChair($form);
	}

	/**
	 * Edit cochair form
	 *
	 * @param ilPropertyFormGUI $a_form
	 */
	protected function editCoChair(ilPropertyFormGUI $a_form = null)
	{
		global $tpl, $lng, $ilTabs, $ilCtrl;

		$ilTabs->setBackTarget($lng->txt("back"), $ilCtrl->getLinkTarget($this, "listCoChairs"));

		if(!$a_form)
		{
			$a_form = $this->initCoChairForm();
		}
		$tpl->setContent($a_form->getHTML());
	}

	/**
	 * Update existing cochair
	 */
	protected function updateCoChair()
	{
		global $tpl, $lng, $ilCtrl;

		$form = $this->initCoChairForm();
		if($form->checkInput())
		{
			$this->cochair->setSalutation($form->getInput("salutation"));
			$this->cochair->setName($form->getInput("name"));
			if($this->cochair->update())
			{
				ilUtil::sendSuccess($lng->txt("adn_cochair_updated"), true);
				$ilCtrl->redirect($this, "listCoChairs");
			}
		}

		$form->setValuesByPost();
		$this->editCoChair($form);
	}

	/**
	 * Build cochair form
	 * @return object
	 */
	protected function initCoChairForm($a_create = false)
	{
		global  $lng, $ilCtrl;

		include_once "Services/Form/classes/class.ilPropertyFormGUI.php";
		$form = new ilPropertyFormGUI();
		$form->setFormAction($ilCtrl->getFormAction($this, "listCoChairs"));

		include_once "Services/ADN/MD/classes/class.adnWMO.php";
		$form->setTitle($lng->txt("adn_cochair").": ".adnWMO::lookupName($this->wmo_id));
		
		$salutation = new ilSelectInputGUI($lng->txt("adn_salutation"), "salutation");
		$options = array("f" => $lng->txt("adn_salutation_f"),
			"m" => $lng->txt("adn_salutation_m"));
	    $salutation->setOptions($options);
		$salutation->setRequired(true);
		$form->addItem($salutation);

		$name = new ilTextInputGUI($lng->txt("adn_last_name"), "name");
		$name->setRequired(true);
		$name->setMaxLength(50);
		$form->addItem($name);

		if($a_create)
		{
			$form->addCommandButton("saveCoChair", $lng->txt("save"));
		}
		else
		{
			$salutation->setValue($this->cochair->getSalutation());
			$name->setValue($this->cochair->getName());

			$form->addCommandButton("updateCoChair", $lng->txt("save"));
		}
		$form->addCommandButton("listCoChairs", $lng->txt("cancel"));

		return $form;
	}

	/**
	 * Confirm deletion of cochairs
	 */
	public function confirmDeleteCoChairs()
	{
		global $ilCtrl, $tpl, $lng, $ilTabs;

		// check whether at least one item has been seleced
		if (!is_array($_POST["cch_id"]) || count($_POST["cch_id"]) == 0)
		{
			ilUtil::sendFailure($lng->txt("no_checkbox"), true);
			$ilCtrl->redirect($this, "listCoChairs");
		}
		else
		{
			$ilTabs->setBackTarget($lng->txt("back"),
				$ilCtrl->getLinkTarget($this, "listCoChairs"));

			// display confirmation message
			include_once("./Services/Utilities/classes/class.ilConfirmationGUI.php");
			$cgui = new ilConfirmationGUI();
			$cgui->setFormAction($ilCtrl->getFormAction($this));
			$cgui->setHeaderText($lng->txt("adn_sure_delete_cochairs"));
			$cgui->setCancel($lng->txt("cancel"), "listCoChairs");
			$cgui->setConfirm($lng->txt("delete"), "deleteCoChairs");

			include_once("./Services/ADN/MD/classes/class.adnCoChair.php");

			// list objects that should be deleted
			foreach ($_POST["cch_id"] as $i)
			{
				$cgui->addItem("cch_id[]", $i, adnCoChair::lookupName($i));
			}

			$tpl->setContent($cgui->getHTML());
		}
	}

	/**
	 * Delete cochairs
	 */
	protected function deleteCoChairs()
	{
		global $ilCtrl, $lng;

		include_once("./Services/ADN/MD/classes/class.adnCoChair.php");

		if (is_array($_POST["cch_id"]))
		{
			foreach ($_POST["cch_id"] as $i)
			{
				$cochair = new adnCoChair($i);
				$cochair->delete();
			}
		}
		ilUtil::sendSuccess($lng->txt("adn_cochair_deleted"), true);
		$ilCtrl->redirect($this, "listCoChairs");
	}
}

?>