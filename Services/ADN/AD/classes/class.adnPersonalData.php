<?php

/* Copyright (c) 1998-2014 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Get personal data
 *
 * @author Alex Killing <alex.killing@gmx.de>
 */
class adnPersonalData
{
	/**
	 * Get personal data
	 *
	 * @param
	 * @return
	 */
	static function getData($a_filter, $a_mode)
	{
		include_once("./Services/ADN/ES/classes/class.adnCertifiedProfessional.php");
		$pd_data = adnCertifiedProfessional::getAllCandidates($a_filter);

		include_once("./Services/ADN/ES/classes/class.adnCertificate.php");
		$cert_data = adnCertificate::getAllCertificates(array(), true, true);
		$last_certificate = array();
		foreach ($cert_data as $c)
		{
			if ($c["issued_on"] != "" && $c["issued_on"] > $last_certificate[$c["cp_professional_id"]])
			{
				$last_certificate[$c["cp_professional_id"]] = $c["issued_on"];
			}
		}

		$date = new DateTime(date('Y-m-d'));
		$date->sub(new DateInterval('P2Y'));
		$y2 = $date->format('Y-m-d');
		$date->sub(new DateInterval('P5Y'));
		$y7 = $date->format('Y-m-d');

		// get date of last certificate
		$data = array();
		foreach ($pd_data as $k => $v)
		{
			$v["last_certificate"] = $last_certificate[$v["id"]];
			if (($a_mode != "cand" || ($v["last_certificate"] == "" && $v["create_date"] < $y2)) &&
				($a_mode != "cert" || ($v["last_certificate"] != "" && $v["last_certificate"] < $y7))
			)
			{
				$data[$k] = $v;
			}
		}

		return $data;
	}

}

?>