<?php
/* Copyright (c) 2010 Leifos, GPL, see docs/LICENSE */

include_once("./Services/Table/classes/class.ilTable2GUI.php");

/**
 * ADN license table GUI class
 *
 * List all licenses
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @version $Id: class.adnLicenseTableGUI.php 27873 2011-02-25 16:21:55Z jluetzen $
 *
 * @ingroup ServicesADN
 */
class adnLicenseTableGUI extends ilTable2GUI
{
    protected int $type;
    
    /**
     * Constructor
     *
     * @param object $a_parent_obj parent gui object
     * @param string $a_parent_cmd parent default command
     * @param int $a_type parent type
     */
    public function __construct($a_parent_obj, $a_parent_cmd, $a_type)
    {

        $this->type = (int) $a_type;

        parent::__construct($a_parent_obj, $a_parent_cmd);

        $this->setId("adn_ed_lic");
        $this->setTitle($this->lng->txt("adn_licenses"));

        $this->importData();

        if (adnPerm::check(adnPerm::ED, adnPerm::WRITE) && $this->type == adnLicense::TYPE_CHEMICALS) {
            $this->addMultiCommand("confirmLicensesChemDeletion", $this->lng->txt("delete"));
            $this->addColumn("", "", "1");
        }

        $this->addColumn($this->lng->txt("adn_title"), "name");
        $this->addColumn($this->lng->txt("actions"));
        
        $this->setDefaultOrderField("name");
        $this->setDefaultOrderDirection("asc");
        
        $this->setFormAction($this->ctrl->getFormAction($a_parent_obj));
        $this->setRowTemplate("tpl.licenses_row.html", "Services/ADN/ED");
    }

    /**
     * Import data from DB
     */
    protected function importData()
    {
        include_once "Services/ADN/ED/classes/class.adnLicense.php";
        $licenses = adnLicense::getAllLicenses($this->type);

        $this->setData($licenses);
        $this->setMaxCount(sizeof($licenses));
    }
    
    /**
     * Fill table row
     *
     * @param array	$a_set data array
     */
    protected function fillRow($a_set)
    {
        
        // actions...
        $this->ctrl->setParameter($this->parent_obj, "lcs_id", $a_set["id"]);

        if (adnPerm::check(adnPerm::ED, adnPerm::WRITE)) {
            // ...edit
            if ($this->type == adnLicense::TYPE_CHEMICALS) {
                $cmd = "editChemLicense";
            } else {
                $cmd = "editGasLicense";
            }
            $this->tpl->setCurrentBlock("action");
            $this->tpl->setVariable(
                "TXT_CMD",
                $this->lng->txt("edit")
            );
            $this->tpl->setVariable(
                "HREF_CMD",
                $this->ctrl->getLinkTarget($this->parent_obj, $cmd)
            );
            $this->tpl->parseCurrentBlock();

            if ($this->type == adnLicense::TYPE_CHEMICALS) {
                // checkbox for deletion
                $this->tpl->setCurrentBlock("cbox");
                $this->tpl->setVariable("VAL_ID", $a_set["id"]);
                $this->tpl->parseCurrentBlock();
            }
        }

        if (adnPerm::check(adnPerm::ED, adnPerm::READ)) {
            // download
            if ($a_set["file"]) {
                $this->tpl->setCurrentBlock("action");
                $this->tpl->setVariable(
                    "TXT_CMD",
                    $this->lng->txt("download")
                );
                $this->tpl->setVariable(
                    "HREF_CMD",
                    $this->ctrl->getLinkTarget($this->parent_obj, "downloadFile")
                );
                $this->tpl->parseCurrentBlock();
            }
        }
        
        $this->ctrl->setParameter($this->parent_obj, "lcs_id", "");
    
        // properties
        $this->tpl->setVariable("VAL_NAME", $a_set["name"]);
    }
}
