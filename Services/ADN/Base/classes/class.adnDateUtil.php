<?php
/* Copyright (c) 2010 Leifos, GPL, see docs/LICENSE */

/**
 * Date utilities class (helper functions)
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.de>
 * @version $Id: class.adnDateUtil.php 27871 2011-02-25 15:29:26Z jluetzen $
 *
 * @ingroup ServicesADN
 */
class adnDateUtil
{
	/**
	 * Calculate age in years from given date
	 *
	 * @param ilDateTime $a_date date
	 * @param ilDateTime $a_reference reference date (now if not given)
	 * @return int
	 */
	public static function getAge(ilDateTime $a_date, ilDateTime $a_reference = null)
    {
		if(!$a_reference)
		{
			$a_reference = new ilDateTime(time(), IL_CAL_UNIX);
		}
		$stamp = $a_date->get(IL_CAL_UNIX);
		$ref = $a_reference->get(IL_CAL_UNIX);
		if($stamp < $ref)
		{
			return floor(($ref - $stamp)/(60*60*24*365.2425));
		}
	}
}
?>