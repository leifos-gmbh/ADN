<?php
/* Copyright (c) 2010 Leifos, GPL, see docs/LICENSE */

include_once("./Services/ADN/ED/classes/class.adnMCQuestion.php");
include_once("./Services/ADN/EC/classes/class.adnTest.php");

/**
 * E-Learning GUI class. The main user interface class of the e-learning part of the ADN
 * application. Users can start new (anonymous) tests (multiple choice only) and
 * download information documents.
 *
 * @author Alex Killing <killing@leifos.com>
 * @version $Id: class.adnELearningGUI.php 37734 2012-10-19 18:17:28Z akill $
 *
 * @ilCtrl_Calls adnELearningGUI: adnTestGUI
 *
 * @ingroup ServicesADN
 *
 */
class adnELearningGUI
{
    /**
     * Execute command
     */
    public function executeCommand()
    {
        global $ilCtrl, $lng, $tpl;

        $tpl->setTitle($lng->txt("adn_elearning"));

        $next_class = $ilCtrl->getNextClass();

        // forward command to next gui class in control flow
        switch ($next_class) {
            case "adntestgui":
                include_once("./Services/ADN/EC/classes/class.adnTestGUI.php");
                $test_gui = new adnTestGUI(
                    adnTestGUI::MODE_ELEARNING,
                    $_SESSION["sheet_questions"]
                );
                $ilCtrl->forwardCommand($test_gui);
                break;

            // no next class:
            // this class is responsible to process the command
            default:
                $cmd = $ilCtrl->getCmd("showFrontPage");

                switch ($cmd) {
                    // commands that need read permission
                    case "showFrontPage":
                    case "downloadInfoLetter":
                    case "startTest":
                    case "showResult":
                    case "downloadSolutions":
                        $this->$cmd();
                        break;

                }
                break;
        }
    }

    /**
     * Show front page
     */
    public function showFrontPage()
    {
        global $tpl, $lng, $ilCtrl;

        $etpl = new ilTemplate(
            "tpl.front_page.html",
            true,
            true,
            "Services/ADN/EL"
        );

        // subject areas
        include_once("./Services/ADN/ED/classes/class.adnSubjectArea.php");
        foreach (adnSubjectArea::getAllAreas() as $k => $txt) {
            $etpl->setCurrentBlock("st_row");
            $etpl->setVariable("TXT_SUBJECT_AREA", $txt);
            $etpl->setVariable("TXT_START_TEST", $lng->txt("adn_start_test"));
            $ilCtrl->setParameter($this, "sa_id", $k);
            $etpl->setVariable(
                "HREF_START_TEST",
                $ilCtrl->getLinkTarget($this, "startTest")
            );
            $ilCtrl->setParameter($this, "sa_id", "");
            $etpl->parseCurrentBlock();
        }

        // information letters
        include_once "Services/ADN/EP/classes/class.adnExamInfoLetter.php";
        $letters = adnExamInfoLetter::getAllLetters();
        foreach ($letters as $k => $f) {
            $etpl->setCurrentBlock("il_row");
            $etpl->setVariable("TXT_INFO_LETTER", $f["file"]);
            $etpl->setVariable("TXT_DOWNLOAD", $lng->txt("download"));
            $ilCtrl->setParameter($this, "il_id", $f["id"]);
            $etpl->setVariable(
                "HREF_DOWNLOAD",
                $ilCtrl->getLinkTarget($this, "downloadInfoLetter")
            );
            $ilCtrl->setParameter($this, "il_id", "");
            $etpl->parseCurrentBlock();
        }

        $etpl->setVariable("TXT_SELF_TEST", $lng->txt("adn_self_test"));
        $etpl->setVariable("TXT_INFO_SHEETS", $lng->txt("adn_information_letters"));
        $tpl->setContent($etpl->get());
    }

    /**
     * Download information letter
     */
    public function downloadInfoLetter()
    {
        global $lng, $ilCtrl;

        include_once("./Services/ADN/EP/classes/class.adnExamInfoLetter.php");
        $letter = new adnExamInfoLetter((int) $_GET["il_id"]);
        $file = $letter->getFilePath() . $letter->getId();
        if (file_exists($file)) {
            ilUtil::deliverFile($file, $letter->getFileName());
        } else {
            ilUtil::sendFailure($lng->txt("adn_file_corrupt"), true);
            $ilCtrl->redirect($this, "showFrontPage");
        }
    }

    /**
     * Start test
     */
    public function startTest()
    {
        global $ilCtrl;

        include_once "./Services/ADN/ED/classes/class.adnQuestionTargetNumbers.php";
        $sheet_questions = adnQuestionTargetNumbers::generateMCSheet($_GET["sa_id"]);
        $_SESSION["sheet_questions"] = $sheet_questions;
        $_SESSION["given_answer"] = array();
        $ilCtrl->redirectByClass("adntestgui", "");
    }

    /**
     * Show result
     */
    public function showResult()
    {
        global $tpl, $lng, $ilToolbar, $ilCtrl;

        $ilToolbar->addButton(
            $lng->txt("adn_back_to_front_page"),
            $ilCtrl->getLinkTarget($this, "showFrontPage")
        );
        $ilToolbar->addButton(
            $lng->txt("adn_download_solutions"),
            $ilCtrl->getLinkTarget($this, "downloadSolutions")
        );

        // show score
        $score = 0;
        $map = array(1 => "a", 2 => "b", 3 => "c", 4 => "d");
        foreach ($_SESSION["sheet_questions"] as $q) {
            $question = new adnMCQuestion($q);
            if ($map[$_SESSION["given_answer"][$q]] ==
                $question->getCorrectAnswer()) {
                $score++;
            }
        }
        ilUtil::sendInfo($lng->txt("adn_your_score") . ": " . $score . " " .
            $lng->txt("adn_score_points"));

        include_once("./Services/ADN/EL/classes/class.adnELResultTableGUI.php");
        $res_table = new adnELResultTableGUI(
            $this,
            "showResult",
            $_SESSION["sheet_questions"],
            $_SESSION["given_answer"]
        );

        $tpl->setContent($res_table->getHTML());
    }

    /**
     * Download solutions
     */
    public function downloadSolutions()
    {
        global $tpl, $ilCtrl;

        include_once './Services/ADN/Report/exceptions/class.adnReportException.php';
        try {
            include_once("./Services/ADN/Report/classes/class.adnReportOnlineExam.php");
            $report = new adnReportOnlineExam();
            $report->createELearningSheet($_SESSION['sheet_questions'], $_SESSION['given_answer']);
            ilUtil::deliverFile(
                $report->getOutfile(),
                'Loesungsbogen.pdf',
                'application/pdf'
            );
        } catch (adnReportException $e) {
            ilUtil::sendFailure($e->getMessage(), true);
            $ilCtrl->redirect($this, 'listEvents');
        }
    }
}
