<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once './Modules/Test/classes/class.ilTestServiceGUI.php';
require_once './Modules/Test/classes/class.ilTestPDFGenerator.php';
require_once './Modules/Test/classes/class.ilTestArchiver.php';

/**
 * @author		Björn Heyser <bheyser@databay.de>
 * @version		$Id$
 *
 * @package     Modules/Test
 */
class ilTestArchiveService
{
	/**
	 * @var ilObjTest
	 */
	protected $testOBJ;
	
	/**
	 * @var ilTestParticipantData
	 */
	protected $participantData;
	
	/**
	 * @var bool
	 */
	protected $considerHiddenQuestionsEnabled;
	
	/**
	 * @var ilTestResultHeaderLabelBuilder
	 */
	protected $testResultHeaderLabelBuilder;
	
	public function __construct(ilObjTest $testOBJ)
	{
		global $ilObjDataCache, $lng;
		
		$this->testOBJ = $testOBJ;
		$this->participantData = null;
		
		$this->considerHiddenQuestionsEnabled = true;
		
		require_once 'Modules/Test/classes/class.ilTestResultHeaderLabelBuilder.php';
		$this->testResultHeaderLabelBuilder = new ilTestResultHeaderLabelBuilder($lng, $ilObjDataCache);
	}
	
	/**
	 * @return ilTestParticipantData
	 */
	public function getParticipantData()
	{
		return $this->participantData;
	}
	
	/**
	 * @param ilTestParticipantData $participantData
	 */
	public function setParticipantData(ilTestParticipantData $participantData)
	{
		$this->participantData = $participantData;
	}
	
	public function isConsiderHiddenQuestionsEnabled()
	{
		return $this->considerHiddenQuestionsEnabled;
	}
	
	public function setConsiderHiddenQuestionsEnabled($considerHiddenQuestionsEnabled)
	{
		$this->considerHiddenQuestionsEnabled = $considerHiddenQuestionsEnabled;
	}
	
	public function archivePassesByActives($passesByActives)
	{
		foreach($passesByActives as $activeId => $passes)
		{
			foreach($passes as $pass)
			{
				$this->archiveActivesPass($activeId, $pass);
			}
		}
	}
	
	public function archiveActivesPass($activeId, $pass)
	{
		$content = $this->renderOverviewContent($activeId, $pass);
		$filename = $this->buildOverviewFilename($activeId, $pass);
		
		ilTestPDFGenerator::generatePDF($content, ilTestPDFGenerator::PDF_OUTPUT_FILE, $filename);

		$archiver = new ilTestArchiver($this->testOBJ->getId());
		$archiver->setParticipantData($this->getParticipantData());
		$archiver->handInTestResult($activeId, $pass, $filename);

		unlink($filename);
	}

	/**
	 * @param $activeId
	 * @param $pass
	 * @return string
	 */
	private function renderOverviewContent($activeId, $pass)
	{
		$results = $this->testOBJ->getTestResult(
			$activeId, $pass, false, $this->isConsiderHiddenQuestionsEnabled()
		);
		
		$gui = new ilTestServiceGUI($this->testOBJ);
		
		return $gui->getPassListOfAnswers(
			$results, $activeId, $pass, true, false, false, true, false, null, $this->testResultHeaderLabelBuilder
		);
	}

	/**
	 * @param $activeId
	 * @param $pass
	 * @return string
	 */
	private function buildOverviewFilename($activeId, $pass)
	{
		$tmpFileName = ilUtil::ilTempnam();
		return dirname($tmpFileName).'/scores-'.$this->testOBJ->getId().'-'.$activeId.'-'.$pass.'.pdf';
	}
}