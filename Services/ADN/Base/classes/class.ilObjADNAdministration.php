<?php
/* Copyright (c) 2010 Leifos, GPL, see docs/LICENSE */

require_once "./Services/Object/classes/class.ilObject.php";

/**
 * Settings/Permission object class for ADN training administration
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id: class.ilObjADNAdministration.php 27867 2011-02-25 09:35:47Z akill $
 *
 * @ingroup ServicesADN
 */
class ilObjADNAdministration extends ilObject
{
	/**
	* Constructor
	* @access	public
	* @param	integer	reference_id or object_id
	* @param	boolean	treat the id as reference_id (true) or object_id (false)
	*/
	function ilObjADNAdministration($a_id = 0,$a_call_by_reference = true)
	{
		$this->type = "xaad";
		$this->ilObject($a_id,$a_call_by_reference);
	}
}
?>
