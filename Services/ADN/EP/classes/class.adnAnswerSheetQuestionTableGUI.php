<?php
/* Copyright (c) 2010 Leifos, GPL, see docs/LICENSE */

include_once("./Services/Table/classes/class.ilTable2GUI.php");
include_once("./Services/ADN/ED/classes/class.adnExaminationQuestionGUI.php");

/**
 * Question table GUI class (answer sheet context)
 *
 * List all questions (for parent [sub-]objective) which may be assigned to sheet
 * If subjected objective target [sub-]objectives may be selected
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.de>
 * @version $Id: class.adnAnswerSheetQuestionTableGUI.php 28233 2011-03-24 16:02:48Z akill $
 *
 * @ingroup ServicesADN
 */
class adnAnswerSheetQuestionTableGUI extends ilTable2GUI
{
    // [array] captions for foreign keys
    protected $map;

    // [adnAnswerSheet] answer sheet
    protected $sheet;

    // [int] objective
    protected $objective_id;

    // [int] subobjective
    protected $subobjective_id;

    // [array] questions (objective is subjected)
    protected $question_ids;
    
    /**
     * Constructor
     *
     * @param object $a_parent_obj parent gui object
     * @param string $a_parent_cmd parent default command
     * @param adnAnswerSheet $a_sheet answer sheet
     * @param int $a_obj_id objective id
     * @param int $a_sobj_id subobjective id
     * @param array $a_question_ids questions (objective is subjected)
     */
    public function __construct(
        $a_parent_obj,
        $a_parent_cmd,
        adnAnswerSheet $a_sheet,
        $a_obj_id,
        $a_sobj_id,
        array $a_question_ids = null
    )
    {
        global $ilCtrl, $lng;

        parent::__construct($a_parent_obj, $a_parent_cmd);

        $this->sheet = $a_sheet;
        $this->objective_id = (int) $a_obj_id;
        $this->subobjective_id = (int) $a_sobj_id;
        $this->question_ids = $a_question_ids;

        $this->setId("adn_tbl_pcd");

        $title = $a_parent_obj->getFullSheetTitle();
        $this->setTitle($title["title"]);
        if ($title["description"]) {
            $this->setDescription($title["description"]);
        }
        
        if (adnPerm::check(adnPerm::EP, adnPerm::WRITE)) {
            if (!$this->question_ids) {
                $this->addMultiCommand("saveAddQuestions", $lng->txt("adn_add_questions"));
            } else {
                $this->addMultiCommand("saveAddQuestions", $lng->txt("adn_save_subjected_questions"));

                include_once "Services/ADN/ED/classes/class.adnObjective.php";
                $obj = new adnObjective($this->objective_id);
                $this->objective_options = adnObjective::getObjectivesSelect(
                    $obj->getCatalogArea(),
                    $obj->getType(),
                    true
                );
            }
            $this->addColumn("", "", 1);
        }

        $this->addColumn($this->lng->txt("adn_number"), "number");
        $this->addColumn($this->lng->txt("adn_question"), "text");
        $this->initFilter();
        
        $this->setDefaultOrderField("number");
        $this->setDefaultOrderDirection("asc");

        $this->setFormAction($ilCtrl->getFormAction($a_parent_obj));
        $this->setRowTemplate("tpl.question_row.html", "Services/ADN/EP");

        $this->importData();
    }

    /**
     * Import data from DB
     */
    protected function importData()
    {
        $questions = $this->sheet->getQuestions();

        include_once "Services/ADN/ED/classes/class.adnExaminationQuestion.php";
        if ($this->subobjective_id) {
            $ids = adnExaminationQuestion::getBySubobjective($this->subobjective_id, null, true);
        } else {
            $ids = adnExaminationQuestion::getByObjective($this->objective_id, null, true);
        }

        // list all available / valid questions
        
        include_once "Services/ADN/ED/classes/class.adnObjective.php";
        include_once "Services/ADN/ED/classes/class.adnSubobjective.php";
        include_once "Services/ADN/ED/classes/class.adnMCQuestion.php";
        include_once "Services/ADN/ED/classes/class.adnCaseQuestion.php";
        include_once "Services/ADN/ED/classes/class.adnGoodRelatedAnswer.php";
        $data = array();
        foreach ($ids as $id) {
            if (!in_array($id, $questions) && (!$this->question_ids ||
                in_array($id, $this->question_ids))) {
                $valid = true;
                if ($this->sheet->getType() == adnAnswerSheet::TYPE_MC) {
                    $question = new adnMCQuestion($id);
                } else {
                    $question = new adnCaseQuestion($id);

                    // check specific question goods against answer sheet good
                    if ($question->isGoodSpecific()) {
                        $goods = $question->getGoods();
                        if (!in_array($this->sheet->getNewGood(), $goods)) {
                            $valid = false;
                        }
                    }

                    // check if there is matching answer for current sheet
                    if ($valid) {
                        $correct_answer = adnGoodRelatedAnswer::getAnswerForSheet(
                            $this->sheet,
                            $question
                        );
                        if (!$correct_answer) {
                            $valid = false;
                        }
                    }
                }

                if ($valid) {
                    $data[] = array("id" => $id,
                        "number" => $question->buildADNNumber(),
                        "text" => $question->getQuestion());
                }
            }
        }

        $this->setData($data);
        $this->setMaxCount(sizeof($data));
    }
    
    /**
     * Fill table row
     *
     * @param array $a_set data array
     */
    protected function fillRow($a_set)
    {
        global $lng, $ilCtrl;

        // properties
        $this->tpl->setVariable("VAL_NUMBER", $a_set["number"]);
        $this->tpl->setVariable(
            "VAL_QUESTION",
            adnExaminationQuestionGUI::replaceBBCode($a_set["text"])
        );

        if (!$this->question_ids) {
            $this->tpl->setCurrentBlock("cbox");
            $this->tpl->setVariable("VAL_ID", $a_set["id"]);
            $this->tpl->parseCurrentBlock();
        } else {
            $this->tpl->setCurrentBlock("select_option");
            foreach ($this->objective_options as $id => $caption) {
                $this->tpl->setVariable("OPTION_VALUE", $id);
                $this->tpl->setVariable("OPTION_CAPTION", $caption);
                $this->tpl->parseCurrentBlock();
            }
            
            $this->tpl->setCurrentBlock("select");
            $this->tpl->setVariable("VAL_ID", $a_set["id"]);
            $this->tpl->parseCurrentBlock();
        }
    }
}
