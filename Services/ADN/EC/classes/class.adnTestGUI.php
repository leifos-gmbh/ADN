<?php
/* Copyright (c) 2010 Leifos, GPL, see docs/LICENSE */

include_once("./Services/ADN/ED/classes/class.adnMCQuestion.php");
include_once("./Services/ADN/EC/classes/class.adnTest.php");

/**
 * Test GUI class. The main user interface class for online tests. It offers the
 * forward/backward navigation and displays single mc questions and their answers.
 *
 * @author Alex Killing <killing@leifos.com>
 * @version $Id: class.adnTestGUI.php 27884 2011-02-27 21:01:07Z akill $
 *
 * @ilCtrl_Calls adnTestGUI:
 *
 * @ingroup ServicesADN
 *
 */
class adnTestGUI
{
    public const MODE_ONLINE = "online";
    public const MODE_ELEARNING = "elearning";

    protected string $mode;
    /**
     * @var int[]
     */
    protected array $questions = [];
    protected ?adnCertifiedProfessional $cp = null;
    protected int $cp_id = 0;
    protected ?adnAssignment $ass = null;
    protected int $ass_id = 0;
    protected int $event_id = 0;
    protected ?adnAnswerSheet $sheet = null;
    protected int $cand_sheet_id = 0;

    protected ilLanguage $lng;
    protected ilCtrl $ctrl;
    protected ilGlobalTemplateInterface $tpl;
    protected ilToolbarGUI $toolbar;

    /**
     * Constructor
     */
    public function __construct($a_mode = self::MODE_ONLINE, $a_questions = null)
    {
        global $DIC;
        $this->lng = $DIC->language();
        $this->ctrl = $DIC->ctrl();
        $this->tpl = $DIC->ui()->mainTemplate();
        $this->toolbar = $DIC->toolbar();
        $this->mode = $a_mode;
        if ($a_mode == self::MODE_ONLINE) {
            $this->determineCandidateAndExamination();
        } elseif ($a_mode == self::MODE_ELEARNING) {
            $cnt = 1;
            $this->questions = array();
            foreach ($a_questions as $q_id) {
                $this->questions[] = array("nr" => $cnt++, "q_id" => $q_id);
            }
        }
    }

    /**
     * Determine candidate and exermination
     */
    public function determineCandidateAndExamination()
    {

        $login = $_SESSION["adn_test_user"];
        $code = $_SESSION["adn_access_code"];

        include_once("./Services/ADN/ES/classes/class.adnCertifiedProfessional.php");
        $this->cp_id = adnCertifiedProfessional::getCPIdForUserLogin($login);
        $this->cp = new adnCertifiedProfessional($this->cp_id);
        include_once("./Services/ADN/EP/classes/class.adnAssignment.php");
        $this->ass_id = adnAssignment::getAssignmentIdForCodeAndCP($this->cp_id, $code);
        $this->ass = new adnAssignment($this->ass_id);

        $this->event_id = $this->ass->getEvent();

        include_once("./Services/ADN/EP/classes/class.adnAnswerSheetAssignment.php");
        $sheets = adnAnswerSheetAssignment::getAllSheets($this->cp_id, $this->event_id);

        // get mc sheet
        foreach ($sheets as $sh) {
            include_once("./Services/ADN/EP/classes/class.adnAnswerSheet.php");
            $sheet = new adnAnswerSheet($sh["ep_answer_sheet_id"]);
            if ($sheet->getType() == adnAnswerSheet::TYPE_MC) {
                $this->cand_sheet_id = $sh["id"];
                $this->sheet = $sheet;
            }
        }

        // get questions
        $cnt = 1;
        $this->questions = array();
        if (is_object($this->sheet)) {
            foreach ($this->sheet->getQuestionsInObjectiveOrder(true) as $q_id) {
                $this->questions[] = array("nr" => $cnt++, "q_id" => $q_id);
            }
        } else {
            ilUtil::sendFailure($this->lng->txt("adn_no_mc_sheet_found"));
        }
    }

    /**
     * Execute command
     */
    public function executeCommand()
    {

        $this->tpl->setTitle($this->lng->txt("adn_online_test"));

        $next_class = $this->ctrl->getNextClass();

        // forward command to next gui class in control flow
        switch ($next_class) {
            // no next class:
            // this class is responsible to process the command
            default:
                //$cmd = $ilCtrl->getCmd("showNextQuestion");
                $cmd = $this->ctrl->getCmd("showIntro");

                switch ($cmd) {
                    // commands that need read permission
                    case "showNextQuestion":
                    case "showPreviousQuestion":
                    case "jumpToQuestion":
                    case "showQuestionList":
                    case "jumpToQuestionList":
                    case "showQuestion":
                    case "finishTestConfirmation":
                    case "finishTest":
                    case "showImage":
                    case "showIntro":
                        $this->$cmd();
                        break;

                }
                break;
        }
    }

    protected function showIntro()
    {
        global $DIC;

        $list = $DIC->ui()->factory()->listing()->unordered(
            [
                $this->lng->txt("adn_intro1"),
                $this->lng->txt("adn_intro2"),
                $this->lng->txt("adn_intro3")
            ]
        );
        $panel = $DIC->ui()->factory()->panel()->standard(
            $this->lng->txt("adn_intro"),
            $list
        );
        $button = $DIC->ui()->factory()->button()->standard(
            $this->lng->txt("adn_start"),
            $this->ctrl->getLinkTarget($this, "showNextQuestion")
        );

        $this->tpl->setContent($DIC->ui()->renderer()->render(
            [$panel, $button]
        ));
    }

    /**
     * Show next question
     */
    public function showNextQuestion()
    {

        if ((int) $_POST["q_id"] > 0 && (int) $_POST["given_anser"] == 0) {
            ilUtil::sendInfo($this->lng->txt("adn_no_answer_given_next"), true);
        }

        $this->saveAnswer();

        $c_id = (int) $_GET["q_id"];
        $next_q_id = $this->questions[0]["q_id"];
        foreach ($this->questions as $k => $q) {
            if ($q["q_id"] == $c_id) {
                if (isset($this->questions[$k + 1])) {
                    $next_q_id = $this->questions[$k + 1]["q_id"];
                }
            }
        }

        $this->ctrl->setParameter($this, "q_id", $next_q_id);
        $this->ctrl->redirect($this, "showQuestion");
    }

    /**
     * Show previous question
     */
    public function showPreviousQuestion()
    {

        if ((int) $_POST["q_id"] > 0 && (int) $_POST["given_anser"] == 0) {
            ilUtil::sendInfo($this->lng->txt("adn_no_answer_given_prev"), true);
        }

        $this->saveAnswer();

        $c_id = (int) $_GET["q_id"];
        $next_q_id = $c_id;
        foreach ($this->questions as $k => $q) {
            if ($q["q_id"] == $c_id) {
                if (isset($this->questions[$k - 1])) {
                    $next_q_id = $this->questions[$k - 1]["q_id"];
                }
            }
        }

        $this->ctrl->setParameter($this, "q_id", $next_q_id);
        $this->ctrl->redirect($this, "showQuestion");
    }

    /**
     * Jump to question
     */
    public function jumpToQuestion()
    {

        $this->saveAnswer();
        $this->ctrl->setParameter($this, "q_id", $_GET["q_id"]);
        $this->ctrl->redirect($this, "showQuestion");
    }

    /**
     * Jump to question list
     */
    public function jumpToQuestionList()
    {

        $this->saveAnswer();

        $this->ctrl->setParameter($this, "q_id", $_GET["q_id"]);
        $this->ctrl->redirect($this, "showQuestionList");
    }

    /**
     * Show question list
     */
    public function showQuestionList()
    {

        $this->ctrl->setParameter($this, "q_id", $_GET["q_id"]);
        $this->toolbar->addButton(
            $this->lng->txt("adn_finish_test"),
            $this->ctrl->getLinkTarget($this, "finishTestConfirmation")
        );

        include_once("./Services/ADN/EC/classes/class.adnTestQuestionListTableGUI.php");
        $table = new adnTestQuestionListTableGUI(
            $this,
            "showQuestionList",
            $this->questions,
            $this->cand_sheet_id
        );
        $this->tpl->setContent($table->getHTML());
    }

    /**
     * Show question
     */
    protected function showQuestion()
    {

        $markups = array("[u]", "[/u]", "[f]", "[/f]", "[h]", "[/h]", "[t]", "[/t]");
        $markups_html = array("<u>", "</u>", "<b>", "</b>", "<sup>", "</sup>", "<sub>", "</sub>");

        $q_id = (int) $_GET["q_id"];
        $this->ctrl->setParameter($this, "q_id", $q_id);

        $first = false;
        $last = false;
        if (is_array($this->questions)) {
            foreach ($this->questions as $k => $q) {
                if ($q["q_id"] == $q_id) {
                    $qnr = $k;
                    if ($k == 0) {
                        $first = true;
                    } elseif ($k == count($this->questions) - 1) {
                        $last = true;
                    }
                }
            }
        }
        $qnr = $qnr + 1;

        // navigation toolbar
        $tb = new ilToolbarGUI();
        $tb->setCloseFormTag(false);
        $tb->setOpenFormTag(false);
        if (!$first) {
            $tb->addFormButton($this->lng->txt("adn_previous_question"), "showPreviousQuestion");
        }
        if (!$last) {
            $tb->addFormButton($this->lng->txt("adn_next_question"), "showNextQuestion");
        }
        $tb->addFormButton($this->lng->txt("adn_question_overview"), "jumpToQuestionList");
        $tb->addFormButton($this->lng->txt("adn_finish_test"), "finishTestConfirmation");

        $question = new adnMCQuestion($q_id);

        $qtpl = new ilTemplate("tpl.test_question.html", true, true, "Services/ADN/EC");

        if ($this->mode == self::MODE_ELEARNING) {
            $previous_answer = $_SESSION["given_answer"][$q_id];
        } else {
            $previous_answer = adnTest::lookupAnswer($this->cand_sheet_id, (int) $q_id);
        }

        $ans = array(1 => "A", 2 => "B", 3 => "C", 4 => "D");
        foreach ($ans as $k => $nr) {
            if ($previous_answer == $k) {
                $qtpl->touchBlock("checked");
            }
            $m = "getAnswer" . $nr;
            $qtpl->setCurrentBlock("answer");
            $answer = $question->$m();
            $img = "";
            if ($question->getFilename($k + 1)) {
                $this->ctrl->setParameter($this, "img", $k + 1);
                $img = "<div><img src=\"" . $this->ctrl->getLinkTarget($this, "showImage") . "\" /></div>";
            }
            $qtpl->setVariable(
                "ANSWER",
                $img . str_replace($markups, $markups_html, $answer["text"])
            );
            $qtpl->setVariable("KEY", $k);
            $qtpl->setVariable("VAL_NR", $nr);
            $qtpl->parseCurrentBlock();
        }

        $img = "";
        if ($question->getFilename(1)) {
            $this->ctrl->setParameter($this, "img", 1);
            $img = "<div><img src=\"" . $this->ctrl->getLinkTarget($this, "showImage") . "\" /></div>";
        }

        $cnt = !is_array($this->questions)
            ? 0
            : count($this->questions);
        $head = $this->lng->txt("adn_question_x_of_y");
        $head = str_replace("%x", $qnr, $head);
        $head = str_replace("%y", $cnt, $head);
        $qtpl->setVariable(
            "QUESTION_HEAD",
            $head
        );
        $qtpl->setVariable("QUESTION",
            $img . str_replace($markups, $markups_html, $question->getQuestion()));
        $qtpl->setVariable("FORMACTION", $this->ctrl->getFormAction($this));
        $qtpl->setVariable("TOOLBAR", $tb->getHTML());
        $qtpl->setVariable("QID", $q_id);
        //$qtpl->setVariable("TOOLBAR2", $tb->getHTML());

        $this->tpl->setContent($qtpl->get());
    }

    /**
     * Save answer
     */
    protected function saveAnswer()
    {
        if ((int) $_POST["q_id"] > 0) {
            if ($this->mode == self::MODE_ELEARNING) {
                $_SESSION["given_answer"][$_POST["q_id"]] = (int) $_POST["given_anser"];
            } else {
                adnTest::saveAnswer(
                    $this->cand_sheet_id,
                    (int) $_POST["q_id"],
                    (int) $_POST["given_anser"]
                );
            }
        }
    }

    /**
     * Finish test confirmation
     */
    public function finishTestConfirmation()
    {

        $this->saveAnswer();

        ilUtil::sendQuestion($this->lng->txt("adn_really_finish_test"));

        $this->ctrl->setParameter($this, "q_id", $_GET["q_id"]);
        $this->toolbar->addButton(
            $this->lng->txt("yes"),
            $this->ctrl->getLinkTarget($this, "finishTest")
        );
        $this->toolbar->addButton(
            $this->lng->txt("no"),
            $this->ctrl->getLinkTarget($this, "showQuestion")
        );
    }

    /**
     * Test finished
     */
    public function finishTest()
    {

        if ($this->mode == self::MODE_ELEARNING) {
            $this->ctrl->redirectByClass("adnelearninggui", "showResult");
        } else {
            ilUtil::sendSuccess($this->lng->txt("adn_test_finished"));
            $this->toolbar->addButton($this->lng->txt("logout"), "logout.php");
        }
    }

    /**
     * Show/Deliver question images
     */
    protected function showImage()
    {
        $q_id = (int) $_GET["q_id"];
        $question = new adnMCQuestion($q_id);

        if ($question) {
            $id = (string) $_REQUEST["img"];

            $file = $question->getFilePath() . $q_id . "_" . $id;
            if (file_exists($file)) {
                ilUtil::deliverFile($file, $question->getFileName(1));
            }
        }
    }
}
