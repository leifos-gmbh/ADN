<?php
/* Copyright (c) 2010 Leifos, GPL, see docs/LICENSE */

include_once("./Services/Table/classes/class.ilTable2GUI.php");

/**
 * Answer sheet table GUI class (preparation context)
 *
 * List all sheets for event with assignment info
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.de>
 * @version $Id: class.adnAnswerSheetTableGUI.php 27887 2011-02-28 10:54:39Z jluetzen $
 *
 * @ingroup ServicesADN
 */
class adnAnswerSheetTableGUI extends ilTable2GUI
{
	// [array] captions for foreign keys
	protected $map;

	// [int] examination event
	protected $event_id;

	// [bool]
	protected $archived;
	
	/**
	 * Constructor
	 *
	 * @param object $a_parent_obj parent gui object
	 * @param string $a_parent_cmd parent default command
	 * @param int $a_event_id examination event id
	 * @param bool $a_archived is event current?
	 */
	function __construct($a_parent_obj, $a_parent_cmd, $a_event_id, $a_archived)
	{
		global $ilCtrl, $lng;

		parent::__construct($a_parent_obj, $a_parent_cmd);

		$this->event_id = $a_event_id;
		$this->archived = $a_archived;
		$this->setId("adn_tbl_pcd");

		include_once "Services/ADN/EP/classes/class.adnExaminationEvent.php";
		$this->setTitle($lng->txt("adn_answer_sheets").": ".
			adnExaminationEvent::lookupName($this->event_id));
		
		if(!$this->archived && adnPerm::check(adnPerm::EP, adnPerm::WRITE))
		{
			$this->addMultiCommand("confirmSheetsDeletion", $lng->txt("adn_delete_answer_sheets"));
			$this->addColumn("", "", 1);
		}
		
		$this->addColumn($this->lng->txt("adn_number"), "nr");
		$this->addColumn($this->lng->txt("type"), "type");
		$this->addColumn($this->lng->txt("adn_generated_on"), "generated_on");
		$this->addColumn($this->lng->txt("adn_number_of_candidates"), "candidates");
		$this->addColumn($this->lng->txt("actions"));
		$this->initFilter();
		
		$this->setDefaultOrderField("nr");
		$this->setDefaultOrderDirection("asc");

		include_once "Services/ADN/EP/classes/class.adnAnswerSheet.php";
		$this->map["type"] = array(adnAnswerSheet::TYPE_MC => $this->lng->txt("adn_type_mc"),
			adnAnswerSheet::TYPE_CASE => $this->lng->txt("adn_type_case"));

		$this->setFormAction($ilCtrl->getFormAction($a_parent_obj));
		$this->setRowTemplate("tpl.sheet_row.html", "Services/ADN/EP");

		$this->importData();
	}

	/**
	 * Import data from DB
	 */
	protected function importData()
	{
		include_once("./Services/ADN/EP/classes/class.adnAnswerSheet.php");
		$sheets = adnAnswerSheet::getAllSheets($this->event_id);
		
		if(sizeof($sheets))
		{
			include_once("./Services/ADN/EP/classes/class.adnAnswerSheetAssignment.php");
			foreach($sheets as $idx => $item)
			{
				$assignments = adnAnswerSheetAssignment::getSheetsSelect(false, $this->event_id, $item["id"]);
				$sheets[$idx]["candidates"] = sizeof($assignments);

				$sheets[$idx]["type"] = $this->map["type"][$item["type"]];
			}
		}
		
		$this->setData($sheets);
		$this->setMaxCount(sizeof($sheets));
	}
	
	/**
	 * Fill table row
	 *
	 * @param	array	$a_set	data array
	 */
	protected function fillRow($a_set)
	{
		global $lng, $ilCtrl;

		if(!$this->archived)
		{
			// actions...

			$ilCtrl->setParameter($this->parent_obj, "sh_id", $a_set["id"]);

			// list questions
			$this->tpl->setCurrentBlock("action");
			$this->tpl->setVariable("TXT_CMD", $lng->txt("adn_questions"));
			$this->tpl->setVariable("HREF_CMD",
				$ilCtrl->getLinkTarget($this->parent_obj, "listQuestionsForSheet"));
			$this->tpl->parseCurrentBlock();

			$ilCtrl->setParameter($this->parent_obj, "sh_id", "");

			if(adnPerm::check(adnPerm::EP, adnPerm::WRITE))
			{
				$this->tpl->setCurrentBlock("cbox");
				$this->tpl->setVariable("VAL_ID", $a_set["id"]);
				$this->tpl->parseCurrentBlock();
			}
		}
		
		// properties
		$this->tpl->setVariable("VAL_NUMBER", $a_set["nr"]);
		$this->tpl->setVariable("VAL_TYPE", $a_set["type"]);
		$this->tpl->setVariable("VAL_CANDIDATES", $a_set["candidates"]);

		$this->tpl->setVariable("VAL_GENERATED", ilDatePresentation::formatDate($a_set["generated_on"]));
	}
	
}
?>