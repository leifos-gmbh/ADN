<?php
// cr-008 start
/* Copyright (c) 2017 Leifos, GPL, see docs/LICENSE */

/**
 * Personal data maintenance GUI
 *
 * @author Alex Killing <killing@leifos.de>
 * @version $Id$
 *
 * @ilCtrl_Calls adnPersonalDataMaintenanceGUI:
 *
 * @ingroup ServicesADN
 */
class adnPersonalDataMaintenanceGUI
{
	/**
	 * @var int
	 */
	protected $pid;

	/**
	 * Constructor
	 */
	function __construct()
	{
		global $lng;

		$this->pid = (int) $_GET["pid"];
		$this->lng = $lng;
	}


	/**
	 * Execute command
	 */
	public function executeCommand()
	{
		global $ilCtrl;
		
		$next_class = $ilCtrl->getNextClass();
		// forward command to next gui class in control flow
		switch ($next_class)
		{
			// no next class:
			// this class is responsible to process the command
			default:
				$cmd = $ilCtrl->getCmd("listPersonalData");

				switch($cmd)
				{
					// commands that need read permission
					case "listPersonalData":
					case "applyFilter":
					case "resetFilter":
					case "showPersonalDataDetails":
						if(adnPerm::check(adnPerm::AD, adnPerm::READ))
						{
							$this->$cmd();
						}
						break;
					
					// commands that need write permission
					case "":
						if(adnPerm::check(adnPerm::AD, adnPerm::WRITE))
						{
							$this->$cmd();
						}
						break;
					
				}
				break;
		}
	}

	/**
	 * List personal data
	 */
	protected function listPersonalData()
	{
		global $tpl;

		// table of countries
		include_once("./Services/ADN/AD/classes/class.adnPersonalDataTableGUI.php");
		$table = new adnPersonalDataTableGUI($this, "listPersonalData");
		
		// output table
		$tpl->setContent($table->getHTML());
	}

	/**
	 * Apply filter settings (from table gui)
	 */
	protected function applyFilter()
	{
		include_once("./Services/ADN/AD/classes/class.adnPersonalDataTableGUI.php");
		$table = new adnPersonalDataTableGUI($this, "listPersonalData");
		$table->resetOffset();
		$table->writeFilterToSession();

		$this->listPersonalData();
	}

	/**
	 * Reset filter settings (from table gui)
	 */
	protected function resetFilter()
	{
		include_once("./Services/ADN/AD/classes/class.adnPersonalDataTableGUI.php");
		$table = new adnPersonalDataTableGUI($this, "listPersonalData");
		$table->resetOffset();
		$table->resetFilter();

		$this->listPersonalData();
	}

	/**
	 * Show personal data details
	 */
	function showPersonalDataDetails()
	{
		global $tpl, $lng;

		$dtpl = new ilTemplate("tpl.pd_details.html", true, true, "Services/ADN/AD");

		// certificates

		//- adn_ep_cand_sheet -> adn_ep_answer_sheet (Prüfungsbögen) -> adn_ep_exam_event (Prüfungstermin)
		//- adn_ep_exam_invitation (Prüfungseinladung) -> adn_ep_exam_event (Prüfungstermin) (gibt es inc
		//- adn_ep_assignment (Prüfungskandidat) -> adn_ep_exam_event
		//- adn_cp_invoice (Kostenbescheide)
		//- adn_es_certificate (Bescheinigungen)

		/*
		// exam invitations (stored in adn_ep_assignment)
		$dtpl->setCurrentBlock("dblock");
		$dtpl->setVariable("HEAD_TITLE", $lng->txt("adn_ep_ins"));
		$dtpl->parseCurrentBlock();*/

		include_once("./Services/UIComponent/GroupedList/classes/class.ilGroupedListGUI.php");

		// exam candidates
		$dtpl->setCurrentBlock("dblock");
		$dtpl->setVariable("HEAD_TITLE", $lng->txt("adn_exam_candidate"));
		include_once("./Services/ADN/EP/classes/class.adnAssignment.php");
		include_once("./Services/ADN/EP/classes/class.adnExaminationEvent.php");
		$li = new ilGroupedListGUI();
		foreach (adnAssignment::getAllAssignments(array("user_id" => $this->pid)) as $ass)
		{
			$li->addEntry(adnExaminationEvent::lookupName($ass["ep_exam_event_id"]));
		}
		$dtpl->setVariable("LIST", $li->getHTML());
		$dtpl->parseCurrentBlock();

		// answer sheets
		$dtpl->setCurrentBlock("dblock");
		$dtpl->setVariable("HEAD_TITLE", $lng->txt("adn_answer_sheets"));
		include_once("./Services/ADN/EP/classes/class.adnAnswerSheetAssignment.php");
		include_once("./Services/ADN/EP/classes/class.adnAnswerSheet.php");
		$li = new ilGroupedListGUI();
		foreach (adnAnswerSheetAssignment::getAllSheets($this->pid) as $s)
		{
			$li->addEntry(adnAnswerSheet::lookupName($s["ep_answer_sheet_id"]).
				", ".adnExaminationEvent::lookupName(adnAnswerSheet::lookupEvent($s["ep_answer_sheet_id"])).
				", ".$this->lng->txt("adn_generated_on").": ".ilDatePresentation::formatDate(new ilDateTime($s["generated_on"], IL_CAL_DATETIME))
			);
		}
		$dtpl->setVariable("LIST", $li->getHTML());
		$dtpl->parseCurrentBlock();

		// certificates
		$dtpl->setCurrentBlock("dblock");
		$dtpl->setVariable("HEAD_TITLE", $lng->txt("adn_certificates"));
		include_once("./Services/ADN/ES/classes/class.adnCertificate.php");
		$li = new ilGroupedListGUI();
		foreach (adnCertificate::getAllCertificates(array("user_id" => $this->pid)) as $cert)
		{
			$c = new adnCertificate($cert["id"]);
			$li->addEntry($c->getFullCertificateNumber().", ".$this->lng->txt("adn_valid_until").": ".ilDatePresentation::formatDate($c->getValidUntil()));
		}
		$dtpl->setVariable("LIST", $li->getHTML());
		$dtpl->parseCurrentBlock();

		// invoices
		$dtpl->setCurrentBlock("dblock");
		$dtpl->setVariable("HEAD_TITLE", $lng->txt("adn_invoices"));
		$dtpl->parseCurrentBlock();

		$tpl->setContent($dtpl->get());
	}


}
// cr-008 end
?>