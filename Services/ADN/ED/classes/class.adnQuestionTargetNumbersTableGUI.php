<?php
/* Copyright (c) 2010 Leifos, GPL, see docs/LICENSE */

include_once("./Services/Table/classes/class.ilTable2GUI.php");

/**
 * ADN question target number for objectives table GUI class
 *
 * List question target numbers (for area and type)
 *
 * @author Alex Killing <killing@leifos.com>
 * @version $Id: class.adnQuestionTargetNumbersTableGUI.php 27874 2011-02-25 16:36:28Z jluetzen $
 *
 * @ingroup ServicesADN
 */
class adnQuestionTargetNumbersTableGUI extends ilTable2GUI
{
    protected string $area_id;
    protected string $type;
    protected int $overall = 0;

    /**
     * Constructor
     *
     * @param object $a_parent_obj parent gui object
     * @param string $a_parent_cmd parent default command
     * @param string $a_area_id parent subject area
     * @param string $a_type parent type
     * @param array $a_targets row data
     */
    public function __construct($a_parent_obj, $a_parent_cmd, $a_area_id, $a_type, array $a_targets)
    {
        global $ilCtrl, $lng;

        $this->area_id = (string) $a_area_id;
        $this->type = (string) $a_type;

        $this->setId("adn_ed_qtn");

        parent::__construct($a_parent_obj, $a_parent_cmd);

        $this->setLimit(9999);
        $this->disable("footer");

        $this->setTitle($lng->txt("adn_target_nr_of_questions"));

        if (adnPerm::check(adnPerm::ED, adnPerm::WRITE)) {
            $this->addMultiCommand("confirmTargetsDeletion", $lng->txt("delete"));
            $this->addColumn("", "", 1);
        }
        
        $this->addColumn($this->lng->txt("adn_objective"), "title");
        $this->addColumn($this->lng->txt("adn_target_number"), "nr_of_questions");
        $this->addColumn($this->lng->txt("adn_max_one_per_objective"), "max_one_per_objective");
        $this->addColumn($this->lng->txt("actions"));

        $this->setDefaultOrderField("objective");
        $this->setDefaultOrderDirection("asc");

        $this->setFormAction($ilCtrl->getFormAction($a_parent_obj));
        $this->setRowTemplate("tpl.target_nr_of_questions_row.html", "Services/ADN/ED");

        $this->importData($a_targets);
    }

    /**
     * Import data from DB
     */
    protected function importData($a_targets = null)
    {
        global $lng;

        // value mapping (to have correct sorting)
        if ($a_targets) {
            include_once "Services/ADN/ED/classes/class.adnObjective.php";
            include_once "./Services/ADN/ED/classes/class.adnSubobjective.php";
            include_once "Services/ADN/ED/classes/class.adnCatalogNumbering.php";

            foreach ($a_targets as $idx => $item) {
                $a_targets[$idx]["max_one_per_objective"] =
                    ($item["max_one_per_objective"] ? $lng->txt("yes") : $lng->txt("no"));

                $all_obj = array();
                if ($item["objectives"]) {
                    foreach ($item["objectives"] as $obj_id) {
                        $obj = new adnObjective($obj_id);
                        $all_obj[] = adnCatalogNumbering::getAreaTextRepresentation($obj->getCatalogArea()) .
                            " / " . $obj->getNumber() . " " . $obj->getName();
                    }
                }
                if ($item["subobjectives"]) {
                    foreach ($item["subobjectives"] as $sobj_id) {
                        $sobj = new adnSubobjective($sobj_id);
                        $obj = new adnObjective($sobj->getObjective());
                        $all_obj[] = adnCatalogNumbering::getAreaTextRepresentation($obj->getCatalogArea()) .
                            " / " . $obj->getNumber() . " " . $obj->getName() .
                            " / " . $sobj->getNumber() . " " . $sobj->getName();
                    }
                }
                sort($all_obj);
                $a_targets[$idx]["title"] = implode("<br />", $all_obj);
            }

            $this->setData($a_targets);
            $this->setMaxCount(sizeof($a_targets));
        }
    }

    /**
     * Fill table row
     *
     * @param array $a_set data array
     */
    protected function fillRow($a_set)
    {
        global $lng, $ilCtrl;

        $this->overall += $a_set["nr_of_questions"];

        // actions...

        if (adnPerm::check(adnPerm::ED, adnPerm::WRITE)) {
            $ilCtrl->setParameter($this->parent_obj, "tgt_id", $a_set["id"]);

            // ...edit
            $this->tpl->setCurrentBlock("action");
            $this->tpl->setVariable("TXT_CMD", $lng->txt("edit"));
            $this->tpl->setVariable("HREF_CMD", $ilCtrl->getLinkTarget($this->parent_obj, "editTarget"));
            $this->tpl->parseCurrentBlock();

            $ilCtrl->setParameter($this->parent_obj, "tgt_id", "");

            // checkbox for deletion
            $this->tpl->setCurrentBlock("cbox");
            $this->tpl->setVariable("VAL_ID", $a_set["id"]);
            $this->tpl->parseCurrentBlock();
        }


        // properties
        
        $this->tpl->setVariable("VAL_TARGET_NR", $a_set["nr_of_questions"]);
        $this->tpl->setVariable("VAL_SINGLE", $a_set["max_one_per_objective"]);
        $this->tpl->setVariable("VAL_TITLE", $a_set["title"]);
    }
}
