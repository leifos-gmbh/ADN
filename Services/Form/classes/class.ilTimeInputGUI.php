<?php
// adn-patch start

/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * This class represents a time property in a property form.
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @version $Id$
 * @ingroup	ServicesForm
 */
include_once("./Services/Table/interfaces/interface.ilTableFilterItem.php");
class ilTimeInputGUI extends ilSubEnabledFormPropertyGUI implements ilTableFilterItem
{
	protected $time = "00:00:00";
	protected $minute_step_size = 1;

	/**
	 * Constructor
	 *
	 * @param	string	$a_title	Title
	 * @param	string	$a_postvar	Post Variable
	 */
	function __construct($a_title = "", $a_postvar = "")
	{
		parent::__construct($a_title, $a_postvar);
		$this->setType("time");
	}

	/**
	 * Set minute step size
	 * E.g 5 => The selection will only show 00,05,10... minutes
	 *
	 * @access public
	 * @param int minute step_size 1,5,10,15,20...
	 *
	 */
	public function setMinuteStepSize($a_step_size)
	{
		$this->minute_step_size = $a_step_size;
	}

	/**
	 * Get minute step size
	 *
	 * @access public
	 *
	 */
	public function getMinuteStepSize()
	{
		return $this->minute_step_size;
	}

	/**
	 * set value, HH:MM:SS
	 *
	 * @param	string	$a_value
	 */
	function setValue($a_value)
	{
		$this->time = (string)$a_value;
	}

	/**
	 * Get value, HH:MM:SS
	 *
	 * @return	string
	 */
	function getValue()
	{
		return $this->time;
	}

	/**
	 * Set value by array
	 *
	 * @param	array	$a_values	value array
	 */
	function setValueByArray($a_values)
	{
		$post = $a_values[$this->getPostVar()];
		if($post)
		{
			$hours = (int)$post["h"];
			$minutes = (int)$post["m"];
			$this->setValue(str_pad($hours, 2, "0", STR_PAD_LEFT).":".
				str_pad($minutes, 2, "0", STR_PAD_LEFT).":00");
		}

		foreach($this->getSubItems() as $item)
		{
			$item->setValueByArray($a_values);
		}
	}

	/**
	 * Check input, strip slashes etc. set alert, if input is not ok.
	 *
	 * @return	boolean		Input ok, true/false
	 */
	function checkInput()
	{
		global $ilUser;

		if ($this->getDisabled())
		{
			return true;
		}

		$post = $_POST[$this->getPostVar()];
		if(isset($post["h"]) && isset($post["m"]))
		{
			$hours = (int)$post["h"];
			$minutes = (int)$post["m"];
			if($hours >= 0 && $hours < 24 && $minutes >= 0 && $minutes < 60)
			{
				return true;
			}
		}

		return false;
	}

	/**
	 * Insert property html
	 *
	 */
	function render()
	{
		global $lng,$ilUser;

		$tpl = new ilTemplate("tpl.prop_time.html", true, true, "Services/Form");

		$tpl->setCurrentBlock("prop_time");

		$hours = $minutes = NULL;
		$value = $this->getValue();
		if($value)
		{
			$value = explode(":", $value);
			$hours = $value[0];
			$minutes = $value[1];
		}

		$tpl->setVariable("TIME_SELECT",
			ilUtil::makeTimeSelect($this->getPostVar(), true,
				$hours, $minutes, 0,
				true, array('minute_steps' => $this->getMinuteStepSize(),
					'disabled' => $this->getDisabled())));

		$tpl->setVariable("TXT_TIME", $lng->txt("adn_hh_mm"));
		$tpl->parseCurrentBlock();

		return $tpl->get();
	}

	/**
	 * Insert property html
	 *
	 * @return	int	Size
	 */
	function insert(&$a_tpl)
	{
		$html = $this->render();

		$a_tpl->setCurrentBlock("prop_generic");
		$a_tpl->setVariable("PROP_GENERIC", $html);
		$a_tpl->parseCurrentBlock();
	}

	/**
	 * Get HTML for table filter
	 */
	function getTableFilterHTML()
	{
		$html = $this->render();
		return $html;
	}

	/**
	 * parse post value to make it comparable
	 *
	 * used by combination input gui
	 */
	function getPostValueForComparison()
	{
		return $_POST[$this->getPostVar()]["h"].
			str_pad($_POST[$this->getPostVar()]["m"], 2, "0", STR_PAD_LEFT);
	}
}
// adn-patch end
?>