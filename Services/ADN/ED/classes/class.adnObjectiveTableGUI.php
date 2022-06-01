<?php
/* Copyright (c) 2010 Leifos, GPL, see docs/LICENSE */

include_once("./Services/Table/classes/class.ilTable2GUI.php");
include_once "Services/ADN/ED/classes/class.adnCatalogNumbering.php";
include_once "Services/ADN/ED/classes/class.adnObjective.php";

/**
 * ADN objective table GUI class
 *
 * List all objectives (either case or mc)
 *
 * @author Alex Killing <killing@leifos.de>
 * @version $Id: class.adnObjectiveTableGUI.php 27873 2011-02-25 16:21:55Z jluetzen $
 *
 * @ingroup ServicesADN
 */
class adnObjectiveTableGUI extends ilTable2GUI
{
    protected string $type;
    /**
     * @var array<string, mixed>
     */
    protected array $filter = [];
    
    /**
     * Constructor
     *
     * @param object $a_parent_obj parent gui object
     * @param string $a_parent_cmd parent default command
     * @param string $a_type objective type
     */
    public function __construct($a_parent_obj, $a_parent_cmd, $a_type)
    {

        $this->type = (string) $a_type;
        $this->setId("adn_ed_obj" . $this->type);

        parent::__construct($a_parent_obj, $a_parent_cmd);
        
        $this->setTitle($this->lng->txt("adn_objectives"));

        if (adnPerm::check(adnPerm::ED, adnPerm::WRITE)) {
            if ($this->type == adnObjective::TYPE_MC) {
                $cmd_type = "MC";
            } else {
                $cmd_type = "Case";
            }
            $this->addColumn("", "", "1");
            $this->addMultiCommand("confirm" . $cmd_type . "ObjectiveDeletion", $this->lng->txt("delete"));
        }

        if ($this->type == adnObjective::TYPE_MC) {
            $this->addColumn($this->lng->txt("adn_catalog_area"), "catalog_area");
        } else {
            $this->addColumn($this->lng->txt("adn_subject_area"), "catalog_area");
        }
        
        $this->addColumn($this->lng->txt("adn_nr"), "adn_number");
        $this->addColumn($this->lng->txt("adn_title"), "name");
        $this->addColumn($this->lng->txt("adn_topic"), "topic");
        $this->addColumn($this->lng->txt("actions"));

        $this->initFilter();
        
        $this->setDefaultOrderField("catalog_area");
        $this->setDefaultOrderDirection("asc");
        
        $this->addHiddenInput("type", $this->type);

        $this->setFormAction($this->ctrl->getFormAction($a_parent_obj));
        $this->setRowTemplate("tpl.objective_row.html", "Services/ADN/ED");

        $this->importData();
    }

    /**
     * Import data from DB
     */
    protected function importData()
    {
        $this->filter["type"] = $this->type;

        include_once "Services/ADN/ED/classes/class.adnObjective.php";
        $objectives = adnObjective::getAllObjectives($this->filter);

        // value mapping (to have correct sorting)
        if (sizeof($objectives)) {
            foreach ($objectives as $idx => $item) {
                $objectives[$idx]["catalog_area"] =
                    adnCatalogNumbering::getAreaTextRepresentation($item["catalog_area"]);
            }
        }

        $this->setData($objectives);
        $this->setMaxCount(sizeof($objectives));
    }

    /**
     * Init filter
     */
    public function initFilter()
    {

        // catalog area
        include_once "Services/ADN/ED/classes/class.adnCatalogNumbering.php";
        if ($this->type == adnObjective::TYPE_MC) {
            $f = $this->addFilterItemByMetaType(
                "catalog_area",
                self::FILTER_SELECT,
                false,
                $this->lng->txt("adn_catalog_area")
            );
            $options = array("" => $this->lng->txt("adn_filter_all")) + adnCatalogNumbering::getMCAreas();
        } else {
            $f = $this->addFilterItemByMetaType(
                "catalog_area",
                self::FILTER_SELECT,
                false,
                $this->lng->txt("adn_subject_area")
            );
            $options = array("" => $this->lng->txt("adn_filter_all")) + adnCatalogNumbering::getCaseAreas();
        }
        $f->setOptions($options);
        $f->readFromSession();
        $this->filter["catalog_area"] = $f->getValue();

        // number from to
        $f = $this->addFilterItemByMetaType(
            "nr",
            self::FILTER_TEXT_RANGE,
            false,
            $this->lng->txt("adn_nr")
        );
        $f->readFromSession();
        $this->filter["nr"] = $f->getValue();

        // title
        $f = $this->addFilterItemByMetaType(
            "title",
            self::FILTER_TEXT,
            false,
            $this->lng->txt("adn_title")
        );
        $f->readFromSession();
        $this->filter["title"] = $f->getValue();
    }

    /**
     * Fill table row
     *
     * @param array	$a_set data array
     */
    protected function fillRow($a_set)
    {

        // actions...

        $this->ctrl->setParameter($this->parent_obj, "ob_id", $a_set["id"]);

        if (adnPerm::check(adnPerm::ED, adnPerm::WRITE)) {
            // ...edit
            $this->tpl->setCurrentBlock("action");
            $this->tpl->setVariable("TXT_CMD", $this->lng->txt("adn_edit_objective"));
            $this->tpl->setVariable(
                "HREF_CMD",
                $this->ctrl->getLinkTarget($this->parent_obj, "editObjective")
            );
            $this->tpl->parseCurrentBlock();

            // checkbox for deletion
            $this->tpl->setCurrentBlock("cbox");
            $this->tpl->setVariable("VAL_ID", $a_set["id"]);
            $this->tpl->parseCurrentBlock();
        }
        
        if (adnPerm::check(adnPerm::ED, adnPerm::READ) && $this->type == adnObjective::TYPE_MC) {
            // ...subobjectives
            $this->tpl->setCurrentBlock("action");
            $this->tpl->setVariable("TXT_CMD", $this->lng->txt("adn_subobjectives"));
            $this->tpl->setVariable(
                "HREF_CMD",
                $this->ctrl->getLinkTargetByClass("adnsubobjectivegui", "listSubobjectives")
            );
            $this->tpl->parseCurrentBlock();
        }

        $this->ctrl->setParameter($this->parent_obj, "ob_id", "");

        
        // properties

        $this->tpl->setVariable("VAL_CATALOG_AREA", $a_set["catalog_area"]);
        $this->tpl->setVariable("VAL_NR", $a_set["adn_number"]);
        $this->tpl->setVariable("VAL_TITLE", $a_set["name"]);
        $this->tpl->setVariable("VAL_TOPIC", $a_set["topic"]);
    }
}
