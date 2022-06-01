<?php
/* Copyright (c) 2010 Leifos, GPL, see docs/LICENSE */

include_once("./Services/Table/classes/class.ilTable2GUI.php");
include_once("./Services/ADN/ED/classes/class.adnMCQuestion.php");
include_once("./Services/ADN/ED/classes/class.adnExaminationQuestionGUI.php");

/**
 * ADN test question list table GUI class. This class offers an overview of all questions
 * of an online test. The user can click on each question to navigate to it.
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id: class.adnTestQuestionListTableGUI.php 28233 2011-03-24 16:02:48Z akill $
 *
 * @ingroup ServicesADN
 */
class adnTestQuestionListTableGUI extends ilTable2GUI
{
    protected int $cand_sheet_id;
    /**
     * @var int[]
     */
    protected array $questions;
    /**
     * Constructor
     *
     * @param object $a_parent_obj parent gui object
     * @param string $a_parent_cmd parent default command
     * @param array $a_questions questions
     * @param int $a_cand_sheet_id sheet id
     */
    public function __construct($a_parent_obj, $a_parent_cmd, $a_questions, $a_cand_sheet_id)
    {
        
        $this->cand_sheet_id = (int) $a_cand_sheet_id;
        $this->questions = $a_questions;
        
        parent::__construct($a_parent_obj, $a_parent_cmd);

        $cnt = 1;
        $this->setData($this->questions);
        $this->setTitle($this->lng->txt("adn_questions"));
        
        $this->addColumn($this->lng->txt("adn_nr"));
        $this->addColumn($this->lng->txt("adn_question"));
        $this->addColumn($this->lng->txt("adn_answered"));
        $this->addColumn($this->lng->txt("actions"));
        
        $this->setDefaultOrderField("title");
        $this->setDefaultOrderDirection("asc");
        $this->setLimit(100);

        $this->setFormAction($this->ctrl->getFormAction($a_parent_obj));
        $this->setRowTemplate("tpl.test_questions_row.html", "Services/ADN/EC");
    }
    
    /**
     * Fill table row
     *
     * @param array $a_set data array
     */
    protected function fillRow($a_set)
    {

        $question = new adnMCQuestion($a_set["q_id"]);

        // actions...
        $this->ctrl->setParameter($this->parent_obj, "q_id", $a_set["q_id"]);
        
        // ...show question
        $this->tpl->setCurrentBlock("action");
        $this->tpl->setVariable(
            "TXT_CMD",
            $this->lng->txt("adn_show_question")
        );
        $this->tpl->setVariable(
            "HREF_CMD",
            $this->ctrl->getLinkTarget($this->parent_obj, "jumpToQuestion")
        );
        $this->tpl->parseCurrentBlock();
        
        $this->ctrl->setParameter($this->parent_obj, "q_id", "");

        // check if answer is given
        if (adnTest::lookupAnswer($this->cand_sheet_id, $a_set["q_id"]) > 0) {
            $this->tpl->setVariable("VAL_ANSWERED", "X");
        } else {
            $this->tpl->setVariable("VAL_ANSWERED", "");
        }
        
        // properties
        $this->tpl->setVariable("VAL_NR", $a_set["nr"]);
        $this->tpl->setVariable(
            "VAL_QUESTION",
            adnExaminationQuestionGUI::replaceBBCode($question->getQuestion())
        );
    }
}
