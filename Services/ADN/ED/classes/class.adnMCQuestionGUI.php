<?php
/* Copyright (c) 2010 Leifos, GPL, see docs/LICENSE */

include_once("./Services/ADN/ED/classes/class.adnExaminationQuestionGUI.php");

/**
 * ADN mc question GUI class
 *
 * MC question forms and persistence
 *
 * @author Alex Killing <killing@leifos.de>
 * @version $Id: class.adnMCQuestionGUI.php 27883 2011-02-27 19:30:41Z akill $
 *
 * @ingroup ServicesADN
 */
class adnMCQuestionGUI extends adnExaminationQuestionGUI
{
    // current mc question object
    protected ?adnMCQuestion $question = null;
    protected ilToolbarGUI $toolbar;
    /**
     * Constructor
     */
    public function __construct()
    {
        global $DIC;
        $this->toolbar = $DIC->toolbar();
        parent::__construct();
        // save mc question ID through requests
        $this->ctrl->saveParameter($this, array("eq_id"));
    
        $this->readMCQuestion();
    }

    /**
     * Execute command
     */
    public function executeCommand()
    {

        $this->tpl->setTitle($this->lng->txt("adn_ed") . " - " . $this->lng->txt("adn_ed_eqs"));

        $this->setTabs("mc_questions");

        $next_class = $this->ctrl->getNextClass();

        // forward command to next gui class in control flow
        switch ($next_class) {
            // no next class:
            // this class is responsible to process the command
            default:
                $cmd = $this->ctrl->getCmd("listMCQuestions");

                switch ($cmd) {
                    // commands that need read permission
                    case "listMCQuestions":
                    case "applyFilter":
                    case "resetFilter":
                    case "showImage":
                    case "showMCQuestion":
                    case "showMCBackup":
                        if (adnPerm::check(adnPerm::ED, adnPerm::READ)) {
                            $this->$cmd();
                        }
                        break;

                    // commands that need write permission
                    case "addMCQuestion":
                    case "saveMCQuestion":
                    case "editMCQuestion":
                    case "updateMCQuestion":
                    case "updateBackupMCQuestion":
                    case "confirmQuestionDeletion":
                    case "deleteMCQuestion":
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
     * Read mc question
     */
    protected function readMCQuestion()
    {
        if ((int) $_GET["eq_id"] > 0) {
            include_once("./Services/ADN/ED/classes/class.adnMCQuestion.php");
            $this->question = new adnMCQuestion((int) $_GET["eq_id"]);
        }
    }

    /**
     * List all mc questions
     */
    protected function listMCQuestions()
    {

        // add button incl. area select
        if (adnPerm::check(adnPerm::ED, adnPerm::WRITE)) {
            include_once("./Services/ADN/ED/classes/class.adnObjective.php");
            $options = adnObjective::getAllMCCatalogAreas();
            include_once("./Services/Form/classes/class.ilSelectInputGUI.php");
            $si = new ilSelectInputGUI($this->lng->txt("adn_catalog_area"), "catalog_area");
            $si->setOptions($options);
            $this->toolbar->addInputItem($si, true);
            $this->toolbar->setFormAction($this->ctrl->getFormAction($this, "addMCQuestion"));
            $this->toolbar->addFormButton($this->lng->txt("adn_add_mc_question"), "addMCQuestion");
        }

        // table of mc questions
        include_once("./Services/ADN/ED/classes/class.adnExaminationQuestionTableGUI.php");
        $table = new adnExaminationQuestionTableGUI($this, "listMCQuestions");

        // output table
        $this->tpl->setContent($table->getHTML());
    }

    /**
     * Apply filter settings (from table gui)
     */
    protected function applyFilter()
    {
        include_once("./Services/ADN/ED/classes/class.adnExaminationQuestionTableGUI.php");
        $table = new adnExaminationQuestionTableGUI($this, "listMCQuestions");
        $table->resetOffset();
        $table->writeFilterToSession();

        $this->listMCQuestions();
    }

    /**
     * Reset filter settings (from table gui)
     */
    protected function resetFilter()
    {
        include_once("./Services/ADN/ED/classes/class.adnExaminationQuestionTableGUI.php");
        $table = new adnExaminationQuestionTableGUI($this, "listMCQuestions");
        $table->resetOffset();
        $table->resetFilter();

        $this->listMCQuestions();
    }

    /**
     * Add new mc question form
     *
     * @param ilPropertyFormGUI $a_form
     */
    protected function addMCQuestion(ilPropertyFormGUI $a_form = null)
    {

        if (!$a_form) {
            $area = (int) $_REQUEST["catalog_area"];
            $a_form = $this->initMCQuestionForm($area, "create");
        }
        $this->tpl->setContent($a_form->getHTML());
    }

    /**
     * Edit mc question form
     *
     * @param ilPropertyFormGUI $a_form
     */
    protected function editMCQuestion(ilPropertyFormGUI $a_form = null)
    {

        if (!$a_form) {
            $a_form = $this->initMCQuestionForm($this->question->getCatalogArea(), "edit");
        }
        $this->tpl->setContent($a_form->getHTML());
    }

    /**
     * Show mc question (read-only)
     */
    protected function showMCQuestion()
    {

        $this->tabs->setBackTarget($this->lng->txt("back"), $this->ctrl->getLinkTarget($this, "listMCQuestions"));

        $form = $this->initMCQuestionForm($this->question->getCatalogArea(), "show");
        $form = $form->convertToReadonly();
        $this->tpl->setContent($form->getHTML());
    }

    /**
     * Show mc question backupn (read-only)
     */
    protected function showMCBackup()
    {

        $this->tabs->setBackTarget($this->lng->txt("back"), $this->ctrl->getLinkTarget($this, "listMCQuestions"));

        $this->question->readBackup();

        $form = $this->initMCQuestionForm($this->question->getCatalogArea(), "backup");
        $form = $form->convertToReadonly();
        $this->tpl->setContent($form->getHTML());
    }

    /**
     * Init mc question form.
     *
     * @param int $a_catalog_area
     * @param string $a_mode form mode ("create" | "edit" | "show" | "backup")
     * @return ilPropertyFormGUI
     */
    protected function initMCQuestionForm($a_catalog_area, $a_mode = "edit")
    {

        $this->tabs->setBackTarget(
            $this->lng->txt("back"),
            $this->ctrl->getLinkTarget($this, "listMCQuestions")
        );

        $form = $this->initBaseForm($a_catalog_area, $a_mode);

        // correct answer
        $options = array(
            "a" => "A",
            "b" => "B",
            "c" => "C",
            "d" => "D"
        );
        $correct = new ilRadioGroupInputGUI($this->lng->txt("adn_correct_answer"), "correct_answer");
        foreach ($options as $option => $caption) {
            $correct->addOption(new ilRadioOption($caption, $option));
        }
        $correct->setRequired(true);
        $form->addItem($correct);

        // answers
        include_once("./Services/Form/classes/class.ilFormSectionHeaderGUI.php");
        $answer = array();
        foreach (array("A", "B", "C", "D") as $n) {
            include_once("./Services/Form/classes/class.ilFormSectionHeaderGUI.php");
            $sh = new ilFormSectionHeaderGUI();
            $sh->setTitle($this->lng->txt("adn_answer") . " " . $n);
            $form->addItem($sh);

            // answer
            $answer[$n]["text"] = new ilTextAreaInputGUI(
                $this->lng->txt("adn_answer_text"),
                "answer_text_" . $n
            );
            $answer[$n]["text"]->setCols(80);
            $answer[$n]["text"]->setRows(5);
            $answer[$n]["text"]->setRequired(true);
            $answer[$n]["text"]->setSpecialCharacters(true);
            $answer[$n]["text"]->setFormId($form->getId());
            $form->addItem($answer[$n]["text"]);

            // answer image
            $answer[$n]["image"] = new ilImageFileInputGUI(
                $this->lng->txt("adn_image_for_answer"),
                "answer_image_" . $n
            );
            $form->addItem($answer[$n]["image"]);
        }

        if ($a_mode == "create") {
            // creation: save/cancel buttons and title
            $form->addCommandButton("saveMCQuestion", $this->lng->txt("save"));
            $form->addCommandButton("listMCQuestions", $this->lng->txt("cancel"));
            $form->setTitle($this->lng->txt("adn_add_mc_question"));
        } else {
            $this->addFormLastChange($form, $a_mode);

            $correct->setValue($this->question->getCorrectAnswer());

            foreach (array("A", "B", "C", "D") as $idx => $n) {
                $answer_values = $this->question->{"getAnswer" . $n}();
                $answer[$n]["text"]->setValue($answer_values["text"]);

                if ($answer_values["image"]) {
                    $file = $this->question->getFilePath() . $this->question->getId() . "_" . ($idx + 2);
                    if (file_exists($file)) {
                        $this->ctrl->setParameter($this, "img", ($idx + 2));
                        $answer[$n]["image"]->setImage($this->ctrl->getLinkTarget($this, "showImage"));
                        $this->ctrl->setParameter($this, "img", "");
                        $answer[$n]["image"]->setAlt($answer_values["image"]);
                    }
                }
            }

            switch ($a_mode) {
                case "edit":
                    // editing: update/cancel buttons and title
                    $form->addCommandButton("updateMCQuestion", $this->lng->txt("save"));
                    $form->addCommandButton("updateBackupMCQuestion", $this->lng->txt("adn_save_backup"));
                    $form->addCommandButton("listMCQuestions", $this->lng->txt("cancel"));
                    $form->setTitle($this->lng->txt("adn_edit_mc_question"));
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
     * Create new mc question
     */
    protected function saveMCQuestion()
    {

        $form = $this->initMCQuestionForm((int) $_REQUEST["catalog_area"], "create");

        // check input
        if ($form->checkInput()) {
            // input ok: create new mc question
            include_once("./Services/ADN/ED/classes/class.adnMCQuestion.php");
            $question = new adnMCQuestion();

            if ($this->setFormValues($question, $form)) {
                foreach (array("A", "B", "C", "D") as $idx => $n) {
                    $file = $form->getInput("answer_image_" . $n);
                    $question->importFile($file["tmp_name"], $file["name"], ($idx + 2));
                }

                $question->setCorrectAnswer($form->getInput("correct_answer"));
                $question->setAnswerA($form->getInput("answer_text_A"));
                $question->setAnswerB($form->getInput("answer_text_B"));
                $question->setAnswerC($form->getInput("answer_text_C"));
                $question->setAnswerD($form->getInput("answer_text_D"));

                if ($question->save()) {
                    // show success message and return to list
                    ilUtil::sendSuccess($this->lng->txt("adn_mc_question_created"), true);
                    $this->ctrl->setParameter($this, "eq_id", $question->getId());
                    $this->ctrl->redirect($this, "listMCQuestions");
                }
            }
        }

        // input not valid: show form again
        $form->setValuesByPost();
        $this->addMCQuestion($form);
    }

    /**
     * Update mc question
     */
    protected function updateMCQuestion()
    {

        $form = $this->initMCQuestionForm((int) $_REQUEST["catalog_area"], "edit");

        // check input
        if ($form->checkInput()) {
            if ($this->setFormValues($this->question, $form)) {
                $id = $this->question->getId();
                foreach (array("A", "B", "C", "D") as $idx => $n) {
                    if (!$form->getInput("answer_image_" . $n . "_delete")) {
                        $file = $form->getInput("answer_image_" . $n);
                        $this->question->importFile($file["tmp_name"], $file["name"], ($idx + 2));
                    } else {
                        $this->question->removeFile($id . "_" . ($idx + 2));
                        $this->question->setFileName("", ($idx + 2));
                    }
                }
                
                $this->question->setCorrectAnswer($form->getInput("correct_answer"));
                $this->question->setAnswerA($form->getInput("answer_text_A"));
                $this->question->setAnswerB($form->getInput("answer_text_B"));
                $this->question->setAnswerC($form->getInput("answer_text_C"));
                $this->question->setAnswerD($form->getInput("answer_text_D"));

                if ($this->question->update()) {
                    // show success message and return to list
                    ilUtil::sendSuccess($this->lng->txt("adn_mc_question_updated"), true);
                    $this->ctrl->redirect($this, "listMCQuestions");
                }
            }
        }

        // input not valid: show form again
        $form->setValuesByPost();
        $this->editMCQuestion($form);
    }

    /**
     * Create backup and update question
     */
    protected function updateBackupMCQuestion()
    {

        // only 1 backup per questions
        $this->question->removeBackups();
        
        $backup = clone $this->question;
        $backup->setBackupOf($backup->getId());
        $backup->setId(null);

        if ($backup->save()) {
            // clone files
            $path = $this->question->getFilePath();
            for ($loop = 1; $loop < 6; $loop++) {
                if ($this->question->getFileName($loop)) {
                    $source = $path . $this->question->getId() . "_" . $loop;
                    $target = $path . $backup->getId() . "_" . $loop;
                    copy($source, $target);
                }
            }

            $this->updateMCQuestion();
        } else {
            ilUtil::sendSuccess($this->lng->txt("adn_backup_fail"));
        }
    }

    /**
     * Confirm mc question deletion
     */
    protected function confirmQuestionDeletion()
    {

        // check whether at least one item has been seleced
        if (!is_array($_POST["question_id"]) || count($_POST["question_id"]) == 0) {
            ilUtil::sendFailure($this->lng->txt("no_checkbox"), true);
            $this->ctrl->redirect($this, "listMCQuestions");
        } else {
            $this->tabs->setBackTarget(
                $this->lng->txt("back"),
                $this->ctrl->getLinkTarget($this, "listMCQuestions")
            );

            // display confirmation message
            include_once("./Services/Utilities/classes/class.ilConfirmationGUI.php");
            $cgui = new ilConfirmationGUI();
            $cgui->setFormAction($this->ctrl->getFormAction($this));
            $cgui->setHeaderText($this->lng->txt("adn_sure_delete_mc_questions"));
            $cgui->setCancel($this->lng->txt("cancel"), "listMCQuestions");
            $cgui->setConfirm($this->lng->txt("delete"), "deleteMCQuestion");

            // list objects that should be deleted
            foreach ($_POST["question_id"] as $i) {
                include_once("./Services/ADN/ED/classes/class.adnMCQuestion.php");
                $cgui->addItem("question_id[]", $i, adnMCQuestion::lookupName($i));
            }

            $this->tpl->setContent($cgui->getHTML());
        }
    }

    /**
     * Delete mc question
     */
    protected function deleteMCQuestion()
    {

        include_once("./Services/ADN/ED/classes/class.adnMCQuestion.php");

        if (is_array($_POST["question_id"])) {
            foreach ($_POST["question_id"] as $i) {
                $question = new adnMCQuestion($i);
                $question->delete();
            }
        }
        ilUtil::sendSuccess($this->lng->txt("adn_mc_question_deleted"), true);
        $this->ctrl->redirect($this, "listMCQuestions");
    }
}
