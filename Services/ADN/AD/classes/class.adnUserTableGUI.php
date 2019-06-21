<?php
/* Copyright (c) 2010 Leifos, GPL, see docs/LICENSE */

include_once("./Services/Table/classes/class.ilTable2GUI.php");

/**
 * User table GUI class
 *
 * List all users
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.de>
 * @version $Id: class.adnUserTableGUI.php 27872 2011-02-25 15:42:09Z jluetzen $
 *
 * @ingroup ServicesADN
 */
class adnUserTableGUI extends ilTable2GUI
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

		$this->setId("adn_tbl_adusr");

		$this->setTitle($lng->txt("adn_ad_usr"));

		$this->addCommandButton("saveUsers", $lng->txt("save"));
		
		$this->addColumn($this->lng->txt("adn_last_name"), "last_name");
		$this->addColumn($this->lng->txt("adn_first_name"), "first_name");
		$this->addColumn($this->lng->txt("adn_user_sign"), "sign");
		
		$this->setDefaultOrderField("last_name");
		$this->setDefaultOrderDirection("asc");

		$this->setFormAction($ilCtrl->getFormAction($a_parent_obj));
		$this->setRowTemplate("tpl.user_row.html", "Services/ADN/AD");

		$this->importData();
	}

	/**
	 * Import data from DB
	 */
	protected function importData()
	{
		include_once "Services/ADN/AD/classes/class.adnUser.php";
		$users = adnUser::getAllUsers();

		$this->setData($users);
		$this->setMaxCount(sizeof($users));
	}
	
	/**
	 * Fill table row
	 *
	 * @param array $a_set data array
	 */
	protected function fillRow($a_set)
	{
		global $lng, $ilCtrl;

		// properties
		$this->tpl->setVariable("VAL_LAST_NAME", $a_set["last_name"]);
		$this->tpl->setVariable("VAL_FIRST_NAME", $a_set["first_name"]);
		$this->tpl->setVariable("VAL_ID", $a_set["id"]);
		$this->tpl->setVariable("VAL_SIGN", $a_set["sign"]);
	}
}

?>