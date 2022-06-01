<?php
/* Copyright (c) 2010 Leifos, GPL, see docs/LICENSE */

/**
 * ADN subobjective GUI class
 *
 * Subobjective list, forms and persistence (objective is mandatory)
 *
 * @author Alex Killing <killing@leifos.de>
 * @version $Id: class.adnSubobjectiveGUI.php 27874 2011-02-25 16:36:28Z jluetzen $
 *
 * @ingroup ServicesADN
 */
class adnSubobjectiveGUI
{
    // current subobjective object
    protected ?adnSubobjective $subobjective = null;

    protected ilCtrl $ctrl;
    protected ilGlobalTemplateInterface $tpl;
    protected ilToolbarGUI $toolbar;
    protected ilLanguage $lng;
    protected ilTabsGUI $tabs;
    /**
     * Constructor
     */
    public function __construct()
    {
        global $DIC;
        $this->ctrl = $DIC->ctrl();
        $this->tpl = $DIC->ui()->mainTemplate();
        $this->toolbar = $DIC->toolbar();
        $this->lng = $DIC->language();
        $this->tabs = $DIC->tabs();

        $this->objective_id = (int) $_REQUEST["ob_id"];

        // save subobjective ID through requests
        $this->ctrl->saveParameter($this, array("sob_id"));
        
        $this->readSubobjective();
    }

    /**
     * Execute command
     */
    public function executeCommand()
    {

        $next_class = $this->ctrl->getNextClass();

        // forward command to next gui class in control flow
        switch ($next_class) {
            // no next class:
            // this class is responsible to process the command
            default:
                $cmd = $this->ctrl->getCmd("listSubobjectives");

                switch ($cmd) {
                    // commands that need read permission
                    case "listSubobjectives":
                        if (adnPerm::check(adnPerm::ED, adnPerm::READ)) {
                            $this->$cmd();
                        }
                        break;

                    // commands that need write permission
                    case "addSubobjective":
                    case "saveSubobjective":
                    case "editSubobjective":
                    case "updateSubobjective":
                    case "confirmSubobjectiveDeletion":
                    case "deleteSubobjective":
                        if (adnPerm::check(adnPerm::ED, adnPerm::WRITE)) {
                            $this->$cmd();
                        }
                        break;

                }
                break;
        }
    }

    /**
     * Read subobjective
     */
    protected function readSubobjective()
    {
        if ((int) $_GET["sob_id"] > 0) {
            include_once("./Services/ADN/ED/classes/class.adnSubobjective.php");
            $this->subobjective = new adnSubobjective((int) $_GET["sob_id"]);
        }
    }

    /**
     * List all subobjectives
     */
    protected function listSubobjectives()
    {

        $this->tabs->setBackTarget(
            $this->lng->txt("back"),
            $this->ctrl->getLinkTargetByClass("adnObjectiveGUI", "listObjectives")
        );

        if (adnPerm::check(adnPerm::ED, adnPerm::WRITE)) {
            $this->toolbar->addButton(
                $this->lng->txt("adn_add_subobjective"),
                $this->ctrl->getLinkTarget($this, "addSubobjective")
            );
        }

        // table of objectives
        include_once("./Services/ADN/ED/classes/class.adnSubobjectiveTableGUI.php");
        $table = new adnSubobjectiveTableGUI($this, "listSubobjectives", $this->objective_id);

        // output table
        $this->tpl->setContent($table->getHTML());
    }

    /**
     * Add new subobjective form
     *
     * @param ilPropertyFormGUI $a_form
     */
    protected function addSubobjective(ilPropertyFormGUI $a_form = null)
    {

        if (!$a_form) {
            $a_form = $this->initSubobjectiveForm("create");
        }
        $this->tpl->setContent($a_form->getHTML());
    }

    /**
     * Edit subobjective form
     *
     * @param ilPropertyFormGUI $a_form
     */
    protected function editSubobjective(ilPropertyFormGUI $a_form = null)
    {

        if (!$a_form) {
            $a_form = $this->initSubobjectiveForm("edit");
        }
        $this->tpl->setContent($a_form->getHTML());
    }

    /**
     * Init subobjective form.
     *
     * @param string $a_mode form mode ("create" | "edit")
     * @return ilPropertyFormGUI $form
     */
    protected function initSubobjectiveForm($a_mode = "edit")
    {

        $this->tabs->setBackTarget(
            $this->lng->txt("back"),
            $this->ctrl->getLinkTarget($this, "listSubobjectives")
        );

        // get form object and add input fields
        include_once("Services/Form/classes/class.ilPropertyFormGUI.php");
        $form = new ilPropertyFormGUI();

        include_once "Services/ADN/ED/classes/class.adnObjective.php";
        $obj = new adnObjective($this->objective_id);
        if ($obj->getType() != adnObjective::TYPE_CASE) {
            $number = new ilNumberInputGUI($this->lng->txt("adn_number"), "number");
            $number->setRequired(true);
            $number->setSize(10);
            $number->setMaxLength(50);
            $form->addItem($number);
        } else {
            $number = new ilTextInputGUI($this->lng->txt("adn_number"), "number");
            $number->setRequired(true);
            $number->setSize(5);
            $number->setMaxLength(5);
            $form->addItem($number);
        }

        $name = new ilTextInputGUI($this->lng->txt("adn_title"), "name");
        $name->setRequired(true);
        $name->setMaxLength(100);
        $form->addItem($name);

        $topic = new ilTextInputGUI($this->lng->txt("adn_topic"), "topic");
        $topic->setMaxLength(200);
        $form->addItem($topic);

        include_once "Services/ADN/ED/classes/class.adnObjective.php";
        $objective = new adnObjective($this->objective_id);
        $objective = $objective->buildADNNumber() . " " . $objective->getName();

        if ($a_mode == "create") {
            // creation: save/cancel buttons and title
            $form->addCommandButton("saveSubobjective", $this->lng->txt("save"));
            $form->addCommandButton("listSubobjectives", $this->lng->txt("cancel"));
            $form->setTitle($this->lng->txt("adn_add_subobjective") . ": " . $objective);
        } else {
            $name->setValue($this->subobjective->getName());
            $topic->setValue($this->subobjective->getTopic());
            $number->setValue($this->subobjective->getNumber());

            // editing: update/cancel buttons and title
            $form->addCommandButton("updateSubobjective", $this->lng->txt("save"));
            $form->addCommandButton("listSubobjectives", $this->lng->txt("cancel"));
            $form->setTitle($this->lng->txt("adn_edit_subobjective") . ": " . $objective);
        }

        $form->setFormAction($this->ctrl->getFormAction($this));

        return $form;
    }

    /**
     * Create subobjective
     */
    protected function saveSubobjective()
    {

        $form = $this->initSubobjectiveForm("create");

        // check input
        if ($form->checkInput()) {
            // input ok: create new subobjective
            include_once("./Services/ADN/ED/classes/class.adnSubobjective.php");
            $subobjective = new adnSubobjective();
            $subobjective->setObjective($this->objective_id);
            $subobjective->setNumber($form->getInput("number"));
            $subobjective->setName($form->getInput("name"));
            $subobjective->setTopic($form->getInput("topic"));

            if ($subobjective->isUniqueNumber()) {
                if ($subobjective->save()) {
                    // show success message and return to list
                    ilUtil::sendSuccess($this->lng->txt("adn_subobjective_created"), true);
                    $this->ctrl->redirect($this, "listSubobjectives");
                }
            } else {
                $form->getItemByPostVar("number")->setAlert($this->lng->txt("adn_unique_number"));
            }
        }

        // input not valid: show form again
        $form->setValuesByPost();
        $this->addSubobjective($form);
    }

    /**
     * Update subobjective
     */
    protected function updateSubobjective()
    {

        $form = $this->initSubobjectiveForm("edit");

        // check input
        if ($form->checkInput()) {
            // perform update
            $this->subobjective->setNumber($form->getInput("number"));
            $this->subobjective->setName($form->getInput("name"));
            $this->subobjective->setTopic($form->getInput("topic"));

            if ($this->subobjective->isUniqueNumber()) {
                if ($this->subobjective->update()) {
                    // show success message and return to list
                    ilUtil::sendSuccess($this->lng->txt("adn_subobjective_updated"), true);
                    $this->ctrl->redirect($this, "listSubobjectives");
                }
            } else {
                $form->getItemByPostVar("number")->setAlert($this->lng->txt("adn_unique_number"));
            }
        }

        // input not valid: show form again
        $form->setValuesByPost();
        $this->editSubobjective($form);
    }

    /**
     * Confirm subobjective deletion
     */
    protected function confirmSubobjectiveDeletion()
    {

        // check whether at least one item has been seleced
        if (!is_array($_POST["subobjective_id"]) || count($_POST["subobjective_id"]) == 0) {
            ilUtil::sendFailure($this->lng->txt("no_checkbox"), true);
            $this->ctrl->redirect($this, "listSubobjectives");
        } else {
            $this->tabs->setBackTarget(
                $this->lng->txt("back"),
                $this->ctrl->getLinkTarget($this, "listSubobjectives")
            );

            // display confirmation message
            include_once("./Services/Utilities/classes/class.ilConfirmationGUI.php");
            $cgui = new ilConfirmationGUI();
            $cgui->setFormAction($this->ctrl->getFormAction($this));
            $cgui->setHeaderText($this->lng->txt("adn_sure_delete_subobjectives"));
            $cgui->setCancel($this->lng->txt("cancel"), "listSubobjectives");
            $cgui->setConfirm($this->lng->txt("delete"), "deleteSubobjective");

            // list objects that should be deleted
            foreach ($_POST["subobjective_id"] as $i) {
                include_once("./Services/ADN/ED/classes/class.adnSubobjective.php");
                $cgui->addItem("subobjective_id[]", $i, adnSubobjective::lookupName($i));
            }

            $this->tpl->setContent($cgui->getHTML());
        }
    }

    /**
     * Delete subobjective
     */
    protected function deleteSubobjective()
    {

        include_once("./Services/ADN/ED/classes/class.adnSubobjective.php");

        $has_failed = false;
        if (is_array($_POST["subobjective_id"])) {
            foreach ($_POST["subobjective_id"] as $i) {
                $subobjective = new adnSubobjective($i);
                if (!$subobjective->delete()) {
                    $has_failed = true;
                }
            }
        }
        if (!$has_failed) {
            ilUtil::sendSuccess($this->lng->txt("adn_subobjective_deleted"), true);
        } else {
            ilUtil::sendFailure($this->lng->txt("adn_subobjective_not_deleted"), true);
        }
        $this->ctrl->redirect($this, "listSubobjectives");
    }
}
