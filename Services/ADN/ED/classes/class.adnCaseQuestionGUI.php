<?php
/* Copyright (c) 2010 Leifos, GPL, see docs/LICENSE */

include_once("./Services/ADN/ED/classes/class.adnExaminationQuestionGUI.php");

/**
 * ADN case question GUI class
 *
 * Case question forms (good related answers are in separate class, called through here)
 *
 * @author Alex Killing <killing@leifos.de>
 * @version $Id: class.adnCaseQuestionGUI.php 27873 2011-02-25 16:21:55Z jluetzen $
 *
 * @ilCtrl_Calls adnCaseQuestionGUI: adnGoodRelatedAnswerGUI
 *
 * @ingroup ServicesADN
 */
class adnCaseQuestionGUI extends adnExaminationQuestionGUI
{
    // current case question object
    protected ?adnCaseQuestion $question = null;
    protected ilToolbarGUI $toolbar;
    /**
     * Constructor
     */
    public function __construct()
    {
        global $DIC;
        $this->toolbar = $DIC->toolbar();
        parent::__construct();
        // save case question ID through requests
        $this->ctrl->saveParameter($this, array("eq_id"));
        
        $this->readCaseQuestion();
    }
    
    /**
     * Execute command
     */
    public function executeCommand()
    {

        $this->tpl->setTitle($this->lng->txt("adn_ed") . " - " . $this->lng->txt("adn_ed_eqs"));

        $this->setTabs("case_questions");
        
        $next_class = $this->ctrl->getNextClass();
        
        // forward command to next gui class in control flow
        switch ($next_class) {
            case "adngoodrelatedanswergui":

                // tab handling
                
                $this->tabs->addSubTab(
                    "editcase",
                    $this->lng->txt("adn_edit_case_question"),
                    $this->ctrl->getLinkTarget($this, "editCaseQuestion")
                );

                $this->tabs->addSubTab(
                    "goodrelatedanswer",
                    $this->lng->txt("adn_good_related_answers"),
                    $this->ctrl->getLinkTargetByClass("adngoodrelatedanswergui", "listAnswers")
                );

                $this->tabs->activateSubTab("goodrelatedanswer");

                include_once("./Services/ADN/ED/classes/class.adnGoodRelatedAnswerGUI.php");
                $cqa_gui = new adnGoodRelatedAnswerGUI();
                $this->ctrl->forwardCommand($cqa_gui);
                break;

            // no next class:
            // this class is responsible to process the command
            default:
                $cmd = $this->ctrl->getCmd("listCaseQuestions");
                
                switch ($cmd) {
                    // commands that need read permission
                    case "listCaseQuestions":
                    case "applyFilter":
                    case "resetFilter":
                    case "showImage":
                    case "showCaseQuestion":
                    case "showCaseBackup":
                        if (adnPerm::check(adnPerm::ED, adnPerm::READ)) {
                            $this->$cmd();
                        }
                        break;
                    
                    // commands that need write permission
                    case "addCaseQuestion":
                    case "saveCaseQuestion":
                    case "editCaseQuestion":
                    case "updateCaseQuestion":
                    case "updateBackupCaseQuestion":
                    case "confirmQuestionDeletion":
                    case "deleteCaseQuestion":
                    case "activateQuestion":
                    case "deactivateQuestion":
                        if (adnPerm::check(adnPerm::ED, adnPerm::WRITE)) {
                            $this->$cmd();
                        }
                        break;
                    
                }
                break;
        }
    }
    
    /**
     * Read case question
     */
    protected function readCaseQuestion()
    {
        if ((int) $_GET["eq_id"] > 0) {
            include_once("./Services/ADN/ED/classes/class.adnCaseQuestion.php");
            $this->question = new adnCaseQuestion((int) $_GET["eq_id"]);
        }
    }
    
    /**
     * List all case questions
     */
    protected function listCaseQuestions()
    {

        // add button incl. area select
        if (adnPerm::check(adnPerm::ED, adnPerm::WRITE)) {
            include_once("./Services/ADN/ED/classes/class.adnObjective.php");
            $options = adnObjective::getAllCaseCatalogAreas();
            include_once("./Services/Form/classes/class.ilSelectInputGUI.php");
            $si = new ilSelectInputGUI($this->lng->txt("adn_subject_area"), "catalog_area");
            $si->setOptions($options);
            $this->toolbar->addInputItem($si, true);
            $this->toolbar->setFormAction($this->ctrl->getFormAction($this, "addCaseQuestion"));
            $this->toolbar->addFormButton($this->lng->txt("adn_add_case_question"), "addCaseQuestion");
        }

        // table of mc quesstions
        include_once("./Services/ADN/ED/classes/class.adnExaminationQuestionTableGUI.php");
        $table = new adnExaminationQuestionTableGUI($this, "listCaseQuestions", true);

        // output table
        $this->tpl->setContent($table->getHTML());
    }

    /**
     * Apply filter settings (from table gui)
     */
    protected function applyFilter()
    {
        include_once("./Services/ADN/ED/classes/class.adnExaminationQuestionTableGUI.php");
        $table = new adnExaminationQuestionTableGUI($this, "listCaseQuestions", true);
        $table->resetOffset();
        $table->writeFilterToSession();

        $this->listCaseQuestions();
    }

    /**
     * Reset filter settings (from table gui)
     */
    protected function resetFilter()
    {
        include_once("./Services/ADN/ED/classes/class.adnExaminationQuestionTableGUI.php");
        $table = new adnExaminationQuestionTableGUI($this, "listCaseQuestions", true);
        $table->resetOffset();
        $table->resetFilter();

        $this->listCaseQuestions();
    }
    
    /**
     * Add new case question form
     *
     * @param ilPropertyFormGUI $a_form
     */
    protected function addCaseQuestion(ilPropertyFormGUI $a_form = null)
    {

        if (!$a_form) {
            $area = (int) $_REQUEST["catalog_area"];
            $a_form = $this->initCaseQuestionForm($area, "create");
        }
        $this->tpl->setContent($a_form->getHTML());
    }
    
    /**
     * Edit case question form
     *
     * @param ilPropertyFormGUI $a_form
     */
    protected function editCaseQuestion(ilPropertyFormGUI $a_form = null)
    {

        $this->tabs->addSubTab(
            "editcase",
            $this->lng->txt("adn_edit_case_question"),
            $this->ctrl->getLinkTarget($this, "editCaseQuestion")
        );

        $this->tabs->addSubTab(
            "goodrelatedanswer",
            $this->lng->txt("adn_good_related_answers"),
            $this->ctrl->getLinkTargetByClass("adngoodrelatedanswergui", "listAnswers")
        );

        $this->tabs->activateSubTab("editcase");

        if (!$a_form) {
            $a_form = $this->initCaseQuestionForm($this->question->getCatalogArea(), "edit");
        }
        $this->tpl->setContent($a_form->getHTML());
    }

    /**
     * Show case question (read-only)
     */
    protected function showCaseQuestion()
    {

        $this->tabs->setBackTarget($this->lng->txt("back"), $this->ctrl->getLinkTarget($this, "listCaseQuestions"));

        $form = $this->initCaseQuestionForm($this->question->getCatalogArea(), "show");
        $form = $form->convertToReadonly();
        $this->tpl->setContent($form->getHTML());
    }

    /**
     * Show case question backup (read-only)
     */
    protected function showCaseBackup()
    {

        $this->tabs->setBackTarget($this->lng->txt("back"), $this->ctrl->getLinkTarget($this, "listCaseQuestions"));

        $this->question->readBackup();

        $form = $this->initCaseQuestionForm($this->question->getCatalogArea(), "backup");
        $form = $form->convertToReadonly();
        $this->tpl->setContent($form->getHTML());
    }
    
    /**
     * Init case question form
     *
     * @param int $a_catalog_area
     * @param string $a_mode form mode ("create" | "edit" | "show" | "backup")
     * @return ilPropertyFormGUI
     */
    protected function initCaseQuestionForm($a_catalog_area, $a_mode = "edit")
    {

        $this->tabs->setBackTarget(
            $this->lng->txt("back"),
            $this->ctrl->getLinkTarget($this, "listCaseQuestions")
        );

        $form = $this->initBaseForm($a_catalog_area, $a_mode);

        // default answer
        $default = new ilTextAreaInputGUI($this->lng->txt("adn_default_answer"), "default_answer");
        $default->setCols(80);
        $default->setRows(10);
        $default->setSpecialCharacters(true);
        $default->setFormId($form->getId());
        $form->addItem($default);

        // good specific
        $specific = new ilCheckboxInputGUI($this->lng->txt("adn_good_specific_answer"), "specific");
        $form->addItem($specific);

        // goods (foreign key)
        include_once "Services/ADN/ED/classes/class.adnGoodInTransit.php";
        include_once "Services/ADN/ED/classes/class.adnCatalogNumbering.php";
        $goods = null;
        // different goods for gas/chemical areas
        if ($a_mode != "create") {
            $goods = $this->question->getGoods();
        }
        if (adnCatalogNumbering::isGasArea($a_catalog_area)) {
            $goods = adnGoodInTransit::getGoodsSelect(adnGoodInTransit::TYPE_GAS, null, $goods);
        } else {
            $goods = adnGoodInTransit::getGoodsSelect(adnGoodInTransit::TYPE_CHEMICALS, null, $goods);
        }
        if ($goods) {
            foreach ($goods as $good_id => $good_name) {
                $box = new ilCheckboxInputGUI($good_name, "goods[]");
                $box->setValue($good_id);

                if ($a_mode != "create" && $this->question->hasGood($good_id)) {
                    $box->setChecked(true);
                }

                $specific->addSubItem($box);
            }
        }

        // include good related answers (in read-only modes)
        if ($a_mode == "show" || $a_mode == "backup") {
            include_once "Services/ADN/ED/classes/class.adnGoodRelatedAnswer.php";
            $answers = adnGoodRelatedAnswer::getAllAnswers($this->question->getId());
            if ($answers) {
                include_once "Services/ADN/ED/classes/class.adnGoodInTransit.php";
                $related = array();
                foreach ($answers as $answer) {
                    $goods = array();
                    foreach ($answer["goods"] as $good_id) {
                        $goods[] = adnGoodInTransit::lookupName($good_id);
                    }
                    $related[] = implode("; ", $goods) . ":<br />" . $answer["answer"];
                }
                $static = new ilNonEditableValueGUI($this->lng->txt("adn_good_related_answers"));
                $static->setValue("<div>" . implode("</div><div style=\"margin-top:5px\">", $related) . "</div>");
                $form->addItem($static);
            }
        }

        if ($a_mode == "create") {
            // creation: save/cancel buttons and title
            $form->addCommandButton("saveCaseQuestion", $this->lng->txt("save"));
            $form->addCommandButton("listCaseQuestions", $this->lng->txt("cancel"));
            $form->setTitle($this->lng->txt("adn_add_case_question"));
        } else {
            $this->addFormLastChange($form, $a_mode);
            
            $default->setValue($this->question->getDefaultAnswer());
            $specific->setChecked($this->question->isGoodSpecific());

            switch ($a_mode) {
                case "edit":
                    // editing: update/cancel buttons and title
                    $form->addCommandButton("updateCaseQuestion", $this->lng->txt("save"));
                    $form->addCommandButton("updateBackupCaseQuestion", $this->lng->txt("adn_save_backup"));
                    $form->addCommandButton("listCaseQuestions", $this->lng->txt("cancel"));
                    $form->setTitle($this->lng->txt("adn_edit_case_question"));
                    break;

                case "show":
                    $form->setTitle($this->lng->txt("adn_show_details"));
                    break;

                case "backup":
                    $form->setTitle($this->lng->txt("adn_show_backup"));
                    break;
            }
        }

        $form->setFormAction($this->ctrl->getFormAction($this));

        return $form;
    }
    
    /**
     * Create new case question
     */
    protected function saveCaseQuestion()
    {
        
        $form = $this->initCaseQuestionForm((int) $_REQUEST["catalog_area"], "create");
        
        // check input
        if ($form->checkInput()) {
            // input ok: create new case question
            include_once("./Services/ADN/ED/classes/class.adnCaseQuestion.php");
            $case_question = new adnCaseQuestion();

            if ($this->setFormValues($case_question, $form)) {
                $case_question->setDefaultAnswer($form->getInput("default_answer"));
                $case_question->setGoodSpecific($form->getInput("specific"));

                // reset sub-items if obsolete
                if ($case_question->isGoodSpecific()) {
                    $case_question->setGoods($form->getInput("goods"));
                } else {
                    $case_question->setGoods(null);
                }

                if ($case_question->save()) {
                    // show success message and return to list
                    ilUtil::sendSuccess($this->lng->txt("adn_case_question_created"), true);
                    $this->ctrl->setParameter($this, "eq_id", $case_question->getId());
                    $this->ctrl->redirect($this, "listCaseQuestions");
                }
            }
        }
        
        // input not valid: show form again
        $form->setValuesByPost();
        $this->addCaseQuestion($form);
    }
    
    /**
     * Update case question
     */
    protected function updateCaseQuestion()
    {
        
        $form = $this->initCaseQuestionForm((int) $_REQUEST["catalog_area"], "edit");
        
        // check input
        if ($form->checkInput()) {
            if ($this->setFormValues($this->question, $form)) {
                $this->question->setDefaultAnswer($form->getInput("default_answer"));
                $this->question->setGoodSpecific($form->getInput("specific"));

                // reset sub-items if obsolete
                if ($this->question->isGoodSpecific()) {
                    $this->question->setGoods($form->getInput("goods"));
                } else {
                    $this->question->setGoods(null);
                }

                if ($this->question->update()) {
                    // show success message and return to list
                    ilUtil::sendSuccess($this->lng->txt("adn_case_question_updated"), true);
                    $this->ctrl->redirect($this, "listCaseQuestions");
                }
            }
        }
        
        // input not valid: show form again
        $form->setValuesByPost();
        $this->editCaseQuestion($form);
    }

    /**
     * Create backup and update question
     */
    protected function updateBackupCaseQuestion()
    {

        // only 1 backup per question
        $this->question->removeBackups();

        $backup = clone $this->question;
        $backup->setBackupOf($this->question->getId());
        $backup->setId(null);
        if ($backup->save()) {
            // clone good related answers
            include_once "Services/ADN/ED/classes/class.adnGoodRelatedAnswer.php";
            $all = adnGoodRelatedAnswer::getAllAnswers($this->question->getId());
            if ($all) {
                foreach ($all as $item) {
                    $answer = new adnGoodRelatedAnswer($item["id"]);
                    $answer->setQuestionId($backup->getId());
                    $answer->setId(null);
                    $answer->save();
                }
            }

            // clone file
            $path = $this->question->getFilePath();
            if ($this->question->getFileName(1)) {
                $source = $path . $this->question->getId() . "_1";
                $target = $path . $backup->getId() . "_1";
                copy($source, $target);
            }

            $this->updateCaseQuestion();
        } else {
            ilUtil::sendSuccess($this->lng->txt("adn_backup_fail"));
        }
    }
    
    /**
     * Confirm case question deletion
     */
    protected function confirmQuestionDeletion()
    {
        
        // check whether at least one item has been seleced
        if (!is_array($_POST["question_id"]) || count($_POST["question_id"]) == 0) {
            ilUtil::sendFailure($this->lng->txt("no_checkbox"), true);
            $this->ctrl->redirect($this, "listCaseQuestions");
        } else {
            $this->tabs->setBackTarget(
                $this->lng->txt("back"),
                $this->ctrl->getLinkTarget($this, "listCaseQuestions")
            );

            // display confirmation message
            include_once("./Services/Utilities/classes/class.ilConfirmationGUI.php");
            $cgui = new ilConfirmationGUI();
            $cgui->setFormAction($this->ctrl->getFormAction($this));
            $cgui->setHeaderText($this->lng->txt("adn_sure_delete_case_questions"));
            $cgui->setCancel($this->lng->txt("cancel"), "listCaseQuestions");
            $cgui->setConfirm($this->lng->txt("delete"), "deleteCaseQuestion");
            
            // list objects that should be deleted
            foreach ($_POST["question_id"] as $i) {
                include_once("./Services/ADN/ED/classes/class.adnCaseQuestion.php");
                $cgui->addItem("question_id[]", $i, adnCaseQuestion::lookupName($i));
            }
            
            $this->tpl->setContent($cgui->getHTML());
        }
    }
    
    /**
     * Delete case question
     */
    protected function deleteCaseQuestion()
    {
        
        include_once("./Services/ADN/ED/classes/class.adnCaseQuestion.php");
        
        if (is_array($_POST["question_id"])) {
            foreach ($_POST["question_id"] as $i) {
                $case_question = new adnCaseQuestion($i);
                $case_question->delete();
            }
        }
        ilUtil::sendSuccess($this->lng->txt("adn_case_question_deleted"), true);
        $this->ctrl->redirect($this, "listCaseQuestions");
    }
}
