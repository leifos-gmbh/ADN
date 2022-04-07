<?php
/* Copyright (c) 2010 Leifos, GPL, see docs/LICENSE */

include_once "Services/ADN/ED/classes/class.adnExaminationQuestion.php";

/**
 * Case question application class
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.de>
 * @version $Id: class.adnCaseQuestion.php 27883 2011-02-27 19:30:41Z akill $
 *
 * @ingroup ServicesADN
 */
class adnCaseQuestion extends adnExaminationQuestion
{
    protected string $answer = '';
    protected bool $good_specific = false;
    /**
     * @var int[]
     */
    protected array $goods = [];

    /**
     * Set default answer
     *
     * @param string $a_answer
     */
    public function setDefaultAnswer($a_answer)
    {
        $this->answer = (string) $a_answer;
    }

    /**
     * Get default answer
     *
     * @return string
     */
    public function getDefaultAnswer()
    {
        return $this->answer;
    }

    /**
     * Set good specific (will reset goods if set to false)
     *
     * @param bool $a_specific
     */
    public function setGoodSpecific($a_specific)
    {
        $this->good_specific = (bool) $a_specific;

        if (!$this->good_specific) {
            $this->setGoods([]);
        }
    }

    /**
     * Is question specific for certain goods?
     *
     * @return bool
     */
    public function isGoodSpecific()
    {
        return $this->good_specific;
    }

    /**
     * Set goods
     *
     * @param array $a_goods
     */
    public function setGoods($a_goods)
    {
        $this->goods = $a_goods;
    }

    /**
     * Get goods
     *
     * @return array
     */
    public function getGoods()
    {
        return $this->goods;
    }

    /**
     * Is question active for given good?
     *
     * @param int $a_id good id
     * @return bool
     */
    public function hasGood($a_id)
    {
        if (is_array($this->goods) && in_array($a_id, $this->goods)) {
            return true;
        }
        return false;
    }

    /**
     * Read db entry
     */
    public function read()
    {
        global $ilDB;

        $id = $this->getId();
        if (!$id) {
            return;
        }

        $res = $ilDB->query("SELECT default_answer,good_specific_question" .
            " FROM adn_ed_question_case" .
            " WHERE ed_question_id = " . $ilDB->quote($id, "integer"));
        $set = $ilDB->fetchAssoc($res);
        $this->setDefaultAnswer($set["default_answer"]);
        $this->setGoodSpecific($set["good_specific_question"]);

        // read goods from sub-table
        $res = $ilDB->query("SELECT ed_good_id" .
            " FROM adn_ed_quest_case_good WHERE ed_question_id = " .
            $ilDB->quote($this->getId(), "integer"));
        $goods = array();
        while ($row = $ilDB->fetchAssoc($res)) {
            $goods[] = $row["ed_good_id"];
        }
        $this->setGoods($goods);

        parent::read();
    }

    /**
     * Convert properties to DB fields
     *
     * @return array (ed_question_id, default_answer, good_specific_question)
     */
    protected function CasePropertiesToFields()
    {
        $fields = array("ed_question_id" => array("integer", $this->getId()),
            "default_answer" => array("text", $this->getDefaultAnswer()),
            "good_specific_question" => array("integer", $this->isGoodSpecific()));

        return $fields;
    }

    /**
     * Create new db entry
     *
     * @return int new id
     */
    public function save()
    {
        global $ilDB;

        parent::save();
    
        $fields = $this->CasePropertiesToFields();
        
        $ilDB->insert("adn_ed_question_case", $fields);

        $this->saveGoods();

        return $this->getId();
    }

    /**
     * Update db entry
     *
     * @return bool
     */
    public function update()
    {
        global $ilDB;
        
        $id = $this->getId();
        if (!$id) {
            return;
        }

        $fields = $this->CasePropertiesToFields();
        
        $ilDB->update(
            "adn_ed_question_case",
            $fields,
            array("ed_question_id" => array("integer", $id))
        );

        $this->saveGoods();

        parent::update();

        return true;
    }

    /**
     * Delete from DB
     *
     * @param bool $a_force do not use archived flag in any case
     * @return bool
     */
    public function delete($a_force = false)
    {
        global $ilDB;

        $id = $this->getId();
        if ($id) {
            // U.PV.5.4: always set flag?
            if (!$a_force) {
                $this->setArchived(true);
                $this->update();
            } else {
                // remove good related answers
                include_once "Services/ADN/ED/classes/class.adnGoodRelatedAnswer.php";
                $all = adnGoodRelatedAnswer::getAllAnswers($id);
                if (is_array($all) && count($all) > 0) {
                    foreach ($all as $item) {
                        $answer = new adnGoodRelatedAnswer($item["id"]);
                        $answer->delete();
                    }
                }
        
                $ilDB->manipulate("DELETE FROM adn_ed_quest_case_good WHERE ed_question_id = " .
                    $ilDB->quote($id, "integer"));
                $ilDB->manipulate("DELETE FROM adn_ed_question_case WHERE ed_question_id = " .
                    $ilDB->quote($id, "integer"));
                
                parent::delete();
            }
            return true;
        }
    }

    /**
     * Save goods (in subtable)
     */
    protected function saveGoods()
    {
        global $ilDB;

        $id = $this->getId();
        if ($id) {
            $ilDB->manipulate("DELETE FROM adn_ed_quest_case_good" .
                " WHERE ed_question_id = " . $ilDB->quote($id, "integer"));

            if (is_array($this->goods) && count($this->goods) > 0) {
                foreach ($this->goods as $good_id) {
                    $fields = array("ed_question_id" => array("integer", $id),
                        "ed_good_id" => array("integer", $good_id));

                    $ilDB->insert("adn_ed_quest_case_good", $fields);
                }
            }
        }
    }

    /**
     * Check if good is used by any question
     *
     * @param int $a_id good id
     * @return bool
     */
    public static function findByGood($a_id)
    {
        global $ilDB;

        $res = $ilDB->query("SELECT ed_question_id" .
            " FROM adn_ed_quest_case_good" .
            " WHERE ed_good_id = " . $ilDB->quote((int) $a_id, "integer"));
        return (bool) $ilDB->numRows($res);
    }
}
