<?php
/* Copyright (c) 2010 Leifos, GPL, see docs/LICENSE */

include_once("./Services/Table/classes/class.ilTable2GUI.php");

/**
 * Character table GUI class
 *
 * List all special characters
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.de>
 * @version $Id: class.adnCharacterTableGUI.php 27872 2011-02-25 15:42:09Z jluetzen $
 *
 * @ingroup ServicesADN
 */
class adnCharacterTableGUI extends ilTable2GUI
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

		$this->setId("adn_tbl_adchr");

		$this->setTitle($lng->txt("adn_characters"));

		if(adnPerm::check(adnPerm::AD, adnPerm::WRITE))
		{
			$this->addMultiCommand("confirmDeleteCharacters", $lng->txt("delete"));
			$this->addColumn("", "");
		}

		$this->addColumn($this->lng->txt("adn_name"), "name");
		$this->addColumn($this->lng->txt("actions"));
		
		$this->setDefaultOrderField("name");
		$this->setDefaultOrderDirection("asc");

		$this->setFormAction($ilCtrl->getFormAction($a_parent_obj));
		$this->setRowTemplate("tpl.character_row.html", "Services/ADN/AD");

		$this->importData();
	}

	/**
	 * Import data from DB
	 */
	protected function importData()
	{
		include_once "Services/ADN/AD/classes/class.adnCharacter.php";
		$characters = adnCharacter::getAllCharacters($this->wmo_id);

		$this->setData($characters);
		$this->setMaxCount(sizeof($characters));
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

		if(adnPerm::check(adnPerm::AD, adnPerm::WRITE))
		{
			$ilCtrl->setParameter($this->parent_obj, "chr_id", $a_set["id"]);

			// edit
			$this->tpl->setCurrentBlock("action");
			$this->tpl->setVariable("TXT_CMD", $lng->txt("edit"));
			$this->tpl->setVariable("HREF_CMD",
				$ilCtrl->getLinkTarget($this->parent_obj, "editCharacter"));
			$this->tpl->parseCurrentBlock();

			$ilCtrl->setParameter($this->parent_obj, "chr_id", "");

			// checkbox for deletion
			$this->tpl->setCurrentBlock("cbox");
			$this->tpl->setVariable("VAL_ID", $a_set["id"]);
			$this->tpl->parseCurrentBlock();
		}

		// properties
		$this->tpl->setVariable("VAL_NAME", $a_set["name"]);
	}
}
?>