<?php
/* Copyright (c) 2010 Leifos, GPL, see docs/LICENSE */

include_once("./Services/Table/classes/class.ilTable2GUI.php");

/**
 * ADN training facility table GUI class
 *
 * List all facilities for parent provider
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @version $Id: class.adnTrainingFacilityTableGUI.php 27891 2011-02-28 11:46:53Z jluetzen $
 *
 * @ingroup ServicesADN
 */
class adnTrainingFacilityTableGUI extends ilTable2GUI
{
    protected int $provider_id = 0;
    
    /**
     * Constructor
     *
     * @param object $a_parent_obj parent gui object
     * @param string $a_parent_cmd parent default command
     * @param int $a_provider_id current provider
     */
    public function __construct($a_parent_obj, $a_parent_cmd, $a_provider_id)
    {
        global $ilCtrl, $lng;

        $this->provider_id = (int) $a_provider_id;

        $this->setId("adn_ta_trfac");

        parent::__construct($a_parent_obj, $a_parent_cmd);

        include_once "Services/ADN/TA/classes/class.adnTrainingProvider.php";
        $this->setTitle($lng->txt("adn_training_facilities") . ": " .
            adnTrainingProvider::lookupName($this->provider_id));
        
        $this->addColumn("", "", "1");
        $this->addColumn($this->lng->txt("adn_name"), "name");
        $this->addColumn($this->lng->txt("actions"));
        
        $this->setDefaultOrderField("name");
        $this->setDefaultOrderDirection("asc");
        
        $this->setFormAction($ilCtrl->getFormAction($a_parent_obj));
        $this->setRowTemplate("tpl.facilities_row.html", "Services/ADN/TA");
        
        $this->addMultiCommand("confirmTrainingFacilitiesDeletion", $lng->txt("delete"));

        $this->importData();
    }

    /**
     * Import data from DB
     */
    protected function importData()
    {
        include_once "Services/ADN/TA/classes/class.adnTrainingFacility.php";
        $facilities = adnTrainingFacility::getAllTrainingFacilities($this->provider_id);

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
        $ilCtrl->setParameter($this->parent_obj, "tf_id", $a_set["id"]);
        
        // ...edit
        $this->tpl->setCurrentBlock("action");
        $this->tpl->setVariable(
            "TXT_CMD",
            $lng->txt("edit")
        );
        $this->tpl->setVariable(
            "HREF_CMD",
            $ilCtrl->getLinkTarget($this->parent_obj, "editTrainingFacility")
        );
        $this->tpl->parseCurrentBlock();
        
        $ilCtrl->setParameter($this->parent_obj, "tf_id", "");

        
        // properties
        $this->tpl->setVariable("VAL_ID", $a_set["id"]);
        $this->tpl->setVariable("VAL_NAME", $a_set["name"]);
    }
}
