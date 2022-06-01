<?php
/* Copyright (c) 2010 Leifos, GPL, see docs/LICENSE */

/**
 * Case application class
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.de>
 * @version $Id: class.adnCase.php 27867 2011-02-25 09:35:47Z akill $
 *
 * @ingroup ServicesADN
 */
class adnCase extends adnDBBase
{
    protected int $id;
    protected string $subject_area;
    protected bool $butan;
    protected string $text;

    protected ilLanguage $lng;

    /**
     * Constructor
     *
     * @param int $a_id instance id
     */
    public function __construct($a_id = 0)
    {
        global $DIC;
        $this->lng = $DIC->language();

        if ($a_id !== 0) {
            $this->setId($a_id);
            $this->read();
        }
    }

    /**
     * Set id
     *
     * @param int $a_id
     */
    public function setId($a_id)
    {
        $this->id = (int) $a_id;
    }

    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set subject area
     *
     * @param string $a_area
     */
    public function setArea($a_area)
    {
        $this->subject_area = (string) $a_area;
    }

    /**
     * Get subject area
     *
     * @return string
     */
    public function getArea()
    {
        return $this->subject_area;
    }

    /**
     * Set butan
     *
     * @param bool $a_value
     */
    public function setButan($a_value)
    {
        $this->butan = (bool) $a_value;
    }

    /**
     * Get butan
     *
     * @return bool
     */
    public function getButan()
    {
        return $this->butan;
    }

    /**
     * Set text
     *
     * @param string $a_text
     */
    public function setText($a_text)
    {
        $this->text = (string) $a_text;
    }

    /**
     * Get text
     *
     * @return string
     */
    public function getText()
    {
        return $this->text;
    }

    /**
     * Read db entry
     */
    public function read()
    {

        $id = $this->getId();
        if (!$id) {
            return;
        }

        $res = $this->db->query("SELECT subject_area,butan,text" .
            " FROM adn_ed_case" .
            " WHERE id = " . $this->db->quote($this->getId(), "integer"));
        $set = $this->db->fetchAssoc($res);
        $this->setArea($set["subject_area"]);
        $this->setButan($set["butan"]);
        $this->setText($set["text"]);

        parent::_read($id, "adn_ed_case");
    }

    /**
     * Convert properties to DB fields
     *
     * @return array (subject_area, butan, text)
     */
    protected function propertiesToFields()
    {
        $fields = array("subject_area" => array("text", $this->getArea()),
            "butan" => array("integer", $this->getButan()),
            "text" => array("text", $this->getText()));
            
        return $fields;
    }

    /**
     * Create new db entry
     *
     * @return int new id
     */
    public function save()
    {

        // sequence
        $this->setId($this->db->nextId("adn_ed_case"));
        $id = $this->getId();

        $fields = $this->propertiesToFields();
        $fields["id"] = array("integer", $id);

        $this->db->insert("adn_ed_case", $fields);

        parent::_save($id, "adn_ed_case");
        
        return $id;
    }

    /**
     * Update db entry
     *
     * @return bool
     */
    public function update()
    {
        
        $id = $this->getId();
        if (!$id) {
            return;
        }

        $fields = $this->propertiesToFields();

        $this->db->update("adn_ed_case", $fields, array("id" => array("integer", $id)));

        parent::_update($id, "adn_ed_case");

        return true;
    }

    /**
     * Get case id by subject area
     *
     * @param string $a_area
     * @param int $a_butan
     * @return int
     */
    public static function getIdByArea($a_area, $a_butan = false)
    {
        global $DIC;

        $ilDB = $DIC->database();
        
        $res = $ilDB->query("SELECT id" .
            " FROM adn_ed_case" .
            " WHERE subject_area = " . $ilDB->quote($a_area, "text") .
            " AND butan = " . $ilDB->quote($a_butan, "integer"));
        $row = $ilDB->fetchAssoc($res);
        return $row["id"];
    }

    /**
     * Translate placeholders for given answer sheet
     *
     * @param adnAnswerSheet $a_sheet
     * @param adnExaminationEvent $a_event
     * @return string
     */
    public function getTranslatedText(adnAnswerSheet $a_sheet, adnExaminationEvent $a_event = null)
    {
        
        $text = $this->getText();

        if (!$a_event) {
            include_once "Services/ADN/EP/classes/class.adnExaminationEvent.php";
            include_once "Services/ADN/ED/classes/class.adnSubjectArea.php";
            $a_event = new adnExaminationEvent($a_sheet->getEvent());
        }

        // gas
        if ($a_event->getType() == adnSubjectArea::GAS) {
            $good_id = $a_sheet->getNewGood();
            if ($good_id) {
                include_once "Services/ADN/ED/classes/class.adnGoodInTransit.php";
                $good = new adnGoodInTransit($good_id);

                // german only for now
                $text = str_replace("[UN-Nr]", "UN " . $good->getNumber(), $text);
                $text = str_replace("[Bezeichnung]", $good->getName(), $text);
            }
        }
        // chemicals
        else {
            $goods = array(1 => $a_sheet->getPreviousGood(),
                2 => $a_sheet->getNewGood());

            foreach ($goods as $idx => $good_id) {
                if ($good_id) {
                    include_once "Services/ADN/ED/classes/class.adnGoodInTransit.php";
                    $good = new adnGoodInTransit($good_id);

                    // german only for now
                    $text = str_replace("[UN-Nr" . $idx . "]", "UN " . $good->getNumber(), $text);
                    $text = str_replace("[Bezeichnung" . $idx . "]", $good->getName(), $text);
                    $text = str_replace("[Klasse" . $idx . "]", $good->getClass(), $text);
                    $text = str_replace("[Klassifizierungscode" . $idx . "]", $good->getClassCode(), $text);
                    $text = str_replace("[Verpackungsgruppe" . $idx . "]", $good->getPackingGroup(), $text);
                }
                // no previous good
                else {
                    // german only for now
                    $text = str_replace("[UN-Nr" . $idx . "]", "", $text);
                    $text = str_replace("[Bezeichnung" . $idx . "]", $this->lng->txt("adn_no_previous_good"), $text);
                    $text = str_replace("[Klasse" . $idx . "]", "", $text);
                    $text = str_replace("[Klassifizierungscode" . $idx . "]", "", $text);
                    $text = str_replace("[Verpackungsgruppe" . $idx . "]", "", $text);
                }
            }
        }

        return $text;
    }
}
