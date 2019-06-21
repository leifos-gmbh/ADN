<?php
/* Copyright (c) 2010 Leifos, GPL, see docs/LICENSE */

include_once("./Services/Table/classes/class.ilTable2GUI.php");

/**
 * Exam statistics table GUI class
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @version $Id: class.adnStatisticsExamsTableGUI.php 30175 2011-08-07 13:56:30Z smeyer $
 *
 * @ingroup ServicesADN
 */
class adnStatisticsExamsTableGUI extends ilTable2GUI
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

		$this->setId("adn_st_exs");
		
		$this->setTitle($lng->txt("adn_st_exs"));
		
		$this->addColumn($this->lng->txt("adn_type_of_exam"), "type");
		$this->addColumn($this->lng->txt("adn_number_of_examination_events"), "events");
		$this->addColumn($this->lng->txt("adn_number_of_participants"), "participants");
		$this->addColumn($this->lng->txt("adn_number_of_successful_participants"), "success");
		$this->addColumn($this->lng->txt("adn_success_quota"), "quota");

		#$this->setDefaultOrderField("type");
		#$this->setDefaultOrderDirection("asc");
		$this->setExternalSorting(true);
		$this->disable('sort');

		$this->setResetCommand("resetExamsFilter");
		$this->setFilterCommand("applyExamsFilter");
		
		$this->setFormAction($ilCtrl->getFormAction($a_parent_obj));
		$this->setRowTemplate("tpl.exams_row.html", "Services/ADN/ST");

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
		$tmp = adnStatistics::getExams($this->filter);

		$data = array();
		if($tmp)
		{
			include_once "Services/ADN/ED/classes/class.adnSubjectArea.php";

			$sum = array();
			foreach($tmp as $type => $item)
			{
				$quota = 0;
				if((int)$item["participants"])
				{
					$quota = (string) $item["successful"]/(int)$item["participants"] * 100;
				}

				$data[] = array(
					"type" => adnSubjectArea::getTextRepresentation($type),
					"events" => $item["type"],
					"participants" => (string) $item["participants"],
					"success" => (string) $item["successful"],
					"quota" => round($quota, 2));

				$sum['events'] += $item['type'];
				$sum['participants'] += (string) $item['participants'];
				$sum['success'] += (string) $item['successful'];
			}

			$sum['type'] = $lng->txt('adn_total');
			$sum['quota'] = '0';
			if((int) $sum['participants'])
			{
				$sum['quota'] = round((int) $sum['success'] / (int) $sum['participants'] * 100, 2);
			}
			$data[] = $sum;
		}
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
		$this->tpl->setVariable("VAL_EVENTS", $a_set["events"]);
		$this->tpl->setVariable("VAL_PARTICIPANTS", $a_set["participants"]);
		$this->tpl->setVariable("VAL_SUCCESS", $a_set["success"]);
		$this->tpl->setVariable("VAL_QUOTA", $a_set["quota"]."%");
	}
}

?>