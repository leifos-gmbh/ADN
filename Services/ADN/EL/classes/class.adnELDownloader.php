<?php
/* Copyright (c) 2012 Leifos, GPL, see docs/LICENSE */

include_once("./Services/ADN/Base/classes/class.adnDBBase.php");
include_once("./Services/ADN/ED/classes/class.adnMCQuestion.php");
include_once("./Services/ADN/EC/classes/class.adnTest.php");
include_once ("./Services/Authentication/classes/class.ilAuthUtils.php");		// to get auth mode constants

/**
 * E-Learning downloader class
 *
 * @author Alex Killing <killing@leifos.com>
 * @version $Id$
 *
 * @ingroup ServicesADN
 *
 */
class adnELDownloader
{
	/**
	 * Constructor
	 */
	function __construct()
	{
		
	}

	/**
	 * Extract client and client based session id
	 *
	 * @param string $sid soap session id
	 */
	function __explodeSid($sid)
	{
		$exploded = explode('::',$sid);

		return is_array($exploded) ? $exploded : array('sid' => '','client' => '');
	}

	/**
	 * Init authentication (client id, session id) so that ILIAS initialisation
	 * will work.
	 *
	 * @param string $sid session id
	 */
	public function initAuth($sid)
	{
		list($sid,$client) = $this->__explodeSid($sid);
		define('CLIENT_ID',$client);
		$_COOKIE['ilClientId'] = $client;
		$_COOKIE['PHPSESSID'] = $sid;
	}

	/**
	 * Init ILIAS (soap mode)
	 */
	public function initIlias()
	{
		include_once("./Services/Init/classes/class.ilInitialisation.php");
		$init = new ilInitialisation();
		return $init->initILIAS("soap");
	}

	/**
	 * Process download
	 */
	function process()
	{
		switch($_REQUEST["cmd"])
		{
			// send scoring sheet as download
			case "scoring_sheet":
				$this->initAuth($_REQUEST["sid"]);
				$this->initIlias();
				
				global $lng;
				$lng->loadLanguageModule("adn");
				
//				var_dump($_SESSION["sheet_questions"]);
//				var_dump($_SESSION["given_answer"]);
				include_once("./Services/ADN/Report/classes/class.adnReportOnlineExam.php");
				$report = new adnReportOnlineExam();
				$report->createELearningSheet($_SESSION['sheet_questions'],$_SESSION['given_answer']);
				ilUtil::deliverFile(
					$report->getOutfile(),
					'Loesungsbogen.pdf',
					'application/pdf'
				);
				break;

			// send info sheet
			case "info_sheet":
				$this->initAuth($_REQUEST["sid"]);
				$this->initIlias();
				include_once("./Services/ADN/EP/classes/class.adnExamInfoLetter.php");
				$letter = new adnExamInfoLetter((int) $_GET["id"]);
				$file = $letter->getFilePath().$letter->getId();
				if(file_exists($file))
				{
					ilUtil::deliverFile($file, $letter->getFileName());
				}
				break;
				
			case "image":
				$this->initAuth($_REQUEST["sid"]);
				$this->initIlias();

				$q_id = (int) $_GET["q_id"];
				include_once("./Services/ADN/ED/classes/class.adnMCQuestion.php");
				$question = new adnMCQuestion($q_id);
				if($question)
				{
					$id = (string)$_REQUEST["img"];
					$file = $question->getFilePath().$q_id."_".$id;
					if(file_exists($file))
					{
						ilUtil::deliverFile($file, $question->getFileName($id));
					}
				}
				break;
		}
	}
}

?>
