<?php
/* Copyright (c) 2012 Leifos, GPL, see docs/LICENSE */

include_once("./Services/ADN/Base/classes/class.adnDBBase.php");
include_once("./Services/ADN/ED/classes/class.adnMCQuestion.php");
include_once("./Services/ADN/EC/classes/class.adnTest.php");
include_once './webservice/soap/classes/class.ilSoapAdministration.php';

/**
 * E-Learning SOAP class. The main soap interface class of the e-learning part of the ADN
 * application.
 *
 * @author Alex Killing <killing@leifos.com>
 * @version $Id$
 *
 * @ingroup ServicesADN
 *
 */
class adnSoapELearning extends ilSoapAdministration
{
    /**
     * Constructor
     *
     * @param
     * @return
     */
    public function __construct()
    {
    }
    
    
    /**
     * Get subject areas
     *
     * @param string $sid session id
     * @return array array of subject areas, keys: "sa_id" ID, "sa_title" subject area title
     */
    public function getSubjectAreas($sid)
    {
        $this->initAuth($sid);
        $this->initIlias();

        if (!$this->__checkSession($sid)) {
            return $this->__raiseError($this->__getMessage(), $this->__getMessageCode());
        }

        global $lng;
        $lng->loadLanguageModule("adn");
        
        $resp = array();
        
        include_once("./Services/ADN/ED/classes/class.adnSubjectArea.php");
        foreach (adnSubjectArea::getAllAreas() as $k => $txt) {
            $resp[] = array('sa_id' => $k,
                'sa_title' => $txt);
        }
        
        return $resp;
    }
    
    /**
     * Create a new test for a subject area
     *
     * @param string $sid session id
     * @param string $a_sa_id subject area id
     * @return boolean success true/false
     */
    public function createTest($sid, $a_sa_id)
    {
        $this->initAuth($sid);
        $this->initIlias();

        if (!$this->__checkSession($sid)) {
            return $this->__raiseError($this->__getMessage(), $this->__getMessageCode());
        }

        global $lng;
        $lng->loadLanguageModule("adn");
        
        include_once "./Services/ADN/ED/classes/class.adnQuestionTargetNumbers.php";
        $sheet_questions = adnQuestionTargetNumbers::generateMCSheet($a_sa_id);
        $_SESSION["sheet_questions"] = $sheet_questions;
        $_SESSION["given_answer"] = array();
        
        if (!is_array($sheet_questions)) {
            return false;
        }
        
        return true;
    }

    /**
     * Get question overview of current test
     *
     * @param string $sid session id
     * @return array array of questions, keys: "id" question ID, "text" question text, "nr" => number, "answered" 1/0
     */
    public function getQuestionOverview($sid)
    {
        $this->initAuth($sid);
        $this->initIlias();

        if (!$this->__checkSession($sid)) {
            return $this->__raiseError($this->__getMessage(), $this->__getMessageCode());
        }

        global $lng;
        $lng->loadLanguageModule("adn");
        
        include_once("./Services/ADN/ED/classes/class.adnMCQuestion.php");
        include_once("./Services/ADN/ED/classes/class.adnExaminationQuestionGUI.php");
        
        $questions = array();
        $cnt = 1;
        foreach ($_SESSION["sheet_questions"] as $q) {
            $question = new adnMCQuestion($q);
            $questions[] = array("q_id" => (int) $q,
                "q_text" => adnExaminationQuestionGUI::replaceBBCode($question->getQuestion()),
                "q_nr" => $cnt++,
                "answered" => (isset($_SESSION["given_answer"][$q])) ? 1 : 0);
        }

        return $questions;
    }

    /**
     * Process a question (give an answer) and retrieve the next question
     *
     * @param string $sid session id
     * @param string $a_save_q_id ID of questions that anwer should be saved (0 for now save action)
     * @param string $a_given_answer (1-4 or 0 for no answer)
     * @param string $a_req_q_id ID of requested question (0 for first question)
     * @return array
     */
    public function processQuestion($sid, $a_save_q_id, $a_given_answer, $a_req_q_id)
    {
        $this->initAuth($sid);
        $this->initIlias();

        if (!$this->__checkSession($sid)) {
            return $this->__raiseError($this->__getMessage(), $this->__getMessageCode());
        }

        global $lng;
        $lng->loadLanguageModule("adn");
        
        // save the answer
        if ($a_save_q_id > 0 && $a_given_answer > 0) {
            $_SESSION["given_answer"][$a_save_q_id] = $a_given_answer;
        }
        
        include_once("./Services/ADN/ED/classes/class.adnMCQuestion.php");
        include_once("./Services/ADN/ED/classes/class.adnExaminationQuestionGUI.php");

        $prev_q_id = $next_q_id = 0;
        if ($a_req_q_id == 0 || !in_array($a_req_q_id, $_SESSION["sheet_questions"])) {
            $a_req_q_id = $_SESSION["sheet_questions"][0];
            $next_q_id = (int) $_SESSION["sheet_questions"][1];
        } else {
            $last = 0;
            $is_requested = false;
            foreach ($_SESSION["sheet_questions"] as $q) {
                // if the requested has been found in the last run
                // set the next id to the current one
                if ($is_requested) {
                    $next_q_id = $q;
                    $is_requested = false;
                }
                
                // if we found the question, the previous one is the last one before
                if ($q == $a_req_q_id) {
                    $prev_q_id = $last;
                    $is_requested = true;
                }
                $last = $q;
            }
        }
        
        $question = new adnMCQuestion($a_req_q_id);
        
        $img_file = "";
        $img_path = "";
        if ($question->getFilename(1)) {
            $img_file = $question->getFilePath() . $a_req_q_id . "_1";
            $img_path = ILIAS_HTTP_PATH . "/el_download.php?cmd=image&q_id=" . $a_req_q_id . "&img=1&sid=" . $sid;
        }

        $response = array(
            "q_id" => (int) $a_req_q_id,
            "q_text" => adnExaminationQuestionGUI::replaceBBCode($question->getQuestion()),
            "q_image" => $img_path,
            "next_q_id" => $next_q_id,
            "prev_q_id" => $prev_q_id
        );

        $markups = array("[u]", "[/u]", "[f]", "[/f]", "[h]", "[/h]", "[t]", "[/t]");
        $markups_html = array("<u>", "</u>", "<b>", "</b>", "<sup>", "</sup>", "<sub>", "</sub>");

        foreach (array(1 => "A", 2 => "B", 3 => "C", 4 => "D") as $k => $nr) {
            $m = "getAnswer" . $nr;
            $answer = $question->$m();

            $image = "";
            $img_path = "";
            if ($question->getFilename($k + 1)) {
                $image = $question->getFilePath() . $a_req_q_id . "_" . ($k + 1);
                $img_path = ILIAS_HTTP_PATH . "/el_download.php?cmd=image&q_id=" . $a_req_q_id . "&img=" . ($k + 1) . "&sid=" . $sid;
            }
            $response["answers"][] = array(
                "nr" => $k,
                "text" => str_replace($markups, $markups_html, $answer["text"]),
                "image" => $img_path);
        }
        
        return $response;
    }

    /**
     * Process last answer, finish test and return results
     *
     * @param string $sid session id
     * @param string $a_save_q_id ID of questions that anwer should be saved (0 for now save action)
     * @param string $a_given_answer (1-4 or 0 for no answer)
     * @return array
     */
    public function finishTest($sid, $a_save_q_id, $a_given_answer)
    {
        $this->initAuth($sid);
        $this->initIlias();

        if (!$this->__checkSession($sid)) {
            return $this->__raiseError($this->__getMessage(), $this->__getMessageCode());
        }

        global $lng;
        $lng->loadLanguageModule("adn");
        
        // save the answer
        if ($a_save_q_id > 0 && $a_given_answer > 0) {
            $_SESSION["given_answer"][$a_save_q_id] = $a_given_answer;
        }
        
        include_once("./Services/ADN/ED/classes/class.adnMCQuestion.php");
        include_once("./Services/ADN/ED/classes/class.adnExaminationQuestionGUI.php");

        $markups = array("[u]", "[/u]", "[f]", "[/f]", "[h]", "[/h]", "[t]", "[/t]");
        $markups_html = array("<u>", "</u>", "<b>", "</b>", "<sup>", "</sup>", "<sub>", "</sub>");
        
        $nr = 1;
        $results = array();
        foreach ($_SESSION["sheet_questions"] as $q) {
            $question = new adnMCQuestion($q);

            $img_file = "";
            $img_path = "";
            if ($question->getFilename(1)) {
                $img_file = $question->getFilePath() . $q . "_1";
                $img_path = ILIAS_HTTP_PATH . "/el_download.php?cmd=image&q_id=" . $q . "&img=1&sid=" . $sid;
            }

            $map = array(1 => "A", 2 => "B", 3 => "C", 4 => "D");
            
            $given_answer_text = "";
            $given_answer_image = "";
            $given_answer = 0;
            $given_answer_image_path = "";
            if ($_SESSION["given_answer"][$q] > 0) {
                $g = $map[$_SESSION["given_answer"][$q]];
                $m = "getAnswer" . $g;
                $quest = $question->$m();
                $g_nr = current(array_keys($map, $g));
                $given_answer_text = str_replace($markups, $markups_html, $quest["text"]);
                $given_answer = $_SESSION["given_answer"][$q];
                if ($question->getFilename($g_nr + 1)) {
                    $given_answer_image = $question->getFilePath() . $q . "_" . ($g_nr + 1);
                    $given_answer_image_path = ILIAS_HTTP_PATH . "/el_download.php?cmd=image&q_id=" . $q . "&img=" . ($g_nr + 1) . "&sid=" . $sid;
                }
            }
            $a = strtoupper($question->getCorrectAnswer());
            $m = "getAnswer" . $a;
            $quest = $question->$m();
            $correct_answer_text = str_replace($markups, $markups_html, $quest["text"]);
            $correct_answer = current(array_keys($map, $a));
            $correct_answer_image = "";
            $correct_answer_image_path = "";
            if ($question->getFilename($correct_answer + 1)) {
                $correct_answer_image = $question->getFilePath() . $q . "_" . ($correct_answer + 1);
                $correct_answer_image_path = ILIAS_HTTP_PATH . "/el_download.php?cmd=image&q_id=" . $q . "&img=" . ($correct_answer + 1) . "&sid=" . $sid;
            }

            $results[] = array(
                "q_id" => (int) $q,
                "q_nr" => (int) $nr,
                "q_text" => adnExaminationQuestionGUI::replaceBBCode($question->getQuestion()),
                "q_image" => $img_path,
                'correct' => (int) ($given_answer == $correct_answer),
                "given_answer" => (int) $given_answer,
                "given_answer_text" => $given_answer_text,
                "given_answer_image" => $given_answer_image_path,
                "correct_answer" => (int) $correct_answer,
                "correct_answer_text" => $correct_answer_text,
                "correct_answer_image" => $correct_answer_image_path
                );
            $nr++;
        }
        
        return $results;
    }
    
    /**
     * Get scoring sheet url
     *
     * @param string $sid session id
     * @return string url
     */
    public function getScoringSheet($sid)
    {
        $this->initAuth($sid);
        $this->initIlias();

        if (!$this->__checkSession($sid)) {
            return $this->__raiseError($this->__getMessage(), $this->__getMessageCode());
        }

        global $lng;
        $lng->loadLanguageModule("adn");

        return ILIAS_HTTP_PATH . "/el_download.php?cmd=scoring_sheet&sid=" . $sid;
    }
    
    /**
     * Get urls of information sheets
     *
     * @param string $sid session id
     * @return array array of strings (urls)
     */
    public function getInformationSheets($sid)
    {
        $this->initAuth($sid);
        $this->initIlias();

        if (!$this->__checkSession($sid)) {
            return $this->__raiseError($this->__getMessage(), $this->__getMessageCode());
        }

        global $lng;
        $lng->loadLanguageModule("adn");
        
        $result = array();

        include_once "Services/ADN/EP/classes/class.adnExamInfoLetter.php";
        $letters = adnExamInfoLetter::getAllLetters();
        foreach ($letters as $k => $f) {
            $result[] = array(
                "filename" => $f["file"],
                "url" => ILIAS_HTTP_PATH . "/el_download.php?cmd=info_sheet&id=" . $f["id"] . "&sid=" . $sid
                );
        }

        return $result;
    }
}
