<?php
// cr-008 start
/* Copyright (c) 1998-2017 Leifos GmbH, Extended GPL, see docs/LICENSE */

include_once "Services/Cron/classes/class.ilCronJob.php";
include_once("./Services/ADN/Base/classes/class.adnDBBase.php");

/**
 * Notification on old personal data
 *
 * @author Alex Killing <killing@leifos.com>
 */
class adnPersonalDataNotification extends ilCronJob
{
	public function getId()
	{
		return "adn_pd_notification";
	}

	public function getTitle()
	{
		global $lng;

		return $lng->txt("adn_personal_data_notification");
	}

	public function getDescription()
	{
		global $lng;

		return $lng->txt("adn_personal_data_notification_desc");
	}

	public function getDefaultScheduleType()
	{
		return self::SCHEDULE_TYPE_DAILY;
	}

	public function getDefaultScheduleValue()
	{
		return;
	}

	public function hasAutoActivation()
	{
		return false;
	}

	public function hasFlexibleSchedule()
	{
		return true;
	}

	public function run()
	{
		global $lng;

		$status = ilCronJobResult::STATUS_NO_ACTION;
		$status_details = null;

		$setting = new ilSetting("cron");
		$last_run = $setting->get(get_class($this));

		// no last run?
		if(!$last_run)
		{
			$last_run = date("Y-m-d H:i:s", strtotime("yesterday"));

			$status_details = "No previous run found - starting from yesterday.";
		}
		// migration: used to be date-only value
		else if(strlen($last_run) == 10)
		{
			$last_run .= " 00:00:00";

			$status_details = "Switched from daily runs to open schedule.";
		}

		include_once "Services/ADN/AD/classes/class.adnPersonalData.php";
		$cand = adnPersonalData::getData(array(), "cand");
		$wmo = array();
		foreach ($cand as $c)
		{
			$wmo[$c["registered_by_wmo_id"]]["cand"][] = $c;
		}
		$cert = adnPersonalData::getData(array(), "cert");
//		var_dump($cand);
//		var_dump($cert);
//		exit;
		foreach ($cert as $c)
		{
			$wmo[$c["registered_by_wmo_id"]]["cert"][] = $c;
		}
//var_dump($wmo); exit;
		foreach ($wmo as $wmo_id => $data)
		{
			$cand_nr =  (is_array($data["cand"]))
				? count($data["cand"])
				: 0;
			$cert_nr =  (is_array($data["cert"]))
				? count($data["cert"])
				: 0;
			if (($cand_nr + $cert_nr) > 0)
			{
				// send email to wmo
				$this->sendMail($wmo_id, $cand_nr, $cert_nr);

				// mails were sent - set cron job status accordingly
				$status = ilCronJobResult::STATUS_OK;
			}
		}
		// save last run
		$setting->set(get_class($this), date("Y-m-d H:i:s"));

		$result = new ilCronJobResult();
		$result->setStatus($status);

		if($status_details)
		{
			$result->setMessage($status_details);
		}

		return $result;
	}


	/**
	 * Send news mail for 1 user and n objects
	 *
	 * @param int $a_wmo_id
	 * @param int $a_cand_nr
	 * @param int $a_cert_nr
	 */
	protected function sendMail($a_wmo_id, $a_cand_nr, $a_cert_nr)
	{
		global $lng;

		//include_once "./Services/Notification/classes/class.ilSystemNotification.php";
		//$ntf = new ilSystemNotification();
		//$ntf->setLangModules(array("crs", "news"));

		// user specific language
		//$lng = "de";

		/*include_once './Services/Locator/classes/class.ilLocatorGUI.php';
		require_once "HTML/Template/ITX.php";
		require_once "./Services/UICore/classes/class.ilTemplateHTMLITX.php";
		require_once "./Services/UICore/classes/class.ilTemplate.php";
		require_once "./Services/Link/classes/class.ilLink.php";*/

		include_once("./Services/ADN/MD/classes/class.adnWMO.php");
		$wmo = new adnWMO($a_wmo_id);
		$mail_adress = $wmo->getEmail();

		$lng->loadLanguageModule("adn");
		$message = $lng->txtlng("adn", "adn_new_delete_candidates_mess", "de");
		$subject = $lng->txtlng("adn", "adn_new_delete_candidates_subj", "de");

		$message = str_replace("{WSD}", $wmo->getName(), $message);
		$user_str = "";
		if ($a_cand_nr > 0)
		{
			$user_str.= $lng->txt("adn_ad_pd_cand").": ".$a_cand_nr." <br />".ILIAS_HTTP_PATH."/goto.php?client_id=".CLIENT_ID.
				"&target=adn_candd_".$wmo->getId()."<br />";
		}
		if ($a_cert_nr > 0)
		{
			$user_str.= $lng->txt("adn_ad_pd_cert").": ".$a_cert_nr." <br />".ILIAS_HTTP_PATH."/goto.php?client_id=".CLIENT_ID.
				"&target=adn_certd_".$wmo->getId()."<br />";
		}
		$message = str_replace("{USER}", $user_str, $message);

		// #10044
		include_once("./Services/Mail/classes/class.ilMail.php");
		$mail = new ilMail(ANONYMOUS_USER_ID);
		$mail->enableSOAP(false); // #10410
//	echo "-$a_wmo_id-$mail_adress-";
		$ret = $mail->sendMail($mail_adress,
			null,
			null,
			$subject,
			$message,
			null,
			array("email"));
	//echo $ret; exit;
	}

	/*public function addToExternalSettingsForm($a_form_id, array &$a_fields, $a_is_active)
	{
		global $lng;

		switch($a_form_id)
		{
			case ilAdministrationSettingsFormHandler::FORM_COURSE:
			case ilAdministrationSettingsFormHandler::FORM_GROUP:
				$a_fields["enable_course_group_notifications"] = $a_is_active ?
					$lng->txt("enabled") :
					$lng->txt("disabled");
				break;
		}
	}*/

}
// cr-008 end
?>