<?php
/* Copyright (c) 2010 Leifos, GPL, see docs/LICENSE */

include_once("./Services/Table/classes/class.ilTable2GUI.php");

/**
 * Country table GUI class
 *
 * List countries
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.de>
 * @version $Id: class.adnCountryTableGUI.php 27876 2011-02-25 16:51:38Z jluetzen $
 *
 * @ingroup ServicesADN
 */
class adnCountryTableGUI extends ilTable2GUI
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

        $this->setId("adn_tbl_mdcnt");

        $this->setTitle($this->lng->txt("adn_countries"));

        if (adnPerm::check(adnPerm::MD, adnPerm::WRITE)) {
            $this->addMultiCommand("confirmDeleteCountries", $this->lng->txt("delete"));
            $this->addColumn("", "");
        }

        $this->addColumn($this->lng->txt("adn_country_code"), "code");
        $this->addColumn($this->lng->txt("adn_name"), "name");
        $this->addColumn($this->lng->txt("actions"));
        
        $this->setDefaultOrderField("code");
        $this->setDefaultOrderDirection("asc");

        $this->setFormAction($this->ctrl->getFormAction($a_parent_obj));
        $this->setRowTemplate("tpl.country_row.html", "Services/ADN/MD");

        $this->importData();
    }

    /**
     * Import data from DB
     */
    protected function importData()
    {
        include_once "Services/ADN/MD/classes/class.adnCountry.php";
        $countries = adnCountry::getAllCountries();

        $this->setData($countries);
        $this->setMaxCount(sizeof($countries));
    }
    
    /**
     * Fill table row
     *
     * @param array $a_set data array
     */
    protected function fillRow($a_set)
    {

        // actions...

        if (adnPerm::check(adnPerm::MD, adnPerm::WRITE)) {
            $this->ctrl->setParameter($this->parent_obj, "cnt_id", $a_set["id"]);

            // edit
            $this->tpl->setCurrentBlock("action");
            $this->tpl->setVariable("TXT_CMD", $this->lng->txt("edit"));
            $this->tpl->setVariable(
                "HREF_CMD",
                $this->ctrl->getLinkTarget($this->parent_obj, "editCountry")
            );
            $this->tpl->parseCurrentBlock();

            $this->ctrl->setParameter($this->parent_obj, "cnt_id", "");

            // checkbox for deletion
            $this->tpl->setCurrentBlock("cbox");
            $this->tpl->setVariable("VAL_ID", $a_set["id"]);
            $this->tpl->parseCurrentBlock();
        }
        
        // properties
        $this->tpl->setVariable("VAL_CODE", $a_set["code"]);
        $this->tpl->setVariable("VAL_NAME", $a_set["name"]);
    }
}
