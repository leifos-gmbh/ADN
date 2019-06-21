<?php
/* Copyright (c) 2010 Leifos, GPL, see docs/LICENSE */

include_once("./Services/Table/classes/class.ilTable2GUI.php");
include_once("./Services/ADN/TA/classes/class.adnTypesOfTraining.php");
include_once "Services/ADN/TA/classes/class.adnTrainingFacility.php";

/**
 * ADN training event table GUI class
 *
 * List all training events, provider is optional
 *
 * @author Alex Killing <killing@leifos.de>
 * @version $Id: class.adnTrainingEventTableGUI.php 32202 2011-12-19 15:59:44Z jluetzen $
 *
 * @ingroup ServicesADN
 */
class adnTrainingEventTableGUI extends ilTable2GUI
{
	protected $provider_id; // [int]
	protected $current; // [bool]
	protected $overview; // [bool]
	protected $restrict_type; // [string]
	
	/**
	 * Constructor
	 *
	 * @param object $a_parent_obj parent gui object
	 * @param string $a_parent_cmd parent default command
	 * @param int $a_provider_id current provider
	 * @param bool $a_current display current events
	 * @param bool $a_assignment view mode: assign last training event
	 * @param bool $a_overview provider context ?
	 * @param string $a_restrict_type restrict to certain training types
	 */
	function __construct($a_parent_obj, $a_parent_cmd, $a_provider_id = false,
		$a_current = true, $a_assignment = false, $a_overview = false, $a_restrict_type = null)
	{
		global $ilCtrl, $lng;

		$this->provider_id = (int)$a_provider_id;
		$this->current = (bool)$a_current;
		$this->assignment = (bool)$a_assignment;
		$this->overview = (bool)$a_overview;
		$this->restrict_type = (string)$a_restrict_type;
		if($this->assignment)
		{
			$this->current = null;
		}
		
		$this->setId("adn_ta_te".(int)$a_current.(int)$a_overview);

		parent::__construct($a_parent_obj, $a_parent_cmd);

		$title = $lng->txt("adn_training_events");
		if($this->provider_id)
		{
			include_once("./Services/ADN/TA/classes/class.adnTrainingProvider.php");
			$title .= ": ".adnTrainingProvider::lookupName($this->provider_id);

			if($this->restrict_type)
			{
				include_once("./Services/ADN/ED/classes/class.adnSubjectArea.php");
				$title .= " (".adnSubjectArea::getTextRepresentation($this->restrict_type).")";
			}
		}
		$this->setTitle($title);

		if(adnPerm::check(adnPerm::TA, adnPerm::WRITE) && $this->current && !$this->assignment &&
			!$this->overview)
		{
			$this->addMultiCommand("confirmTrainingEventDeletion", $lng->txt("delete"));
			$this->addColumn("", "", "1");
		}
		
		$this->addColumn($this->lng->txt("adn_type_of_training"), "type_caption");
		$this->addColumn($this->lng->txt("adn_date_from"), "date_from");
		$this->addColumn($this->lng->txt("adn_date_to"), "date_to");
		$this->addColumn($this->lng->txt("adn_training_facility"), "facility");
		if(!$this->provider_id)
		{
			$this->addColumn($this->lng->txt("adn_training_provider"), "provider");
		}
		$this->addColumn($this->lng->txt("actions"));
		
		$this->setDefaultOrderField("type_caption");
		$this->setDefaultOrderDirection("asc");

		if($this->assignment)
		{
			$this->setResetCommand("resetTrainingEventFilter");
			$this->setFilterCommand("applyTrainingEventFilter");			
		}

		$this->initFilter();
		
		$this->setFormAction($ilCtrl->getFormAction($a_parent_obj));
		$this->setRowTemplate("tpl.training_events_row.html", "Services/ADN/TA");

		$this->importData();
	}

	/**
	 * Import data from DB
	 */
	protected function importData()
	{
		include_once "Services/ADN/TA/classes/class.adnTrainingEvent.php";
		$events = adnTrainingEvent::getAllTrainingEvents($this->provider_id, $this->current,
			$this->filter);

		// value mapping (to have correct sorting)
		if(sizeof($events))
		{
			foreach($events as $idx => $item)
			{
				// we cannot use internal mapping because of archived values
				$events[$idx]["facility"] = adnTrainingFacility::lookupName($item["ta_facility_id"]);
				$events[$idx]["type_caption"] = adnTypesOfTraining::getTextRepresentation($item["type"]);

				if(!$this->provider_id)
				{
					$events[$idx]["provider"] =
						adnTrainingProvider::lookupName($item["ta_provider_id"]);
				}
			}
		}

		$this->setData($events);
		$this->setMaxCount(sizeof($events));
	}

	/**
	 * Init filter
	 */
	function initFilter()
	{
		global $lng;

		include_once "Services/ADN/TA/classes/class.adnTrainingProvider.php";
		
		$types = adnTypesOfTraining::getAllTypes();
		asort($types);

		if($this->provider_id)
		{
			$provider = new adnTrainingProvider($this->provider_id);
			$approved = $provider->getTypesOfTraining();
			foreach($types as $id => $caption)
			{
				if(!in_array($id, $approved))
				{
					unset($types[$id]);
				}
			}
		}

		if(!$this->restrict_type)
		{
			// type of training
			include_once("./Services/Form/classes/class.ilSelectInputGUI.php");
			$options = array(""=>$lng->txt("adn_filter_all"))+$types;
			$si = new ilSelectInputGUI($lng->txt("adn_type_of_training"), "type_of_training");
			$si->setOptions($options);
			$this->addFilterItem($si);
			$si->readFromSession();
			$this->filter["type_of_training"] = $si->getValue();
		}
		else
		{
			$this->filter["type_of_training"] = $this->restrict_type;
		}

		// date from to
		$si = $this->addFilterItemByMetaType("date", self::FILTER_DATE_RANGE, false,
			$lng->txt("adn_date"));
		$si->readFromSession();
		$this->filter["date"] = $si->getDate();

		// training facilities
		include_once("./Services/Form/classes/class.ilSelectInputGUI.php");
		$options = array(""=>$lng->txt("adn_filter_all"))+
			adnTrainingFacility::getTrainingFacilitiesSelect($this->provider_id);
		$si = new ilSelectInputGUI($lng->txt("adn_training_facility"), "training_facility");
		$si->setOptions($options);
		$this->addFilterItem($si);
		$si->readFromSession();
		$this->filter["training_facility"] = $si->getValue();
		
		if(!$this->provider_id)
		{
			$si = $this->addFilterItemByMetaType("provider", self::FILTER_SELECT, false,
				$lng->txt("adn_training_provider"));
			$si->setOptions(array(""=>$lng->txt("adn_filter_all"))+
				adnTrainingProvider::getTrainingProvidersSelect());
		    $si->readFromSession();
			$this->filter["provider"] = $si->getValue();
		}
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

		$ilCtrl->setParameter($this->parent_obj, "te_id", $a_set["id"]);

		if(!$this->assignment)
		{
			if(adnPerm::check(adnPerm::TA, adnPerm::WRITE))
			{
				// ...edit
				if($this->current)
				{
					$this->tpl->setCurrentBlock("action");
					$this->tpl->setVariable("TXT_CMD",
						$lng->txt("adn_edit"));
					$this->tpl->setVariable("HREF_CMD",
						$ilCtrl->getLinkTarget($this->parent_obj, "editTrainingEvent"));
					$this->tpl->parseCurrentBlock();

					// checkbox for deletion
					if(!$this->overview)
					{
						$this->tpl->setCurrentBlock("cbox");
						$this->tpl->setVariable("VAL_ID", $a_set["id"]);
						$this->tpl->parseCurrentBlock();
					}
				}
			}
			else if(adnPerm::check(adnPerm::TA, adnPerm::READ))
			{
				// ...show details
				$this->tpl->setCurrentBlock("action");
				$this->tpl->setVariable("TXT_CMD",
					$lng->txt("adn_show_details"));
				$this->tpl->setVariable("HREF_CMD",
					$ilCtrl->getLinkTarget($this->parent_obj, "showTrainingEvent"));
				$this->tpl->parseCurrentBlock();
			}
		}
		else
		{
			// save last training
			$this->tpl->setCurrentBlock("action");
			$this->tpl->setVariable("TXT_CMD",
				$lng->txt("adn_set_last_training"));
			$this->tpl->setVariable("HREF_CMD",
				$ilCtrl->getLinkTarget($this->parent_obj, "saveLastTraining"));
			$this->tpl->parseCurrentBlock();
		}

		$ilCtrl->setParameter($this->parent_obj, "te_id", "");

		// properties
		$this->tpl->setVariable("VAL_DATE_FROM",
			ilDatePresentation::formatDate($a_set["date_from"]));
		$this->tpl->setVariable("VAL_DATE_TO", ilDatePresentation::formatDate($a_set["date_to"]));
		$this->tpl->setVariable("VAL_TRAINING_FACILITY", $a_set["facility"]);

		$legend_color = adnTypesOfTraining::getColorForType($a_set["type"]);
		$this->tpl->setVariable("VAL_TYPE_OF_TRAINING", $a_set["type_caption"]);
		$this->tpl->setVariable("COLOR", $legend_color);

		$this->legend["<span style=\"background-color:".$legend_color.
			"; border: 1px solid grey;\">&nbsp;&nbsp;&nbsp;</span>"] = $a_set["type_caption"];
	
		if(!$this->provider_id)
		{
			$this->tpl->setCurrentBlock("provider");
			$this->tpl->setVariable("VAL_PROVIDER", $a_set["provider"]);
			$this->tpl->parseCurrentBlock();
		}
	}
}
?>
