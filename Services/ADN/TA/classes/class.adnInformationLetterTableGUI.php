<?php
/* Copyright (c) 2010 Leifos, GPL, see docs/LICENSE */

include_once("./Services/Table/classes/class.ilTable2GUI.php");

/**
 * ADN information letter table GUI class
 *
 * List all letters
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @version $Id: class.adnInformationLetterTableGUI.php 27891 2011-02-28 11:46:53Z jluetzen $
 *
 * @ingroup ServicesADN
 */
class adnInformationLetterTableGUI extends ilTable2GUI
{
	/**
	 * Constructor
	 *
	 * @param object $a_parent_obj parent gui object
	 * @param string $a_parent_cmd parent default command
	 */
	function __construct($a_parent_obj, $a_parent_cmd)
	{
		global $ilCtrl, $lng;

		parent::__construct($a_parent_obj, $a_parent_cmd);

		$this->setId("adn_ta_inf");
		
		$this->setTitle($lng->txt("adn_information_letters"));

		if(adnPerm::check(adnPerm::TA, adnPerm::WRITE))
		{
			$this->addMultiCommand("confirmInformationLettersDeletion", $lng->txt("delete"));
			$this->addColumn("", "", "1");
		}

		$this->addColumn($this->lng->txt("adn_name"), "name");
		$this->addColumn($this->lng->txt("actions"));
		
		$this->setDefaultOrderField("name");
		$this->setDefaultOrderDirection("asc");
		
		$this->setFormAction($ilCtrl->getFormAction($a_parent_obj));
		$this->setRowTemplate("tpl.letters_row.html", "Services/ADN/TA");
		
		$this->importData();
	}

	/**
	 * Import data from DB
	 */
	protected function importData()
	{
		include_once "Services/ADN/TA/classes/class.adnInformationLetter.php";
		$letters = adnInformationLetter::getAllInformationLetters();

		$this->setData($letters);
		$this->setMaxCount(sizeof($letters));
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

		$ilCtrl->setParameter($this->parent_obj, "il_id", $a_set["id"]);

		if(adnPerm::check(adnPerm::TA, adnPerm::WRITE))
		{
			// checkbox for deletion
			$this->tpl->setCurrentBlock("cbox");
			$this->tpl->setVariable("VAL_ID", $a_set["id"]);
			$this->tpl->parseCurrentBlock();
		}
		
		if(adnPerm::check(adnPerm::TA, adnPerm::READ))
		{
			// download
			$this->tpl->setCurrentBlock("action");
			$this->tpl->setVariable("TXT_CMD",
				$lng->txt("download"));
			$this->tpl->setVariable("HREF_CMD",
				$ilCtrl->getLinkTarget($this->parent_obj, "downloadInformationLetter"));
			$this->tpl->parseCurrentBlock();
		}
		
		$ilCtrl->setParameter($this->parent_obj, "il_id", "");

		
		// properties
		$this->tpl->setVariable("VAL_NAME", $a_set["name"]);
	}
}

?>