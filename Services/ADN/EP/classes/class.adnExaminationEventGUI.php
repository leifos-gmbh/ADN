<?php
/* Copyright (c) 2010 Leifos, GPL, see docs/LICENSE */

/**
 * Examination event GUI class
 *
 * Event list, forms and persistence
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.de>
 * @version $Id: class.adnExaminationEventGUI.php 27888 2011-02-28 11:09:28Z jluetzen $
 *
 * @ilCtrl_Calls adnExaminationEventGUI:
 *
 * @ingroup ServicesADN
 */
class adnExaminationEventGUI
{
    // [adnExaminationEvent] current event object
    protected $event = null;

    // [bool] current or past events
    protected $archived = null;

    /**
     * Constructor
     */
    public function __construct()
    {
        global $ilCtrl;
        
        // save event ID through requests
        $ilCtrl->saveParameter($this, array("ev_id"));
        $ilCtrl->saveParameter($this, array("arc"));

        $this->archived = (bool) $_REQUEST["arc"];
        
        $this->readEvent();
    }
    
    /**
     * Execute command
     */
    public function executeCommand()
    {
        global $ilCtrl, $lng, $tpl;

        $tpl->setTitle($lng->txt("adn_ep") . " - " . $lng->txt("adn_ep_ees"));
        adnIcon::setTitleIcon("ep_ees");
        
        $next_class = $ilCtrl->getNextClass();
        
        // forward command to next gui class in control flow
        switch ($next_class) {
            // no next class:
            // this class is responsible to process the command
            default:
                $cmd = $ilCtrl->getCmd("listEvents");

                switch ($cmd) {
                    // commands that need read permission
                    case "listEvents":
                    case "applyFilter":
                    case "resetFilter":
                    case "showEvent":
                        if (adnPerm::check(adnPerm::EP, adnPerm::READ)) {
                            $this->$cmd();
                        }
                        break;
                    
                    // commands that need write permission
                    case "addEvent":
                    case "saveEvent":
                    case "editEvent":
                    case "updateEvent":
                    case "confirmEventsDeletion":
                    case "deleteEvents":
                        if (adnPerm::check(adnPerm::EP, adnPerm::WRITE)) {
                            $this->$cmd();
                        }
                        break;
                    
                }
                break;
        }

        $this->setTabs();
    }
    
    /**
     * Read event
     */
    protected function readEvent()
    {
        if ((int) $_GET["ev_id"] > 0) {
            include_once("./Services/ADN/EP/classes/class.adnExaminationEvent.php");
            $this->event = new adnExaminationEvent((int) $_GET["ev_id"]);
        }
    }

    /**
     * Apply filter settings (from table gui)
     */
    protected function applyFilter()
    {
        include_once("./Services/ADN/ES/classes/class.adnExaminationEventTableGUI.php");
        $table = new adnExaminationEventTableGUI(
            $this,
            "listEvents",
            adnExaminationEventTableGUI::MODE_PREPARATION,
            $this->archived
        );
        $table->resetOffset();
        $table->writeFilterToSession();

        $this->listEvents();
    }

    /**
     * Reset filter settings (from table gui)
     */
    protected function resetFilter()
    {
        include_once("./Services/ADN/ES/classes/class.adnExaminationEventTableGUI.php");
        $table = new adnExaminationEventTableGUI(
            $this,
            "listEvents",
            adnExaminationEventTableGUI::MODE_PREPARATION,
            $this->archived
        );
        $table->resetOffset();
        $table->resetFilter();

        $this->listEvents();
    }
    
    /**
     * List current (or archived) examination events
     */
    protected function listEvents()
    {
        global $tpl, $ilToolbar, $lng, $ilCtrl;

        if (!$this->archived && adnPerm::check(adnPerm::EP, adnPerm::WRITE)) {
            $ilToolbar->addButton(
                $lng->txt("adn_add_examination_event"),
                $ilCtrl->getLinkTarget($this, "addEvent")
            );
        }

        // table of examination events
        include_once("./Services/ADN/ES/classes/class.adnExaminationEventTableGUI.php");
        $table = new adnExaminationEventTableGUI(
            $this,
            "listEvents",
            adnExaminationEventTableGUI::MODE_PREPARATION,
            $this->archived
        );
        
        // output table
        $tpl->setContent($table->getHTML());
    }

    /**
     * Add new event form
     *
     * @param ilPropertyFormGUI $a_form
     */
    protected function addEvent(ilPropertyFormGUI $a_form = null)
    {
        global $tpl, $ilTabs, $ilCtrl, $lng;

        $ilTabs->setBackTarget($lng->txt("back"), $ilCtrl->getLinkTarget($this, "listEvents"));

        if (!$a_form) {
            $a_form = $this->initEventForm("create");
        }
        $tpl->setContent($a_form->getHTML());
    }

    /**
     * Edit event form
     *
     * @param ilPropertyFormGUI $a_form
     */
    protected function editEvent(ilPropertyFormGUI $a_form = null)
    {
        global $tpl, $ilTabs, $ilCtrl, $lng;

        $ilTabs->setBackTarget($lng->txt("back"), $ilCtrl->getLinkTarget($this, "listEvents"));

        if (!$a_form) {
            $a_form = $this->initEventForm("edit");
        }
        $tpl->setContent($a_form->getHTML());
    }

    /**
     * Show event (read-only)
     */
    protected function showEvent()
    {
        global $tpl, $ilTabs, $ilCtrl, $lng;

        $ilTabs->setBackTarget($lng->txt("back"), $ilCtrl->getLinkTarget($this, "listEvents"));

        $form = $this->initEventForm("show");
        $form = $form->convertToReadonly();
        $tpl->setContent($form->getHTML());
    }

    /**
     * Init event form
     *
     * @param	string	$a_mode		form mode ("create" | "edit" | "show")
     * @return	ilPropertyFormGUI
     */
    protected function initEventForm($a_mode = "edit")
    {
        global $lng, $ilCtrl;

        // get form object and add input fields
        include_once("Services/Form/classes/class.ilPropertyFormGUI.php");
        $form = new ilPropertyFormGUI();

        // subject area (foreign, but static)
        $type = new ilSelectInputGUI($lng->txt("adn_type_of_exam"), "type");
        $type->setRequired(true);
        include_once "Services/ADN/ED/classes/class.adnSubjectArea.php";
        $type->setOptions(adnSubjectArea::getAllAreas());
        $form->addItem($type);

        $date = new ilDateTimeInputGUI($lng->txt("date"), "date");
        $date->setRequired(true);
        // $date_from->setShowTime(true);
        $form->addItem($date);

        include_once "Services/Form/classes/class.ilCombinationInputGUI.php";
        include_once "Services/Form/classes/class.ilTimeInputGUI.php";
        $time = new ilCombinationInputGUI($lng->txt("adn_timeframe"), $time);
        $time->setRequired(true);
        $time_from = new ilTimeInputGUI("", "time_from");
        $time->addCombinationItem("from", $time_from);
        $time_to = new ilTimeInputGUI("", "time_to");
        $time->addCombinationItem("to", $time_to);
        $time->setComparisonMode(ilCombinationInputGUI::COMPARISON_ASCENDING);
        $form->addItem($time);

        // exam facility (foreign key)
        $facility = new ilSelectInputGUI($lng->txt("adn_exam_facility"), "facility");
        $facility->setRequired(true);
        $fac = null;
        if ($a_mode != "create") {
            $fac = $this->event->getFacility();
        }
        include_once "Services/ADN/MD/classes/class.adnExamFacility.php";
        $facility->setOptions(adnExamFacility::getFacilitiesSelect(null, $fac));
        $form->addItem($facility);

        // co-chair (foreign key)
        include_once "Services/ADN/MD/classes/class.adnCoChair.php";
        $cos = null;
        if ($a_mode != "create") {
            $cos[] = $this->event->getCoChair1();
            $cos[] = $this->event->getCoChair2();
            $cos[] = $this->event->getChairman();
        }
        $cochairs = adnCoChair::getCoChairsSelect(null, $cos);
        
        $chair = new ilSelectInputGUI($lng->txt("adn_chairman"), "chair");
        $chair->setRequired(true);
        $chair->setOptions($cochairs);
        $form->addItem($chair);

        $cochair1 = new ilSelectInputGUI($lng->txt("adn_cochair") . " 1", "cochair1");
        $cochair1->setRequired(true);
        $cochair1->setOptions($cochairs);
        $form->addItem($cochair1);

        $cochair2 = new ilSelectInputGUI($lng->txt("adn_cochair") . " 2", "cochair2");
        $cochair2->setOptions(['' => ''] + $cochairs);
        $form->addItem($cochair2);

        $cost = new ilNumberInputGUI($lng->txt("adn_additional_costs"), "cost");
        $cost->setDecimals(2);
        $cost->setMaxLength(6);
        $cost->setSize(6);
        $cost->setSuffix("EUR");
        $form->addItem($cost);

        if ($a_mode == "create") {
            // creation: save/cancel buttons and title
            $form->addCommandButton("saveEvent", $lng->txt("save"));
            $form->addCommandButton("listEvents", $lng->txt("cancel"));
            $form->setTitle($lng->txt("adn_add_examination_event"));
        } else {
            // parse/split dates
            $date_from = $this->event->getDateFrom()->get(IL_CAL_DATETIME);
            $date_to = $this->event->getDateTo()->get(IL_CAL_DATETIME);
            $date->setDate(new ilDate($this->event->getDateFrom()->get(IL_CAL_DATE), IL_CAL_DATE));
            $time->setValue(array("from" => substr($date_from, -8),
                "to" => substr($date_to, -8)));

            $type->setValue($this->event->getType());
            $facility->setValue($this->event->getFacility());
            $chair->setValue($this->event->getChairman());
            $cochair1->setValue($this->event->getCoChair1());
            $cochair2->setValue($this->event->getCoChair2());
            $cost->setValue($this->event->getCosts());

            if ($a_mode != "show") {
                // editing: update/cancel buttons and title
                $form->addCommandButton("updateEvent", $lng->txt("save"));
                $form->addCommandButton("listEvents", $lng->txt("cancel"));
                $form->setTitle($lng->txt("adn_edit_examination_event"));
            } else {
                $form->setTitle($lng->txt("adn_examination_event"));
            }
        }

        $form->setFormAction($ilCtrl->getFormAction($this));

        return $form;
    }

    /**
     * Create new event
     */
    protected function saveEvent()
    {
        global $tpl, $lng, $ilCtrl;

        $form = $this->initEventForm("create");

        // check input
        if ($form->checkInput()) {
            // input ok: create new event
            include_once("./Services/ADN/EP/classes/class.adnExaminationEvent.php");
            $event = new adnExaminationEvent();
            $event->setType($form->getInput("type"));
            $event->setFacility($form->getInput("facility"));
            $event->setChairman($form->getInput("chair"));
            $event->setCoChair1($form->getInput("cochair1"));
            $event->setCoChair2($form->getInput("cochair2") ? $form->getInput('cochair2') : null);
            $event->setCosts($form->getInput("cost"));

            // converting form input to ilDateTime
            $date = $form->getInput("date");
            $time_from = $form->getInput("time_from");
            $time_from = str_pad($time_from["h"], 2, "0", STR_PAD_LEFT) . ":" .
                str_pad($time_from["m"], 2, "0", STR_PAD_LEFT) . ":00";
            $time_to = $form->getInput("time_to");
            $time_to = str_pad($time_to["h"], 2, "0", STR_PAD_LEFT) . ":" .
                str_pad($time_to["m"], 2, "0", STR_PAD_LEFT) . ":00";
            $event->setDateFrom(new ilDateTime($date . " " . $time_from, IL_CAL_DATETIME));
            $event->setDateTo(new ilDateTime($date . " " . $time_to, IL_CAL_DATETIME));
            
            if ($event->save()) {
                // show success message and return to list
                ilUtil::sendSuccess($lng->txt("adn_examination_event_created"), true);
                $ilCtrl->redirect($this, "listEvents");
            }
        }

        // input not valid: show form again
        $form->setValuesByPost();
        $this->addEvent($form);
    }

    /**
     * Update event
     */
    protected function updateEvent()
    {
        global $lng, $ilCtrl, $tpl;

        $form = $this->initEventForm("edit");

        // check input
        if ($form->checkInput()) {
            // perform update
            $this->event->setType($form->getInput("type"));
            $this->event->setFacility($form->getInput("facility"));
            $this->event->setChairman($form->getInput("chair"));
            $this->event->setCoChair1($form->getInput("cochair1"));
            $this->event->setCoChair2($form->getInput("cochair2"));
            $this->event->setCosts($form->getInput("cost"));

            // converting form input to ilDateTime
            $date = $form->getInput("date");
            $time_from = $form->getInput("time_from");
            $time_from = str_pad($time_from["h"], 2, "0", STR_PAD_LEFT) . ":" .
                str_pad($time_from["m"], 2, "0", STR_PAD_LEFT) . ":00";
            $time_to = $form->getInput("time_to");
            $time_to = str_pad($time_to["h"], 2, "0", STR_PAD_LEFT) . ":" .
                str_pad($time_to["m"], 2, "0", STR_PAD_LEFT) . ":00";
            $this->event->setDateFrom(new ilDateTime($date . " " . $time_from, IL_CAL_DATETIME));
            $this->event->setDateTo(new ilDateTime($date . " " . $time_to, IL_CAL_DATETIME));
             
            if ($this->event->update()) {
                // show success message and return to list
                ilUtil::sendSuccess($lng->txt("adn_examination_event_updated"), true);
                $ilCtrl->redirect($this, "listEvents");
            }
        }

        // input not valid: show form again
        $form->setValuesByPost();
        $this->editEvent($form);
    }

    /**
     * Confirm events deletion
     */
    protected function confirmEventsDeletion()
    {
        global $ilCtrl, $tpl, $lng, $ilTabs;

        // check whether at least one item has been seleced
        if (!is_array($_POST["event_id"]) || count($_POST["event_id"]) == 0) {
            ilUtil::sendFailure($lng->txt("no_checkbox"), true);
            $ilCtrl->redirect($this, "listEvents");
        } else {
            $ilTabs->setBackTarget(
                $lng->txt("back"),
                $ilCtrl->getLinkTarget($this, "listEvents")
            );
            
            // display confirmation message
            include_once("./Services/Utilities/classes/class.ilConfirmationGUI.php");
            $cgui = new ilConfirmationGUI();
            $cgui->setFormAction($ilCtrl->getFormAction($this));
            $cgui->setHeaderText($lng->txt("adn_sure_delete_examination_events"));
            $cgui->setCancel($lng->txt("cancel"), "listEvents");
            $cgui->setConfirm($lng->txt("delete"), "deleteEvents");

            // list objects that should be deleted
            include_once("./Services/ADN/EP/classes/class.adnExaminationEvent.php");
            foreach ($_POST["event_id"] as $i) {
                $cgui->addItem("event_id[]", $i, adnExaminationEvent::lookupName($i));
            }

            $tpl->setContent($cgui->getHTML());
        }
    }

    /**
     * Delete events
     */
    protected function deleteEvents()
    {
        global $ilCtrl, $lng;

        include_once("./Services/ADN/EP/classes/class.adnExaminationEvent.php");

        if (is_array($_POST["event_id"])) {
            foreach ($_POST["event_id"] as $i) {
                $event = new adnExaminationEvent($i);
                $event->delete();
            }
        }
        ilUtil::sendSuccess($lng->txt("adn_examination_event_deleted"), true);
        $ilCtrl->redirect($this, "listEvents");
    }
    
    /**
     * Set tabs
     */
    public function setTabs()
    {
        global $ilTabs, $lng, $txt, $ilCtrl;

        $ilCtrl->setParameter($this, "arc", "");

        $ilTabs->addTab(
            "current",
            $lng->txt("adn_current_examination_events"),
            $ilCtrl->getLinkTarget($this, "listEvents")
        );


        $ilCtrl->setParameter($this, "arc", "1");

        $ilTabs->addTab(
            "archived",
            $lng->txt("adn_archived_examination_events"),
            $ilCtrl->getLinkTarget($this, "listEvents")
        );

        $ilCtrl->setParameter($this, "arc", $this->archived);

        if ($this->archived) {
            $ilTabs->activateTab("archived");
        } else {
            $ilTabs->activateTab("current");
        }
    }
}
