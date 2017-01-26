<?php
// cr-008 start
/* Copyright (c) 2010 Leifos, GPL, see docs/LICENSE */

include_once("./Services/Table/classes/class.ilTable2GUI.php");

/**
 * Personal data table GUI class
 *
 * List and filter personal data
 *
 * @author Alex Killing <killing@leifos.de>
 * @version $Id$
 *
 * @ingroup ServicesADN
 */
class adnPersonalDataTableGUI extends ilTable2GUI
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

		$this->setId("adn_tbl_adpdm");

		$this->setTitle($lng->txt("adn_ad_personal_data"));

		//$this->addCommandButton("saveUsers", $lng->txt("save"));

		$this->addColumn("", "", "1px", true);
		$this->addColumn($this->lng->txt("adn_last_name"), "last_name");
		$this->addColumn($this->lng->txt("adn_first_name"), "first_name");
		$this->addColumn($this->lng->txt("adn_birthdate"), "birthdate");
		$this->addColumn($this->lng->txt("adn_city"), "pa_city");
		$this->addColumn($this->lng->txt("adn_street"), "pa_street");
		$this->addColumn("ID", "id");
		$this->addColumn($this->lng->txt("actions"));
		
		$this->setDefaultOrderField("last_name");
		$this->setDefaultOrderDirection("asc");

		$this->setFormAction($ilCtrl->getFormAction($a_parent_obj));
		$this->setRowTemplate("tpl.personal_data_row.html", "Services/ADN/AD");

		$this->initFilter();
		$this->importData();
	}

	/**
	 * Import data from DB
	 */
	protected function importData()
	{
		include_once "Services/ADN/ES/classes/class.adnCertifiedProfessional.php";
		$data = adnCertifiedProfessional::getAllCandidates($this->filter);

		$this->setData($data);
		//$this->setMaxCount(sizeof($users));
	}

	/**
	 * Init filter
	 */
	function initFilter()
	{
		global $lng;

		// equal last name
		$name = $this->addFilterItemByMetaType("equal_last_name", self::FILTER_CHECKBOX, false,
			$lng->txt("adn_ad_equal_last_name"));
		$name->readFromSession();
		$this->filter["equal"]["last_name"] = $name->getChecked();

		// equal birthdate
		$name = $this->addFilterItemByMetaType("equal_birthdate", self::FILTER_CHECKBOX, false,
			$lng->txt("adn_ad_equal_birthdate"));
		$name->readFromSession();
		$this->filter["equal"]["birthdate"] = $name->getChecked();

		// equal city
		$name = $this->addFilterItemByMetaType("equal_city", self::FILTER_CHECKBOX, false,
			$lng->txt("adn_ad_equal_city"));
		$name->readFromSession();
		$this->filter["equal"]["pa_city"] = $name->getChecked();

		// equal street
		$name = $this->addFilterItemByMetaType("equal_street", self::FILTER_CHECKBOX, false,
			$lng->txt("adn_ad_equal_street"));
		$name->readFromSession();
		$this->filter["equal"]["pa_street"] = $name->getChecked();
	}

	/**
	 * Fill table row
	 *
	 * @param array $a_set data array
	 */
	protected function fillRow($a_set)
	{
		global $ilCtrl, $lng;

		$ilCtrl->setParameter($this->parent_obj, "pid", $a_set["id"]);
		$this->tpl->setCurrentBlock("action");
		$this->tpl->setVariable("HREF_CMD", $ilCtrl->getLinkTarget($this->parent_obj, "showPersonalDataDetails"));
		$this->tpl->setVariable("TXT_CMD", $lng->txt("adn_details"));
		$this->tpl->parseCurrentBlock();
		$ilCtrl->setParameter($this->parent_obj, "pid", $_GET["pid"]);

		// properties
		$this->tpl->setVariable("VAL_LAST_NAME", $a_set["last_name"]);
		$this->tpl->setVariable("VAL_FIRST_NAME", $a_set["first_name"]);
		$this->tpl->setVariable("VAL_BIRTHDATE", ilDatePresentation::formatDate(
			new ilDate($a_set["birthdate"], IL_CAL_DATE))." ".$a_set["birthdate"]);
		$this->tpl->setVariable("VAL_CITY", $a_set["pa_city"]);
		$this->tpl->setVariable("VAL_STREET", $a_set["pa_street"]." ".$a_set["pa_street_no"]);
		$this->tpl->setVariable("VAL_ID", $a_set["id"]);
	}
}
// cr-008 end
?>