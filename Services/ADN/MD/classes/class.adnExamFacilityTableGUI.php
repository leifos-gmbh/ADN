<?php
/* Copyright (c) 2010 Leifos, GPL, see docs/LICENSE */

include_once("./Services/Table/classes/class.ilTable2GUI.php");

/**
 * Exam facility table GUI class
 *
 * List all facilities for wmo
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.de>
 * @version $Id: class.adnExamFacilityTableGUI.php 27876 2011-02-25 16:51:38Z jluetzen $
 *
 * @ingroup ServicesADN
 */
class adnExamFacilityTableGUI extends ilTable2GUI
{
    protected int $wmo_id = 0;
    
    /**
     * Constructor
     *
     * @param object $a_parent_obj parent gui object
     * @param string $a_parent_cmd parent default command
     * @param string $a_wmo_id current wmo id
     */
    public function __construct($a_parent_obj, $a_parent_cmd, $a_wmo_id)
    {
        global $ilCtrl, $lng;

        $this->wmo_id = $a_wmo_id;

        parent::__construct($a_parent_obj, $a_parent_cmd);

        $this->setId("adn_tbl_mdefc");

        include_once "Services/ADN/MD/classes/class.adnWMO.php";
        $this->setTitle($lng->txt("adn_exam_facilities") . ": " . adnWMO::lookupName($this->wmo_id));
        
        if (adnPerm::check(adnPerm::MD, adnPerm::WRITE)) {
            $this->addMultiCommand("confirmDeleteExamFacilities", $lng->txt("delete"));
            $this->addColumn("", "");
        }
        
        $this->addColumn($this->lng->txt("adn_exam_facility_name"), "name");
        $this->addColumn($this->lng->txt("adn_street"), "street");
        $this->addColumn($this->lng->txt("adn_street_number"), "street_no");
        $this->addColumn($this->lng->txt("adn_postal_code"), "postal_code");
        $this->addColumn($this->lng->txt("adn_city"), "city");
        $this->addColumn($this->lng->txt("actions"));
        
        $this->setDefaultOrderField("name");
        $this->setDefaultOrderDirection("asc");

        $this->setFormAction($ilCtrl->getFormAction($a_parent_obj));
        $this->setRowTemplate("tpl.exam_facility_row.html", "Services/ADN/MD");

        $this->importData();
    }

    /**
     * Import data from DB
     */
    protected function importData()
    {
        include_once "Services/ADN/MD/classes/class.adnExamFacility.php";
        $facilities = adnExamFacility::getAllExamFacilities($this->wmo_id);

        $this->setData($facilities);
        $this->setMaxCount(sizeof($facilities));
    }
    
    /**
     * Fill table row
     *
     * @param array $a_set data array
     */
    protected function fillRow($a_set)
    {
        global $lng, $ilCtrl;

        // actions...

        if (adnPerm::check(adnPerm::MD, adnPerm::WRITE)) {
            $ilCtrl->setParameter($this->parent_obj, "ef_id", $a_set["id"]);

            // edit
            $this->tpl->setCurrentBlock("action");
            $this->tpl->setVariable("TXT_CMD", $lng->txt("edit"));
            $this->tpl->setVariable(
                "HREF_CMD",
                $ilCtrl->getLinkTarget($this->parent_obj, "editExamFacility")
            );
            $this->tpl->parseCurrentBlock();

            $ilCtrl->setParameter($this->parent_obj, "ef_id", "");

            // checkbox for deletion
            $this->tpl->setCurrentBlock("cbox");
            $this->tpl->setVariable("VAL_ID", $a_set["id"]);
            $this->tpl->parseCurrentBlock();
        }

        // properties
        $this->tpl->setVariable("VAL_NAME", $a_set["name"]);
        $this->tpl->setVariable("VAL_STREET", $a_set["street"]);
        $this->tpl->setVariable("VAL_STREET_NO", $a_set["street_no"]);
        $this->tpl->setVariable("VAL_CODE", $a_set["postal_code"]);
        $this->tpl->setVariable("VAL_CITY", $a_set["city"]);
    }
}
