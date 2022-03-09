<?php
/* Copyright (c) 2010 Leifos, GPL, see docs/LICENSE */

include_once("./Services/Table/classes/class.ilTable2GUI.php");

/**
 * ADN question target number objectives table GUI class
 *
 * List all objectives/questions (for area and type)
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @version $Id: class.adnQuestionTargetNumbersObjectiveTableGUI.php 27883 2011-02-27 19:30:41Z akill $
 *
 * @ingroup ServicesADN
 */
class adnQuestionTargetNumbersObjectiveTableGUI extends ilTable2GUI
{
    protected $area_id; // [string]
    protected $type; // [string]
    protected $entry; // [object]
    protected $mode; // [string]

    /**
     * Constructor
     *
     * @param object $a_parent_obj parent gui object
     * @param string $a_parent_cmd parent default command
     * @param string $a_area_id parent subject area
     * @param string $a_type parent type
     * @param object $a_entry parent entry
     * @param string $a_mode form mode
     */
    public function __construct(
        $a_parent_obj,
        $a_parent_cmd,
        $a_area_id,
        $a_type,
        $a_entry = null,
        $a_mode = false
    )
    {
        global $ilCtrl, $lng, $ilToolbar;

        $this->area_id = (string) $a_area_id;
        $this->type = (string) $a_type;
        $this->entry = $a_entry;
        $this->mode = (string) $a_mode;

        parent::__construct($a_parent_obj, $a_parent_cmd);

        $this->setId("adn_ed_qtno");

        $this->setLimit(9999);
        $this->disable("footer");

        $this->setTitle($lng->txt("adn_target_nr_of_questions"));

        $this->addColumn($this->lng->txt("adn_objective"));

        $this->setFormAction($ilCtrl->getFormAction($a_parent_obj));
        $this->setRowTemplate("tpl.target_nr_objective_row.html", "Services/ADN/ED");

        $this->importData();


        // form

        $post = null;
        if (isset($_POST["number"])) {
            $post = array("number" => (int) $_POST["number"],
                "single" => (bool) $_POST["single"]);
        }

        if (adnPerm::check(adnPerm::ED, adnPerm::WRITE)) {
            $ilToolbar->setFormAction($ilCtrl->getFormAction($a_parent_obj));
            $ilToolbar->setCloseFormTag(false);

            include_once "Services/Form/classes/class.ilPropertyFormGUI.php";
            $number = new ilTextInputGUI($lng->txt("adn_target_number") .
                " <span class=\"asterisk\">*</span>", "number");
            $number->setRequired(true);
            $number->setSize(2);
            $number->setMaxLength(2);
            $ilToolbar->addInputItem($number, $lng->txt("adn_target_number"));

            $max = new ilCheckboxInputGUI($lng->txt("adn_max_one_per_objective"), "single");
            $ilToolbar->addInputItem($max, $lng->txt("adn_max_one_per_objective"));
        }

        // creation: save/cancel buttons and title
        if ($this->mode == "create") {
            if (adnPerm::check(adnPerm::ED, adnPerm::WRITE)) {
                if ($post) {
                    if ($post["number"]) {
                        $number->setValue($post["number"]);
                    }
                    $max->setChecked($post["single"]);
                }
                
                $ilToolbar->addFormButton($lng->txt("save"), "saveTarget");
                $ilToolbar->addFormButton($lng->txt("cancel"), "listTargets");
            }
            
            $this->setTitle($lng->txt("adn_add_question_target_number"));
        } else {
            if (adnPerm::check(adnPerm::ED, adnPerm::WRITE)) {
                if (!$post) {
                    $number->setValue($this->entry->getNumber());
                    $max->setChecked($this->entry->isSingle());
                } else {
                    if ($post["number"]) {
                        $number->setValue($post["number"]);
                    }
                    $max->setChecked($post["single"]);
                }

                // editing: update/cancel buttons and title
                $ilToolbar->addFormButton($lng->txt("save"), "updateTarget");
                $ilToolbar->addFormButton($lng->txt("cancel"), "listTargets");
            }
            
            $this->setTitle($lng->txt("adn_edit_question_target_number"));
        }
    }

    /**
     * Import data from DB
     */
    protected function importData()
    {
        include_once "Services/ADN/ED/classes/class.adnCatalogNumbering.php";
        include_once "Services/ADN/ED/classes/class.adnObjective.php";
        include_once "./Services/ADN/ED/classes/class.adnSubobjective.php";
        if ($this->area_id == adnSubjectArea::GAS) {
            $catalog = adnCatalogNumbering::getGasAreas();
        } elseif ($this->area_id == adnSubjectArea::CHEMICAL) {
            $catalog = adnCatalogNumbering::getChemicalsAreas();
        } else {
            $catalog = adnCatalogNumbering::getBaseAreas();
        }

        // reload data
        $active_obj = $active_sobj = array();
        if (isset($_POST["number"])) {
            if ($_POST["objective_id"]) {
                foreach ($_POST["objective_id"] as $obj_id) {
                    $active_obj[] = $obj_id;
                }
            }
            if ($_POST["subobjective_id"]) {
                foreach ($_POST["subobjective_id"] as $sobj_id) {
                    $active_sobj[] = $sobj_id;
                }
            }
        } elseif ($this->mode != "create") {
            foreach ($this->entry->getObjectives() as $item) {
                if ($item["ed_subobjective_id"]) {
                    $active_sobj[] = $item["ed_subobjective_id"];
                } else {
                    $active_obj[] = $item["ed_objective_id"];
                }
            }
        }

        // build (sub-)objective & questions tree
        $data = array();
        $filter = array("catalog_area" => $catalog, "type" => $this->type);
        foreach (adnObjective::getAllObjectives($filter) as $obj_data) {
            $obj_id = $obj_data["id"];
            $obj_name = $obj_data["nr"] . " " . $obj_data["name"];

            $objective = new adnObjective($obj_id);
            $catalog_area = $objective->getCatalogArea();

            $data[$catalog_area]["obj_" . $obj_id] = array("id" => $obj_id,
                "name" => $obj_name,
                "type" => "objective",
                "checked" => in_array($obj_id, $active_obj),
                "indent" => "<span style=\"margin-left:25px;\">&nbsp;</span>");

            // subojectives
            $sobj = adnSubobjective::getAllSubobjectives($obj_id);
            if ($sobj) {
                // disable parent objective checkbox
                $data[$catalog_area]["obj_" . $obj_id]["id"] = "";
                
                foreach ($sobj as $sobj_data) {
                    $sobj_id = $sobj_data["id"];
                    $sobj_name = $sobj_data["nr"] . " " . $sobj_data["name"];

                    $data[$catalog_area]["sobj_" . $sobj_id] = array("id" => $sobj_id,
                        "name" => $sobj_name,
                        "type" => "subobjective",
                        "checked" => in_array($sobj_id, $active_sobj),
                        "indent" => "<span style=\"margin-left:50px;\">&nbsp;</span>");
                }
            }
        }

        ksort($data);
        foreach ($data as $area => $items) {
            $res[] = array("id" => "",
                "name" => adnCatalogNumbering::getAreaTextRepresentation($area));

            foreach ($items as $item) {
                $res[] = $item;
            }
        }

        $this->setData($res);
        $this->setMaxCount(sizeof($res));
    }

    /**
     * Fill table row
     *
     * @param array $a_set data array
     */
    protected function fillRow($a_set)
    {
        if ($a_set["id"] && adnPerm::check(adnPerm::ED, adnPerm::WRITE)) {
            $this->tpl->setCurrentBlock("checkbox");
            $this->tpl->setVariable("VAL_ID", $a_set["id"]);
            $this->tpl->setVariable("VAL_TYPE", $a_set["type"]);

            if ($a_set["checked"]) {
                $this->tpl->setVariable("VAL_CHECKED", " checked=\"checked\"");
            }

            $this->tpl->parseCurrentBlock();
        }
        
        $this->tpl->setVariable("VAL_NAME", $a_set["name"]);
        $this->tpl->setVariable("VAL_AREA", $a_set["area"]);
        $this->tpl->setVariable("INDENT", $a_set["indent"]);
    }
}
