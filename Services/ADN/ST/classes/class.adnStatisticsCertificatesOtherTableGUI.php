<?php
/* Copyright (c) 2010 Leifos, GPL, see docs/LICENSE */

include_once("./Services/Table/classes/class.ilTable2GUI.php");

/**
 * Certificate (other applications) statistics table GUI class
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @version $Id: class.adnStatisticsCertificatesOtherTableGUI.php 27867 2011-02-25 09:35:47Z akill $
 *
 * @ingroup ServicesADN
 */
class adnStatisticsCertificatesOtherTableGUI extends ilTable2GUI
{
	// [array] captions for foreign keys
	protected $map;

	// [array] current filter
	protected $filter;
	
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

		$this->setId("adn_st_cos");
		
		$this->setTitle($lng->txt("adn_st_cos"));
		
		$this->addColumn($this->lng->txt("adn_other_applications"), "type");
		$this->addColumn($this->lng->txt("adn_statistics_count"), "count");

		$this->setDefaultOrderField("type");
		$this->setDefaultOrderDirection("asc");

		$this->setResetCommand("resetCertificatesOtherFilter");
		$this->setFilterCommand("applyCertificatesOtherFilter");
		
		$this->setFormAction($ilCtrl->getFormAction($a_parent_obj));
		$this->setRowTemplate("tpl.other_row.html", "Services/ADN/ST");

		include_once "Services/ADN/MD/classes/class.adnWMO.php";
		$this->map["wmo"] = adnWMO::getWMOsSelect();

		$this->initFilter();
		
		$this->importData();
	}

	/**
	 * Init filter
	 */
	function initFilter()
	{
		global $lng;

		$wsd = $this->addFilterItemByMetaType("wmo", self::FILTER_SELECT, false,
			$lng->txt("adn_wmo"));
		$wsd->setOptions(array(0 => $lng->txt("adn_filter_all"))+$this->map["wmo"]);
		$wsd->readFromSession();
		$this->filter["wmo"] = $wsd->getValue();

		$date = $this->addFilterItemByMetaType("date", self::FILTER_DATE_RANGE, false,
			$lng->txt("adn_timeframe"));
		$date->readFromSession();
		$this->filter["date"] = $date->getDate();
		
		if(!$this->filter["date"]["from"] || !$this->filter["date"]["to"])
		{
			$date->setValue(array("from"=>new ilDate(date("Y")."-01-01 00:00:00", IL_CAL_DATETIME),
				"to" => new ilDate(time(), IL_CAL_UNIX)));
			$this->filter["date"] = $date->getDate();
		}
	}

	/**
	 * Import data from DB
	 */
	protected function importData()
	{
		global $lng;
		
		include_once "Services/ADN/ST/classes/class.adnStatistics.php";
		$count = adnStatistics::getCertificatesOther($this->filter);

		$data = array();
		$data[] = array("type" => $lng->txt("adn_application_duplicates"),
			"count" => $count);

		$this->setData($data);
		$this->setMaxCount(sizeof($data));
	}
	
	/**
	 * Fill table row
	 *
	 * @param array $a_set data array
	 */
	protected function fillRow($a_set)
	{
		// properties
		$this->tpl->setVariable("VAL_TYPE", $a_set["type"]);
		$this->tpl->setVariable("VAL_COUNT", $a_set["count"]);
	}
}

?>