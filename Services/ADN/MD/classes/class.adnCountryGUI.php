<?php
/* Copyright (c) 2010 Leifos, GPL, see docs/LICENSE */

/**
 * Country GUI class
 *
 * Country list, forms and persistence
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.de>
 * @version $Id: class.adnCountryGUI.php 28757 2011-05-02 14:38:22Z jluetzen $
 *
 * @ilCtrl_Calls adnCountryGUI:
 *
 * @ingroup ServicesADN
 */
class adnCountryGUI
{
	// current country object
	protected $country = null;
	
	/**
	 * Constructor
	 */
	public function __construct()
	{
		global $ilCtrl;
		
		// save country ID through requests
		$ilCtrl->saveParameter($this, array("cnt_id"));
		
		$this->readCountry();
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
				$cmd = $ilCtrl->getCmd("listCountries");
				
				switch($cmd)
				{
					// commands that need read permission
					case "listCountries":
						if(adnPerm::check(adnPerm::MD, adnPerm::READ))
						{
							$this->$cmd();
						}
						break;
					
					// commands that need write permission
					case "editCountry":
					case "addCountry":
					case "saveCountry":
					case "updateCountry":
					case "confirmDeleteCountries":
					case "deleteCountries":
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
	 * Read country
	 */
	protected function readCountry()
	{
		if ((int)$_GET["cnt_id"] > 0)
		{
			include_once("./Services/ADN/MD/classes/class.adnCountry.php");
			$this->country = new adnCountry((int)$_GET["cnt_id"]);
		}
	}
	
	/**
	 * List all countries
	 */
	protected function listCountries()
	{
		global $tpl, $ilCtrl, $ilToolbar, $lng;

		if(adnPerm::check(adnPerm::MD, adnPerm::WRITE))
		{
			$ilToolbar->addButton($lng->txt("adn_add_country"),
				$ilCtrl->getLinkTarget($this, "addCountry"));
		}
		
		// table of countries
		include_once("./Services/ADN/MD/classes/class.adnCountryTableGUI.php");
		$table = new adnCountryTableGUI($this, "listCountries");
		
		// output table
		$tpl->setContent($table->getHTML());
	}

	/**
	 * Add country form
	 *
	 * @param ilPropertyFormGUI $a_form
	 */
	protected function addCountry(ilPropertyFormGUI $a_form = null)
	{
		global $tpl, $lng, $ilTabs, $ilCtrl;

		$ilTabs->setBackTarget($lng->txt("back"), $ilCtrl->getLinkTarget($this, "listCountries"));

		if(!$a_form)
		{
			$a_form = $this->initCountryForm(true);
		}
		$tpl->setContent($a_form->getHTML());
	}

	/**
	 * Create new country
	 */
	protected function saveCountry()
	{
		global $tpl, $lng, $ilCtrl;

		$form = $this->initCountryForm(true);
		if($form->checkInput())
		{
			include_once("./Services/ADN/MD/classes/class.adnCountry.php");
			$country = new adnCountry();
			$country->setCode($form->getInput("code"));
			$country->setName($form->getInput("name"));
			if($country->save())
			{
				ilUtil::sendSuccess($lng->txt("adn_country_created"), true);
				$ilCtrl->redirect($this, "listCountries");
			}
		}
		
		$form->setValuesByPost();
		$this->addCountry($form);
	}

	/**
	 * Edit country form
	 *
	 * @param ilPropertyFormGUI $a_form
	 */
	protected function editCountry(ilPropertyFormGUI $a_form = null)
	{
		global $tpl, $lng, $ilTabs, $ilCtrl;

		$ilTabs->setBackTarget($lng->txt("back"), $ilCtrl->getLinkTarget($this, "listCountries"));

		if(!$a_form)
		{
			$a_form = $this->initCountryForm();
		}
		$tpl->setContent($a_form->getHTML());
	}

	/**
	 * Update existing country
	 */
	protected function updateCountry()
	{
		global $tpl, $lng, $ilCtrl;

		$form = $this->initCountryForm();
		if($form->checkInput())
		{
			$this->country->setCode($form->getInput("code"));
			$this->country->setName($form->getInput("name"));
			if($this->country->update())
			{
				ilUtil::sendSuccess($lng->txt("adn_country_updated"), true);
				$ilCtrl->redirect($this, "listCountries");
			}
		}

		$form->setValuesByPost();
		$this->editCountry($form);
	}

	/**
	 * Build country form
	 * 
	 * @return ilPropertyFormGUI
	 */
	protected function initCountryForm($a_create = false)
	{
		global  $lng, $ilCtrl;

		include_once "Services/Form/classes/class.ilPropertyFormGUI.php";
		$form = new ilPropertyFormGUI();
		$form->setTitle($lng->txt("adn_country"));
		$form->setFormAction($ilCtrl->getFormAction($this, "listCountries"));

		$name = new ilTextInputGUI($lng->txt("adn_name"), "name");
		$name->setRequired(true);
		$name->setMaxLength(100);
		$form->addItem($name);

		$code = new ilTextInputGUI($lng->txt("adn_country_code"), "code");
		$code->setRequired(true);
		$code->setMaxLength(2);
		$code->setSize(2);
		$form->addItem($code);

		if($a_create)
		{
			$form->addCommandButton("saveCountry", $lng->txt("save"));
		}
		else
		{
			$code->setValue($this->country->getCode());
			$name->setValue($this->country->getName());

			$form->addCommandButton("updateCountry", $lng->txt("save"));
		}
		$form->addCommandButton("listCountries", $lng->txt("cancel"));

		return $form;
	}

	/**
	 * Confirm deletion of countries
	 */
	public function confirmDeleteCountries()
	{
		global $ilCtrl, $tpl, $lng, $ilTabs;

		// check whether at least one item has been seleced
		if (!is_array($_POST["cnt_id"]) || count($_POST["cnt_id"]) == 0)
		{
			ilUtil::sendFailure($lng->txt("no_checkbox"), true);
			$ilCtrl->redirect($this, "listCountries");
		}
		else
		{
			$ilTabs->setBackTarget($lng->txt("back"),
				$ilCtrl->getLinkTarget($this, "listCountries"));

			// display confirmation message
			include_once("./Services/Utilities/classes/class.ilConfirmationGUI.php");
			$cgui = new ilConfirmationGUI();
			$cgui->setFormAction($ilCtrl->getFormAction($this));
			$cgui->setHeaderText($lng->txt("adn_sure_delete_countries"));
			$cgui->setCancel($lng->txt("cancel"), "listCountries");
			$cgui->setConfirm($lng->txt("delete"), "deleteCountries");

			include_once("./Services/ADN/MD/classes/class.adnCountry.php");

			// list objects that should be deleted
			foreach ($_POST["cnt_id"] as $i)
			{
				$cgui->addItem("cnt_id[]", $i, adnCountry::lookupName($i));
			}

			$tpl->setContent($cgui->getHTML());
		}
	}

	/**
	 * Delete countries
	 */
	protected function deleteCountries()
	{
		global $ilCtrl, $lng;

		include_once("./Services/ADN/MD/classes/class.adnCountry.php");

		if (is_array($_POST["cnt_id"]))
		{
			foreach ($_POST["cnt_id"] as $i)
			{
				$country = new adnCountry($i);
				$country->delete();
			}
		}
		ilUtil::sendSuccess($lng->txt("adn_country_deleted"), true);
		$ilCtrl->redirect($this, "listCountries");
	}
}

?>