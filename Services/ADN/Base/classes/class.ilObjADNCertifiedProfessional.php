<?php
/* Copyright (c) 2010 Leifos, GPL, see docs/LICENSE */

require_once "./Services/Object/classes/class.ilObject.php";

/**
 * Settings/Permission object class for ADN certified professional
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id: class.ilObjADNCertifiedProfessional.php 27867 2011-02-25 09:35:47Z akill $
 *
 * @ingroup ServicesADN
 */
class ilObjADNCertifiedProfessional extends ilObject
{
	/**
	 * Constructor
	 *
	 * @param	integer	reference_id or object_id
	 * @param	boolean	treat the id as reference_id (true) or object_id (false)
	 */
	function ilObjADNCertifiedProfessional($a_id = 0,$a_call_by_reference = true)
	{
		$this->type = "xacp";
		$this->ilObject($a_id,$a_call_by_reference);
	}
}
?>
