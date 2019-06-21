<?php
/* Copyright (c) 2010 Leifos, GPL, see docs/LICENSE */

/**
 * Examination invitation GUI class
 *
 * List all assigned candidates for event and generate invitations
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.de>
 * @version $Id: class.adnExaminationInvitationGUI.php 27888 2011-02-28 11:09:28Z jluetzen $
 *
 * @ilCtrl_Calls adnExaminationInvitationGUI:
 *
 * @ingroup ServicesADN
 */
class adnExaminationInvitationGUI
{
	/**
	 * Constructor
	 */
	public function __construct()
	{
		global $ilCtrl;
		
		// save event ID through requests
		$ilCtrl->saveParameter($this, array("ev_id"));
	}
	
	/**
	 * Execute command
	 */
	public function executeCommand()
	{
		global $ilCtrl, $lng, $tpl;

		$tpl->setTitle($lng->txt("adn_ep")." - ".$lng->txt("adn_ep_ins"));
		
		$next_class = $ilCtrl->getNextClass();
		
		// forward command to next gui class in control flow
		switch ($next_class)
		{
			// no next class:
			// this class is responsible to process the command
			default:
				$cmd = $ilCtrl->getCmd("listEvents");

				switch ($cmd)
				{
					// commands that need read permission
					case "listEvents":
					case "applyFilter":
					case "resetFilter":
						if(adnPerm::check(adnPerm::EP, adnPerm::READ))
						{
							$this->$cmd();
						}
						break;
					
					// commands that need write permission
					case "listCandidates":
					case "saveInvitations":
						if(adnPerm::check(adnPerm::EP, adnPerm::WRITE))
						{
							$this->$cmd();
						}
						break;
					
				}
				break;
		}
	}
	/**
	 * List examination events (has to be selected first)
	 */
	protected function listEvents()
	{
		global $tpl, $ilToolbar, $lng, $ilCtrl;

		// table of examination events
		include_once("./Services/ADN/ES/classes/class.adnExaminationEventTableGUI.php");
		$table = new adnExaminationEventTableGUI($this, "listEvents",
			adnExaminationEventTableGUI::MODE_INVITATION);

		// output table
		$tpl->setContent($table->getHTML());
	}

	/**
	 * Apply filter settings (from table gui)
	 */
	protected function applyFilter()
	{
		include_once("./Services/ADN/ES/classes/class.adnExaminationEventTableGUI.php");
		$table = new adnExaminationEventTableGUI($this, "listEvents",
			adnExaminationEventTableGUI::MODE_INVITATION);
		$table->resetOffset();
		$table->writeFilterToSession();

		$this->listEvents();
	}

	/**
	 * Reset filter settings (from table gui)
	 */
	protected function resetFilter()
	{
		include_once("./Services/ADN/ES/classes/class.adnExaminationEventTableGUI.php");
		$table = new adnExaminationEventTableGUI($this, "listEvents",
			adnExaminationEventTableGUI::MODE_INVITATION);
		$table->resetOffset();
		$table->resetFilter();

		$this->listEvents();
	}

	/**
	 * List all candiates for event
	 */
	protected function listCandidates()
	{
		global $tpl, $ilCtrl, $ilTabs, $lng;

		$ilTabs->setBackTarget($lng->txt("back"), $ilCtrl->getLinkTarget($this, "listEvents"));

		$event_id = (int)$_REQUEST["ev_id"];
		if(!$event_id)
		{
			return;
		}

		$ilCtrl->setParameter($this, "ev_id", $event_id);

		// table of candidates
		include_once("./Services/ADN/EP/classes/class.adnAssignmentTableGUI.php");
		$table = new adnAssignmentTableGUI($this, "assignCandidates", $event_id,
			adnAssignmentTableGUI::MODE_INVITATION);

		// output table
		$tpl->setContent($table->getHTML());
	}

	/**
	 * Process invitation generation and deliver file (pdf)
	 */
	protected function saveInvitations()
	{
		global $ilCtrl, $lng;
		
		// check whether at least one item has been seleced
		if (!is_array($_POST["cand_id"]) || count($_POST["cand_id"]) == 0)
		{
			ilUtil::sendFailure($lng->txt("no_checkbox"), true);
			$ilCtrl->redirect($this, "listCandidates");
		}
		else
		{
			$event_id = (int)$_REQUEST["ev_id"];
			if(!$event_id)
			{
				return;
			}
			
			include_once "Services/ADN/EP/classes/class.adnAssignment.php";
			foreach($_POST["cand_id"] as $candidate_id)
			{
				$invitation = new adnAssignment(null, $candidate_id, $event_id);
				if($invitation->getId())
				{
					$invitation->setInvitedOn(new ilDate(time(), IL_CAL_UNIX));
					$invitation->update();
				}
			}
			
			// create report
			include_once './Services/ADN/Report/exceptions/class.adnReportException.php';
			try
			{
				include_once './Services/ADN/EP/classes/class.adnExaminationEvent.php';
				include_once("./Services/ADN/Report/classes/class.adnReportInvitation.php");
				$report = new adnReportInvitation(new adnExaminationEvent($event_id));
				$report->setCandidates((array) $_POST['cand_id']);
				$report->create();
				
				ilUtil::deliverFile($report->getOutfile(),'einladungen.pdf','application/pdf');
			}
			catch(adnReportException $e)
			{
				ilUtil::sendFailure($e->getMessage(),true);
				$ilCtrl->redirect($this,'listCertificates');
			}
		}
	}
}

?>