<?php
/* Copyright (c) 2010 Leifos, GPL, see docs/LICENSE */

/**
 * Score notification GUI class. This class handles the user interface for accessing
 * /downloading the score notification letters for candidates after an exam has been taken.
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.de>
 * @version $Id: class.adnScoreNotificationGUI.php 27884 2011-02-27 21:01:07Z akill $
 *
 * @ilCtrl_Calls adnScoreNotificationGUI:
 *
 * @ingroup ServicesADN
 */
class adnScoreNotificationGUI
{
	/**
	 * Constructor
	 */
	public function __construct()
	{
		global $ilCtrl;

		// keep event
		$ilCtrl->saveParameter($this, "ev_id");
	}
	
	/**
	 * Execute command
	 */
	public function executeCommand()
	{
		global $ilCtrl, $lng, $tpl;

		$tpl->setTitle($lng->txt("adn_es")." - ".$lng->txt("adn_es_sns"));
		
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
			        case "listParticipants":
					case "downloadLetter":
						if(adnPerm::check(adnPerm::ES, adnPerm::READ))
						{
							$this->$cmd();
						}
						break;
					
					// commands that need write permission
					case "createLetters":
					case 'downloadLetter':
						if(adnPerm::check(adnPerm::ES, adnPerm::WRITE))
						{
							$this->$cmd();
						}
						break;
					
				}
				break;
		}
	}

	/**
	 * List all events
	 */
	protected function listEvents()
	{
		global $tpl;

		// table of examination events
		include_once("./Services/ADN/ES/classes/class.adnExaminationEventTableGUI.php");
		$table = new adnExaminationEventTableGUI($this, "listEvents",
			adnExaminationEventTableGUI::MODE_NOTIFICATION, true);
		
		// output table
		$tpl->setContent($table->getHTML());
	}

	/**
	 * Apply filter settings
	 */
	protected function applyFilter()
	{
		include_once("./Services/ADN/ES/classes/class.adnExaminationEventTableGUI.php");
		$table = new adnExaminationEventTableGUI($this, "listEvents",
			adnExaminationEventTableGUI::MODE_NOTIFICATION, true);
		$table->resetOffset();
		$table->writeFilterToSession();

		$this->listEvents();
	}

	/**
	 * Reset filter settings
	 */
	protected function resetFilter()
	{
		include_once("./Services/ADN/ES/classes/class.adnExaminationEventTableGUI.php");
		$table = new adnExaminationEventTableGUI($this, "listEvents",
			adnExaminationEventTableGUI::MODE_NOTIFICATION, true);
		$table->resetOffset();
		$table->resetFilter();

		$this->listEvents();
	}

	/**
	 * List event participants
	 */
	protected function listParticipants()
	{
		global $tpl, $ilCtrl, $ilTabs, $lng;

		$ilTabs->setBackTarget($lng->txt("back"), $ilCtrl->getLinkTarget($this, "listEvents"));

		$event_id = (int)$_REQUEST["ev_id"];
		if(!$event_id)
		{
			return;
		}

		// table of examination events
		include_once("./Services/ADN/ES/classes/class.adnCandidateTableGUI.php");
		$table = new adnCandidateTableGUI($this, "listParticipants", $event_id, true);

		// output table
		$tpl->setContent($table->getHTML());
	}

	/**
	 * Create notification letters
	 */	
	protected function createLetters()
	{
		global $lng,$ilCtrl;
		
		$event_id = $_REQUEST['ev_id'];
		$ass_ids = $_REQUEST['ass_id'];
		if(empty($ass_ids))
		{
			ilUtil::sendFailure($lng->txt('select_one'),true);
			$ilCtrl->redirect($this,'listParticipants');
		}
		// create report
		include_once './Services/ADN/Report/exceptions/class.adnReportException.php';
		try
		{
			include_once './Services/ADN/EP/classes/class.adnExaminationEvent.php';
			include_once("./Services/ADN/Report/classes/class.adnReportScoreNotificationLetter.php");
			$report = new adnReportScoreNotificationLetter(new adnExaminationEvent($event_id));
			$report->setAssignments($ass_ids);
			$report->create();
		
			ilUtil::sendSuccess($lng->txt('adn_report_created_score_notification'),true);
			$ilCtrl->redirect($this,'listParticipants');		
		}
		catch(adnReportException $e)
		{
			ilUtil::sendFailure($e->getMessage(),true);
			$ilCtrl->redirect($this,'listParticipants');
		}
		catch(InvalidArgumentException $e)
		{
			ilUtil::sendFailure($lng->txt('adn_report_score_err_not_scored'),true);
			$ilCtrl->redirect($this,'listParticipants');
		}
	}
	
	/**
	 * Download one letter
	 */	
	protected function downloadLetter()
	{
		global $lng,$ilCtrl;
		
		$event_id = $_REQUEST['ev_id'];
		$ass_id = $_REQUEST['ass_id'];
		if(!$ass_id or !$event_id)
		{
			ilUtil::sendFailure($lng->txt('select_one'),true);
			$ilCtrl->redirect($this,'listParticipants');
		}
		
		include_once("./Services/ADN/Report/classes/class.adnReportScoreNotificationLetter.php");
		ilUtil::deliverFile(
			adnReportScoreNotificationLetter::getFile($event_id, $ass_id),
			'Antwortschreiben_'.$ass_id.'.pdf',
			'application/pdf'
		);
	}
}

?>