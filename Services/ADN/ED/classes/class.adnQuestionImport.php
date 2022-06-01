<?php
/* Copyright (c) 2010 Leifos, GPL, see docs/LICENSE */

include_once "Services/ADN/ED/classes/class.adnObjective.php";
include_once "./Services/ADN/ED/classes/class.adnSubobjective.php";
include_once "Services/ADN/ED/classes/class.adnExaminationQuestion.php";
include_once "Services/ADN/ED/classes/class.adnMCQuestion.php";
include_once "Services/ADN/ED/classes/class.adnCaseQuestion.php";
include_once "Services/ADN/ED/classes/class.adnGoodRelatedAnswer.php";
include_once "Services/ADN/ED/classes/class.adnQuestionTargetNumbers.php";
include_once "Services/ADN/ED/classes/class.adnCatalogNumbering.php";
include_once "Services/ADN/ED/classes/class.adnGoodInTransit.php";
include_once "Services/ADN/ED/classes/class.adnGoodInTransitCategory.php";
include_once "Services/ADN/ED/classes/class.adnQuestionExport.php";

/**
 * (MC) question import application class
 *
 * Imports xml structure into database, this relies fully on the application classes, no direct sql
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.de>
 * @version $Id: class.adnQuestionImport.php 61425 2015-12-09 09:55:28Z jluetzen $
 *
 * @ingroup ServicesADN
 */
class adnQuestionImport
{
    /**
     * @var array<int, array<string, mixed>>
     */
    protected array $objectives;
    /**
     * @var array<int, array<string, int>>
     */
    protected array $subobjectives;
    /**
     * @var string[]
     */
    protected array $good_categories;
    /**
     * @var int[]
     */
    protected array $goods;
    /**
     * @var string[]
     */
    protected array $files;
    /**
     * @var array<string, array<string, array<int, mixed>>>
     */
    protected array $log;

    protected ilDBInterface $db;

    public function __construct()
    {
        global $DIC;
        $this->db = $DIC->database();
    }
    /**
     * Import mc question data from zip file (or string)
     *
     * @param string $a_tmp_file
     * @param bool $a_mc
     * @param bool $a_case
     * @param bool $a_objectives
     * @param bool $a_targets
     * @param bool $a_goods
     * @param bool $a_delete_all
     * @param bool $a_update
     * @param bool $a_dry_run
     * @return bool
     */
    public function processImport(
        $a_tmp_file,
        $a_mc,
        $a_case,
        $a_objectives = false,
        $a_targets = false,
        $a_goods = false,
        $a_delete_all = false,
        $a_update = false,
        $a_dry_run = true
    )
    {
        $zip = new ZipArchive;
        if ($zip->open((string) $a_tmp_file)) {
            $data = new SimpleXMLElement($zip->getFromName("adn_export.xml"));
            $a_update = (bool) $a_update;
            $a_dry_run = (bool) $a_dry_run;

            // delete existing data
            if ((bool) $a_delete_all && !$a_dry_run) {
                if ((bool) $a_mc) {
                    $this->deleteMCQuestions();
                }
                if ((bool) $a_case) {
                    $this->deleteCaseQuestions();
                }
                if ((bool) $a_objectives) {
                    $this->deleteObjectives();
                }
                if ((bool) $a_targets) {
                    $this->deleteTargets();
                }
                if ((bool) $a_goods) {
                    $this->deleteGoods();
                }

                // @todo: delete all files
            }

            // import goods
            if ((bool) $a_goods) {
                $this->importGoodCategories(
                    $data->good_categories->good_category,
                    $a_delete_all,
                    $a_update,
                    $a_dry_run
                );
                $this->importGoods($data->goods->good, $a_delete_all, $a_update, $a_dry_run);
            }
            // map sequences to un-nr
            else {
                $this->buildGoodsMap();
            }
            // import (sub-)objectives
            if ((bool) $a_objectives) {
                $this->importObjectives(
                    $data->objectives->objective,
                    $a_delete_all,
                    $a_update,
                    $a_dry_run
                );
                $this->importSubobjectives(
                    $data->subobjectives->subobjective,
                    $a_delete_all,
                    $a_update,
                    $a_dry_run
                );
            }
            // map sequences to numbers
            else {
                $this->buildObjectiveMap();
            }
            if ((bool) $a_targets) {
                $this->importTargets(
                    $data->target_numbers->area,
                    $a_delete_all,
                    $a_update,
                    $a_dry_run
                );
            }
            if ((bool) $a_mc) {
                $this->importMCQuestions(
                    $data->questions_mc->question,
                    $a_delete_all,
                    $a_update,
                    $a_dry_run
                );
            }
            if ((bool) $a_case) {
                $this->importCaseQuestions(
                    $data->questions_case->question,
                    $a_delete_all,
                    $a_update,
                    $a_dry_run
                );
            }
            $this->importFiles($zip, $a_delete_all, $a_update, $a_dry_run);
             
            if ($a_dry_run) {
                return $this->log;
            }
            return true;
        }
        return false;
    }

    /**
     * Delete all goods and good categories
     */
    protected function deleteGoods()
    {
        foreach (adnGoodInTransit::getGoodsSelect(null, null, null, true) as $id => $caption) {
            $good = new adnGoodInTransit($id);
            $good->delete(true);
        }
        foreach (adnGoodInTransitCategory::getCategoriesSelect() as $id => $caption) {
            $cat = new adnGoodInTransitCategory($id);
            $cat->delete();
        }
    }

    /**
     * Delete all (sub-)objectives
     */
    protected function deleteObjectives()
    {
        foreach (adnObjective::getObjectivesSelect() as $id => $caption) {
            $obj = new adnObjective($id);
            $obj->delete();
        }
    }

    /**
     * Delete all mc questions
     */
    protected function deleteMCQuestions()
    {
        $areas = array_keys(adnCatalogNumbering::getMCAreas());
        foreach ($areas as $area) {
            $all = adnExaminationQuestion::getAllQuestions(array("catalog_area" => $area), false, false, null, null, null, null, false);
            foreach ($all as $item) {
                $quest = new adnMCQuestion($item["id"]);
                $quest->delete(true);
            }
        }
    }
    
    /**
     * Delete all case questions
     */
    protected function deleteCaseQuestions()
    {
        $areas = array_keys(adnCatalogNumbering::getCaseAreas());
        foreach ($areas as $area) {
            $all = adnExaminationQuestion::getAllQuestions(array("catalog_area" => $area), true, false, null, null, null, null, false);
            foreach ($all as $item) {
                $quest = new adnCaseQuestion($item["id"]);
                $quest->delete(true);
            }
        }
    }

    /**
     * Delete all questions targets
     */
    protected function deleteTargets()
    {

        // delete totals
        $this->db->manipulate("DELETE FROM adn_ed_question_total");

        foreach (adnQuestionTargetNumbers::getAllTargets() as $item) {
            $target = new adnQuestionTargetNumbers($item["id"]);
            $target->delete(true);
        }
    }

    /**
     * Import good categories from XML
     *
     * @param SimpleXMLElement $data
     * @param bool $a_delete_all
     * @param bool $a_update
     * @param bool $a_dry_run
     */
    protected function importGoodCategories(
        SimpleXMLElement $data,
        $a_delete_all,
        $a_update,
        $a_dry_run
    )
    {
        $map = array();
        foreach (adnGoodInTransitCategory::getCategoriesSelect() as $id => $caption) {
            $map[$caption] = $id;
        }

        foreach ($data as $category) {
            $valid = true;
            
            $old_id = false;
            $name = (string) $category->name;
            $type = (int) $category->type;
            if (isset($map[$name])) {
                if (!$a_update && (!$a_delete_all || !$a_dry_run)) {
                    $valid = false;
                } else {
                    $old_id = $map[$name];
                }
            }

            if ($valid) {
                $cat = new adnGoodInTransitCategory($old_id);
                $cat->setName($name);
                $cat->setType($type);

                if (!$a_dry_run) {
                    if ($old_id) {
                        if ($cat->update()) {
                            $this->good_categories[$name] = $old_id;
                        } else {
                            $valid = false;
                        }
                    } else {
                        if ($cat->save()) {
                            $this->good_categories[$name] = $cat->getId();
                        } else {
                            $valid = false;
                        }
                    }
                } else {
                    $this->good_categories[$name] = 99;
                }
            }
            
            if ($valid) {
                $this->log["good_category"]["valid"][] = $name;
            } else {
                $this->log["good_category"]["invalid"][] = $name;
            }
        }
    }

    /**
     * Import goods from XML
     *
     * @param SimpleXMLElement $data
     * @param bool $a_delete_all
     * @param bool $a_update
     * @param bool $a_dry_run
     */
    protected function importGoods(SimpleXMLElement $data, $a_delete_all, $a_update, $a_dry_run)
    {
        $map = array();
        foreach (adnGoodInTransit::getAllGoods() as $item) {
            $map[(int) $item["un_nr"]] = $item["id"];
        }

        foreach ($data as $good) {
            $valid = true;
            $old_id = false;

            $number = (int) $good->number;
            $category = (string) $good->category;
            if ($category && !isset($this->good_categories[$category])) {
                $valid = false;
            } else {
                if (isset($map[$number])) {
                    if (!$a_update && (!$a_delete_all || !$a_dry_run)) {
                        $valid = false;
                    } else {
                        $old_id = $map[$number];
                    }
                }

                if ($valid) {
                    $git = new adnGoodInTransit($old_id);
                    $git->setType((int) $good->type);
                    $git->setName((string) $good->name);
                    $git->setClass((string) $good->class);
                    $git->setClassCode((string) $good->class_code);
                    $git->setPackingGroup((string) $good->packing_group);

                    if (!$a_dry_run) {
                        if ($old_id) {
                            if (!$git->update()) {
                                $valid = false;
                            } else {
                                $this->goods[$number] = $old_id;
                            }
                        } else {
                            $git->setNumber($number);
                            $git->setCategory($this->good_categories[$category]);

                            if (!$git->save()) {
                                $valid = false;
                            } else {
                                $this->goods[$number] = $git->getId();
                            }
                        }
                    } else {
                        $this->goods[$number] = 99;
                    }
                }
            }

            if ($valid) {
                $this->log["good"]["valid"][] = $number;
            } else {
                $this->log["good"]["invalid"][] = $number;
            }
        }
    }

    /**
     * Map good ids to un-nr
     */
    protected function buildGoodsMap()
    {
        foreach (adnGoodInTransit::getAllGoods() as $item) {
            $this->goods[$item["un_nr"]] = $item["id"];
        }
    }

    /**
     * Import question target numbers (incl. overall)
     *
     * @param SimpleXMLElement $data
     * @param bool $a_delete_all
     * @param bool $a_update
     * @param bool $a_dry_run
     */
    protected function importTargets(SimpleXMLElement $data, $a_delete_all, $a_update, $a_dry_run)
    {
        foreach ($data as $area) {
            $area_id = (string) $area["id"];
            foreach ($area->type as $type) {
                $type_id = (int) $type["id"];
                
                // save overall
                if (!$a_dry_run) {
                    adnQuestionTargetNumbers::saveOverall($area_id, $type_id, (int) $type["overall"]);
                }

                if ($type->target) {
                    foreach ($type->target as $target) {
                        $valid = true;

                        $target_obj = new adnQuestionTargetNumbers();
                        $target_obj->setArea($area_id);
                        $target_obj->setType($type_id);
                        $target_obj->setNumber((int) $target->nr);
                        $target_obj->setSingle((bool) (string) $target->max_one);

                        $obj = (string) $target->objectives;
                        $sobj = (string) $target->subobjectives;
                        $objectives = array();
                        if ($obj) {
                            foreach (explode(";", $obj) as $obj_nr) {
                                if (!isset($this->objectives[$obj_nr])) {
                                    $valid = false;
                                } else {
                                    $objectives[] = array("ed_objective_id" =>
                                        $this->objectives[$obj_nr],
                                        "ed_subobjective_id" => null);
                                }
                            }
                        }
                        if ($sobj) {
                            foreach (explode(";", $sobj) as $sobj_nr) {
                                // not the best way, but as value is normalized...
                                $obj_nr = array_shift(explode(".", $sobj_nr));
                                
                                if (!isset($this->objectives[$obj_nr]) ||
                                    !isset($this->subobjectives[$sobj_nr])) {
                                    $valid = false;
                                } else {
                                    $objectives[] = array("ed_objective_id" =>
                                        $this->objectives[$obj_nr],
                                        "ed_subobjective_id" => $this->subobjectives[$sobj_nr]);
                                }
                            }
                        }
                        $target_obj->setObjectives($objectives);

                        if (!$a_dry_run) {
                            // we cannot update - there is no clear identifier
                            if (!$target_obj->save()) {
                                $valid = false;
                            }
                        }

                        if ($valid) {
                            $this->log["target"]["valid"][] = $area_id . "/" . $type_id;
                        } else {
                            $this->log["target"]["invalid"][] = $area_id . "/" . $type_id;
                        }
                    }
                }
            }
        }
    }

    /**
     * Map (sub-)objective sequences to numbers
     */
    protected function buildObjectiveMap()
    {
        foreach (adnObjective::getAllObjectives() as $item) {
            $number = adnQuestionExport::buildNumber($item["catalog_area"], $item["nr"]);
            $this->objectives[$number] = $item["id"];

            $subs = adnSubobjective::getAllSubobjectives($item["id"]);
            if ($subs) {
                foreach ($subs as $sitem) {
                    $snumber = adnQuestionExport::buildNumber(
                        $item["catalog_area"],
                        $item["nr"],
                        $sitem["nr"]
                    );
                    $this->subobjectives[$snumber] = $sitem["id"];
                }
            }
        }
    }

    /**
     * Import objectives from XML
     *
     * @param SimpleXMLElement $data
     * @param bool $a_delete_all
     * @param bool $a_update
     * @param bool $a_dry_run
     */
    protected function importObjectives(
        SimpleXMLElement $data,
        $a_delete_all,
        $a_update,
        $a_dry_run
    )
    {
        foreach ($data as $objective) {
            $valid = true;
            $adn_number = (string) $objective->number;

            $number = $this->parseObjectiveNumber($adn_number);
            if ($number) {
                $old_id = $this->getObjectiveByNumber($number);
                if ($old_id && !$a_update && (!$a_delete_all || !$a_dry_run)) {
                    $valid = false;
                } else {
                    $obj = new adnObjective($old_id);
                    $obj->setName((string) $objective->title);
                    $obj->setTopic((string) $objective->topic);

                    if (!$a_dry_run) {
                        if ($old_id) {
                            if ($obj->update()) {
                                $this->objectives[$adn_number] = $old_id;
                            } else {
                                $valid = false;
                            }
                        } else {
                            // only if new entry
                            $obj->setCatalogArea($number["catalog_area"]);
                            $obj->setNumber($number["objective"]);
                            $obj->setType((int) $objective->type);
                            
                            if ($obj->save()) {
                                $this->objectives[$adn_number] = $obj->getId();
                            } else {
                                $valid = false;
                            }
                        }
                    } else {
                        $this->objectives[$adn_number] = 99;
                    }
                }
            } else {
                $valid = false;
            }

            if ($valid) {
                $this->log["obj"]["valid"][] = $adn_number;
            } else {
                $this->log["obj"]["invalid"][] = $adn_number;
            }
        }
    }

    /**
     * Get objective id by adn number
     *
     * @param array $number
     * @return int
     */
    protected function getObjectiveByNumber(array $a_number)
    {
        $unique = adnObjective::getAllObjectives(array("catalog_area" => $a_number["catalog_area"],
            "nr" => array("from" => $a_number["objective"], "to" => $a_number["objective"])));
        if (sizeof($unique) == 1) {
            return $unique[0]["id"];
        }
    }

    /**
     * Get subobjective id by adn number
     *
     * @param array $number
     * @return int
     */
    protected function getSubobjectiveByNumber(array $a_number)
    {
        $obj_id = $this->getObjectiveByNumber($a_number);
        if ($obj_id) {
            $unique = adnSubobjective::getAllSubobjectives($obj_id, $a_number["subobjective"]);
            if (sizeof($unique) == 1) {
                return $unique[0]["id"];
            }
        }
    }

    /**
     * Get question id by adn number
     *
     * @param array $number
     * @return int
     */
    protected function getQuestionByNumber(array $a_number)
    {
        if ($a_number["subobjective"]) {
            $sobj_id = $this->getSubobjectiveByNumber($a_number);
            $unique = adnExaminationQuestion::getBySubobjective($sobj_id, $a_number["nr"]);
        } else {
            $obj_id = $this->getObjectiveByNumber($a_number);
            $unique = adnExaminationQuestion::getByObjective($obj_id, $a_number["nr"]);
        }
        if (sizeof($unique) == 1) {
            return $unique[0];
        }
    }

    /**
     * Import subobjectives from XML
     *
     * @param SimpleXMLElement $data
     * @param bool $a_delete_all
     * @param bool $a_update
     * @param bool $a_dry_run
     */
    protected function importSubobjectives(
        SimpleXMLElement $data,
        $a_delete_all,
        $a_update,
        $a_dry_run
    )
    {
        foreach ($data as $subobjective) {
            $valid = true;
            $adn_number = (string) $subobjective->number;

            $number = $this->parseSubobjectiveNumber($adn_number);
            if ($number) {
                $old_id = $this->getSubobjectiveByNumber($number);
                if ($old_id && !$a_update && (!$a_delete_all || !$a_dry_run)) {
                    $valid = false;
                } else {
                    $sobj = new adnSubobjective($old_id);
                    $sobj->setName((string) $subobjective->title);
                    $sobj->setTopic((string) $subobjective->topic);

                    if (!$a_dry_run) {
                        if ($old_id) {
                            if ($sobj->update()) {
                                $this->subobjectives[$adn_number] = $old_id;
                            } else {
                                $valid = false;
                            }
                        } else {
                            // only if new entry
                            $obj_id = adnQuestionExport::buildNumber(
                                $number["catalog_area"],
                                $number["objective"]
                            );
                            $sobj->setObjective($this->objectives[$obj_id]);
                            $sobj->setNumber($number["subobjective"]);

                            if ($sobj->save()) {
                                $this->subobjectives[$adn_number] = $sobj->getId();
                            } else {
                                $valid = false;
                            }
                        }
                    } else {
                        $this->subobjectives[$adn_number] = 99;
                    }
                }
            } else {
                $valid = false;
            }

            if ($valid) {
                $this->log["sobj"]["valid"][] = $adn_number;
            } else {
                $this->log["sobj"]["invalid"][] = $adn_number;
            }
        }
    }

    /**
     * Import mc question from XML
     *
     * @param SimpleXMLElement $data
     * @param bool $a_delete_all
     * @param bool $a_update
     * @param bool $a_dry_run
     */
    protected function importMCQuestions(
        SimpleXMLElement $data,
        $a_delete_all,
        $a_update,
        $a_dry_run
    )
    {
        foreach ($data as $question) {
            $valid = true;
            $adn_number = (string) $question->number;

            $number = $this->parseQuestionNumber($adn_number);
            if ($number) {
                $old_id = $this->getQuestionByNumber($number);
                if ($old_id && !$a_update && (!$a_delete_all || !$a_dry_run)) {
                    $valid = false;
                } else {
                    $correct = (string) $question->correct;
                    if (!in_array($correct, array("a", "b", "c", "d"))) {
                        $valid = false;
                    } else {
                        // to be able to import invalid questions
                        $title = (string) $question->title;
                        if (!$title) {
                            $title = " ";
                        }

                        $quest = new adnMCQuestion($old_id);
                        
                        if (!$a_dry_run &&
                            $old_id) {
                            // backup old version first - question id will not change
                            $this->backupMCQuestion($quest);
                        }
                        
                        $quest->setName($title);
                        $quest->setQuestion((string) $question->text);
                        $quest->setStatus((bool) (string) $question->status);
                        $quest->setCorrectAnswer($correct);
                        $quest->setFileName((string) $question->image, 1);

                        $quest->setAnswerA(
                            (string) $question->answers->A->text,
                            (string) $question->answers->A->image
                        );
                        $quest->setAnswerB(
                            (string) $question->answers->B->text,
                            (string) $question->answers->B->image
                        );
                        $quest->setAnswerC(
                            (string) $question->answers->C->text,
                            (string) $question->answers->C->image
                        );
                        $quest->setAnswerD(
                            (string) $question->answers->D->text,
                            (string) $question->answers->D->image
                        );

                        if (!$a_dry_run) {
                            $file_old_id = (string) $question->image_id;

                            if ($old_id) {
                                if ($quest->update()) {
                                    $this->files[$file_old_id] = $old_id;
                                } else {
                                    $valid = false;
                                }
                            } else {
                                // only if new entry
                                $obj_id = adnQuestionExport::buildNumber(
                                    $number["catalog_area"],
                                    $number["objective"]
                                );
                                $sobj_id = null;
                                if ($number["subobjective"]) {
                                    $sobj_id = adnQuestionExport::buildNumber(
                                        $number["catalog_area"],
                                        $number["objective"],
                                        $number["subobjective"]
                                    );
                                }
                                if ($sobj_id) {
                                    $quest->setSubobjective($this->subobjectives[$sobj_id]);
                                } else {
                                    $quest->setObjective($this->objectives[$obj_id]);
                                }
                                $quest->setNumber($number["nr"]);

                                if ($quest->save()) {
                                    $this->files[$file_old_id] = $quest->getId();
                                } else {
                                    $valid = false;
                                }
                            }
                        }
                    }
                }
            } else {
                $valid = false;
            }
            
            if ($valid) {
                $this->log["mc_quest"]["valid"][] = $adn_number;
            } else {
                $this->log["mc_quest"]["invalid"][] = $adn_number;
            }
        }
    }
    
    /**
     * Create backup version of existing MC question
     *
     * @param adnMCQuestion $a_question
     */
    protected function backupMCQuestion(adnMCQuestion $a_question)
    {
        // seee adnMCQuestionGUI::updateBackupMCQuestion()
        
        // only 1 backup per question
        $a_question->removeBackups();
        
        $backup = clone $a_question;
        $backup->setBackupOf($a_question->getId());
        $backup->setId(null);
        
        if ($backup->save()) {
            // clone files
            $path = $a_question->getFilePath();
            for ($loop = 1; $loop < 6; $loop++) {
                if ($a_question->getFileName($loop)) {
                    $source = $path . $a_question->getId() . "_" . $loop;
                    $target = $path . $backup->getId() . "_" . $loop;
                    copy($source, $target);
                }
            }
        }
    }

    /**
     * Import case question from XML
     *
     * @param SimpleXMLElement $data
     * @param bool $a_delete_all
     * @param bool $a_update
     * @param bool $a_dry_run
     */
    protected function importCaseQuestions(
        SimpleXMLElement $data,
        $a_delete_all,
        $a_update,
        $a_dry_run
    )
    {
        foreach ($data as $question) {
            $valid = true;
            $adn_number = (string) $question->number;

            $number = $this->parseQuestionNumber($adn_number);
            if ($number) {
                $old_id = $this->getQuestionByNumber($number);
                if ($old_id && !$a_update && (!$a_delete_all || !$a_dry_run)) {
                    $valid = false;
                } else {
                    // to be able to import invalid questions
                    $title = (string) $question->title;
                    if (!$title) {
                        $title = " ";
                    }
                
                    $quest = new adnCaseQuestion($old_id);
                    $quest->setName($title);
                    $quest->setQuestion((string) $question->text);
                    $quest->setStatus((bool) (string) $question->status);
                    $quest->setFileName((string) $question->image, 1);
                    $quest->setDefaultAnswer((string) $question->default_answer);

                    // specific goods
                    $specific = (bool) (string) $question->good_specific;
                    if ($specific) {
                        $quest->setGoodSpecific(true);
                        if (!(string) $question->goods) {
                            $valid = false;
                        } else {
                            $goods = array();
                            foreach (explode(";", (string) $question->goods) as $good_id) {
                                if (!isset($this->goods[$good_id])) {
                                    $valid = false;
                                } else {
                                    $goods[] = $this->goods[$good_id];
                                }
                            }
                            $quest->setGoods($goods);
                        }
                    } else {
                        $quest->setGoodSpecific(false);
                    }

                    // good related answers
                    $answers = array();
                    if ($question->answers) {
                        foreach ($question->answers->answer as $answer) {
                            $text = (string) $answer->text;
                            if (!$text) {
                                $text = " ";
                            }
                            $butan = (int) $answer->butan_or_empty;
                            $goods = array();
                            foreach (explode(";", (string) $answer->goods) as $good_id) {
                                if (!isset($this->goods[$good_id])) {
                                    $valid = false;
                                } else {
                                    $goods[] = $this->goods[$good_id];
                                }
                            }

                            $answers[] = array("text" => $text,
                                "butan_or_empty" => $butan,
                                "goods" => $goods);
                        }
                    }

                    if (!$a_dry_run) {
                        $file_old_id = (string) $question->image_id;

                        if ($old_id) {
                            if ($quest->update()) {
                                $this->files[$file_old_id] = $old_id;
                            } else {
                                $valid = false;
                            }
                        } else {
                            // only if new entry
                            $obj_id = adnQuestionExport::buildNumber(
                                $number["catalog_area"],
                                $number["objective"]
                            );
                            $sobj_id = null;
                            if ($number["subobjective"]) {
                                $sobj_id = adnQuestionExport::buildNumber(
                                    $number["catalog_area"],
                                    $number["objective"],
                                    $number["subobjective"]
                                );
                            }
                            if ($sobj_id) {
                                $quest->setSubobjective($this->subobjectives[$sobj_id]);
                            } else {
                                $quest->setObjective($this->objectives[$obj_id]);
                            }
                            $quest->setNumber($number["nr"]);

                            if ($quest->save()) {
                                $this->files[$file_old_id] = $quest->getId();
                            } else {
                                $valid = false;
                            }
                        }

                        // save answers
                        if ($valid && sizeof($answers)) {
                            // @todo: what about updates? (we are not handling existing data yet)
                            foreach ($answers as $answer) {
                                $answer_obj = new adnGoodRelatedAnswer();
                                $answer_obj->setQuestionId($quest->getId());
                                $answer_obj->setAnswer($answer["text"]);
                                $answer_obj->setButanOrEmpty($answer["butan_or_empty"]);
                                $answer_obj->setGoods($answer["goods"]);
                                $answer_obj->save();
                            }
                        }
                    }
                }
            } else {
                $valid = false;
            }

            if ($valid) {
                $this->log["case_quest"]["valid"][] = $adn_number;
            } else {
                $this->log["case_quest"]["invalid"][] = $adn_number;
            }
        }
    }

    /**
     * Import files from zip archive
     *
     * @param ZipArchive $zip
     * @param bool $a_delete_all
     * @param bool $a_update
     * @param bool $a_dry_run
     */
    protected function importFiles(ZipArchive $zip, $a_delete_all, $a_update, $a_dry_run)
    {
        $path = new adnMCQuestion();
        $path = $path->getFilePath();

        if (is_array($this->files) && count($this->files) > 0) {
            // create target directory
            if (!is_dir($path)) {
                $base = ilUtil::getDataDir();
                ilUtil::createDirectory($base . "/adn");
                ilUtil::createDirectory($base . "/adn/ed_question");
            }

            foreach ($this->files as $old_id => $new_id) {
                for ($loop = 1; $loop < 6; $loop++) {
                    $file = $zip->getFromName("images/" . $old_id . "_" . $loop);
                    if ($file) {
                        $valid = true;
                    
                        $target = $path . $new_id . "_" . $loop;
                        if (file_exists($target) && !$a_update && (!$a_delete_all || !$a_dry_run)) {
                            $valid = false;
                        } elseif (!$a_dry_run) {
                            file_put_contents($target, $file);
                        }
                        $file = null;

                        if ($valid) {
                            $this->log["files"]["valid"][] = $target;
                        } else {
                            $this->log["files"]["invalid"][] = $target;
                        }
                    }
                }
            }
        }
    }

    /**
     * Parse xml objective number to parts
     *
     * @param string $a_number
     * @return array
     */
    protected function parseObjectiveNumber($a_number)
    {
        if (preg_match("/^([0-9]+) ([A-E])$/", trim($a_number), $parts)) {
            $res["catalog_area"] = (int) $parts[1];
            $res["objective"] = (string) $parts[2];
        } elseif (preg_match("/^([0-9]+) ([0-9]+)$/", trim($a_number), $parts)) {
            $res["catalog_area"] = (int) $parts[1];
            $res["objective"] = (int) $parts[2];
        }

        if (adnCatalogNumbering::isValidArea($res["catalog_area"])) {
            return $res;
        }

        return false;
    }

    /**
     * Parse xml subobjective number to parts
     *
     * @param string $a_number
     * @return array
     */
    protected function parseSubobjectiveNumber($a_number)
    {
        if (preg_match("/^([0-9]+) ([0-9]+)\.([0-9]+)$/", trim($a_number), $parts)) {
            $res["catalog_area"] = (int) $parts[1];
            $res["objective"] = (int) $parts[2];
            $res["subobjective"] = (int) $parts[3];

            $adn_number = adnQuestionExport::buildNumber($res["catalog_area"], $res["objective"]);
            if (isset($this->objectives[$adn_number])) {
                return $res;
            }
        }

        return false;
    }

    /**
     * Parse xml question number to parts
     *
     * @param string $a_number
     * @return array
     */
    protected function parseQuestionNumber($a_number)
    {
        // case
        if (preg_match("/^([0-9]+) ([A-E])\-([0-9a-zA-Z\/\-]+)$/", trim($a_number), $parts)) {
            $res["catalog_area"] = (int) $parts[1];
            $res["objective"] = (string) $parts[2];
            $res["subobjective"] = null;
            $res["nr"] = (string) $parts[3];
        }
        // mc
        elseif (preg_match("/^([0-9]+) ([0-9]+)\.([0-9]+)\-([0-9]+)$/", trim($a_number), $parts)) {
            $res["catalog_area"] = (int) $parts[1];
            $res["objective"] = (int) $parts[2];
            $res["subobjective"] = (int) $parts[3];
            $res["nr"] = (int) $parts[4];
        }

        $adn_number = adnQuestionExport::buildNumber($res["catalog_area"], $res["objective"]);
        if (isset($this->objectives[$adn_number])) {
            if ($res["subobjective"]) {
                $adn_number = adnQuestionExport::buildNumber(
                    $res["catalog_area"],
                    $res["objective"],
                    $res["subobjective"]
                );
                if (!isset($this->subobjectives[$adn_number])) {
                    return false;
                }
            }
            return $res;
        }

        return false;
    }
}
