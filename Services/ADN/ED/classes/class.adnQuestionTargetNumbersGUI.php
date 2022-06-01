<?php
/* Copyright (c) 2010 Leifos, GPL, see docs/LICENSE */

/**
 * ADN question target numbers GUI class
 *
 * Target number list, forms and persistence
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @version $Id: class.adnQuestionTargetNumbersGUI.php 27874 2011-02-25 16:36:28Z jluetzen $
 *
 * @ilCtrl_Calls adnQuestionTargetNumbersGUI:
 *
 * @ingroup ServicesADN
 */
class adnQuestionTargetNumbersGUI
{
    // current area id
    protected string $area_id = '';

    // current type
    protected string $type = '';

    // current target object
    protected ?adnQuestionTargetNumbers $target = null;

    protected ilCtrl $ctrl;
    protected ilLanguage $lng;
    protected ilGlobalTemplateInterface $tpl;
    protected ilToolbarGUI $toolbar;
    protected ilTabsGUI $tabs;
    
    /**
     * Constructor
     */
    public function __construct()
    {
        global $DIC;
        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();
        $this->tpl = $DIC->ui()->mainTemplate();
        $this->toolbar = $DIC->toolbar();
        $this->tabs = $DIC->tabs();

        $this->area_id = (string) $_REQUEST["area_id"];
        $this->type = (string) $_REQUEST["type_id"];

        // save area, type and target ID through requests
        $this->ctrl->saveParameter($this, array("area_id"));
        $this->ctrl->saveParameter($this, array("type_id"));
        $this->ctrl->saveParameter($this, array("tgt_id"));
        
        $this->readTarget();
    }
    
    /**
     * Execute command
     */
    public function executeCommand()
    {

        $this->tpl->setTitle($this->lng->txt("adn_ed") . " - " . $this->lng->txt("adn_ed_nqs"));
        
        $next_class = $this->ctrl->getNextClass();

        $this->showTabs();
        
        // forward command to next gui class in control flow
        switch ($next_class) {
            // no next class:
            // this class is responsible to process the command
            default:
                $cmd = $this->ctrl->getCmd("listTargets");
                
                switch ($cmd) {
                    // commands that need read permission
                    case "listTargets":
                        if (adnPerm::check(adnPerm::ED, adnPerm::READ)) {
                            $this->$cmd();
                        }
                        break;
                    
                    // commands that need write permission
                    case "addTarget":
                    case "saveTarget":
                    case "editTarget":
                    case "updateTarget":
                    case "confirmTargetsDeletion":
                    case "deleteTargets":
                    case "saveOverall":
                        if (adnPerm::check(adnPerm::ED, adnPerm::WRITE)) {
                            $this->$cmd();
                        }
                        break;
                    
                }
                break;
        }
    }
    
    /**
     * Read target
     */
    protected function readTarget()
    {
        if ((int) $_GET["tgt_id"] > 0) {
            include_once("./Services/ADN/ED/classes/class.adnQuestionTargetNumbers.php");
            $this->target = new adnQuestionTargetNumbers((int) $_GET["tgt_id"]);
        }
    }
    
    /**
     * List targets
     */
    public function listTargets()
    {

        include_once "Services/ADN/ED/classes/class.adnQuestionTargetNumbers.php";
        if ($this->type != adnQuestionTargetNumbers::TYPE_CASE) {
            if (adnPerm::check(adnPerm::ED, adnPerm::WRITE)) {
                $this->toolbar->addButton(
                    $this->lng->txt("adn_add_question_target_number"),
                    $this->ctrl->getLinkTarget($this, "addTarget")
                );
            }

            $targets = adnQuestionTargetNumbers::getAllTargets($this->area_id, $this->type);

            include_once("./Services/ADN/ED/classes/class.adnQuestionTargetNumbersTableGUI.php");
            $table = new adnQuestionTargetNumbersTableGUI(
                $this,
                "listTargets",
                $this->area_id,
                $this->type,
                $targets
            );
        }

        include_once("./Services/ADN/ED/classes/class.adnQuestionTargetNumbers.php");
        $overall_value = (int) adnQuestionTargetNumbers::readOverall($this->area_id, $this->type);

        // warning if overall value is different than current sum
        if ($this->type != adnQuestionTargetNumbers::TYPE_CASE) {
            foreach ($targets as $target) {
                $overall_current += $target["nr_of_questions"];
            }
            $diff = abs($overall_value - $overall_current);
            if ($overall_value > $overall_current) {
                ilUtil::sendInfo(sprintf($this->lng->txt("adn_overall_info_lower"), $diff));
            } elseif ($overall_value < $overall_current) {
                ilUtil::sendInfo(sprintf($this->lng->txt("adn_overall_info_higher"), $diff));
            }
        }

        $toolbar = "";
        if (adnPerm::check(adnPerm::ED, adnPerm::WRITE)) {
            include_once("./Services/Form/classes/class.ilPropertyFormGUI.php");
            $overall = new ilTextInputGUI($this->lng->txt("adn_overall"), "overall");
            $overall->setSize(3);
            $overall->setMaxLength(2);
            $overall->setValue($overall_value);

            $toolbar = new ilToolbarGUI();
            $toolbar->setFormAction($this->ctrl->getFormAction($this));
            $toolbar->addInputItem($overall, $this->lng->txt("adn_overall"));
            $toolbar->addFormButton($this->lng->txt("save"), "saveOverall");
            $toolbar = $toolbar->getHTML();
        } else {
            ilUtil::sendInfo($this->lng->txt("adn_overall") . ":  " . $overall_value);
        }

        if ($this->type != adnQuestionTargetNumbers::TYPE_CASE) {
            $this->tpl->setContent($table->getHTML() . "<br />" . $toolbar);
        } else {
            $this->tpl->setContent($toolbar);
        }
    }

    /**
     * Save overall data
     */
    protected function saveOverall()
    {
        
        $overall = (int) $_POST["overall"];
        if (!$overall) {
            ilUtil::sendFailure($this->lng->txt("adn_overall_fail"), true);
        } else {
            include_once("./Services/ADN/ED/classes/class.adnQuestionTargetNumbers.php");
            adnQuestionTargetNumbers::saveOverall($this->area_id, $this->type, $overall);
            ilUtil::sendSuccess($this->lng->txt("adn_overall_saved"), true);
        }

        $this->ctrl->redirect($this, "listTargets");
    }
    
    /**
     * Add new target form
     */
    protected function addTarget()
    {

        $this->tabs->setBackTarget($this->lng->txt("back"), $this->ctrl->getLinkTarget($this, "listTargets"));

        $this->tpl->setContent($this->initTargetForm("create"));
    }
    
    /**
     * Edit target form
     */
    protected function editTarget()
    {

        $this->tabs->setBackTarget($this->lng->txt("back"), $this->ctrl->getLinkTarget($this, "listTargets"));

        $this->tpl->setContent($this->initTargetForm("edit"));
    }
    
    /**
     * Init target form
     *
     * @param string $a_mode form mode ("create" | "edit")
     * @return string
     */
    protected function initTargetForm($a_mode = "edit")
    {

        include_once("./Services/ADN/ED/classes/class.adnQuestionTargetNumbersObjectiveTableGUI.php");
        $table = new adnQuestionTargetNumbersObjectiveTableGUI(
            $this,
            "listTargets",
            $this->area_id,
            $this->type,
            $this->target,
            $a_mode
        );

        return $table->getHTML() . "</form>";
    }
    
    /**
     * Create new target number
     */
    protected function saveTarget()
    {
        
        $post = array("number" => (int) $_POST["number"],
            "single" => (bool) $_POST["single"],
            "obj" => $_POST["objective_id"],
            "sobj" => $_POST["subobjective_id"]);
        
        if ($post["number"] && ($post["obj"] || $post["sobj"])) {
            // input ok: create new area
            include_once("./Services/ADN/ED/classes/class.adnQuestionTargetNumbers.php");
            $target = new adnQuestionTargetNumbers();
            $target->setArea($this->area_id);
            $target->setType($this->type);
            $target->setNumber($post["number"]);
            $target->setSingle($post["single"]);

            $objectives = array();
            if ($post["obj"]) {
                foreach ($post["obj"] as $obj_id) {
                    $objectives[] = array("ed_objective_id" => $obj_id);
                }
            }
            if ($post["sobj"]) {
                foreach ($post["sobj"] as $sobj_id) {
                    $objectives[] = array("ed_subobjective_id" => $sobj_id);
                }
            }
            $target->setObjectives($objectives);
            
            if ($target->save()) {
                // show success message and return to list
                ilUtil::sendSuccess($this->lng->txt("adn_question_target_number_created"), true);
                $this->ctrl->redirect($this, "listTargets");
            }
        } else {
            $mess = "";
            if (!$post["number"]) {
                $mess .= $this->lng->txt("adn_question_target_invalid_number");
            }
            if (!$post["obj"] && !$post["sobj"]) {
                $mess .= " " . $this->lng->txt("adn_question_target_invalid_objective");
            }
            ilUtil::sendFailure($mess);
        }

        // input not valid: show form again
        $this->addTarget();
    }
    
    /**
     * Update target number
     *
     * @return bool
     */
    protected function updateTarget()
    {

        $post = array("number" => (int) $_POST["number"],
            "single" => (bool) $_POST["single"],
            "obj" => $_POST["objective_id"],
            "sobj" => $_POST["subobjective_id"]);

        if ($post["number"] && ($post["obj"] || $post["sobj"])) {
            // perform update
            $this->target->setNumber($post["number"]);
            $this->target->setSingle($post["single"]);

            $objectives = array();
            if ($post["obj"]) {
                foreach ($post["obj"] as $obj_id) {
                    $objectives[] = array("ed_objective_id" => $obj_id);
                }
            }
            if ($post["sobj"]) {
                foreach ($post["sobj"] as $sobj_id) {
                    $objectives[] = array("ed_subobjective_id" => $sobj_id);
                }
            }
            $this->target->setObjectives($objectives);
            
            if ($this->target->update()) {
                // show success message and return to list
                ilUtil::sendSuccess($this->lng->txt("adn_question_target_number_updated"), true);
                $this->ctrl->redirect($this, "listTargets");
            }
        } else {
            $mess = "";
            if (!$post["number"]) {
                $mess .= $this->lng->txt("adn_question_target_invalid_number");
            }
            if (!$post["obj"] && !$post["sobj"]) {
                $mess .= " " . $this->lng->txt("adn_question_target_invalid_objective");
            }
            ilUtil::sendFailure($mess);
        }
        
        // input not valid: show form again
        $this->editTarget();
    }
    
    /**
     * Confirm target deletion
     */
    protected function confirmTargetsDeletion()
    {
        
        // check whether at least one item has been seleced
        if (!is_array($_POST["target_id"]) || count($_POST["target_id"]) == 0) {
            ilUtil::sendFailure($this->lng->txt("no_checkbox"), true);
            $this->ctrl->redirect($this, "listTargets");
        } else {
            $this->tabs->setBackTarget(
                $this->lng->txt("back"),
                $this->ctrl->getLinkTarget($this, "listTargets")
            );

            // display confirmation message
            include_once("./Services/Utilities/classes/class.ilConfirmationGUI.php");
            $cgui = new ilConfirmationGUI();
            $cgui->setFormAction($this->ctrl->getFormAction($this));
            $cgui->setHeaderText($this->lng->txt("adn_sure_delete_question_target_numbers"));
            $cgui->setCancel($this->lng->txt("cancel"), "listTargets");
            $cgui->setConfirm($this->lng->txt("delete"), "deleteTargets");

            // list objects that should be deleted
            include_once("./Services/ADN/ED/classes/class.adnQuestionTargetNumbers.php");
            foreach ($_POST["target_id"] as $i) {
                $cgui->addItem("target_id[]", $i, adnQuestionTargetNumbers::lookupName($i));
            }
            
            $this->tpl->setContent($cgui->getHTML());
        }
    }
    
    /**
     * Delete targets
     */
    protected function deleteTargets()
    {
        
        include_once("./Services/ADN/ED/classes/class.adnQuestionTargetNumbers.php");
        
        if (is_array($_POST["target_id"])) {
            foreach ($_POST["target_id"] as $i) {
                $target = new adnQuestionTargetNumbers($i);
                $target->delete();
            }
        }
        ilUtil::sendSuccess($this->lng->txt("adn_question_target_number_deleted"), true);
        $this->ctrl->redirect($this, "listTargets");
    }

    /**
     * Render tabs
     */
    protected function showTabs()
    {

        // determin current area
        include_once("./Services/ADN/ED/classes/class.adnSubjectArea.php");
        $all_areas = adnSubjectArea::getAllAreas();
        if (isset($all_areas[$this->area_id])) {
            $c_area = $this->area_id;
        } else {
            $c_area = adnSubjectArea::DRY_MATERIAL;
        }
        $this->area_id = $c_area;
        
        // subtabs
        if (in_array($c_area, array(adnSubjectArea::CHEMICAL,
            adnSubjectArea::GAS))) {
            include_once("./Services/ADN/ED/classes/class.adnObjective.php");

            $this->ctrl->setParameter($this, "type_id", adnObjective::TYPE_MC);
            $this->tabs->addSubtab(
                "type_" . adnObjective::TYPE_MC,
                $this->lng->txt("adn_mc_part"),
                $this->ctrl->getLinkTarget($this, "listTargets")
            );

            $this->ctrl->setParameter($this, "type_id", adnObjective::TYPE_CASE);
            $this->tabs->addSubtab(
                "type_" . adnObjective::TYPE_CASE,
                $this->lng->txt("adn_case_part"),
                $this->ctrl->getLinkTarget($this, "listTargets")
            );

            $this->ctrl->setParameter($this, "type_id", $this->type);

            if (!$this->type) {
                $this->tabs->activateSubtab("type_" . adnObjective::TYPE_MC);
                $this->type = 1;
            } else {
                $this->tabs->activateSubtab("type_" . $this->type);
            }
        } else {
            $this->type = null;
            $this->ctrl->setParameter($this, "type_id", "");
        }

        // tabs
        foreach ($all_areas as $k => $v) {
            $this->ctrl->setParameter($this, "area_id", $k);
            $this->tabs->addTab("area_" . $k, $v, $this->ctrl->getLinkTarget($this, "listTargets"));
        }
        $this->ctrl->setParameter($this, "area_id", $this->area_id);
        $this->tabs->activateTab("area_" . $c_area);
    }
}
