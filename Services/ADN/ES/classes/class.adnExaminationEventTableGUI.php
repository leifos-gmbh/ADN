<?php
/* Copyright (c) 2010 Leifos, GPL, see docs/LICENSE */

include_once("./Services/Table/classes/class.ilTable2GUI.php");

/**
 * Examination event table GUI class. The table lists all examination events. This table
 * is used in a lot of contexts of the ADN application and has a mode for these contexts
 * (see MODE constants defined at the beginning of the class). The actions listed in the table
 * depend on these modes.
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.de>
 * @version $Id: class.adnExaminationEventTableGUI.php 28758 2011-05-02 14:48:11Z jluetzen $
 *
 * @ingroup ServicesADN
 */
class adnExaminationEventTableGUI extends ilTable2GUI
{
    // [array] options for select filters (needed for value mapping)
    protected $filter_options;

    // [int] view mode
    protected $mode;

    // [bool] show current or past events
    protected $archived;

    // modes for different usages of this table
    const MODE_SCORING = 1;
    const MODE_CERTIFICATE = 2;
    const MODE_PREPARATION = 3;
    const MODE_ASSIGNMENT = 4;
    const MODE_SHEET = 5;
    const MODE_INVITATION = 6;
    const MODE_ATTENDANCE = 7;
    const MODE_NOTIFICATION = 8;
    const MODE_TEST_PREP = 9;
    const MODE_ONLINE = 10;

    /**
     * Constructor
     *
     * @param object $a_parent_obj parent gui object
     * @param string $a_parent_cmd parent default command
     * @param int $a_mode parent default command
     * @param bool $a_archived show current or past events
     */
    public function __construct($a_parent_obj, $a_parent_cmd, $a_mode, $a_archived = false)
    {
        global $ilCtrl, $lng;

        parent::__construct($a_parent_obj, $a_parent_cmd);

        $this->mode = (int) $a_mode;
        $this->archived = (bool) $a_archived;

        $this->setId("adn_tbl_evt_" . $this->mode . "_" . $this->archived);

        $this->setTitle($lng->txt("adn_examination_events"));

        if (adnPerm::check(adnPerm::EP, adnPerm::WRITE) && !$this->archived) {
            if ($this->mode == self::MODE_PREPARATION) {
                $this->addMultiCommand("confirmEventsDeletion", $lng->txt("delete"));
                $this->addColumn("", "", 1);
            }
        }

        // column headers
        $this->addColumn($this->lng->txt("adn_date"), "date");
        $this->addColumn($this->lng->txt("adn_type_of_exam"), "type");
        $this->addColumn($this->lng->txt("adn_facility"), "facility");
        $this->addColumn($this->lng->txt("adn_time_from"), "time_from");
        $this->addColumn($this->lng->txt("adn_time_to"), "time_to");

        switch ($this->mode) {
            case self::MODE_ASSIGNMENT:
                $this->addColumn($this->lng->txt("adn_assigned_candidates"), "assigned");
                break;
            
            case self::MODE_INVITATION:
                $this->addColumn($this->lng->txt("adn_generated_invitations"), "invitations");
                break;
            
            case self::MODE_ATTENDANCE:
                $this->addColumn($this->lng->txt("adn_attendance_list_created_on"), "attendance");
                break;
        }

        $this->addColumn($this->lng->txt("actions"));
        $this->initFilter();

        $this->setDefaultOrderField("date");
        $this->setDefaultOrderDirection("asc");

        $this->setFormAction($ilCtrl->getFormAction($a_parent_obj));
        $this->setRowTemplate("tpl.event_row.html", "Services/ADN/ES");
        
        $this->importData();
    }

    /**
     * Import data from DB
     */
    protected function importData()
    {
        // get events
        include_once "Services/ADN/EP/classes/class.adnExaminationEvent.php";
        $events = adnExaminationEvent::getAllEvents($this->filter, $this->archived);
        
        // value mapping (to have correct sorting)
        if (sizeof($events)) {
            include_once "Services/ADN/EP/classes/class.adnAssignment.php";
            include_once "Services/ADN/ED/classes/class.adnSubjectArea.php";
            foreach ($events as $idx => $item) {
                $date = new ilDate($item["date_from"]->get(IL_CAL_DATE), IL_CAL_DATE);
                $events[$idx]["date_display"] = ilDatePresentation::formatDate($date);
                $events[$idx]["date"] = $item["date_from"]->get(IL_CAL_FKT_DATE, 'Y-m-d');
                $events[$idx]["time_from"] = $item["date_from"]->get(IL_CAL_FKT_DATE, 'H:i');
                $events[$idx]["time_to"] = $item["date_to"]->get(IL_CAL_FKT_DATE, 'H:i');
                $events[$idx]["type"] = $this->filter_options["type"][$item["subject_area"]];
                $events[$idx]["type_color"] = adnSubjectArea::getColorForArea($item["subject_area"]);

                // we cannot use filter options because of archived values
                $events[$idx]["facility"] = adnExamFacility::lookupCity($item["md_exam_facility_id"]);

                switch ($this->mode) {
                    case self::MODE_ASSIGNMENT:
                        $users = adnAssignment::getAllAssignments(array("event_id" => $item["id"]));
                        $events[$idx]["assigned"] = sizeof($users);
                        break;
                
                    case self::MODE_INVITATION:
                        $users = adnAssignment::getAllInvitations($item["id"]);
                        $events[$idx]["invitations"] = sizeof($users);
                        break;

                    case self::MODE_ATTENDANCE:
                        include_once './Services/ADN/Report/classes/class.adnReportAttendanceList.php';
                        
                        $events[$idx]['attendance'] = '';
                        if (adnReportAttendanceList::lookupLastFile($item['id']) instanceof ilDateTime) {
                            $events[$idx]["attendance"] =
                                ilDatePresentation::formatDate(
                                    adnReportAttendanceList::lookupLastFile($item['id'])
                                );
                        }
                        break;
                }
            }
        }

        $this->setData($events);
        $this->setMaxCount(sizeof($events));
    }

    /**
     * Init filter
     */
    public function initFilter()
    {
        global $lng;

        // types of exam
        $types = $this->addFilterItemByMetaType(
            "type",
            self::FILTER_SELECT,
            false,
            $lng->txt("adn_type_of_exam")
        );
        include_once "Services/ADN/ED/classes/class.adnSubjectArea.php";
        $this->filter_options["type"] = adnSubjectArea::getAllAreas();
        $all = array('' => $lng->txt('adn_filter_all')) + $this->filter_options["type"];
        $types->setOptions($all);
        $types->readFromSession();
        $this->filter["type"] = $types->getValue();

        // date
        $date = $this->addFilterItemByMetaType(
            "date",
            self::FILTER_DATE_RANGE,
            false,
            $lng->txt("adn_date")
        );
        $date->readFromSession();
        $this->filter["date"] = $date->getDate();

        // exam facility
        $facility = $this->addFilterItemByMetaType(
            "facility",
            self::FILTER_SELECT,
            false,
            $lng->txt("adn_facility")
        );
        include_once "Services/ADN/MD/classes/class.adnExamFacility.php";
        $this->filter_options["facility"] = adnExamFacility::getFacilitiesSelect();
        $all = array('' => $lng->txt('adn_filter_all')) + $this->filter_options["facility"];
        $facility->setOptions($all);
        $facility->readFromSession();
        $this->filter["facility"] = $facility->getValue();
    }
    
    /**
     * Fill table row
     *
     * @param array $a_set data array
     */
    protected function fillRow($a_set)
    {
        global $lng, $ilCtrl;

        // actions...
        
        $ilCtrl->setParameter($this->parent_obj, "ev_id", $a_set["id"]);

        switch ($this->mode) {
            // list candidates
            case self::MODE_SCORING:
                $this->tpl->setCurrentBlock("action");
                $this->tpl->setVariable("TXT_CMD", $lng->txt("adn_examination_attendees"));
                $this->tpl->setVariable(
                    "HREF_CMD",
                    $ilCtrl->getLinkTarget($this->parent_obj, "listCandidates")
                );
                $this->tpl->parseCurrentBlock();
                break;

            // list candidates
            case self::MODE_CERTIFICATE:
                $this->tpl->setCurrentBlock("action");
                $this->tpl->setVariable("TXT_CMD", $lng->txt("adn_examination_attendees"));
                $this->tpl->setVariable(
                    "HREF_CMD",
                    $ilCtrl->getLinkTarget($this->parent_obj, "listCandidates")
                );
                $this->tpl->parseCurrentBlock();
                break;

            // edit event
            case self::MODE_PREPARATION:
                if (adnPerm::check(adnPerm::EP, adnPerm::WRITE) && !$this->archived) {
                    $this->tpl->setCurrentBlock("checkbox_column");
                    $this->tpl->setVariable("VAL_ID", $a_set["id"]);
                    $this->tpl->parseCurrentBlock();

                    $this->tpl->setCurrentBlock("action");
                    $this->tpl->setVariable("TXT_CMD", $lng->txt("adn_edit"));
                    $this->tpl->setVariable(
                        "HREF_CMD",
                        $ilCtrl->getLinkTarget($this->parent_obj, "editEvent")
                    );
                    $this->tpl->parseCurrentBlock();
                } elseif (adnPerm::check(adnPerm::EP, adnPerm::READ)) {
                    $this->tpl->setCurrentBlock("action");
                    $this->tpl->setVariable("TXT_CMD", $lng->txt("adn_show_details"));
                    $this->tpl->setVariable(
                        "HREF_CMD",
                        $ilCtrl->getLinkTarget($this->parent_obj, "showEvent")
                    );
                    $this->tpl->parseCurrentBlock();
                }
                break;

            // edit assignment
            case self::MODE_ASSIGNMENT:
                $caption = false;
                if (adnPerm::check(adnPerm::EP, adnPerm::WRITE) && !$this->archived) {
                    $caption = $lng->txt("adn_assign_candidates");
                } elseif (adnPerm::check(adnPerm::EP, adnPerm::READ) && $a_set["assigned"]) {
                    $caption = $lng->txt("adn_show_candidates");
                }
                if ($caption) {
                    $this->tpl->setCurrentBlock("action");
                    $this->tpl->setVariable("TXT_CMD", $caption);
                    $this->tpl->setVariable(
                        "HREF_CMD",
                        $ilCtrl->getLinkTarget($this->parent_obj, "assignCandidates")
                    );
                    $this->tpl->parseCurrentBlock();
                }
                break;

            // edit answer sheet
            case self::MODE_SHEET:
                if (adnPerm::check(adnPerm::EP, adnPerm::READ)) {
                    $this->tpl->setCurrentBlock("action");
                    $this->tpl->setVariable("TXT_CMD", $lng->txt("adn_answer_sheets"));
                    $this->tpl->setVariable(
                        "HREF_CMD",
                        $ilCtrl->getLinkTarget($this->parent_obj, "listSheets")
                    );
                    $this->tpl->parseCurrentBlock();
                }
                break;

            // invitations
            case self::MODE_INVITATION:
                if (adnPerm::check(adnPerm::EP, adnPerm::WRITE)) {
                    $this->tpl->setCurrentBlock("action");
                    $this->tpl->setVariable("TXT_CMD", $lng->txt("adn_generate_invitations"));
                    $this->tpl->setVariable(
                        "HREF_CMD",
                        $ilCtrl->getLinkTarget($this->parent_obj, "listCandidates")
                    );
                    $this->tpl->parseCurrentBlock();
                }
                break;

            // attendance lists
            case self::MODE_ATTENDANCE:
                if (adnPerm::check(adnPerm::EP, adnPerm::WRITE)) {
                    $this->tpl->setCurrentBlock("action");
                    $this->tpl->setVariable("TXT_CMD", $lng->txt("adn_create_attendance_list"));
                    $this->tpl->setVariable(
                        "HREF_CMD",
                        $ilCtrl->getLinkTarget($this->parent_obj, "createList")
                    );
                    $this->tpl->parseCurrentBlock();
                }
                include_once './Services/ADN/Report/classes/class.adnReportAttendanceList.php';
                if (adnReportAttendanceList::lookupLastFile($a_set['id'])) {
                    if (adnPerm::check(adnPerm::EP, adnPerm::READ)) {
                        $this->tpl->setCurrentBlock("action");
                        $this->tpl->setVariable("TXT_CMD", $lng->txt("adn_download_attendance_list"));
                        $this->tpl->setVariable(
                            "HREF_CMD",
                            $ilCtrl->getLinkTarget($this->parent_obj, "downloadList")
                        );
                        $this->tpl->parseCurrentBlock();
                    }
                }
                break;

            // score notification letters
            case self::MODE_NOTIFICATION:
                if (adnPerm::check(adnPerm::EP, adnPerm::READ)) {
                    $this->tpl->setCurrentBlock("action");
                    $this->tpl->setVariable("TXT_CMD", $lng->txt("adn_score_notification_letters"));
                    $this->tpl->setVariable(
                        "HREF_CMD",
                        $ilCtrl->getLinkTarget($this->parent_obj, "listParticipants")
                    );
                    $this->tpl->parseCurrentBlock();
                }
                break;

            // test preparation
            case self::MODE_TEST_PREP:
                $this->tpl->setCurrentBlock("action");
                $this->tpl->setVariable("TXT_CMD", $lng->txt("adn_list_access_codes"));
                $this->tpl->setVariable(
                    "HREF_CMD",
                    $ilCtrl->getLinkTarget($this->parent_obj, "listAccessCodes")
                );
                $this->tpl->parseCurrentBlock();
                break;

            // online answer sheets
            case self::MODE_ONLINE:
                $this->tpl->setCurrentBlock("action");
                $this->tpl->setVariable("TXT_CMD", $lng->txt("adn_online_answer_sheets"));
                $this->tpl->setVariable(
                    "HREF_CMD",
                    $ilCtrl->getLinkTarget($this->parent_obj, "downloadSheets")
                );
                $this->tpl->parseCurrentBlock();
                break;

        }
        
        $ilCtrl->setParameter($this->parent_obj, "ev_id", "");
        
        
        // properties
        $this->tpl->setVariable("VAL_DATE", $a_set["date_display"]);
        $this->tpl->setVariable("VAL_TYPE", $a_set["type"]);
        $this->tpl->setVariable("VAL_TYPE_COLOR", $a_set["type_color"]);
        $this->tpl->setVariable("VAL_FACILITY", $a_set["facility"]);
        $this->tpl->setVariable("VAL_TIME_FROM", $a_set["time_from"]);
        $this->tpl->setVariable("VAL_TIME_TO", $a_set["time_to"]);

        $this->legend["<span style=\"background-color:" .
            $a_set["type_color"] .
            "; border: 1px solid grey;\">&nbsp;&nbsp;&nbsp;</span>"] = $a_set["type"];

        switch ($this->mode) {
            case self::MODE_ASSIGNMENT:
                $this->tpl->setCurrentBlock("assigned_column");
                $this->tpl->setVariable("VAL_ASSIGNED", $a_set["assigned"]);
                $this->tpl->parseCurrentBlock();
                break;
            
            case self::MODE_INVITATION:
                $this->tpl->setCurrentBlock("invitation_column");
                $this->tpl->setVariable("VAL_INVITATIONS", $a_set["invitations"]);
                $this->tpl->parseCurrentBlock();
                break;

            case self::MODE_ATTENDANCE:
                $this->tpl->setCurrentBlock("attendance_column");
                $this->tpl->setVariable("VAL_ATTENDANCE", $a_set["attendance"]);
                $this->tpl->parseCurrentBlock();
                break;
        }
    }
}
