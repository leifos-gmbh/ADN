<?php
/* Copyright (c) 2010 Leifos, GPL, see docs/LICENSE */

/**
 * ADN objective GUI class
 *
 * Objective forms and persistence (case/mc is differentiated throughout)
 *
 * @author Alex Killing <killing@leifos.de>
 * @version $Id: class.adnObjectiveGUI.php 27873 2011-02-25 16:21:55Z jluetzen $
 *
  @ilCtrl_Calls adnObjectiveGUI: adnSubobjectiveGUI
 *
 * @ingroup ServicesADN
 */
class adnObjectiveGUI
{
    // current objective object
    protected ?adnObjective $objective = null;

    /**
     * Constructor
     */
    public function __construct()
    {
        global $ilCtrl;

        // save objective ID through requests
        $ilCtrl->saveParameter($this, array("ob_id"));
        
        $this->readObjective();
    }

    /**
     * Execute command
     */
    public function executeCommand()
    {
        global $ilCtrl, $lng, $tpl;

        $tpl->setTitle($lng->txt("adn_ed") . " - " . $lng->txt("adn_ed_obs"));

        $next_class = $ilCtrl->getNextClass();

        // forward command to next gui class in control flow
        switch ($next_class) {
            case "adnsubobjectivegui":
                include_once("./Services/ADN/ED/classes/class.adnSubobjectiveGUI.php");
                $sob_gui = new adnSubobjectiveGUI();
                $ilCtrl->forwardCommand($sob_gui);
                break;

            // no next class:
            // this class is responsible to process the command
            default:
                $cmd = $ilCtrl->getCmd("listMCObjectives");

                switch ($cmd) {
                    // commands that need read permission
                    case "listObjectives":
                    case "listMCObjectives":
                    case "listCaseObjectives":
                    case "showQuestionTargetNumbers":
                    case "applyFilter":
                    case "resetFilter":
                        if (adnPerm::check(adnPerm::ED, adnPerm::READ)) {
                            $this->$cmd();
                        }
                        break;

                    // commands that need write permission
                    case "addCaseObjective":
                    case "addMCObjective":
                    case "saveObjective":
                    case "editObjective":
                    case "updateObjective":
                    case "confirmMCObjectiveDeletion":
                    case "confirmCaseObjectiveDeletion":
                    case "deleteObjective":
                        if (adnPerm::check(adnPerm::ED, adnPerm::WRITE)) {
                            $this->$cmd();
                        }
                        break;

                }
                break;
        }
    }

    /**
     * Read objective
     */
    protected function readObjective()
    {
        if ((int) $_GET["ob_id"] > 0) {
            include_once("./Services/ADN/ED/classes/class.adnObjective.php");
            $this->objective = new adnObjective((int) $_GET["ob_id"]);
        }
    }

    /**
     * List objectives (used with subobjective back link)
     */
    protected function listObjectives()
    {
        if ($this->objective) {
            if ($this->objective->getType() == adnObjective::TYPE_MC) {
                return $this->listMCObjectives();
            } else {
                return $this->listCaseObjectives();
            }
        }
    }

    /**
     * List mc objectives
     */
    protected function listMCObjectives()
    {
        global $tpl, $ilToolbar, $ilCtrl, $lng;

        $this->setTabs("mc");

        if (adnPerm::check(adnPerm::ED, adnPerm::WRITE)) {
            $ilToolbar->addButton(
                $lng->txt("adn_add_objective"),
                $ilCtrl->getLinkTarget($this, "addMCObjective")
            );
        }

        // table of objectives
        include_once("./Services/ADN/ED/classes/class.adnObjectiveTableGUI.php");
        $table = new adnObjectiveTableGUI($this, "listMCObjectives", adnObjective::TYPE_MC);

        // output table
        $tpl->setContent($table->getHTML());
    }

    /**
     * List case objectives
     */
    protected function listCaseObjectives()
    {
        global $tpl, $ilToolbar, $ilCtrl, $lng;

        $this->setTabs("case");

        if (adnPerm::check(adnPerm::ED, adnPerm::WRITE)) {
            $ilToolbar->addButton(
                $lng->txt("adn_add_objective"),
                $ilCtrl->getLinkTarget($this, "addCaseObjective")
            );
        }

        // table of objectives
        include_once("./Services/ADN/ED/classes/class.adnObjectiveTableGUI.php");
        $table = new adnObjectiveTableGUI($this, "listCaseObjectives", adnObjective::TYPE_CASE);

        // output table
        $tpl->setContent($table->getHTML());
    }

    /**
     * Apply filter settings (from table gui)
     */
    protected function applyFilter()
    {
        include_once("./Services/ADN/ED/classes/class.adnObjectiveTableGUI.php");
        if ((int) $_REQUEST["type"] == adnObjective::TYPE_MC) {
            $cmd = "listMCObjectives";
        } else {
            $cmd = "listCaseObjectives";
        }
        $table = new adnObjectiveTableGUI($this, $cmd, (int) $_REQUEST["type"]);
        $table->resetOffset();
        $table->writeFilterToSession();

        $this->$cmd();
    }

    /**
     * Reset filter settings (from table gui)
     */
    protected function resetFilter()
    {
        include_once("./Services/ADN/ED/classes/class.adnObjectiveTableGUI.php");
        if ((int) $_REQUEST["type"] == adnObjective::TYPE_MC) {
            $cmd = "listMCObjectives";
        } else {
            $cmd = "listCaseObjectives";
        }
        $table = new adnObjectiveTableGUI($this, $cmd, (int) $_REQUEST["type"]);
        $table->resetOffset();
        $table->resetFilter();

        $this->$cmd();
    }

    /**
     * Add new mc objective form
     *
     * @param ilPropertyFormGUI $a_form
     */
    protected function addMCObjective(ilPropertyFormGUI $a_form = null)
    {
        include_once("./Services/ADN/ED/classes/class.adnObjective.php");
        $this->addObjective(adnObjective::TYPE_MC, $a_form);
    }

    /**
     * Add new case objective form
     *
     * @param ilPropertyFormGUI $a_form
     */
    protected function addCaseObjective(ilPropertyFormGUI $a_form = null)
    {
        include_once("./Services/ADN/ED/classes/class.adnObjective.php");
        $this->addObjective(adnObjective::TYPE_CASE, $a_form);
    }

    /**
     * Add new objective form
     *
     * @param int $a_type
     * @param ilPropertyFormGUI $a_form
     */
    protected function addObjective($a_type, ilPropertyFormGUI $a_form = null)
    {
        global $tpl;

        if (!$a_form) {
            $a_form = $this->initObjectiveForm("create", $a_type);
        }
        $tpl->setContent($a_form->getHTML());
    }

    /**
     * Edit objective form
     *
     * @param ilPropertyFormGUI $a_form
     */
    protected function editObjective(ilPropertyFormGUI $a_form = null)
    {
        global $tpl;

        if (!$a_form) {
            $a_form = $this->initObjectiveForm("edit");
        }
        $tpl->setContent($a_form->getHTML());
    }

    /**
     * Init (sub-)objective form.
     *
     * @param string $a_mode form mode ("create" | "edit")
     * @param int $a_type
     * @return ilPropertyFormGUI $form
     */
    protected function initObjectiveForm($a_mode = "edit", $a_type = null)
    {
        global $lng, $ilCtrl, $ilTabs;

        if (!$a_type) {
            $a_type = $this->objective->getType();
        }

        // get form object and add input fields
        include_once("Services/Form/classes/class.ilPropertyFormGUI.php");
        $form = new ilPropertyFormGUI();

        // catalog area
        include_once "Services/ADN/ED/classes/class.adnCatalogNumbering.php";
        include_once "Services/ADN/ED/classes/class.adnObjective.php";
        if ($a_type == adnObjective::TYPE_MC) {
            $cmd_type = "MC";
            $area = new ilSelectInputGUI($lng->txt("adn_catalog_area"), "catalog_area");
            $area->setOptions(adnCatalogNumbering::getMCAreas());
        } else {
            $cmd_type = "Case";
            $area = new ilSelectInputGUI($lng->txt("adn_subject_area"), "catalog_area");
            $area->setOptions(adnCatalogNumbering::getCaseAreas());
        }
        $area->setRequired(true);
        $form->addItem($area);

        // number
        if ($a_type == adnObjective::TYPE_MC) {
            $number = new ilNumberInputGUI($lng->txt("adn_number"), "number");
            $number->setRequired(true);
            $number->setSize(10);
            $number->setMaxLength(50);
            $form->addItem($number);
        }
        // alpha-numeric
        else {
            $number = new ilTextInputGUI($lng->txt("adn_number"), "number");
            $number->setRequired(true);
            $number->setSize(5);
            $number->setMaxLength(5);
            $form->addItem($number);
        }

        $name = new ilTextInputGUI($lng->txt("adn_title"), "name");
        $name->setRequired(true);
        $name->setMaxLength(100);
        $form->addItem($name);

        $topic = new ilTextInputGUI($lng->txt("adn_topic"), "topic");
        $topic->setMaxLength(200);
        $form->addItem($topic);

        // special case: link questions to different parent objective
        if ($a_type == adnObjective::TYPE_CASE) {
            $sheet = new ilSelectInputGUI($lng->txt("adn_objective_case_sheet_hierarchy"), "sheet");
            $sheet->setOptions(array(0 => $lng->txt("no"),
                1 => $lng->txt("yes")));
            $form->addItem($sheet);
        }

        $ilTabs->setBackTarget(
            $lng->txt("back"),
            $ilCtrl->getLinkTarget($this, "list" . $cmd_type . "Objectives")
        );

        if ($a_mode == "create") {
            $type = new ilHiddenInputGUI("type");
            $type->setValue($a_type);
            $form->addItem($type);
            
            // creation: save/cancel buttons and title
            $form->addCommandButton("saveObjective", $lng->txt("save"));
            $form->addCommandButton("list" . $cmd_type . "Objectives", $lng->txt("cancel"));

            if ($a_type == adnObjective::TYPE_MC) {
                $form->setTitle($lng->txt("adn_add_mc_objective"));
            } else {
                $form->setTitle($lng->txt("adn_add_case_objective"));
            }
        } else {
            $area->setValue($this->objective->getCatalogArea());
            $number->setValue($this->objective->getNumber());
            $name->setValue($this->objective->getName());
            $topic->setValue($this->objective->getTopic());

            if ($a_type == adnObjective::TYPE_CASE) {
                $sheet->setValue((int) $this->objective->isSheetSubjected());
            }
            
            // editing: update/cancel buttons and title
            $form->addCommandButton("updateObjective", $lng->txt("save"));
            $form->addCommandButton("list" . $cmd_type . "Objectives", $lng->txt("cancel"));
            $form->setTitle($lng->txt("adn_edit_objective"));
        }

        $form->setFormAction($ilCtrl->getFormAction($this));

        return $form;
    }

    /**
     * Create new objective
     */
    protected function saveObjective()
    {
        global $tpl, $lng, $ilCtrl;

        $form = $this->initObjectiveForm("create", $_POST["type"]);

        // check input
        if ($form->checkInput()) {
            include_once "Services/ADN/ED/classes/class.adnCatalogNumbering.php";
            $area = $form->getInput("catalog_area");
            if ($form->getInput("type") != adnObjective::TYPE_CASE || !adnCatalogNumbering::isBaseArea($area)) {
                // input ok: create new objective
                include_once("./Services/ADN/ED/classes/class.adnObjective.php");
                $objective = new adnObjective();
                $objective->setCatalogArea($form->getInput("catalog_area"));
                $objective->setType($form->getInput("type"));
                $objective->setNumber($form->getInput("number"));
                $objective->setName($form->getInput("name"));
                $objective->setTopic($form->getInput("topic"));
                $objective->setSheetSubjected($form->getInput("sheet"));

                if ($objective->isUniqueNumber()) {
                    if ($objective->save()) {
                        if ($_POST["type"] == adnObjective::TYPE_CASE) {
                            $cmd_type = "Case";
                        } else {
                            $cmd_type = "MC";
                        }

                        // show success message and return to list
                        ilUtil::sendSuccess($lng->txt("adn_objective_created"), true);
                        $ilCtrl->redirect($this, "list" . $cmd_type . "Objectives");
                    }
                } else {
                    $form->getItemByPostVar("number")->setAlert($lng->txt("adn_unique_number"));
                }
            } else {
                ilUtil::sendFailure($lng->txt("adn_area_base_no_case"));
            }
        }

        // input not valid: show form again
        $form->setValuesByPost();
        $this->addObjective($form->getInput("type"), $form);
    }

    /**
     * Update objective
     */
    protected function updateObjective()
    {
        global $lng, $ilCtrl, $tpl;

        $form = $this->initObjectiveForm("edit");

        // check input
        if ($form->checkInput()) {
            // perform update
            $this->objective->setCatalogArea($form->getInput("catalog_area"));
            $this->objective->setType($form->getInput("type"));
            $this->objective->setNumber($form->getInput("number"));
            $this->objective->setName($form->getInput("name"));
            $this->objective->setTopic($form->getInput("topic"));
            $this->objective->setSheetSubjected($form->getInput("sheet"));

            if ($this->objective->isUniqueNumber()) {
                if ($this->objective->update()) {
                    // show success message and return to list
                    ilUtil::sendSuccess($lng->txt("adn_objective_updated"), true);
                    $ilCtrl->redirect($this, "listObjectives");
                }
            } else {
                $form->getItemByPostVar("number")->setAlert($lng->txt("adn_unique_number"));
            }
        }

        // input not valid: show form again
        $form->setValuesByPost();
        $this->editObjective($form);
    }

    /**
     * Confirm case objective deletion
     */
    protected function confirmCaseObjectiveDeletion()
    {
        $this->confirmObjectiveDeletion("Case");
    }

    /**
     * Confirm mc objective deletion
     */
    protected function confirmMCObjectiveDeletion()
    {
        $this->confirmObjectiveDeletion("MC");
    }

    /**
     * Confirm objective deletion
     *
     * @param string $a_type
     */
    protected function confirmObjectiveDeletion($a_type)
    {
        global $ilCtrl, $tpl, $lng, $ilTabs;

        // check whether at least one item has been seleced
        if (!is_array($_POST["objective_id"]) || count($_POST["objective_id"]) == 0) {
            ilUtil::sendFailure($lng->txt("no_checkbox"), true);
            $ilCtrl->redirect($this, "list" . $a_type . "Objectives");
        } else {
            $ilTabs->setBackTarget(
                $lng->txt("back"),
                $ilCtrl->getLinkTarget($this, "list" . $a_type . "Objectives")
            );

            // display confirmation message
            include_once("./Services/Utilities/classes/class.ilConfirmationGUI.php");
            $cgui = new ilConfirmationGUI();
            $cgui->setFormAction($ilCtrl->getFormAction($this));
            $cgui->setHeaderText($lng->txt("adn_sure_delete_objectives"));
            $cgui->setCancel($lng->txt("cancel"), "list" . $a_type . "Objectives");
            $cgui->setConfirm($lng->txt("delete"), "deleteObjective");

            // list objects that should be deleted
            foreach ($_POST["objective_id"] as $i) {
                include_once("./Services/ADN/ED/classes/class.adnObjective.php");
                $cgui->addItem("objective_id[]", $i, adnObjective::lookupName($i));
            }

            $tpl->setContent($cgui->getHTML());
        }
    }

    /**
     * Delete objective
     */
    protected function deleteObjective()
    {
        global $ilCtrl, $lng;

        include_once("./Services/ADN/ED/classes/class.adnObjective.php");

        $has_failed = $type = false;
        if (is_array($_POST["objective_id"])) {
            foreach ($_POST["objective_id"] as $i) {
                $objective = new adnObjective($i);
                if (!$type) {
                    $type = $objective->getType();
                }
                if (!$objective->delete()) {
                    $has_failed = true;
                }
            }
        }
        if (!$has_failed) {
            ilUtil::sendSuccess($lng->txt("adn_objective_deleted"), true);
        } else {
            ilUtil::sendFailure($lng->txt("adn_objective_not_deleted"), true);
        }
        if ($type == adnObjective::TYPE_MC) {
            $ilCtrl->redirect($this, "listMCObjectives");
        } else {
            $ilCtrl->redirect($this, "listCaseObjectives");
        }
    }

    /**
     * Set tabs
     *
     * @param string $a_activate
     */
    public function setTabs($a_activate)
    {
        global $ilTabs, $lng, $txt, $ilCtrl;

        $ilTabs->addTab(
            "mc",
            $lng->txt("adn_mc_part"),
            $ilCtrl->getLinkTarget($this, "listMCObjectives")
        );

        $ilTabs->addTab(
            "case",
            $lng->txt("adn_case_part"),
            $ilCtrl->getLinkTarget($this, "listCaseObjectives")
        );

        $ilTabs->activateTab($a_activate);
    }
}
