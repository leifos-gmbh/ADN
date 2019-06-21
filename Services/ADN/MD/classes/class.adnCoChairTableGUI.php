<?php
/* Copyright (c) 2010 Leifos, GPL, see docs/LICENSE */

include_once("./Services/Table/classes/class.ilTable2GUI.php");

/**
 * Co-chair table GUI class
 *
 * List all co-chairs for wmo
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.de>
 * @version $Id: class.adnCoChairTableGUI.php 27876 2011-02-25 16:51:38Z jluetzen $
 *
 * @ingroup ServicesADN
 */
class adnCoChairTableGUI extends ilTable2GUI
{
	protected $wmo_id; // [int]
	
	/**
	 * Constructor
	 *
	 * @param object $a_parent_obj parent gui object
	 * @param string $a_parent_cmd parent default command
	 * @param int $a_wmo_id current wmo id
	 */
	function __construct($a_parent_obj, $a_parent_cmd, $a_wmo_id)
	{
		global $ilCtrl, $lng;

		$this->wmo_id = (int)$a_wmo_id;

		parent::__construct($a_parent_obj, $a_parent_cmd);

		$this->setId("adn_tbl_mdcch");

		include_once "Services/ADN/MD/classes/class.adnWMO.php";
		$this->setTitle($lng->txt("adn_cochairs").": ".adnWMO::lookupName($this->wmo_id));

		if(adnPerm::check(adnPerm::MD, adnPerm::WRITE))
		{
			$this->addMultiCommand("confirmDeleteCoChairs", $lng->txt("delete"));
			$this->addColumn("", "");
		}
		
		$this->addColumn($this->lng->txt("adn_salutation"), "salutation");
		$this->addColumn($this->lng->txt("adn_name"), "name");
		$this->addColumn($this->lng->txt("actions"));
		
		$this->setDefaultOrderField("salutation");
		$this->setDefaultOrderDirection("asc");

		$this->setFormAction($ilCtrl->getFormAction($a_parent_obj));
		$this->setRowTemplate("tpl.cochair_row.html", "Services/ADN/MD");

		$this->importData();
	}

	/**
	 * Import data from DB
	 */
	protected function importData()
	{
		global $lng;
		
		include_once "Services/ADN/MD/classes/class.adnCoChair.php";
		$cochairs = adnCoChair::getAllCoChairs($this->wmo_id);

		// value mapping (to have correct sorting)
		if(sizeof($cochairs))
		{
			foreach($cochairs as $idx => $item)
			{
				$cochairs[$idx]["salutation"] = $lng->txt("adn_salutation_".$item["salutation"]);
			}
		}

		$this->setData($cochairs);
		$this->setMaxCount(sizeof($cochairs));
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

		if(adnPerm::check(adnPerm::MD, adnPerm::WRITE))
		{
			$ilCtrl->setParameter($this->parent_obj, "cch_id", $a_set["id"]);

			// edit
			$this->tpl->setCurrentBlock("action");
			$this->tpl->setVariable("TXT_CMD", $lng->txt("edit"));
			$this->tpl->setVariable("HREF_CMD",
				$ilCtrl->getLinkTarget($this->parent_obj, "editCoChair"));
			$this->tpl->parseCurrentBlock();

			$ilCtrl->setParameter($this->parent_obj, "cch_id", "");

			// checkbox for deletion
			$this->tpl->setCurrentBlock("cbox");
			$this->tpl->setVariable("VAL_ID", $a_set["id"]);
			$this->tpl->parseCurrentBlock();
		}
	
		// properties
		$this->tpl->setVariable("VAL_SALUTATION", $a_set["salutation"]);
		$this->tpl->setVariable("VAL_NAME", $a_set["name"]);
	}
}

?>