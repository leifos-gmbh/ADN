<?php
/* Copyright (c) 2010 Leifos, GPL, see docs/LICENSE */

/**
 * Statistics application class
 *
 * Handles the data queries for all statistics
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.de>
 * @version $Id: class.adnStatistics.php 28755 2011-05-02 14:22:06Z jluetzen $
 *
 * @ingroup ServicesADN
 */
class adnStatistics
{
    /**
     * Get exams statistics
     *
     * @param array $a_filter
     * @return array
     */
    public static function getExams(array $a_filter = null)
    {
        $res = array();

        // get all types
        include_once "Services/ADN/ED/classes/class.adnSubjectArea.php";
        $areas = adnSubjectArea::getAllAreas();
        foreach ($areas as $type => $caption) {
            $res[$type]["type"] = 0;
            $res[$type]["participants"] = 0;
            $res[$type]["successful"] = 0;
        }
        
        if ($a_filter["wmo"]) {
            include_once "Services/ADN/MD/classes/class.adnExamFacility.php";
            $fac = array();
            foreach (adnExamFacility::getAllExamFacilities() as $item) {
                $fac[$item["id"]] = $item["md_wmo_id"];
            }
        }

        $event_filter = array();
        if ($a_filter["date"]["from"]) {
            $event_filter["date"]["from"] = $a_filter["date"]["from"];
            $event_filter["date"]["to"] = $a_filter["date"]["to"];
        }
        include_once "Services/ADN/EP/classes/class.adnExaminationEvent.php";
        include_once "Services/ADN/EP/classes/class.adnAssignment.php";
        $events = adnExaminationEvent::getAllEvents($event_filter, true);
        foreach ($events as $event) {
            // check wmo by exam facility
            if ($a_filter["wmo"] && $fac[$event["md_exam_facility_id"]] != $a_filter["wmo"]) {
                continue;
            }

            $res[$event["subject_area"]]["type"]++;

            $assignments = adnAssignment::getAllAssignments(array("event_id" => $event["id"]));
            $participants = 0;
            foreach ($assignments as $assignment) {
                if ($assignment["has_participated"]) {
                    $res[$event["subject_area"]]["participants"]++;
                }
                if ($assignment["result_total"] == adnAssignment::SCORE_PASSED) {
                    $res[$event["subject_area"]]["successful"]++;
                }
            }
        }

        return $res;
    }

    /**
     * Get extensions refresher statistics
     *
     * @param array $a_filter
     * @return array
     */
    public static function getExtensionsRefresher(array $a_filter = null)
    {
        include_once "Services/ADN/ES/classes/class.adnCertificate.php";
        $proofs = array(
            adnCertificate::PROOF_TRAIN_DRY,
            adnCertificate::PROOF_TRAIN_TANK,
            adnCertificate::PROOF_TRAIN_COMBINED,
            adnCertificate::PROOF_TRAIN_GAS,
            adnCertificate::PROOF_TRAIN_CHEMICALS
        );
        return adnCertificate::getExtensionStatistics(
            $a_filter["date"]["from"],
            $a_filter["date"]["to"],
            $a_filter["wmo"],
            $proofs
        );
    }

    /**
     * Get extensions experience statistics
     *
     * @param array $a_filter
     * @return array
     */
    public static function getExtensionsExperience(array $a_filter = null)
    {
        include_once "Services/ADN/ES/classes/class.adnCertificate.php";
        $proofs = array(
            adnCertificate::PROOF_EXP_GAS,
            adnCertificate::PROOF_EXP_CHEMICALS
        );
        return adnCertificate::getExtensionStatistics(
            $a_filter["date"]["from"],
            $a_filter["date"]["to"],
            $a_filter["wmo"],
            $proofs
        );
    }

    /**
     * Get certificates for other applications statistics (duplicates)
     *
     * @param array $a_filter
     * @return array
     */
    public static function getCertificatesOther(array $a_filter = null)
    {
        include_once "Services/ADN/ES/classes/class.adnCertificate.php";
        return adnCertificate::getDuplicateStatistics(
            $a_filter["date"]["from"],
            $a_filter["date"]["to"],
            $a_filter["wmo"]
        );
    }

    /**
     * Get certificates total statistics
     *
     * @param array $a_filter
     * @return array
     */
    public static function getCertificatesTotal(array $a_filter = null)
    {
        include_once "Services/ADN/ES/classes/class.adnCertificate.php";
        $res = adnCertificate::getTotalStatistics(
            $a_filter["date"]["from"],
            $a_filter["date"]["to"],
            $a_filter["wmo"]
        );
        
        // every year should return a value
        $from = (int) substr($a_filter["date"]["from"]->get(IL_CAL_DATE), 0, 4);
        $to = (int) substr($a_filter["date"]["to"]->get(IL_CAL_DATE), 0, 4);
        for ($year = $from; $year <= $to; $year++) {
            if (!isset($res[$year])) {
                $res[$year] = 0;
            }
        }
        
        return $res;
    }

    /**
     * Get certificates gas/chem statistics
     *
     * @param array $a_filter
     * @return array
     */
    public static function getCertificatesGasChem(array $a_filter = null)
    {
        include_once "Services/ADN/ES/classes/class.adnCertificate.php";
        $types = array(adnCertificate::CHEMICALS, adnCertificate::GAS);
        return adnCertificate::getTypeStatistics(
            $a_filter["date"]["from"],
            $a_filter["date"]["to"],
            $a_filter["wmo"],
            $types
        );
    }
}
