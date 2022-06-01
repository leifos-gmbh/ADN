<?php
// cr-008 start
/* Copyright (c) 2017 Leifos, GPL, see docs/LICENSE */

/**
 * ADN goto handler
 *
 * @author Alex Killing <killing@leifos.de>
 */
class adnGotoHandler
{
    /**
     * Check
     *
     * @param string $a_target
     * @return bool
     */
    public static function _check($a_target)
    {
        $p = explode("_", $a_target);
        if ($p[0] == "adn" && in_array($p[1], array("candd", "certd")) && is_numeric($p[2])) {
            return true;
        }
        return false;
    }

    /**
     * Goto
     *
     * @param string $a_target
     */
    public static function _goto($a_target)
    {
        global $DIC;
        $ilCtrl = $DIC->ctrl();

        $ilCtrl->setTargetScript("ilias.php");
        $ilCtrl->initBaseClass("adnbasegui");

        $p = explode("_", $a_target);

        if ($p[0] == "adn" && in_array($p[1], array("candd", "certd")) && is_numeric($p[2])) {
            $ilCtrl->setParameterByClass("adnpersonaldatamaintenancegui", "target", $p[1]);
            $ilCtrl->setParameterByClass("adnpersonaldatamaintenancegui", "wmo_id", (int) $p[2]);
            $ilCtrl->redirectByClass(array("adnbasegui", "adncertifiedprofessionalgui", "adnpersonaldatamaintenancegui"), "jumpToList");
        }
    }
}
// cr-008 end
