<?php
/* Copyright (c) 2010 Leifos, GPL, see docs/LICENSE */

/**
 * ADN types of training application class
 *
 * Centralized helper class for all available types of training, including captions and colors
 *
 * @author Alex Killing <killing@leifos.de>
 * @version $Id: class.adnTypesOfTraining.php 27891 2011-02-28 11:46:53Z jluetzen $
 *
 * @ingroup ServicesADN
 */
class adnTypesOfTraining
{
	const DRY_MATERIAL = "dm";
	const TANK = "tank";
	const COMBINED = "comb";
	const GAS = "gas";
	const CHEMICAL = "chem";
	const REP_DRY_MATERIAL = "rep_dm";
	const REP_TANK = "rep_tank";
	const REP_COMBINED = "rep_comb";
	const REP_GAS = "rep_gas";
	const REP_CHEMICAL = "rep_chem";

	/**
	 * Get all types
	 *
	 * @return array array of types of training. key is id, value is text representation
	 */
	static function getAllTypes()
	{
		global $lng;

		return array(
			self::DRY_MATERIAL => $lng->txt("adn_train_type_".self::DRY_MATERIAL),
			self::TANK => $lng->txt("adn_train_type_".self::TANK),
			self::COMBINED => $lng->txt("adn_train_type_".self::COMBINED),
			self::GAS => $lng->txt("adn_train_type_".self::GAS),
			self::CHEMICAL => $lng->txt("adn_train_type_".self::CHEMICAL),
			self::REP_DRY_MATERIAL => $lng->txt("adn_train_type_".self::REP_DRY_MATERIAL),
			self::REP_TANK => $lng->txt("adn_train_type_".self::REP_TANK),
			self::REP_COMBINED => $lng->txt("adn_train_type_".self::REP_COMBINED),
			self::REP_GAS => $lng->txt("adn_train_type_".self::REP_GAS),
			self::REP_CHEMICAL => $lng->txt("adn_train_type_".self::REP_CHEMICAL)
		);
	}

	/**
	 * Get text representation
	 *
	 * @param string $a_type_of_training type of training constant
	 * @return string text representation
	 */
	static function getTextRepresentation($a_type_of_training)
	{
		global $lng;
		
		return $lng->txt("adn_train_type_".$a_type_of_training);
	}

	/**
	 * Get color for type
	 *
	 * @param string $a_type
	 * @return string
	 */
	static function getColorForType($a_type)
	{
		switch ($a_type)
		{
			case self::DRY_MATERIAL:
				return "#D0D0FF";

			case self::TANK:
				return "#D0FFD0";

			case self::COMBINED:
				return "#FFD0D0";

			case self::GAS:
				return "#FFFFC0";

			case self::CHEMICAL:
				return "#FFC0FF";

			case self::REP_DRY_MATERIAL:
				return "#C0C0FF";

			case self::REP_TANK:
				return "#C0FFC0";

			case self::REP_COMBINED:
				return "#FFC0C0";

			case self::REP_GAS:
				return "#FFFFB0";

			case self::REP_CHEMICAL:
				return "#FFB0FF";
		}
	}
}

?>