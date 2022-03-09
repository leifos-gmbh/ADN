<?php
/* Copyright (c) 2010 Leifos, GPL, see docs/LICENSE */

/**
 * ADN user application class. This class provides methods
 * that wrap functionalities of the ILIAS user class for the ADN
 * project
 *
 * @author Alex Killing <alex.killing>
 * @version $Id: class.adnUser.php 27867 2011-02-25 09:35:47Z akill $
 *
 * @ingroup ServicesADN
 */
class adnUser
{
    /**
     * Get WMO ID of current user
     *
     * @return int wmo id (0, if no wmo id could be determined)
     */
    public static function lookupWMOId()
    {
        global $ilUser;

        // get value of wmo code of user
        $ud = $ilUser->getUserDefinedData();
        include_once("./Services/User/classes/class.ilUserDefinedFields.php");
        $udf = ilUserDefinedFields::_getInstance();
        $fid = $udf->fetchFieldIdFromName("wmo_code");
        $wmo_code = (int) $ud["f_" . $fid];

        if ($wmo_code >= 100 && ($wmo_code % 100) == 0) {
            $wmo_code = $wmo_code / 100;
        }

        if ($wmo_code > 0) {
            // get wmo id by wmo code
            // assumption: code is unique (which is the case for ADN)
            include_once("./Services/ADN/MD/classes/class.adnWMO.php");
            $wmo_id = adnWMO::lookupIdForCode($wmo_code);
            if ($wmo_id > 0) {
                return $wmo_id;
            }
        }
        return 0;
    }

    /**
     * Get all users (with zsuk or wsd role)
     *
     * @return array (user_id => id, last_name, first_name, sign)
     */
    public static function getAllUsers()
    {
        global $rbacreview;

        // get role ids from name (hardcoded)
        $zsuk_id = array_pop(ilObject::_getIdsForTitle("ZSUK", "role"));
        $wsd_id = array_pop(ilObject::_getIdsForTitle("WSD", "role"));

        // get all users with matching roles
        $users = array();
        $users = array_merge($users, $rbacreview->assignedUsers($zsuk_id));
        $users = array_merge($users, $rbacreview->assignedUsers($wsd_id));

        $res = array();
        if ($users) {
            foreach (ilObjUser::_getUserData($users) as $item) {
                $res[$item["usr_id"]] = array(
                    "id" => $item["usr_id"],
                    "last_name" => $item["lastname"],
                    "first_name" => $item["firstname"],
                    "sign" => ""
                    );
            }

            // add "sign" values from user defined field
            include_once './Services/User/classes/class.ilUserDefinedFields.php';
            $definition = ilUserDefinedFields::_getInstance();
            $sign_id = $definition->fetchFieldIdFromName("sign");
            if ($sign_id) {
                include_once './Services/User/classes/class.ilUserDefinedData.php';
                foreach (ilUserDefinedData::lookupData(array_keys($res), array($sign_id)) as $user_id => $item) {
                    $res[$user_id]["sign"] = $item[$sign_id];
                }
            }
        }
        return $res;
    }
}
