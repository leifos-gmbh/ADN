<?php
/* Copyright (c) 2010 Leifos, GPL, see docs/LICENSE */

include_once("./Services/Table/classes/class.ilTable2GUI.php");

/**
 * ADN subobjective table GUI class
 *
 * List all subobjectives for objective
 *
 * @author Alex Killing <killing@leifos.de>
 * @version $Id: class.adnSubobjectiveTableGUI.php 27874 2011-02-25 16:36:28Z jluetzen $
 *
 * @ingroup ServicesADN
 */
class adnSubobjectiveTableGUI extends ilTable2GUI
{
    protected int $objective_id;
    
    /**
     * Constructor
     *
     * @param object $a_parent_obj parent gui object
     * @param string $a_parent_cmd parent default command
     * @param int $a_objective_id parent objective
     */
    public function __construct($a_parent_obj, $a_parent_cmd, $a_objective_id = 0)
    {

        $this->objective_id = (int) $a_objective_id;
        
        parent::__construct($a_parent_obj, $a_parent_cmd);

        $this->setId("adn_ed_sobj");

        include_once "Services/ADN/ED/classes/class.adnObjective.php";
        $this->objective = new adnObjective($this->objective_id);
        $this->setTitle($this->lng->txt("adn_subobjectives") . ": " .
            $this->objective->buildADNNumber() . " " . $this->objective->getName());

        if (adnPerm::check(adnPerm::ED, adnPerm::WRITE)) {
            $this->addMultiCommand("confirmSubobjectiveDeletion", $this->lng->txt("delete"));
            $this->addColumn("", "", "1");
        }

        $this->addColumn($this->lng->txt("adn_nr"), "adn_number");
        $this->addColumn($this->lng->txt("adn_title"), "name");
        $this->addColumn($this->lng->txt("adn_topic"), "topic");
        $this->addColumn($this->lng->txt("actions"));

        $this->setDefaultOrderField("adn_number");
        $this->setDefaultOrderDirection("asc");

        $this->setRowTemplate("tpl.subobjective_row.html", "Services/ADN/ED");

        $this->setFormAction($this->ctrl->getFormAction($a_parent_obj));
    
        $this->importData();
    }

    /**
     * Import data from DB
     */
    protected function importData()
    {
        include_once "./Services/ADN/ED/classes/class.adnSubobjective.php";
        $subobjectives = adnSubobjective::getAllSubobjectives($this->objective_id);

        $this->setData($subobjectives);
        $this->setMaxCount(sizeof($subobjectives));
    }

    /**
     * Fill table row
     *
     * @param array $a_set data array
     */
    protected function fillRow($a_set)
    {

        // actions...

        if (adnPerm::check(adnPerm::ED, adnPerm::WRITE)) {
            // ...edit
            $this->ctrl->setParameter($this->parent_obj, "sob_id", $a_set["id"]);

            $this->tpl->setCurrentBlock("action");
            $this->tpl->setVariable("TXT_CMD", $this->lng->txt("adn_edit_subobjective"));
            $this->tpl->setVariable(
                "HREF_CMD",
                $this->ctrl->getLinkTarget($this->parent_obj, "editSubobjective")
            );
            $this->tpl->parseCurrentBlock();

            $this->ctrl->setParameter($this->parent_obj, "sob_id", "");

            // checkbox for deletion
            $this->tpl->setCurrentBlock("cbox");
            $this->tpl->setVariable("VAL_ID", $a_set["id"]);
            $this->tpl->parseCurrentBlock();
        }
        
        // properties
        $this->tpl->setVariable("VAL_NR", $a_set["adn_number"]);
        $this->tpl->setVariable("VAL_TITLE", $a_set["name"]);
        $this->tpl->setVariable("VAL_TOPIC", $a_set["topic"]);
    }
}
