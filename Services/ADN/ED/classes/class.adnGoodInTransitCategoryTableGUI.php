<?php
/* Copyright (c) 2010 Leifos, GPL, see docs/LICENSE */

include_once("./Services/Table/classes/class.ilTable2GUI.php");

/**
 * ADN good in transit category table GUI class
 *
 * List all categories (either gas or chemicals)
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @version $Id: class.adnGoodInTransitCategoryTableGUI.php 27873 2011-02-25 16:21:55Z jluetzen $
 *
 * @ingroup ServicesADN
 */
class adnGoodInTransitCategoryTableGUI extends ilTable2GUI
{
	protected $type; // [string]

	/**
	 * Constructor
	 *
	 * @param object $a_parent_obj parent gui object
	 * @param string $a_parent_cmd parent default command
	 * @param int $a_type parent type id
	 * @param string $a_org_type parent type string
	 */
	function __construct($a_parent_obj, $a_parent_cmd, $a_type, $a_org_type)
	{
		global $ilCtrl, $lng;

		$this->type = (string)$a_type;

		parent::__construct($a_parent_obj, $a_parent_cmd);
		
		$this->setTitle($lng->txt("adn_good_in_transit_categories"));

		if(adnPerm::check(adnPerm::ED, adnPerm::WRITE))
		{
			$this->addMultiCommand("confirm".$a_org_type."CategoriesDeletion", $lng->txt("delete"));
			$this->addColumn("", "", "1");
		}
		
		$this->addColumn($this->lng->txt("adn_name"), "name");
		$this->addColumn($this->lng->txt("actions"));
		
		$this->setDefaultOrderField("name");
		$this->setDefaultOrderDirection("asc");
		
		$this->setFormAction($ilCtrl->getFormAction($a_parent_obj));
		$this->setRowTemplate("tpl.categories_row.html", "Services/ADN/ED");

		$this->importData();
	}

	/**
	 * Import data from DB
	 */
	protected function importData()
	{
		include_once "Services/ADN/ED/classes/class.adnGoodInTransitCategory.php";
		$cats = adnGoodInTransitCategory::getAllCategories($this->type);

		$this->setData($cats);
		$this->setMaxCount(sizeof($cats));
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
			$ilCtrl->setParameter($this->parent_obj, "gct_id", $a_set["id"]);

			// ...edit
			$this->tpl->setCurrentBlock("action");
			$this->tpl->setVariable("TXT_CMD",
				$lng->txt("edit"));
			$this->tpl->setVariable("HREF_CMD",
				$ilCtrl->getLinkTarget($this->parent_obj, "editCategory"));
			$this->tpl->parseCurrentBlock();

			$ilCtrl->setParameter($this->parent_obj, "gct_id", "");

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