<?php
/* Copyright (c) 2010 Leifos, GPL, see docs/LICENSE */

/**
 * Permission wrapper for ADN
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id: class.adnPerm.php 27867 2011-02-25 09:35:47Z akill $
 *
 * @ingroup ServicesADN
 */
class adnPerm
{
    public const READ = "read";
    public const WRITE = "write";

    public const TA = "ta";
    public const ED = "ed";
    public const EP = "ep";
    public const EC = "ec";
    public const ES = "es";
    public const CP = "cp";
    public const ST = "st";
    public const MD = "md";
    public const AD = "ad";

    /**
     * @var array<string, object>
     */
    public static array $perm_objects = [];

    /**
     * Init
     *
     * @param
     * @return
     */
    public static function init()
    {
        // get all
        if (!is_array(self::$perm_objects)) {
            self::$perm_objects = array();
            $components = array("ta", "ed", "ep", "ec", "es", "cp", "st", "md", "ad");
            foreach ($components as $c) {
                $objs = ilObject::_getObjectsByType("xa" . $c);
                if ($ob = current($objs)) {
                    $refs = ilObject::_getAllReferences($ob["obj_id"]);
                    self::$perm_objects[$c] = current($refs);
                }
            }
        }
    }

    /**
     * Check permission
     *
     * @param string component
     * @param string permission
     */
    public static function check($a_component, $a_perm)
    {
        global $DIC;

        $rbacsystem = $DIC->rbac()->system();

        self::init();
        if (isset(self::$perm_objects[$a_component])) {
            switch ($a_perm) {
                case self::READ:
                    return ($rbacsystem->checkAccess($a_perm, self::$perm_objects[$a_component])
                        || $rbacsystem->checkAccess(self::WRITE, self::$perm_objects[$a_component]));
                    break;

                case self::WRITE:
                    return $rbacsystem->checkAccess($a_perm, self::$perm_objects[$a_component]);
                    break;
            }
        }
        return false;
    }
}
