<?php
/* Copyright (c) 2010 Leifos, GPL, see docs/LICENSE */

/**
 * ADN good related answer GUI class
 *
 * Good related answers forms and persistence
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @version $Id: class.adnGoodRelatedAnswerGUI.php 27873 2011-02-25 16:21:55Z jluetzen $
 *
 * @ilCtrl_Calls adnGoodRelatedAnswerGUI:
 *
 * @ingroup ServicesADN
 */
class adnGoodRelatedAnswerGUI
{
    // current case question id
    protected int $question_id = 0;

    // current answer object
    protected ?adnGoodRelatedAnswer $answer = null;

    // gas/chem
    protected bool $show_butan_or_empty = false;
    
    /**
     * Constructor
     */
    public function __construct()
    {
        global $ilCtrl;

        $this->question_id = (int) $_REQUEST["eq_id"];

        // depending on question/catalog area type
        include_once "Services/ADN/ED/classes/class.adnCatalogNumbering.php";
        $question = new adnCaseQuestion($this->question_id);
        if (adnCatalogNumbering::isGasArea($question->getCatalogArea())) {
            $this->show_butan_or_empty = true;
        }

        // save question and asnwer ID through requests
        $ilCtrl->saveParameter($this, array("eq_id"));
        $ilCtrl->saveParameter($this, array("cqa_id"));
        
        $this->readAnswer();
    }
    
    /**
     * Execute command
     */
    public function executeCommand()
    {
        global $ilCtrl;
        
        $next_class = $ilCtrl->getNextClass();
        
        // forward command to next gui class in control flow
        switch ($next_class) {
            // no next class:
            // this class is responsible to process the command
            default:
                $cmd = $ilCtrl->getCmd("listAnswers");
                
                switch ($cmd) {
                    // commands that need read permission
                    case "listAnswers":
                        if (adnPerm::check(adnPerm::ED, adnPerm::READ)) {
                            $this->$cmd();
                        }
                        break;
                    
                    // commands that need write permission
                    case "addAnswer":
                    case "saveAnswer":
                    case "editAnswer":
                    case "updateAnswer":
                    case "confirmAnswersDeletion":
                    case "deleteAnswers":
                        if (adnPerm::check(adnPerm::ED, adnPerm::WRITE)) {
                            $this->$cmd();
                        }
                        break;
                    
                }
                break;
        }
    }
    
    /**
     * Read answer
     */
    protected function readAnswer()
    {
        if ((int) $_GET["cqa_id"] > 0) {
            include_once("./Services/ADN/ED/classes/class.adnGoodRelatedAnswer.php");
            $this->answer = new adnGoodRelatedAnswer((int) $_GET["cqa_id"]);
        }
    }
    
    /**
     * List answers
     */
    public function listAnswers()
    {
        global $tpl, $lng, $ilCtrl, $ilToolbar;

        if (adnPerm::check(adnPerm::ED, adnPerm::WRITE)) {
            $ilToolbar->addButton(
                $lng->txt("adn_add_good_related_answer"),
                $ilCtrl->getLinkTarget($this, "addAnswer")
            );
        }

        include_once("./Services/ADN/ED/classes/class.adnGoodRelatedAnswerTableGUI.php");
        $table = new adnGoodRelatedAnswerTableGUI($this, "listAnswers", $this->question_id);

        $tpl->setContent($table->getHTML());
    }
    
    /**
     * Add new answer form
     *
     * @param ilPropertyFormGUI $a_form
     */
    protected function addAnswer(ilPropertyFormGUI $a_form = null)
    {
        global $tpl, $ilTabs, $ilCtrl, $lng;

        $ilTabs->setBackTarget($lng->txt("back"), $ilCtrl->getLinkTarget($this, "listAnswers"));

        if (!$a_form) {
            $a_form = $this->initAnswerForm("create");
        }
        $tpl->setContent($a_form->getHTML());
    }
    
    /**
     * Edit answer form
     *
     * @param ilPropertyFormGUI $a_form
     */
    protected function editAnswer(ilPropertyFormGUI $a_form = null)
    {
        global $tpl, $ilTabs, $ilCtrl, $lng;

        $ilTabs->setBackTarget($lng->txt("back"), $ilCtrl->getLinkTarget($this, "listAnswers"));

        if (!$a_form) {
            $a_form = $this->initAnswerForm("edit");
        }
        $tpl->setContent($a_form->getHTML());
    }
    
    /**
     * Init answer form
     *
     * @param string $a_mode form mode ("create" | "edit")
     * @return ilPropertyFormGUI
     */
    protected function initAnswerForm($a_mode = "edit")
    {
        global $lng, $ilCtrl;
        
        // get form object and add input fields
        include_once("Services/Form/classes/class.ilPropertyFormGUI.php");
        $form = new ilPropertyFormGUI();
        
        // name
        $answer = new ilTextAreaInputGUI($lng->txt("adn_answer"), "answer");
        $answer->setCols(80);
        $answer->setRows(20);
        $answer->setRequired(true);
        $answer->setSpecialCharacters(true, true);
        $answer->setFormId($form->getId());
        $form->addItem($answer);

        // depending on question type
        if ($this->show_butan_or_empty) {
            // butan
            include_once "Services/ADN/ED/classes/class.adnGoodRelatedAnswer.php";
            $butan = new ilSelectInputGUI($lng->txt("adn_butan_or_empty"), "empty");
            $options = array(adnGoodRelatedAnswer::TYPE_EMPTY => $lng->txt("adn_empty"),
                adnGoodRelatedAnswer::TYPE_BUTAN => $lng->txt("adn_butan"),
                adnGoodRelatedAnswer::TYPE_BUTAN_OR_EMPTY => $lng->txt("adn_butan_or_empty"));
            $butan->setOptions($options);
            $form->addItem($butan);
        }

        // goods (foreign key)
        include_once "Services/ADN/ED/classes/class.adnGoodInTransit.php";
        include_once "Services/ADN/ED/classes/class.adnCatalogNumbering.php";
        include_once "Services/ADN/ED/classes/class.adnCaseQuestion.php";
        $question = new adnCaseQuestion($this->question_id);
        $catalog_area = $question->getCatalogArea();
        $goods = array();
        if ($a_mode != "create") {
            $goods = $this->answer->getGoods();
        }
        if (adnCatalogNumbering::isGasArea($catalog_area)) {
            $goods = adnGoodInTransit::getGoodsSelect(adnGoodInTransit::TYPE_GAS, null, $goods);
        } else {
            $goods = adnGoodInTransit::getGoodsSelect(adnGoodInTransit::TYPE_CHEMICALS, null, $goods);
        }
        if ($goods) {
            $specific = new ilCheckboxGroupInputGUI($lng->txt("adn_goods_in_transit"), "goods");
            $specific->setRequired(true);
            $form->addItem($specific);
            foreach ($goods as $good_id => $good_name) {
                $box = new ilCheckboxOption($good_name, $good_id);
                $specific->addOption($box);
            }
        }

        if ($a_mode == "create") {
            // creation: save/cancel buttons and title
            $form->addCommandButton("saveAnswer", $lng->txt("save"));
            $form->addCommandButton("listAnswers", $lng->txt("cancel"));
            $form->setTitle($lng->txt("adn_add_good_related_answer"));
        } else {
            $answer->setValue($this->answer->getAnswer());
            if ($this->show_butan_or_empty) {
                $butan->setValue($this->answer->GetButanOrEmpty());
            }
            if ($goods) {
                $specific->setValue($this->answer->getGoods());
            }
            
            // editing: update/cancel buttons and title
            $form->addCommandButton("updateAnswer", $lng->txt("save"));
            $form->addCommandButton("listAnswers", $lng->txt("cancel"));
            $form->setTitle($lng->txt("adn_good_related_answer"));
        }
        
        $form->setFormAction($ilCtrl->getFormAction($this));

        return $form;
    }
    
    /**
     * Create new answer
     */
    protected function saveAnswer()
    {
        global $tpl, $lng, $ilCtrl;
        
        $form = $this->initAnswerForm("create");
        
        // check input
        if ($form->checkInput()) {
            // input ok: create new area
            include_once("./Services/ADN/ED/classes/class.adnGoodRelatedAnswer.php");
            $answer = new adnGoodRelatedAnswer();
            $answer->setQuestionId($this->question_id);
            $answer->setAnswer($form->getInput("answer"));
            $answer->setGoods($form->getInput("goods"));

            // depending on question type
            if ($this->show_butan_or_empty) {
                $answer->setButanOrEmpty($form->getInput("empty"));
            }
            
            if ($answer->save()) {
                // show success message and return to list
                ilUtil::sendSuccess($lng->txt("adn_good_related_answer_created"), true);
                $ilCtrl->redirect($this, "listAnswers");
            }
        }

        // input not valid: show form again
        $form->setValuesByPost();
        $this->addAnswer($form);
    }
    
    /**
     * Update answer
     */
    protected function updateAnswer()
    {
        global $lng, $ilCtrl, $tpl;
        
        $form = $this->initAnswerForm("edit");
        
        // check input
        if ($form->checkInput()) {
            // perform update
            $this->answer->setAnswer($form->getInput("answer"));
            $this->answer->setGoods($form->getInput("goods"));

            // depending on question type
            if ($this->show_butan_or_empty) {
                $this->answer->setButanOrEmpty($form->getInput("empty"));
            }
            
            if ($this->answer->update()) {
                // show success message and return to list
                ilUtil::sendSuccess($lng->txt("adn_good_related_answer_updated"), true);
                $ilCtrl->redirect($this, "listAnswers");
            }
        }
        
        // input not valid: show form again
        $form->setValuesByPost();
        $this->editAnswer($form);
    }
    
    /**
     * Confirm answers deletion
     */
    protected function confirmAnswersDeletion()
    {
        global $ilCtrl, $tpl, $lng;
        
        // check whether at least one item has been seleced
        if (!is_array($_POST["answer_id"]) || count($_POST["answer_id"]) == 0) {
            ilUtil::sendFailure($lng->txt("no_checkbox"), true);
            $ilCtrl->redirect($this, "listAnswers");
        } else {
            // display confirmation message
            include_once("./Services/Utilities/classes/class.ilConfirmationGUI.php");
            $cgui = new ilConfirmationGUI();
            $cgui->setFormAction($ilCtrl->getFormAction($this));
            $cgui->setHeaderText($lng->txt("adn_sure_delete_good_related_answers"));
            $cgui->setCancel($lng->txt("cancel"), "listAnswers");
            $cgui->setConfirm($lng->txt("delete"), "deleteAnswers");

            // list objects that should be deleted
            include_once("./Services/ADN/ED/classes/class.adnGoodRelatedAnswer.php");
            foreach ($_POST["answer_id"] as $i) {
                $cgui->addItem("answer_id[]", $i, adnGoodRelatedAnswer::lookupName($i));
            }
            
            $tpl->setContent($cgui->getHTML());
        }
    }
    
    /**
     * Delete answers
     */
    protected function deleteAnswers()
    {
        global $ilCtrl, $lng;
        
        include_once("./Services/ADN/ED/classes/class.adnGoodRelatedAnswer.php");
        
        if (is_array($_POST["answer_id"])) {
            foreach ($_POST["answer_id"] as $i) {
                $answer = new adnGoodRelatedAnswer($i);
                $answer->delete();
            }
        }
        ilUtil::sendSuccess($lng->txt("adn_good_related_answer_deleted"), true);
        ilUtil::sendSuccess($lng->txt("adn_good_related_answer_deleted"), true);
        $ilCtrl->redirect($this, "listAnswers");
    }
}
