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
    
    /**
     * Constructor
     */
    public function __construct()
    {
        global $ilCtrl;
        
        // save case question ID through requests
        $ilCtrl->saveParameter($this, array("eq_id"));
        
        $this->readCaseQuestion();
    }
    
    /**
     * Execute command
     */
    public function executeCommand()
    {
        global $ilCtrl, $ilTabs, $lng, $ilCtrl, $tpl;

        $tpl->setTitle($lng->txt("adn_ed") . " - " . $lng->txt("adn_ed_eqs"));

        $this->setTabs("case_questions");
        
        $next_class = $ilCtrl->getNextClass();
        
        // forward command to next gui class in control flow
        switch ($next_class) {
            case "adngoodrelatedanswergui":

                // tab handling
                
                $ilTabs->addSubTab(
                    "editcase",
                    $lng->txt("adn_edit_case_question"),
                    $ilCtrl->getLinkTarget($this, "editCaseQuestion")
                );

                $ilTabs->addSubTab(
                    "goodrelatedanswer",
                    $lng->txt("adn_good_related_answers"),
                    $ilCtrl->getLinkTargetByClass("adngoodrelatedanswergui", "listAnswers")
                );

                $ilTabs->activateSubTab("goodrelatedanswer");

                include_once("./Services/ADN/ED/classes/class.adnGoodRelatedAnswerGUI.php");
                $cqa_gui = new adnGoodRelatedAnswerGUI();
                $ilCtrl->forwardCommand($cqa_gui);
                break;

            // no next class:
            // this class is responsible to process the command
            default:
                $cmd = $ilCtrl->getCmd("listCaseQuestions");
                
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
        global $tpl, $ilToolbar, $ilCtrl, $lng;

        // add button incl. area select
        if (adnPerm::check(adnPerm::ED, adnPerm::WRITE)) {
            include_once("./Services/ADN/ED/classes/class.adnObjective.php");
            $options = adnObjective::getAllCaseCatalogAreas();
            include_once("./Services/Form/classes/class.ilSelectInputGUI.php");
            $si = new ilSelectInputGUI($lng->txt("adn_subject_area"), "catalog_area");
            $si->setOptions($options);
            $ilToolbar->addInputItem($si, true);
            $ilToolbar->setFormAction($ilCtrl->getFormAction($this, "addCaseQuestion"));
            $ilToolbar->addFormButton($lng->txt("adn_add_case_question"), "addCaseQuestion");
        }

        // table of mc quesstions
        include_once("./Services/ADN/ED/classes/class.adnExaminationQuestionTableGUI.php");
        $table = new adnExaminationQuestionTableGUI($this, "listCaseQuestions", true);

        // output table
        $tpl->setContent($table->getHTML());
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
        global $tpl;

        if (!$a_form) {
            $area = (int) $_REQUEST["catalog_area"];
            $a_form = $this->initCaseQuestionForm($area, "create");
        }
        $tpl->setContent($a_form->getHTML());
    }
    
    /**
     * Edit case question form
     *
     * @param ilPropertyFormGUI $a_form
     */
    protected function editCaseQuestion(ilPropertyFormGUI $a_form = null)
    {
        global $tpl, $ilTabs, $lng, $txt, $ilCtrl;

        $ilTabs->addSubTab(
            "editcase",
            $lng->txt("adn_edit_case_question"),
            $ilCtrl->getLinkTarget($this, "editCaseQuestion")
        );

        $ilTabs->addSubTab(
            "goodrelatedanswer",
            $lng->txt("adn_good_related_answers"),
            $ilCtrl->getLinkTargetByClass("adngoodrelatedanswergui", "listAnswers")
        );

        $ilTabs->activateSubTab("editcase");

        if (!$a_form) {
            $a_form = $this->initCaseQuestionForm($this->question->getCatalogArea(), "edit");
        }
        $tpl->setContent($a_form->getHTML());
    }

    /**
     * Show case question (read-only)
     */
    protected function showCaseQuestion()
    {
        global $tpl, $lng, $ilTabs, $ilCtrl;

        $ilTabs->setBackTarget($lng->txt("back"), $ilCtrl->getLinkTarget($this, "listCaseQuestions"));

        $form = $this->initCaseQuestionForm($this->question->getCatalogArea(), "show");
        $form = $form->convertToReadonly();
        $tpl->setContent($form->getHTML());
    }

    /**
     * Show case question backup (read-only)
     */
    protected function showCaseBackup()
    {
        global $tpl, $lng, $ilTabs, $ilCtrl;

        $ilTabs->setBackTarget($lng->txt("back"), $ilCtrl->getLinkTarget($this, "listCaseQuestions"));

        $this->question->readBackup();

        $form = $this->initCaseQuestionForm($this->question->getCatalogArea(), "backup");
        $form = $form->convertToReadonly();
        $tpl->setContent($form->getHTML());
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
        global $lng, $ilCtrl, $ilTabs;

        $ilTabs->setBackTarget(
            $lng->txt("back"),
            $ilCtrl->getLinkTarget($this, "listCaseQuestions")
        );

        $form = $this->initBaseForm($a_catalog_area, $a_mode);

        // default answer
        $default = new ilTextAreaInputGUI($lng->txt("adn_default_answer"), "default_answer");
        $default->setCols(80);
        $default->setRows(10);
        $default->setSpecialCharacters(true);
        $default->setFormId($form->getId());
        $form->addItem($default);

        // good specific
        $specific = new ilCheckboxInputGUI($lng->txt("adn_good_specific_answer"), "specific");
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
                $static = new ilNonEditableValueGUI($lng->txt("adn_good_related_answers"));
                $static->setValue("<div>" . implode("</div><div style=\"margin-top:5px\">", $related) . "</div>");
                $form->addItem($static);
            }
        }

        if ($a_mode == "create") {
            // creation: save/cancel buttons and title
            $form->addCommandButton("saveCaseQuestion", $lng->txt("save"));
            $form->addCommandButton("listCaseQuestions", $lng->txt("cancel"));
            $form->setTitle($lng->txt("adn_add_case_question"));
        } else {
            $this->addFormLastChange($form, $a_mode);
            
            $default->setValue($this->question->getDefaultAnswer());
            $specific->setChecked($this->question->isGoodSpecific());

            switch ($a_mode) {
                case "edit":
                    // editing: update/cancel buttons and title
                    $form->addCommandButton("updateCaseQuestion", $lng->txt("save"));
                    $form->addCommandButton("updateBackupCaseQuestion", $lng->txt("adn_save_backup"));
                    $form->addCommandButton("listCaseQuestions", $lng->txt("cancel"));
                    $form->setTitle($lng->txt("adn_edit_case_question"));
                    break;

                case "show":
                    $form->setTitle($lng->txt("adn_show_details"));
                    break;

                case "backup":
                    $form->setTitle($lng->txt("adn_show_backup"));
                    break;
            }
        }

        $form->setFormAction($ilCtrl->getFormAction($this));

        return $form;
    }
    
    /**
     * Create new case question
     */
    protected function saveCaseQuestion()
    {
        global $tpl, $lng, $ilCtrl;
        
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
                    ilUtil::sendSuccess($lng->txt("adn_case_question_created"), true);
                    $ilCtrl->setParameter($this, "eq_id", $case_question->getId());
                    $ilCtrl->redirect($this, "listCaseQuestions");
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
        global $lng, $ilCtrl, $tpl;
        
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
                    ilUtil::sendSuccess($lng->txt("adn_case_question_updated"), true);
                    $ilCtrl->redirect($this, "listCaseQuestions");
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
        global $lng;

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
            ilUtil::sendSuccess($lng->txt("adn_backup_fail"));
        }
    }
    
    /**
     * Confirm case question deletion
     */
    protected function confirmQuestionDeletion()
    {
        global $ilCtrl, $tpl, $lng, $ilTabs;
        
        // check whether at least one item has been seleced
        if (!is_array($_POST["question_id"]) || count($_POST["question_id"]) == 0) {
            ilUtil::sendFailure($lng->txt("no_checkbox"), true);
            $ilCtrl->redirect($this, "listCaseQuestions");
        } else {
            $ilTabs->setBackTarget(
                $lng->txt("back"),
                $ilCtrl->getLinkTarget($this, "listCaseQuestions")
            );

            // display confirmation message
            include_once("./Services/Utilities/classes/class.ilConfirmationGUI.php");
            $cgui = new ilConfirmationGUI();
            $cgui->setFormAction($ilCtrl->getFormAction($this));
            $cgui->setHeaderText($lng->txt("adn_sure_delete_case_questions"));
            $cgui->setCancel($lng->txt("cancel"), "listCaseQuestions");
            $cgui->setConfirm($lng->txt("delete"), "deleteCaseQuestion");
            
            // list objects that should be deleted
            foreach ($_POST["question_id"] as $i) {
                include_once("./Services/ADN/ED/classes/class.adnCaseQuestion.php");
                $cgui->addItem("question_id[]", $i, adnCaseQuestion::lookupName($i));
            }
            
            $tpl->setContent($cgui->getHTML());
        }
    }
    
    /**
     * Delete case question
     */
    protected function deleteCaseQuestion()
    {
        global $ilCtrl, $lng;
        
        include_once("./Services/ADN/ED/classes/class.adnCaseQuestion.php");
        
        if (is_array($_POST["question_id"])) {
            foreach ($_POST["question_id"] as $i) {
                $case_question = new adnCaseQuestion($i);
                $case_question->delete();
            }
        }
        ilUtil::sendSuccess($lng->txt("adn_case_question_deleted"), true);
        $ilCtrl->redirect($this, "listCaseQuestions");
    }
}
