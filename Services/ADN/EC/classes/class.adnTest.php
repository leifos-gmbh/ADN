<?php
/* Copyright (c) 2010 Leifos, GPL, see docs/LICENSE */

/**
 * ADN test application class. This class stores and manages the answers given by
 * participants of online tests.
 *
 * @author Alex Killing <killing@leifos.de>
 * @version $Id: class.adnTest.php 27884 2011-02-27 21:01:07Z akill $
 *
 * @ingroup ServicesADN
 */
class adnTest
{
    /**
     * Save answer
     *
     * @param int $a_cand_sheet_id candidate/sheet id
     * @param int $a_q_id question id
     * @param int $a_answer answer
     */
    public static function saveAnswer($a_cand_sheet_id, $a_q_id, $a_answer)
    {
        global $ilDB;

        $set = $ilDB->query("SELECT *" .
            " FROM adn_ec_given_answer" .
            " WHERE ep_cand_sheet_id = " . $ilDB->quote($a_cand_sheet_id, "integer") .
            " AND ed_question_id = " . $ilDB->quote($a_q_id, "integer"));
        if ($rec = $ilDB->fetchAssoc($set)) {
            $ilDB->manipulate("UPDATE adn_ec_given_answer" .
                " SET answer = " . $ilDB->quote($a_answer, "integer") . "," .
                " last_update = " . $ilDB->now() .
                " WHERE ep_cand_sheet_id = " . $ilDB->quote($a_cand_sheet_id, "integer") .
                " AND ed_question_id = " . $ilDB->quote($a_q_id, "integer"));
        } else {
            $ilDB->manipulate("INSERT INTO adn_ec_given_answer" .
                " (ep_cand_sheet_id, ed_question_id, answer, last_update, create_date) VALUES (" .
                $ilDB->quote($a_cand_sheet_id, "integer") . "," .
                $ilDB->quote($a_q_id, "integer") . "," .
                $ilDB->quote($a_answer, "integer") . "," .
                $ilDB->now() . "," .
                $ilDB->now() . ")");
        }
    }

    /**
     * Lookup answer
     *
     * @param int $a_cand_sheet_id candidate/sheet id
     * @param int $a_q_id question id
     * @return int answer
     */
    public static function lookupAnswer($a_cand_sheet_id, $a_q_id)
    {
        global $ilDB;

        $set = $ilDB->query("SELECT answer" .
            " FROM adn_ec_given_answer" .
            " WHERE ep_cand_sheet_id = " . $ilDB->quote($a_cand_sheet_id, "integer") .
            " AND ed_question_id = " . $ilDB->quote($a_q_id, "integer"));
        if ($rec = $ilDB->fetchAssoc($set)) {
            return (int) $rec["answer"];
        }
        return 0;
    }
    
    /**
     * Lookup all answers
     * @param object $a_cand_sheet_id
     * @return
     */
    public static function lookupAnswers($a_cand_sheet_id)
    {
        global $ilDB;
        
        $query = 'SELECT ed_question_id, answer FROM adn_ec_given_answer ' .
            'WHERE ep_cand_sheet_id  = ' . $ilDB->quote($a_cand_sheet_id, 'integer');
        $res = $ilDB->query($query);
        while ($row = $ilDB->fetchRow(DB_FETCHMODE_OBJECT)) {
            $answers[$row->ed_question_id] = $row->answer;
        }
        return (array) $answers;
    }

    /**
     * Lookup mc result
     *
     * @param int $a_event_id
     * @param int $a_candidate_id
     * @return int
     */
    public static function lookupMCResult($a_event_id, $a_candidate_id)
    {
        global $ilDB;

        $cand_sheet_id = 0;
        
        include_once("./Services/ADN/EP/classes/class.adnAnswerSheetAssignment.php");
        $sheets = adnAnswerSheetAssignment::getAllSheets(
            $a_candidate_id,
            $a_event_id
        );

        // get mc sheet
        foreach ($sheets as $sh) {
            include_once("./Services/ADN/EP/classes/class.adnAnswerSheet.php");
            $sheet = new adnAnswerSheet($sh["ep_answer_sheet_id"]);
            if ($sheet->getType() == adnAnswerSheet::TYPE_MC) {
                $cand_sheet_id = $sh["id"];
            }
        }
        $score = 0;
        $given_answers = array();
        if ($cand_sheet_id > 0) {
            include_once("./Services/ADN/ED/classes/class.adnMCQuestion.php");

            // read answers
            $set = $ilDB->query("SELECT answer, ed_question_id" .
                " FROM adn_ec_given_answer" .
                " WHERE ep_cand_sheet_id = " . $ilDB->quote($cand_sheet_id, "integer"));
            $given_answers = array();
            $map = array("a" => 1, "b" => 2, "c" => 3, "d" => 4);
            while ($rec = $ilDB->fetchAssoc($set)) {
                $given_answers[$rec["ed_question_id"]] = $rec["answer"];
                $correct = adnMCQuestion::lookupCorrectAnswer($rec["ed_question_id"]);
                if ($correct !== false) {
                    if ($rec["answer"] == $map[$correct]) {
                        $score++;
                    }
                }
            }
        }
        return $score;
    }

    /**
     * Lookup results of candidate
     * @param int $a_event_id
     * @param int $a_candidate_id
     */
    public static function lookupResults($a_event_id, $a_candidate_id)
    {
        global $ilDB;

        $cand_sheet_id = 0;

        include_once("./Services/ADN/EP/classes/class.adnAnswerSheetAssignment.php");
        $sheets = adnAnswerSheetAssignment::getAllSheets($a_candidate_id, $a_event_id);

        // get mc sheet
        foreach ($sheets as $sh) {
            include_once("./Services/ADN/EP/classes/class.adnAnswerSheet.php");
            $sheet = new adnAnswerSheet($sh["ep_answer_sheet_id"]);
            if ($sheet->getType() == adnAnswerSheet::TYPE_MC) {
                $cand_sheet_id = $sh["id"];
            }
        }
        if (!$cand_sheet_id) {
            return array();
        }

        $query = 'SELECT * FROM adn_ec_given_answer ' .
        'WHERE ep_cand_sheet_id = ' . $ilDB->quote($cand_sheet_id, 'integer');

        $res = $ilDB->query($query);

        $results = array();
        while ($row = $res->fetchObject()) {
            $results[$row->ed_question_id] = $row->answer;
        }

        return (array) $results;
    }

    /**
     * Check if at least one answer has been given
     *
     * @param int $a_cand_sheet_id candidate/sheet id
     * @return bool
     */
    public static function hasAnswered($a_cand_sheet_id)
    {
        global $ilDB;

        $set = $ilDB->query("SELECT answer" .
            " FROM adn_ec_given_answer" .
            " WHERE ep_cand_sheet_id = " . $ilDB->quote($a_cand_sheet_id, "integer"));
        if ($rec = $ilDB->fetchAssoc($set)) {
            return true;
        }
        return false;
    }
}
