<?php
/* Copyright (c) 2010 Leifos, GPL, see docs/LICENSE */

include_once("./Services/Table/classes/class.ilTable2GUI.php");

/**
 * ADN instructor table GUI class
 *
 * List all instructors (for parent provider)
 *
 * @author Alex Killing <killing@leifos.de>
 * @version $Id: class.adnInstructorTableGUI.php 29459 2011-06-09 11:28:09Z jluetzen $
 *
 * @ingroup ServicesADN
 */
class adnInstructorTableGUI extends ilTable2GUI
{
	protected $provider_id; // [int]
	
	/**
	 * Constructor
	 *
	 * @param object $a_parent_obj parent gui object
	 * @param string $a_parent_cmd parent default command
	 * @param int $a_provider_id current provider
	 */
	function __construct($a_parent_obj, $a_parent_cmd, $a_provider_id)
	{
		global $ilCtrl, $lng;

		$this->provider_id = (int)$a_provider_id;

		$this->setId("adn_ta_inst");

		parent::__construct($a_parent_obj, $a_parent_cmd);

		include_once "Services/ADN/TA/classes/class.adnTrainingProvider.php";
		$this->setTitle($lng->txt("adn_instructors").": ".
			adnTrainingProvider::lookupName($this->provider_id));

		if(adnPerm::check(adnPerm::TA, adnPerm::WRITE))
		{
			$this->addMultiCommand("confirmInstructorsDeletion", $lng->txt("delete"));
			$this->addColumn("", "", "1");
		}

		$this->addColumn($this->lng->txt("adn_name"), "last_name");
		$this->addColumn($this->lng->txt("adn_firstname"), "first_name");
		$this->addColumn($this->lng->txt("adn_type_of_training"));
		$this->addColumn($this->lng->txt("adn_area_of_expertise"));
		$this->addColumn($this->lng->txt("actions"));
		
		$this->setDefaultOrderField("last_name");
		$this->setDefaultOrderDirection("asc");
		
		$this->setFormAction($ilCtrl->getFormAction($a_parent_obj));
		$this->setRowTemplate("tpl.instructors_row.html", "Services/ADN/TA");

		$this->importData();
	}

	/**
	 * Import data from DB
	 */
	protected function importData()
	{
		include_once "Services/ADN/TA/classes/class.adnInstructor.php";
		$instructors = adnInstructor::getAllInstructors($this->provider_id);

		// value mapping (to have correct sorting)
		if(sizeof($instructors))
		{
			include_once("./Services/ADN/TA/classes/class.adnTypesOfTraining.php");
			include_once("./Services/ADN/TA/classes/class.adnAreaOfExpertise.php");
			$all_types = adnTypesOfTraining::getAllTypes();
			$all_areas = adnAreaOfExpertise::getAreasOfExpertiseSelect();

			foreach($instructors as $idx => $item)
			{
				$types = array();
				if($item["type_of_training"])
				{
					foreach($item["type_of_training"] as $type)
					{
						$types[] = $all_types[$type];
					}
				}
				$instructors[$idx]["types"] = implode(", ", $types);

				$areas = array();
				if($item["area_of_expertise"])
				{
					foreach($item["area_of_expertise"] as $area)
					{
						$areas[] = $all_areas[$area];
					}
				}
				$instructors[$idx]["areas"] = implode(", ", $areas);

			}
		}

		$this->setData($instructors);
		$this->setMaxCount(sizeof($instructors));
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

		if(adnPerm::check(adnPerm::TA, adnPerm::WRITE))
		{
			$ilCtrl->setParameter($this->parent_obj, "is_id", $a_set["id"]);

			// ...edit
			$this->tpl->setCurrentBlock("action");
			$this->tpl->setVariable("TXT_CMD",
				$lng->txt("edit"));
			$this->tpl->setVariable("HREF_CMD",
				$ilCtrl->getLinkTarget($this->parent_obj, "editInstructor"));
			$this->tpl->parseCurrentBlock();

			$ilCtrl->setParameter($this->parent_obj, "is_id", "");

			// checkbox for deletion
			$this->tpl->setCurrentBlock("cbox");
			$this->tpl->setVariable("VAL_ID", $a_set["id"]);
			$this->tpl->parseCurrentBlock();
		}

		
		// properties
		$this->tpl->setVariable("VAL_NAME", $a_set["last_name"]);
		$this->tpl->setVariable("VAL_FIRSTNAME", $a_set["first_name"]);
		$this->tpl->setVariable("VAL_TYPE_OF_TRAINING", $a_set["types"]);
		$this->tpl->setVariable("VAL_AREA_OF_EXPERTISE", $a_set["areas"]);
	}
}

?>