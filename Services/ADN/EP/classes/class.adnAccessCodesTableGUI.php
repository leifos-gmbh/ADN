<?php
/* Copyright (c) 2010 Leifos, GPL, see docs/LICENSE */

include_once("./Services/Table/classes/class.ilTable2GUI.php");

/**
 * Access codes table GUI class
 *
 * List all access codes for registered candidates
 *
 * @author Alex Killing <killing@leifos.de>
 * @version $Id: class.adnAccessCodesTableGUI.php 27887 2011-02-28 10:54:39Z jluetzen $
 *
 * @ingroup ServicesADN
 */
class adnAccessCodesTableGUI extends ilTable2GUI
{
    // [int] id of examination event
    protected int $event_id;

    /**
     * Constructor
     *
     * @param object $a_parent_obj parent gui object
     * @param string $a_parent_cmd parent default command
     * @param int $a_event_id id of examination event
     */
    public function __construct($a_parent_obj, $a_parent_cmd, $a_event_id)
    {
        global $ilCtrl, $lng;

        $this->event_id = (int) $a_event_id;

        $this->id = "adn_tbl_acc";
        parent::__construct($a_parent_obj, $a_parent_cmd);

        include_once("./Services/ADN/EP/classes/class.adnExaminationEvent.php");
        $this->setTitle($lng->txt("adn_registered_candidates") . ": " .
            adnExaminationEvent::lookupName($this->event_id));
        
        $this->addColumn($this->lng->txt("adn_last_name"), "last_name");
        $this->addColumn($this->lng->txt("adn_first_name"), "first_name");
        $this->addColumn($this->lng->txt("adn_birthdate"), "birthdate");
        $this->addColumn($this->lng->txt("adn_login"), "login");
        $this->addColumn($this->lng->txt("adn_password"), "password");
        
        $this->setDefaultOrderField("last_name");
        $this->setDefaultOrderDirection("asc");

        $this->setFormAction($ilCtrl->getFormAction($a_parent_obj));
        $this->setRowTemplate("tpl.access_code_row.html", "Services/ADN/EP");
    
        $this->importData();
    }

    /**
     * Import data from DB
     */
    protected function importData()
    {
        include_once("./Services/ADN/EP/classes/class.adnAssignment.php");
        $assignments = adnAssignment::getAllAssignments(
            array("event_id" => $this->event_id),
            array("first_name", "last_name", "birthdate",
                "blocked_until", "ilias_user_id")
        );

        if ($assignments) {
            foreach ($assignments as $idx => $item) {
                $assignments[$idx]["login"] =
                    ilObjUser::_lookupLogin($item["ilias_user_id"]);
            }
        }

        $this->setData($assignments);
    }

    /**
     * Fill table row
     *
     * @param array $a_set data array
     */
    protected function fillRow($a_set)
    {
        global $lng, $ilCtrl;

        // properties
        $this->tpl->setVariable("VAL_LAST_NAME", $a_set["last_name"]);
        $this->tpl->setVariable("VAL_FIRST_NAME", $a_set["first_name"]);
        $this->tpl->setVariable(
            "VAL_BIRTHDATE",
            ilDatePresentation::formatDate(
                new ilDate($a_set["birthdate"], IL_CAL_DATE)
            )
        );
        $this->tpl->setVariable("VAL_LOGIN", $a_set["login"]);
        $this->tpl->setVariable("VAL_PASSWORD", $a_set["access_code"]);
    }
}
