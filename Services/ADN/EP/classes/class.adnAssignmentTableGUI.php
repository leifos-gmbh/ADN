<?php
/* Copyright (c) 2010 Leifos, GPL, see docs/LICENSE */

include_once("./Services/Table/classes/class.ilTable2GUI.php");

/**
 * Candidate assignment table GUI class (preparation context)
 *
 * List candidates for assignment to an examination event
 * This will also be used for invitations
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.de>
 * @version $Id: class.adnAssignmentTableGUI.php 53831 2014-09-25 12:37:08Z jluetzen $
 *
 * @ingroup ServicesADN
 */
class adnAssignmentTableGUI extends ilTable2GUI
{
	// [adnExaminationEvent] examination event
	protected $event;

	// [array] captions for foreign keys
	protected $map;

	// [bool] certificate column active?
	protected $has_certificate;
	
	// [int] view mode
	protected $mode;

	// [bool] current or past event
	protected $archived;

	// [array] displayed candidate ids 
	protected $all_candidate_ids = array();


	const MODE_ASSIGNMENT = 1;
	const MODE_INVITATION = 2;

	/**
	 * Constructor
	 *
	 * @param object $a_parent_obj parent gui object
	 * @param string $a_parent_cmd parent default command
	 * @param int $a_event_id id of examination event
	 * @param bool $a_mode invitation view
	 * @param bool $a_archived current or past event
	 */
	function __construct($a_parent_obj, $a_parent_cmd, $a_event_id, $a_mode, $a_archived = false)
	{
		global $ilCtrl, $lng;

		parent::__construct($a_parent_obj, $a_parent_cmd);

		$this->setId("adn_tbl_ccd");
		$this->mode = (int)$a_mode;
		$this->archived = (bool)$a_archived;

		include_once "Services/ADN/EP/classes/class.adnExaminationEvent.php";
		$this->event = new adnExaminationEvent((int)$a_event_id);

		$this->setTitle($lng->txt("adn_candidates").": ".
			adnExaminationEvent::lookupName($this->event->getId()));

		include_once "Services/ADN/MD/classes/class.adnWMO.php";
		$this->map["registered_by"] = adnWMO::getWMOsSelect();

		include_once "Services/ADN/MD/classes/class.adnCountry.php";
		$this->map["country"] = adnCountry::getCountriesSelect();

		if($this->mode == self::MODE_INVITATION)
		{
			if(adnPerm::check(adnPerm::EP, adnPerm::WRITE))
			{
				$this->addMultiCommand("saveInvitations", $lng->txt("adn_generate_invitations"));
			}
			$this->addColumn("", "", 1);
		}
		
		$this->addColumn($this->lng->txt("adn_last_name"), "last_name");
		$this->addColumn($this->lng->txt("adn_first_name"), "first_name");
		$this->addColumn($this->lng->txt("adn_birthdate"), "birthdate");
		$this->addColumn($this->lng->txt("adn_permanent_address"));
		$this->addColumn($this->lng->txt("adn_registered_by"), "registered_by");

		switch($this->mode)
		{
			case self::MODE_ASSIGNMENT:
				$this->addColumn($this->lng->txt("adn_before_deadline"), "deadline");

				include_once "Services/ADN/ED/classes/class.adnSubjectArea.php";
				$this->has_certificate = array_key_exists($this->event->getType(),
					adnSubjectArea::getAreasWithCasePart());
				if($this->has_certificate)
				{
					$this->addColumn($this->lng->txt("adn_has_base_certificate"), "certificate");
				}

				$this->addColumn($this->lng->txt("adn_assigned"), "assigned");


				$this->map["deadline"] = array(0 => $lng->txt("no"),
					1 => $lng->txt("yes"));

				$this->map["certificate"] = array(0 => $lng->txt("no"),
					1 => $lng->txt("adn_certificate_germany"),
					2 => $lng->txt("adn_certificate_foreign"),
					3 => $lng->txt("adn_certificate_germany_and_foreign"));

				if(adnPerm::check(adnPerm::EP, adnPerm::WRITE) && !$this->archived)
				{
					$this->addCommandButton("saveAssignment",
						$this->lng->txt("adn_save_assignment"));
				}

				$this->setResetCommand("resetAssignmentFilter");
				$this->setFilterCommand("applyAssignmentFilter");

				$this->initFilter();
				break;

			case self::MODE_INVITATION:
				$this->addColumn($this->lng->txt("adn_invitation_generated_on"), "generated");
				break;
		}
		
		$this->setDefaultOrderField("last_name");
		$this->setDefaultOrderDirection("asc");

		$this->setFormAction($ilCtrl->getFormAction($a_parent_obj));
		$this->setRowTemplate("tpl.candidate_assignment_row.html", "Services/ADN/EP");
	
		$this->importData();
	}

	/**
	 * Import data from DB
	 */
	protected function importData()
	{
		// load assignments for event
		include_once "Services/ADN/EP/classes/class.adnAssignment.php";
		$assigned = adnAssignment::getAllAssignments(array("event_id"=>$this->event->getId()));
		if($assigned)
		{
			foreach($assigned as $idx => $item)
			{
				$assigned[$idx] = $item["cp_professional_id"];
			}
		}

		$filter = array();
		if($this->filter["wmo"])
		{
			$filter["registered_by"] = $this->filter["wmo"];
		}
		if(!$this->archived)
		{
			$filter["registered_for_exam"] = 1;
			$filter["subject_area"] = $this->event->getType();
		}
		include_once "Services/ADN/ES/classes/class.adnCertifiedProfessional.php";
		$candidates = adnCertifiedProfessional::getAllCandidates($filter);

		// make sure that all existing assignments are shown
		if($assigned)
		{
			$chk = $assigned;
			foreach($candidates as $item)
			{
				$idx = array_search($item["id"], $assigned);
				if($idx !== false)
				{
					unset($chk[$idx]);
				}
			}
			if(sizeof($chk))
			{
				$assigned_candidates =
					adnCertifiedProfessional::getAllCandidates(array("id"=>$chk), true);
				$candidates = array_merge($candidates, $assigned_candidates);
				unset($assigned_candidates);
			}
		}

		if(sizeof($candidates))
		{
			// load invitations for event
			if($this->mode == self::MODE_INVITATION)
			{
				$invitations = adnAssignment::getAllInvitations($this->event->getId());
			}

			$min_date = strtotime("-6months",
				$this->event->getDateFrom()->get(IL_CAL_UNIX, "", ilTimeZone::UTC));
			include_once "Services/ADN/TA/classes/class.adnTrainingEvent.php";
			$events = array();

			if($this->has_certificate)
			{
				include_once "Services/ADN/ES/classes/class.adnCertificate.php";
				$all_valid = adnCertificate::getAllProfessionalsWithValidCertificates();
			}
			
			foreach($candidates as $idx => $item)
			{
				$candidates[$idx]["pa_country"] = $this->map["country"][$item["pa_country"]];
				$candidates[$idx]["registered_by"] =
					$this->map["registered_by"][$item["registered_by_wmo_id"]];
				$candidates[$idx]["birthdate"] = 
					ilDatePresentation::formatDate(new ilDate($item["birthdate"], IL_CAL_DATE));

				if($this->mode == self::MODE_ASSIGNMENT)
				{
					// check deadline (last training was no more than 6 months
					// before exam, correct type)
					$item["deadline"] = false;
					if($item["last_ta_event_id"])
					{
						if(!isset($events[$item["last_ta_event_id"]]))
						{
							$event = new adnTrainingEvent($item["last_ta_event_id"]);
							$date_to = $event->getDateTo()->get(IL_CAL_UNIX, "", ilTimeZone::UTC);
							$events[$item["last_ta_event_id"]]["date"] = $date_to;
							$events[$item["last_ta_event_id"]]["type"] = $event->getType();
						}

						$date_to = $events[$item["last_ta_event_id"]]["date"];
						$type = $events[$item["last_ta_event_id"]]["type"];
						if($date_to >= $min_date && $item["subject_area"] == $type)
						{
							$item["deadline"] = true;
						}
					}

					$candidates[$idx]["deadline"] = $this->map["deadline"][$item["deadline"]];

					if($this->has_certificate)
					{
						$code = 0;
						if(in_array($item["id"], $all_valid))
						{
							if(!$item["foreign_certificate"])
							{
								$code = 1;
							}
							else
							{
								$code = 3;
							}
						}
						else if($item["foreign_certificate"])
						{
							$code = 2;
						}
						$candidates[$idx]["certificate"] = $this->map["certificate"][$code];
					}

					// is currently assigned
					if(in_array($item["id"], $assigned))
					{
						$candidates[$idx]["assigned"] = true;
					}
					else
					{
						// remove if other assignment
						$other = adnAssignment::getAllCurrentUserAssignments($item["id"]);
						if(sizeof($other) || $this->archived)
						{
						  unset($candidates[$idx]);
						  continue;
						}
					}
				}
				else if($this->mode == self::MODE_INVITATION)
				{
					// show only assigned candidates
					if(!in_array($item["id"], $assigned))
					{
						unset($candidates[$idx]);
						continue;
					}

					if(array_key_exists($item["id"], $invitations))
					{
						$candidates[$idx]["generated"] = $invitations[$item["id"]];
					}
				}
			}

			$this->setData($candidates);
			$this->setMaxCount(sizeof($candidates));
		}
	}

	/**
	 * Init filter
	 */
	function initFilter()
	{
		global $lng;

		$wsd = $this->addFilterItemByMetaType("registered_by", self::FILTER_SELECT, false,
			$lng->txt("adn_registered_by"));
		$wsd->setOptions(array(0 => $lng->txt("adn_filter_all"))+$this->map["registered_by"]);
		$wsd->readFromSession();
		$this->filter["wmo"] = $wsd->getValue();
	}
	
	/**
	 * Get all displayed candidate ids
	 * 
	 * @return array
	 */
	function getDisplayedIds()
	{
		return $this->all_candidate_ids;
	}
	
	/**
	 * Fill table row
	 *
	 * @param array $a_set data array
	 */
	protected function fillRow($a_set)
	{
		global $lng, $ilCtrl;
		
		$this->all_candidate_ids[] = $a_set["id"];
		
		// properties
		
		$this->tpl->setVariable("VAL_NAME", $a_set["last_name"]);
		$this->tpl->setVariable("VAL_FIRST_NAME", $a_set["first_name"]);
		$this->tpl->setVariable("VAL_BIRTHDATE", $a_set["birthdate"]);
		$this->tpl->setVariable("VAL_STREET", $a_set["pa_street"]);
		$this->tpl->setVariable("VAL_HNO", $a_set["pa_street_no"]);
		$this->tpl->setVariable("VAL_ZIP", $a_set["pa_postal_code"]);
		$this->tpl->setVariable("VAL_CITY", $a_set["pa_city"]);
		$this->tpl->setVariable("VAL_COUNTRY", $a_set["pa_country"]);
		$this->tpl->setVariable("VAL_REGISTERED_BY", $a_set["registered_by"]);

		if($this->mode == self::MODE_ASSIGNMENT)
		{
			$this->tpl->setVariable("VAL_DEADLINE", $a_set["deadline"]);

			if($this->has_certificate)
			{
				$this->tpl->setCurrentBlock("certificate");
				$this->tpl->setVariable("VAL_CERTIFICATE", $a_set["certificate"]);
				$this->tpl->parseCurrentBlock();
			}

			if(adnPerm::check(adnPerm::EP, adnPerm::WRITE) && !$this->archived)
			{
				$this->tpl->setCurrentBlock("cbox");
				$this->tpl->setVariable("VAL_ID", $a_set["id"]);
				if($a_set["assigned"])
				{
					$this->tpl->setVariable("VAL_ASSIGNED", "checked=\"checked\"");
				}
				$this->tpl->parseCurrentBlock();
			}
			else
			{
				$this->tpl->setCurrentBlock("cbox_static");
				if($a_set["assigned"])
				{
					$this->tpl->setVariable("VAL_ASSIGNED", $lng->txt("yes"));
				}
				else
				{
					$this->tpl->setVariable("VAL_ASSIGNED", $lng->txt("no"));
				}
				$this->tpl->parseCurrentBlock();
			}
		}
		else if($this->mode == self::MODE_INVITATION)
		{
			$this->tpl->setCurrentBlock("cbox_inv");
			$this->tpl->setVariable("VAL_ID", $a_set["id"]);
			$this->tpl->parseCurrentBlock();

			if($a_set["generated"])
			{
				$this->tpl->setVariable("VAL_DEADLINE",
					ilDatePresentation::formatDate($a_set["generated"]));
			}
		}
	}
}

?>