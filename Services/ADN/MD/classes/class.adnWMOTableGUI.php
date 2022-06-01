<?php
/* Copyright (c) 2010 Leifos, GPL, see docs/LICENSE */

include_once("./Services/Table/classes/class.ilTable2GUI.php");

/**
 * WMO table GUI class
 *
 * List all WMOs
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.de>
 * @version $Id: class.adnWMOTableGUI.php 27876 2011-02-25 16:51:38Z jluetzen $
 *
 * @ingroup ServicesADN
 */
class adnWMOTableGUI extends ilTable2GUI
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

        $this->setId("adn_tbl_mdwmo");

        $this->setTitle($this->lng->txt("adn_wmos"));
        
        if (adnPerm::check(adnPerm::MD, adnPerm::WRITE)) {
            $this->addColumn("", "");
            $this->addMultiCommand("confirmDeleteWMOs", $this->lng->txt("delete"));
        }

        $this->addColumn($this->lng->txt("adn_name"), "name");
        $this->addColumn($this->lng->txt("adn_wmo_code"), "code_nr");
        $this->addColumn($this->lng->txt("actions"));
        
        $this->setDefaultOrderField("name");
        $this->setDefaultOrderDirection("asc");

        $this->setFormAction($this->ctrl->getFormAction($a_parent_obj));
        $this->setRowTemplate("tpl.wmo_row.html", "Services/ADN/MD");

        $this->importData();
    }

    /**
     * Import data from DB
     */
    protected function importData()
    {
        include_once "Services/ADN/MD/classes/class.adnWMO.php";
        $wos = adnWMO::getAllWMOs();

        $this->setData($wos);
        $this->setMaxCount(sizeof($wos));
    }
    
    /**
     * Fill table row
     *
     * @param array $a_set data array
     */
    protected function fillRow($a_set)
    {

        // actions...

        $this->ctrl->setParameter($this->parent_obj, "wmo_id", $a_set["id"]);

        if (adnPerm::check(adnPerm::MD, adnPerm::WRITE)) {
            // edit
            $this->tpl->setCurrentBlock("action");
            $this->tpl->setVariable("TXT_CMD", $this->lng->txt("edit"));
            $this->tpl->setVariable(
                "HREF_CMD",
                $this->ctrl->getLinkTarget($this->parent_obj, "editWMO")
            );
            $this->tpl->parseCurrentBlock();

            // checkbox for deletion
            $this->tpl->setCurrentBlock("cbox");
            $this->tpl->setVariable("VAL_ID", $a_set["id"]);
            $this->tpl->parseCurrentBlock();
        }

        if (adnPerm::check(adnPerm::MD, adnPerm::READ)) {
            // co chairs
            $this->tpl->setCurrentBlock("action");
            $this->tpl->setVariable("TXT_CMD", $this->lng->txt("adn_cochairs"));
            $this->tpl->setVariable(
                "HREF_CMD",
                $this->ctrl->getLinkTargetByClass("adnCoChairGUI", "listCoChairs")
            );
            $this->tpl->parseCurrentBlock();

            // exam facilities
            $this->tpl->setCurrentBlock("action");
            $this->tpl->setVariable("TXT_CMD", $this->lng->txt("adn_exam_facilities"));
            $this->tpl->setVariable(
                "HREF_CMD",
                $this->ctrl->getLinkTargetByClass("adnExamFacilityGUI", "listExamFacilities")
            );
            $this->tpl->parseCurrentBlock();
        }

        $this->ctrl->setParameter($this->parent_obj, "cnt_id", "");
        
        // properties
        $this->tpl->setVariable("VAL_CODE", $a_set["code_nr"]);
        $this->tpl->setVariable("VAL_NAME", $a_set["name"]);
    }
}
