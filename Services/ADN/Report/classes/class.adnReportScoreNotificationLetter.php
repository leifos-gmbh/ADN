<?php
/* Copyright (c) 2010 Leifos, GPL, see docs/LICENSE */

include_once './Services/ADN/Report/classes/class.adnReport.php';
include_once './Services/ADN/MD/classes/class.adnWMO.php';

/**
 * Generation of score notification reports
 * Menu "Examination Scoring -> Score Notification Letter"
 * Different reports for status SUCCESS, FAILED
 *
 * @author Stefan Meyer <meyer@leifos.com>
 * @version $Id: class.adnReportScoreNotificationLetter.php 31054 2011-10-09 12:27:57Z smeyer $
 *
 * @ingroup ServicesADN
 */

class adnReportScoreNotificationLetter extends adnReport
{
	const TYPE_DM = 1;
	const TYPE_GAS = 2;
	const TYPE_CHEM = 3;
	
	const SUCCESS = 1;
	const FAILED = 2;
	const NOT_SCORED = 3;
	const FAILED_LIMIT = 4;
	const FAILED_BOTH = 5;
	const FAILED_MC = 6;
	const FAILED_CASE = 7;
	
	private $event = null;
	private $type = null;
	
	private $assignments = array();

	/**
	 * Contructor
	 * @return 
	 */
	public function __construct(adnExaminationEvent $event)
	{
		parent::__construct();
		$this->event = $event;
		
		include_once './Services/ADN/MD/classes/class.adnExamFacility.php';
		$fac = new adnExamFacility($this->getEvent()->getFacility());
		$this->wmo = new adnWMO($fac->getWMO());

	}
	
	/**
	 * Check if report exists
	 * @param object $event_id
	 * @param int $ass_id
	 * @return bool
	 * @access static
	 */
	public static function hasFile($event_id,$ass_id)
	{
		return file_exists(ilUtil::getDataDir().'/adn/report/sno/'.$event_id.'_'.$ass_id.'.pdf');
	}

	// cr-008 start
	/**
	 * Delete report
	 * @param object $event_id
	 * @param object $ass_id
	 */
	public static function deleteFile($event_id,$ass_id)
	{
		$file = ilUtil::getDataDir().'/adn/report/sno/'.$event_id.'_'.$ass_id.'.pdf';
		if (file_exists($file))
		{
			unlink($file);
		}
	}
	// cr-008 end
	
	/**
	 * Get file
	 * @param object $event_id
	 * @param object $ass_id
	 * @return bool
	 * @access static
	 */
	public static function getFile($event_id,$ass_id)
	{
		return ilUtil::getDataDir().'/adn/report/sno/'.$event_id.'_'.$ass_id.'.pdf';
	}
	
	
	
	/**
	 * Get relative data dir
	 * @return string
	 */
	public function getRelativeDataDir()
	{
		return 'sno';
	}

	/**
	 * Set report type
	 * @param object $a_type
	 * @return void
	 */
	public function setType($a_type)
	{
		$this->type = $a_type;
	}
	
	/**
	 * Get report type
	 * @return string
	 */
	public function getType()
	{
		return $this->type;
	}
	
	/**
	 * get certificate object
	 * @return object adnCertificate
	 */
	public function getEvent()
	{
		return $this->event;
	}
	
	/**
	 * Set candidates
	 * 
	 * @param array candidates
	 * @return 
	 */
	public function setAssignments($a_cand)
	{
		$this->assignments = $a_cand;
	}

	/**
	 * Get candidates
	 * @return 
	 */
	public function getAssignments()
	{
		return (array) $this->assignments;
	}
	
	/**
	 * Get wmo
	 * @return object adnWMO
	 */
	public function getWMO()
	{
		return $this->wmo;
	}
	

	/**
	 * Create report
	 * @return
	 * 
	 * @throws adnReportException
	 */
	public function create()
	{
		global $ilUser;
		
		include_once './Services/ADN/Report/classes/class.adnReportFileUtils.php';
		
		// Template by type
		switch($this->getEvent()->getType())
		{
			case 'gas':
				$this->setType(self::TYPE_GAS);
				$form[self::SUCCESS] =
					adnReportFileUtils::getTemplatePathByType(
					adnReportFileUtils::TPL_REPORT_SCORE_NOTIFICATION_SUCCESS_GAS);
				$form[self::FAILED_LIMIT] = adnReportFileUtils::getTemplatePathByType(
					adnReportFileUtils::TPL_REPORT_SCORE_NOTIFICATION_FAILED_GAS_LIMIT);
				$form[self::FAILED_BOTH] = adnReportFileUtils::getTemplatePathByType(
					adnReportFileUtils::TPL_REPORT_SCORE_NOTIFICATION_FAILED_GAS_BOTH);
				$form[self::FAILED_MC] = adnReportFileUtils::getTemplatePathByType(
					adnReportFileUtils::TPL_REPORT_SCORE_NOTIFICATION_FAILED_GAS_MC);
				$form[self::FAILED_CASE] = adnReportFileUtils::getTemplatePathByType(
					adnReportFileUtils::TPL_REPORT_SCORE_NOTIFICATION_FAILED_GAS_CASE);
				break;
				
			case 'chem':
				$this->setType(self::TYPE_CHEM);
				$form[self::SUCCESS] = adnReportFileUtils::getTemplatePathByType(
					adnReportFileUtils::TPL_REPORT_SCORE_NOTIFICATION_SUCCESS_CHEM);
				$form[self::FAILED_LIMIT] = adnReportFileUtils::getTemplatePathByType(
					adnReportFileUtils::TPL_REPORT_SCORE_NOTIFICATION_FAILED_CHEM_LIMIT);
				$form[self::FAILED_BOTH] = adnReportFileUtils::getTemplatePathByType(
					adnReportFileUtils::TPL_REPORT_SCORE_NOTIFICATION_FAILED_CHEM_BOTH);
				$form[self::FAILED_MC] = adnReportFileUtils::getTemplatePathByType(
					adnReportFileUtils::TPL_REPORT_SCORE_NOTIFICATION_FAILED_CHEM_MC);
				$form[self::FAILED_CASE] = adnReportFileUtils::getTemplatePathByType(
					adnReportFileUtils::TPL_REPORT_SCORE_NOTIFICATION_FAILED_CHEM_CASE);
				break;

			case 'dm':
				$this->setType(self::TYPE_DM);
				$form[self::FAILED] = adnReportFileUtils::getTemplatePathByType(
					adnReportFileUtils::TPL_REPORT_SCORE_NOTIFICATION_FAILED_DM);
				break;

			case 'tank':
				$this->setType(self::TYPE_DM);
				$form[self::FAILED] = adnReportFileUtils::getTemplatePathByType(
					adnReportFileUtils::TPL_REPORT_SCORE_NOTIFICATION_FAILED_TANK);
				break;

			case 'comb':
				$this->setType(self::TYPE_DM);
				$form[self::FAILED] = adnReportFileUtils::getTemplatePathByType(
					adnReportFileUtils::TPL_REPORT_SCORE_NOTIFICATION_FAILED_COMB);
				break;
		}
		
		// Write on task (fillPdfTemplate for every candidate) and finally merge them in one PDF.
		include_once './Services/ADN/Report/classes/class.adnTaskScheduleWriter.php';
		$writer = new adnTaskScheduleWriter();
		$writer->xmlStartTag('tasks');

		include_once './Services/ADN/MD/classes/class.adnExamFacility.php';
		$fac = new adnExamFacility($this->getEvent()->getFacility());
		$wmo = $fac->getWMO();

		$all_outfiles = array();
		$count_created = 0;
		foreach($this->getAssignments() as $ass_id)
		{
			include_once './Services/ADN/EP/classes/class.adnAssignment.php';
			$assignment = new adnAssignment($ass_id);
			
			// get candidate
			include_once './Services/ADN/ES/classes/class.adnCertifiedProfessional.php';
			$candidate_id = $assignment->getUser();
			$cand = new adnCertifiedProfessional($candidate_id);

			if($this->getStatus($assignment,$cand) == self::NOT_SCORED)
			{
				continue;
			}
			$count_created++;
			
			$current_form = $form[$this->getStatus($assignment,$cand)];
			
			$writer->xmlStartTag('action',
				array(
					'method'	=> 'fillPdfTemplate'
				)
			);
			
			$outfile = $this->getDataDir().'/'.$this->getEvent()->getId().'_'.$ass_id.'.pdf';
			
			$writer->addParameter('string',$current_form);
			$writer->addParameter('string',$outfile);
				
			
			// DONE: fill map
			$map = $this->addStandardRightColumn(
				$map,
				$wmo,
				$ilUser->getId()
			);
			
			//  Fill standard address
			$map = $this->addStandardAddress(
				$map,
				$wmo,
				$cand->getId()
			);
			// Fill score fields
			$map = $this->addScoreFields(
				$map,
				$cand,
				$assignment
			);
			$writer->addParameter('map',$map);
			$writer->xmlEndTag('action');
		}
		
		if(!$count_created)
		{
			throw new InvalidArgumentException();
		}
		
		// Merge all single pdf's to one pdf
		/*
		$writer->xmlStartTag('action',
			array(
				'method'	=> 'mergePdf'
			)
		);
		$writer->addParameter('vector',$all_outfiles);
		$writer->addParameter('string',$this->initOutfile());
		$writer->xmlEndTag('action');
		*/
		$writer->xmlEndTag('tasks');

		$GLOBALS['ilLog']->write($writer->xmlDumpMem(true));
		
		try
		{
			$adapter = new adnRpcAdapter();
			$adapter->transformationTaskScheduler(
				$writer->xmlDumpMem()
			);
		}
		catch(adnReportException $e)
		{
			throw $e;
		}
	}
	
	/**
	 * Get status of candidate
	 * @param adnAssignment $assignment
	 * @param adnCertifiedProfessional $candidate
	 * @return 
	 */
	protected function getStatus(adnAssignment $assignment,$candidate)
	{
		switch($this->getEvent()->getType())
		{
			case 'gas':
			case 'chem':
				if(
					$assignment->getResultMc() == adnAssignment::SCORE_NOT_SCORED or
					$assignment->getResultCase() == adnAssignment::SCORE_NOT_SCORED)
				{
					return self::NOT_SCORED;
				}

				if(
					$assignment->getResultMc() == adnAssignment::SCORE_FAILED and
					$assignment->getResultCase() == adnAssignment::SCORE_FAILED)
				{
					return self::FAILED_BOTH;
				}
				if(
					$assignment->getResultMc() == adnAssignment::SCORE_FAILED)
				{
					return self::FAILED_MC;
				}
				if(
					$assignment->getResultCase() == adnAssignment::SCORE_FAILED)
				{
					return self::FAILED_CASE;
				}
				if($assignment->getScoreSum() < adnAssignment::TOTAL_SCORE_REQUIRED)
				{
					return self::FAILED_LIMIT;
				}
				return self::SUCCESS;
				
			default:
				if($assignment->getResultMc() == adnAssignment::SCORE_NOT_SCORED)
				{
					return self::NOT_SCORED;
				}
				if($assignment->getResultMc() == adnAssignment::SCORE_FAILED)
				{
					return self::FAILED;
				}
				return self::SUCCESS;
		}
	}
	
	/**
	 * Get points info
	 * @param object adnAssignment
	 * @return 
	 */
	protected function getPoints($assignment)
	{
		switch($this->getEvent()->getType())
		{
			case 'gas':
			case 'chem':
				return array(
					'reached' 	=> $assignment->getScoreMc() + $assignment->getScoreCase(),
					'possible' 	=> 60
				);

					
			default:
				return array(
					'reached' 	=> $assignment->getScoreMc(),
					'possible'	=> 30
				);
		}
		
	}
	
	/**
	 * Add invitation specific fields
	 * @param array $map
	 * @param int wmo
	 * @param int candidate 
	 * @return
	 * @param object $map
	 * @param adnCertifiedProfessional $cand
	 */
	public function addScoreFields($map,$cand,$assignment)
	{
		global $lng,$ilUser;
		
		$lng->loadLanguageModule('dateplaner');
		
		include_once './Services/Calendar/classes/class.ilCalendarUtil.php';

		$map['rcp_salutation'] = 
			$lng->txt('adn_report_salutation_'.$cand->getSalutation()).' '.
			$cand->getLastName().', ';
		
		$map['iss_lastname'] = $ilUser->getLastname();
		$map['exam_wsd'] = $this->getWMO()->getName();
		
		// Costs 
		$costs = $this->getWMO()->getCostCertificate();
		$map['exam_charge'] = sprintf('%01.2f EUR',$costs['value']);
		$map['exam_charge'] = str_replace('.', ',',$map['exam_charge']);
		
		// Date of exam
		$exam = $this->getEvent()->getDateFrom();
		$weekday = $exam->get(IL_CAL_FKT_DATE,'D');
		$weekday = $lng->txt(substr($weekday,0,2).'_long');
		$map['exam_date'] = sprintf(
			$lng->txt('adn_date_long'),
			$weekday,
			$exam->get(IL_CAL_FKT_DATE,'d.m.Y')
		);

		$points = $this->getPoints($assignment);
		$map['exam_points'] = sprintf(
			$lng->txt('adn_report_score_points'),
			$points['reached'],
			$points['possible']
		);
		$map['exam_points'] = str_replace('.', ',', $map['exam_points']);
		
		$map['exam_points_mc'] = sprintf(
			$lng->txt('adn_report_score_points'),
			$assignment->getScoreMc(),
			30
		);
		$map['exam_points_mc'] = str_replace('.', ',', $map['exam_points_mc']);
			
		$map['exam_points_case'] = sprintf(
			$lng->txt('adn_report_score_points'),
			$assignment->getScoreCase(),
			30
		);
		$map['exam_points_case'] = str_replace('.', ',', $map['exam_points_case']);


		if($cand->getBlockedUntil() instanceof ilDate)
		{
			$end = $cand->getBlockedUntil();
			$map['limit'] = (string) $end->get(IL_CAL_FKT_DATE,'d').'. '.
				$lng->txt('month_'.$end->get(IL_CAL_FKT_DATE,'m').'_long').' '.
				$end->get(IL_CAL_FKT_DATE,'Y');
		}
		else
		{
			$map['limit'] = (string) '';
		}

		$map['legal'] = $this->getLegalRemedies($this->getWMO());
		
		return $map;
	}
}
?>