<?php
/* Copyright (c) 2010 Leifos, GPL, see docs/LICENSE */

/**
 * ADN training event GUI class
 *
 * Event list, forms and persistence, provider has to be pre-selected
 *
 * @author Alex Killing <killing@leifos.de>
 * @version $Id: class.adnTrainingEventGUI.php 27985 2011-03-08 13:27:47Z jluetzen $
 *
 * @ingroup ServicesADN
 */
class adnTrainingEventGUI
{
    protected int $provider_id = 0;

    protected ?adnTrainingEvent $training_event = null;

    protected bool $not_overview = false;

    protected bool $archived = false;

    protected ilCtrl $ctrl;
    protected ilGlobalTemplateInterface $tpl;
    protected ilLanguage $lng;
    protected ilToolbarGUI $toolbar;
    protected ilTabsGUI $tabs;
    /**
     * Constructor
     */
    public function __construct()
    {
        global $DIC;
        $this->ctrl = $DIC->ctrl();
        $this->tpl = $DIC->ui()->mainTemplate();
        $this->lng = $DIC->language();
        $this->toolbar = $DIC->toolbar();
        $this->tabs = $DIC->tabs();

        $this->provider_id = (int) $_REQUEST["tp_id"];
        $this->not_overview = (bool) $_REQUEST["istp"];
        $this->archived = (bool) $_REQUEST["arc"];
        
        // save context, training provider, event ID, archived through requests
        $this->ctrl->saveParameter($this, array("istp"));
        $this->ctrl->saveParameter($this, array("tp_id"));
        $this->ctrl->saveParameter($this, array("te_id"));
        $this->ctrl->saveParameter($this, array("arc"));
        
        $this->readTrainingEvent();
    }
    
    /**
     * Execute command
     */
    public function executeCommand()
    {
        
        $next_class = $this->ctrl->getNextClass();

        // set page title
        if (!$this->not_overview) {
            $this->tpl->setTitle($this->lng->txt("adn_ta") . " - " . $this->lng->txt("adn_ta_tes"));
        }
    
        $this->setTabs("current_tr_events");
        
        
        // forward command to next gui class in control flow
        switch ($next_class) {
            // no next class:
            // this class is responsible to process the command
            default:
                $cmd = $this->ctrl->getCmd("listTrainingEvents");

                switch ($cmd) {
                    // commands that need read permission
                    case "listTrainingEvents":
                    case "applyFilter":
                    case "resetFilter":
                    case "showTrainingEvent":
                        if (adnPerm::check(adnPerm::TA, adnPerm::READ)) {
                            $this->$cmd();
                        }
                        break;
                    
                    // commands that need write permission
                    case "addTrainingEvent":
                    case "saveTrainingEvent":
                    case "editTrainingEvent":
                    case "updateTrainingEvent":
                    case "confirmTrainingEventDeletion":
                    case "deleteTrainingEvent":
                        if (adnPerm::check(adnPerm::TA, adnPerm::WRITE)) {
                            $this->$cmd();
                        }
                        break;
                    
                }
                break;
        }
    }
    
    /**
     * Read training event
     */
    protected function readTrainingEvent()
    {
        if ((int) $_GET["te_id"] > 0) {
            include_once("./Services/ADN/TA/classes/class.adnTrainingEvent.php");
            $this->training_event = new adnTrainingEvent((int) $_GET["te_id"]);
            $this->provider_id = $this->training_event->getProvider();
        }
    }
    
    /**
     * List all training events (for provider)
     */
    protected function listTrainingEvents()
    {

        if (!$this->archived && $this->not_overview && $this->provider_id &&
                adnPerm::check(adnPerm::TA, adnPerm::WRITE)) {
            $this->toolbar->addButton(
                $this->lng->txt("adn_add_training_event"),
                $this->ctrl->getLinkTarget($this, "addTrainingEvent")
            );
        }

        if (!$this->not_overview) {
            $this->provider_id = null;
        }

        // table of training events
        include_once("./Services/ADN/TA/classes/class.adnTrainingEventTableGUI.php");
        $table = new adnTrainingEventTableGUI(
            $this,
            "listTrainingEvents",
            $this->provider_id,
            !$this->archived,
            false,
            !$this->not_overview
        );
        
        // output table
        $this->tpl->setContent($table->getHTML());
    }

    /**
     * Apply filter settings (from table gui)
     */
    protected function applyFilter()
    {
        if (!$this->not_overview) {
            $this->provider_id = null;
        }

        include_once("./Services/ADN/TA/classes/class.adnTrainingEventTableGUI.php");
        $table = new adnTrainingEventTableGUI(
            $this,
            "listTrainingEvents",
            $this->provider_id,
            !$this->archived,
            false,
            !$this->not_overview
        );
        $table->resetOffset();
        $table->writeFilterToSession();

        $this->listTrainingEvents();
    }

    /**
     * Reset filter settings (from table gui)
     */
    protected function resetFilter()
    {
        if (!$this->not_overview) {
            $this->provider_id = null;
        }
        
        include_once("./Services/ADN/TA/classes/class.adnTrainingEventTableGUI.php");
        $table = new adnTrainingEventTableGUI(
            $this,
            "listTrainingEvents",
            $this->provider_id,
            !$this->archived,
            false,
            !$this->not_overview
        );
        $table->resetOffset();
        $table->resetFilter();
        
        $this->listTrainingEvents();
    }

    /**
     * Apply filter settings (from table gui)
     */
    protected function applyFilterArchived()
    {
        include_once("./Services/ADN/TA/classes/class.adnTrainingEventTableGUI.php");
        $table = new adnTrainingEventTableGUI(
            $this,
            "listTrainingEvents",
            $this->provider_id,
            false,
            false,
            !$this->not_overview
        );
        $table->resetOffset();
        $table->writeFilterToSession();

        $this->listArchivedTrainingEvents();
    }

    /**
     * Reset filter settings (from table gui)
     */
    protected function resetFilterArchived()
    {
        include_once("./Services/ADN/TA/classes/class.adnTrainingEventTableGUI.php");
        $table = new adnTrainingEventTableGUI(
            $this,
            "listTrainingEvents",
            $this->provider_id,
            false,
            false,
            !$this->not_overview
        );
        $table->resetOffset();
        $table->resetFilter();

        $this->listArchivedTrainingEvents();
    }
    
    /**
     * Add new training event form
     *
     * @param ilPropertyFormGUI $a_form
     */
    protected function addTrainingEvent(ilPropertyFormGUI $a_form = null)
    {

        if (!$a_form) {
            $a_form = $this->initTrainingEventForm("create");
        }
        $this->tpl->setContent($a_form->getHTML());
    }
    
    /**
     * Edit training event form
     *
     * @param ilPropertyFormGUI $a_form
     */
    protected function editTrainingEvent(ilPropertyFormGUI $a_form = null)
    {

        if (!$a_form) {
            $a_form = $this->initTrainingEventForm("edit");
        }
        $this->tpl->setContent($a_form->getHTML());
    }

    /**
     * Show training event (read-only)
     *
     * this does not make much sense as the same information is available in the table gui
     */
    protected function showTrainingEvent()
    {

        $this->tabs->setBackTarget($this->lng->txt("back"), $this->ctrl->getLinkTarget($this, "listTrainingEvents"));

        $form = $this->initTrainingEventForm("show");
        $form = $form->convertToReadonly();
        $this->tpl->setContent($form->getHTML());
    }
    
    /**
     * Init training event form.
     *
     * @param string $a_mode form mode ("create" | "edit" | "show")
     * @return ilPropertyFormGUI
     */
    protected function initTrainingEventForm($a_mode = "edit")
    {

        // get form object and add input fields
        include_once("Services/Form/classes/class.ilPropertyFormGUI.php");
        $form = new ilPropertyFormGUI();

        include_once "Services/ADN/TA/classes/class.adnTrainingProvider.php";
        $provider = new adnTrainingProvider($this->provider_id);

        include_once("./Services/ADN/TA/classes/class.adnTypesOfTraining.php");
        $types_map = adnTypesOfTraining::getAllTypes();
        
        // type of training ("foreign key", depend on provider)
        $type = new ilSelectInputGUI($this->lng->txt("adn_type_of_training"), "type");
        $options = array();
        foreach ($provider->getTypesOfTraining() as $ttype) {
            $options[$ttype] = $types_map[$ttype];
        }
        if ($a_mode != "create") {
            $old_type = $this->training_event->getType();
            if (!isset($options[$old_type])) {
                $options[$old_type] = $types_map[$old_type];
            }
        }
        asort($options);
        $type->setOptions($options);
        $type->setRequired(true);
        $form->addItem($type);

        // date from
        $from = new ilDateTimeInputGUI($this->lng->txt("adn_date_from"), "dfrom");
        $from->setRequired(true);
        $form->addItem($from);

        // date to
        $to = new ilDateTimeInputGUI($this->lng->txt("adn_date_to"), "dto");
        $to->setRequired(true);
        $form->addItem($to);

        // facilities (foreign key)
        include_once "Services/ADN/TA/classes/class.adnTrainingFacility.php";
        $facility = new ilSelectInputGUI($this->lng->txt("adn_training_facility"), "facility");
        $fac = null;
        if ($a_mode != "create") {
            $fac = $this->training_event->getFacility();
        }
        $facility->setOptions(adnTrainingFacility::getTrainingFacilitiesSelect($this->provider_id, $fac));
        $facility->setRequired(true);
        $form->addItem($facility);
        
        if ($a_mode == "create") {
            // creation: save/cancel buttons and title
            $form->addCommandButton("saveTrainingEvent", $this->lng->txt("save"));
            $form->addCommandButton("listTrainingEvents", $this->lng->txt("cancel"));
            $form->setTitle($this->lng->txt("adn_add_training_event") . ": " . $provider->getName());
        } else {
            $type->setValue($this->training_event->getType());
            $from->setDate($this->training_event->getDateFrom());
            $to->setDate($this->training_event->getDateTo());
            $facility->setValue($this->training_event->getFacility());

            if ($a_mode == "edit") {
                // editing: update/cancel buttons and title
                $form->addCommandButton("updateTrainingEvent", $this->lng->txt("save"));
                $form->addCommandButton("listTrainingEvents", $this->lng->txt("cancel"));
                $form->setTitle($this->lng->txt("adn_edit_training_event") . ": " . $provider->getName());
            } else {
                $form->setTitle($this->lng->txt("adn_training_event") . ": " . $provider->getName());

                if (!$_REQUEST["tp_id"]) {
                    $provider = new ilTextInputGUI($this->lng->txt("adn_training_provider"), "provider");
                    $provider->setValue(
                        adnTrainingProvider::lookupName($this->training_event->getProvider())
                    );
                    $form->addItem($provider);
                }
            }
        }
        
        $form->setFormAction($this->ctrl->getFormAction($this));

        return $form;
    }
    
    /**
     * Create new training event
     */
    protected function saveTrainingEvent()
    {
        
        $form = $this->initTrainingEventForm("create");
        
        // check input
        if ($form->checkInput()) {
            $date_from = $form->getInput("dfrom");
            $date_to = $form->getInput("dto");
            if ($date_from <= $date_to) {
                // input ok: create new training event
                include_once("./Services/ADN/TA/classes/class.adnTrainingEvent.php");
                $training_event = new adnTrainingEvent();
                $training_event->setProvider($this->provider_id);
                $training_event->setType($form->getInput("type"));
                $date = $form->getInput("dfrom");
                $training_event->setDateFrom(new ilDate($date, IL_CAL_DATE));
                $date = $form->getInput("dto");
                $training_event->setDateTo(new ilDate($date, IL_CAL_DATE));
                $training_event->setFacility($form->getInput("facility"));

                if ($training_event->save()) {
                    // show success message and return to list
                    ilUtil::sendSuccess($this->lng->txt("adn_training_event_created"), true);
                    $this->ctrl->redirect($this, "listTrainingEvents");
                }
            } else {
                ilUtil::sendFailure($this->lng->txt("form_input_not_valid"));
                $form->getItemByPostVar("dfrom")->setAlert($this->lng->txt("adn_invalid_date_range"));
                $form->getItemByPostVar("dto")->setAlert($this->lng->txt("adn_invalid_date_range"));
            }
        }
        
        // input not valid: show form again
        $form->setValuesByPost();
        $this->addTrainingEvent($form);
    }
    
    /**
     * Update training event
     */
    protected function updateTrainingEvent()
    {
        
        $form = $this->initTrainingEventForm("edit");
        
        // check input
        if ($form->checkInput()) {
            $date_from = $form->getInput("dfrom");
            $date_to = $form->getInput("dto");
            if ($date_from <= $date_to) {
                // perform update
                $this->training_event->setType($form->getInput("type"));
                $this->training_event->setDateFrom(new ilDate($date_from, IL_CAL_DATE));
                $this->training_event->setDateTo(new ilDate($date_to, IL_CAL_DATE));
                $this->training_event->setFacility($form->getInput("facility"));

                if ($this->training_event->update()) {
                    // show success message and return to list
                    ilUtil::sendSuccess($this->lng->txt("adn_training_event_updated"), true);
                    $this->ctrl->redirect($this, "listTrainingEvents");
                }
            } else {
                ilUtil::sendFailure($this->lng->txt("form_input_not_valid"));
                $form->getItemByPostVar("dfrom")->setAlert($this->lng->txt("adn_invalid_date_range"));
                $form->getItemByPostVar("dto")->setAlert($this->lng->txt("adn_invalid_date_range"));
            }
        }
        
        // input not valid: show form again
        $form->setValuesByPost();
        $this->editTrainingEvent($form);
    }
    
    /**
     * Confirm training event deletion
     */
    protected function confirmTrainingEventDeletion()
    {
        
        // check whether at least one item has been seleced
        if (!is_array($_POST["training_event_id"]) || count($_POST["training_event_id"]) == 0) {
            ilUtil::sendFailure($this->lng->txt("no_checkbox"), true);
            $this->ctrl->redirect($this, "listTrainingEvents");
        } else {
            // display confirmation message
            include_once("./Services/Utilities/classes/class.ilConfirmationGUI.php");
            $cgui = new ilConfirmationGUI();
            $cgui->setFormAction($this->ctrl->getFormAction($this));
            $cgui->setHeaderText($this->lng->txt("adn_sure_delete_training_event"));
            $cgui->setCancel($this->lng->txt("cancel"), "listTrainingEvents");
            $cgui->setConfirm($this->lng->txt("delete"), "deleteTrainingEvent");
            
            // list objects that should be deleted
            foreach ($_POST["training_event_id"] as $i) {
                include_once("./Services/ADN/TA/classes/class.adnTrainingEvent.php");
                $cgui->addItem("training_event_id[]", $i, adnTrainingEvent::lookupName($i));
            }
            
            $this->tpl->setContent($cgui->getHTML());
        }
    }
    
    /**
     * Delete training event
     */
    protected function deleteTrainingEvent()
    {
        
        include_once("./Services/ADN/TA/classes/class.adnTrainingEvent.php");
        
        if (is_array($_POST["training_event_id"])) {
            foreach ($_POST["training_event_id"] as $i) {
                $training_event = new adnTrainingEvent($i);
                $training_event->delete();
            }
        }
        ilUtil::sendSuccess($this->lng->txt("adn_training_event_deleted"), true);
        $this->ctrl->redirect($this, "listTrainingEvents");
    }

    /**
     * Set tabs
     */
    public function setTabs($a_activate)
    {

        // back to provider list
        if ($this->not_overview) {
            $this->tabs->setBackTarget(
                $this->lng->txt("back"),
                $this->ctrl->getLinkTargetByClass("adntrainingprovidergui", "listTrainingProviders")
            );
        }
        
        // back to event list (if event form or confirmation)
        if (in_array($this->ctrl->getCmd(), array("editTrainingEvent", "addTrainingEvent",
            "confirmTrainingEventDeletion"))) {
            $this->tabs->setBackTarget(
                $this->lng->txt("back"),
                $this->ctrl->getLinkTarget($this, "listTrainingEvents")
            );
        }

        $this->ctrl->setParameter($this, "arc", "");

        $this->tabs->addTab(
            "current_tr_events",
            $this->lng->txt("adn_current_tr_events"),
            $this->ctrl->getLinkTarget($this, "listTrainingEvents")
        );

        $this->ctrl->setParameter($this, "arc", "1");

        $this->tabs->addTab(
            "archived_tr_events",
            $this->lng->txt("adn_archived_tr_events"),
            $this->ctrl->getLinkTarget($this, "listTrainingEvents")
        );

        $this->ctrl->setParameter($this, "arc", $this->archived);

        if (!$this->archived) {
            $this->tabs->activateTab("current_tr_events");
        } else {
            $this->tabs->activateTab("archived_tr_events");
        }
    }
}
