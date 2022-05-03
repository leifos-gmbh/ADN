<?php
/* Copyright (c) 2010 Leifos, GPL, see docs/LICENSE */

include_once("./Services/Table/classes/class.ilTable2GUI.php");
include_once("./Services/ADN/ED/classes/class.adnMCQuestion.php");
include_once("./Services/ADN/ED/classes/class.adnExaminationQuestion.php");
include_once("./Services/ADN/EC/classes/class.adnTest.php");

/**
 * ADN online answers given by candidate table GUI class. This table displays all given
 * answers by a candidate for an online exam.
 *
 * @author Alex Killing <killing@leifos.com>
 * @version $Id: class.adnOnlineAnswersTableGUI.php 36168 2012-08-13 10:36:30Z jluetzen $
 *
 * @ingroup ServicesADN
 */
class adnOnlineAnswersTableGUI extends ilTable2GUI
{
    protected int $cand_sheet_id = 0;
    protected int $cp_id = 0;
    protected int $event_id = 0;
    /**
     * @var array<string, int>
     */
    protected array $questions = [];
    protected ?adnAnswerSheet $sheet = null;

    /**
     * Constructor
     *
     * @param object $a_parent_obj parent gui object
     * @param string $a_parent_cmd parent default command
     * @param int $a_event_id event id
     * @param int $a_candidate_id candidate id
     */
    public function __construct($a_parent_obj, $a_parent_cmd, $a_event_id, $a_candidate_id)
    {
        global $ilCtrl, $lng;

        $this->cp_id = (int) $a_candidate_id;
        $this->event_id = (int) $a_event_id;
        
        parent::__construct($a_parent_obj, $a_parent_cmd);

        // get mc sheet and questions
        include_once("./Services/ADN/EP/classes/class.adnAnswerSheetAssignment.php");
        $sheets = adnAnswerSheetAssignment::getAllSheets($this->cp_id, $this->event_id);
        foreach ($sheets as $sh) {
            include_once("./Services/ADN/EP/classes/class.adnAnswerSheet.php");
            $sheet = new adnAnswerSheet($sh["ep_answer_sheet_id"]);
            if ($sheet->getType() == adnAnswerSheet::TYPE_MC) {
                $this->cand_sheet_id = $sh["id"];
                $this->sheet = $sheet;
            }
        }
        $this->questions = array();
        if (is_object($this->sheet)) {
            // get questions
            $cnt = 1;
            foreach ($this->sheet->getQuestions() as $q_id) {
                $this->questions[] = array("nr" => $cnt++, "q_id" => $q_id);
            }
        }
        
        // set title and data
        $this->setLimit(100);
        $this->setData($this->questions);
        $this->setTitle($lng->txt("adn_question_overview"));

        // column headers
        $this->addColumn($this->lng->txt("adn_nr"));
        $this->addColumn($this->lng->txt("adn_question"));
        $this->addColumn($this->lng->txt("adn_correct_answer"));
        $this->addColumn($this->lng->txt("adn_given_answer"));
        $this->addColumn($this->lng->txt("adn_correct"));

        $this->setFormAction($ilCtrl->getFormAction($a_parent_obj));
        $this->setRowTemplate("tpl.given_answer_row.html", "Services/ADN/ES");
    }

    /**
     * Render
     */
    public function render()
    {
        if (!is_object($this->sheet)) {
            return "";
        }

        if (!adnTest::hasAnswered($this->cand_sheet_id)) {
            return "";
        }

        return parent::render();
    }

    /**
     * Fill table row
     *
     * @param array $a_set data array
     */
    protected function fillRow($a_set)
    {
        global $lng, $ilCtrl;

        $markups = array("[u]", "[/u]", "[f]", "[/f]", "[h]", "[/h]", "[t]", "[/t]");
        $markups_html = array("<u>", "</u>", "<b>", "</b>", "<sup>", "</sup>", "<sub>", "</sub>");

        // output question and given answer
        $this->tpl->setVariable("VAL_NR", $a_set["nr"]);
        $this->tpl->setVariable(
            "VAL_QUESTION",
            str_replace($markups, $markups_html, adnExaminationQuestion::lookupQuestion($a_set["q_id"]))
        );
        $this->tpl->setVariable(
            "VAL_CORRECT_ANSWER",
            $ca = strtoupper(adnMCQuestion::lookupCorrectAnswer($a_set["q_id"]))
        );
        $map = array(1 => "A", 2 => "B",3 => "C", 4 => "D");
        $ga = adnTest::lookupAnswer($this->cand_sheet_id, $a_set["q_id"]);
        if ($ga > 0) {
            $ga = $map[$ga];
        } else {
            $ga = "-";
        }
        $this->tpl->setVariable("VAL_GIVEN_ANSWER", $ga);
        if ($ga == $ca) {
            $this->tpl->setVariable("VAL_CORRECT", "X");
        }
    }
}
