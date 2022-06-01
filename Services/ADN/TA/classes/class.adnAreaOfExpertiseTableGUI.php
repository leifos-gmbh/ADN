<?php
/* Copyright (c) 2010 Leifos, GPL, see docs/LICENSE */

include_once("./Services/Table/classes/class.ilTable2GUI.php");

/**
 * ADN area of expertise table GUI class
 *
 * List all areas
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @version $Id: class.adnAreaOfExpertiseTableGUI.php 27891 2011-02-28 11:46:53Z jluetzen $
 *
 * @ingroup ServicesADN
 */
class adnAreaOfExpertiseTableGUI extends ilTable2GUI
{
    /**
     * Constructor
     *
     * @param object $a_parent_obj parent gui object
     * @param string $a_parent_cmd parent default command
     */
    public function __construct($a_parent_obj, $a_parent_cmd)
    {

        parent::__construct($a_parent_obj, $a_parent_cmd);

        $this->setId("adn_ta_aoe");
        
        $this->setTitle($this->lng->txt("adn_areas_of_expertise"));

        if (adnPerm::check(adnPerm::TA, adnPerm::WRITE)) {
            $this->addMultiCommand("confirmAreasOfExpertiseDeletion", $this->lng->txt("delete"));
            $this->addColumn("", "", "1");
        }
        
        $this->addColumn($this->lng->txt("adn_name"), "name");
        $this->addColumn($this->lng->txt("actions"));
        
        $this->setDefaultOrderField("name");
        $this->setDefaultOrderDirection("asc");
        
        $this->setFormAction($this->ctrl->getFormAction($a_parent_obj));
        $this->setRowTemplate("tpl.areas_row.html", "Services/ADN/TA");
        
        $this->importData();
    }

    /**
     * Import data from DB
     */
    protected function importData()
    {
        include_once "Services/ADN/TA/classes/class.adnAreaOfExpertise.php";
        $areas = adnAreaOfExpertise::getAllAreasOfExpertise();

        $this->setData($areas);
        $this->setMaxCount(sizeof($areas));
    }
    
    /**
     * Fill table row
     *
     * @param array $a_set data array
     */
    protected function fillRow($a_set)
    {
        
        // actions...

        if (adnPerm::check(adnPerm::TA, adnPerm::WRITE)) {
            $this->ctrl->setParameter($this->parent_obj, "ae_id", $a_set["id"]);

            // ...edit
            $this->tpl->setCurrentBlock("action");
            $this->tpl->setVariable(
                "TXT_CMD",
                $this->lng->txt("edit")
            );
            $this->tpl->setVariable(
                "HREF_CMD",
                $this->ctrl->getLinkTarget($this->parent_obj, "editAreaOfExpertise")
            );
            $this->tpl->parseCurrentBlock();

            $this->ctrl->setParameter($this->parent_obj, "ae_id", "");

            // checkbox for deletion
            $this->tpl->setCurrentBlock("cbox");
            $this->tpl->setVariable("VAL_ID", $a_set["id"]);
            $this->tpl->parseCurrentBlock();
        }

        // properties
        $this->tpl->setVariable("VAL_NAME", $a_set["name"]);
    }
}
