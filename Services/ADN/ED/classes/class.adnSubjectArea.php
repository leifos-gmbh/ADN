<?php
/* Copyright (c) 2010 Leifos, GPL, see docs/LICENSE */

/**
 * ADN subject area application class
 *
 * This is an internal helper class to centralize the subject area handling
 *
 * @author Alex Killing <killing@leifos.de>
 * @version $Id: class.adnSubjectArea.php 27874 2011-02-25 16:36:28Z jluetzen $
 *
 * @ingroup ServicesADN
 */
class adnSubjectArea
{
	const DRY_MATERIAL = "dm";
	const TANK = "tank";
	const COMBINED = "comb";
	const GAS = "gas";
	const CHEMICAL = "chem";

	/**
	 * Get all areas
	 *
	 * @return array array of subject areas. key is id, value is text representation
	 */
	static function getAllAreas()
	{
		global $lng;

		return array(
			self::DRY_MATERIAL => $lng->txt("adn_subject_area_".self::DRY_MATERIAL),
			self::TANK => $lng->txt("adn_subject_area_".self::TANK),
			self::COMBINED => $lng->txt("adn_subject_area_".self::COMBINED),
			self::GAS => $lng->txt("adn_subject_area_".self::GAS),
			self::CHEMICAL => $lng->txt("adn_subject_area_".self::CHEMICAL),
		);
	}

	/**
	 * Get all areas that have a case question part in examination
	 *
	 * @return array array of subject areas. key is id, value is text representation
	 */
	static function getAreasWithCasePart()
	{
		global $lng;

		return array(
			self::GAS => $lng->txt("adn_subject_area_".self::GAS),
			self::CHEMICAL => $lng->txt("adn_subject_area_".self::CHEMICAL),
		);
	}

	/**
	 * Has case part
	 *
	 * @param string subject area
	 * @return boolean true/false
	 */
	static function hasCasePart($a_subj_area)
	{
		$sub_with_case = self::getAreasWithCasePart();
		return array_key_exists($a_subj_area, $sub_with_case);
	}

	/**
	 * Get text representation
	 *
	 * @param string $a_subject_area subject area constant
	 * @return string text representation
	 */
	static function getTextRepresentation($a_subject_area)
	{
		global $lng;
		
		return $lng->txt("adn_subject_area_".$a_subject_area);
	}

	/**
	 * Get color for subject area (use same as corresponging training type)
	 *
	 * @param string $a_area subject area
	 * @return string color code
	 */
	static function getColorForArea($a_type)
	{
		include_once("./Services/ADN/TA/classes/class.adnTypesOfTraining.php");
		return adnTypesOfTraining::getColorForType($a_type);
	}
}

?>