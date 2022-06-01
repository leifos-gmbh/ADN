<?php
/* Copyright (c) 2010 Leifos, GPL, see docs/LICENSE */

/**
 * ADN question catalog numbering scheme application class
 *
 * This is the central place for all numbering scheme related methods, e.g. case/mc areas
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @version $Id: class.adnCatalogNumbering.php 27873 2011-02-25 16:21:55Z jluetzen $
 *
 * @ingroup ServicesADN
 */
class adnCatalogNumbering
{
    /**
     * Get all valid catalog areas
     *
     * @return array (code => caption)
     */
    public static function getAllAreas()
    {
        // 210/310 are case only!
        $valid = array(110, 120, 130, 210, 231, 232, 233, 310, 331, 332, 333);

        $areas = array();
        foreach ($valid as $code) {
            $areas[$code] = self::getAreaTextRepresentation($code);
        }
        return $areas;
    }

    /**
     * Is given area code valid?
     *
     * @param int $a_code
     * @return bool
     */
    public static function isValidArea($a_code)
    {
        $all = self::getAllAreas();
        if (in_array((int) $a_code, array_keys($all))) {
            return true;
        }
        return false;
    }

    /**
     * Get mc catalog areas
     *
     * @return array (code => caption)
     */
    public static function getMCAreas()
    {
        $valid = array(110, 120, 130, 231, 232, 233, 331, 332, 333);

        $areas = array();
        foreach ($valid as $code) {
            $areas[$code] = self::getAreaTextRepresentation($code);
        }
        return $areas;
    }

    /**
     * Get case catalog areas
     *
     * @return array (code => caption)
     */
    public static function getCaseAreas()
    {
        $valid = array(210, 310);

        $areas = array();
        foreach ($valid as $code) {
            $areas[$code] = self::getAreaTextRepresentation($code);
        }
        return $areas;
    }

    /**
     * Convert code level to lang id
     *
     * @param int $a_code
     * @param int $a_level
     * @return string
     */
    protected static function convertAreaCodeToLangId($a_code, $a_level)
    {
        $areas = array(11 => "base",
            12 => "gas",
            13 => "chem",
            21 => "general",
            22 => "dm",
            23 => "tank",
            30 => "basics",
            31 => "phys_chem",
            32 => "prac",
            33 => "emergency"
        );

        $code = (string) $a_code;
        $number = $code[$a_level - 1];
        $index = (int) ($a_level . $number);
        if (isset($areas[$index])) {
            return $areas[$index];
        }
    }

    /**
     * Get text representation
     *
     * @param int $a_code
     * @return string
     */
    public static function getAreaTextRepresentation($a_code)
    {
        global $DIC;
        $lng = $DIC->language();

        switch ($a_code) {
            case 210:
            case 310:
                $res = $lng->txt("adn_catalog_area_" . self::convertAreaCodeToLangId($a_code, 1));
                break;

            default:
                $res = $a_code;
                $res .= " " . $lng->txt("adn_catalog_area_" . self::convertAreaCodeToLangId($a_code, 1));
                $res .= " - " . $lng->txt("adn_catalog_area_" . self::convertAreaCodeToLangId($a_code, 2));
                $res .= " - " . $lng->txt("adn_catalog_area_" . self::convertAreaCodeToLangId($a_code, 3));
                break;
        }
        
        return $res;
    }

    /**
     * Check if given code has area base
     *
     * @param int $a_code
     * @return bool
     */
    public static function isBaseArea($a_code)
    {
        $code = (string) $a_code;
        if ($code[0] == 1) {
            return true;
        }
        return false;
    }

    /**
     * Check if given code has gas
     *
     * @param int $a_code
     * @return bool
     */
    public static function isGasArea($a_code)
    {
        $code = (string) $a_code;
        if ($code[0] == 2) {
            return true;
        }
        return false;
    }


    /**
     * Check if given code has chemicals
     *
     * @param int $a_code
     * @return bool
     */
    public static function isChemicalsArea($a_code)
    {
        $code = (string) $a_code;
        if ($code[0] == 3) {
            return true;
        }
        return false;
    }

    /**
     * Get color for catalog area
     *
     * @param string $a_type catalog area
     * @return string color code
     */
    public static function getColorForArea($a_type)
    {
        $type = (string) $a_type;
        switch ($type[0]) {
            case 1:
                $res = "#eeff" . dechex(255 - substr($type, 1) * 3);
                break;
            
            case 2:
                $res = "#ff" . dechex(255 - substr($type, 2) * 10) . "ee";
                break;
            
            case 3:
                $res = "#" . dechex(255 - substr($type, 2) * 10) . "eeff";
                break;
        }

        return $res;
    }

    /**
     * Get base areas
     *
     * @return array (ids)
     */
    public static function getBaseAreas()
    {
        $all = array();
        foreach (self::getAllAreas() as $id => $name) {
            if (self::isBaseArea($id)) {
                $all[] = $id;
            }
        }
        return $all;
    }

    /**
     * Get gas areas
     *
     * @return array (ids)
     */
    public static function getGasAreas()
    {
        $all = array();
        foreach (self::getAllAreas() as $id => $name) {
            if (self::isGasArea($id)) {
                $all[] = $id;
            }
        }
        return $all;
    }

    /**
     * Get chemicals areas
     *
     * @return array (ids)
     */
    public static function getChemicalsAreas()
    {
        $all = array();
        foreach (self::getAllAreas() as $id => $name) {
            if (self::isChemicalsArea($id)) {
                $all[] = $id;
            }
        }
        return $all;
    }
}
