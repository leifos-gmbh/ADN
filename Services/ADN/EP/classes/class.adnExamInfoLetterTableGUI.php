<?php
/* Copyright (c) 2010 Leifos, GPL, see docs/LICENSE */

include_once("./Services/Table/classes/class.ilTable2GUI.php");

/**
 * ADN exam information letter table GUI class
 *
 * List all info letters
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @version $Id: class.adnExamInfoLetterTableGUI.php 27888 2011-02-28 11:09:28Z jluetzen $
 *
 * @ingroup ServicesADN
 */
class adnExamInfoLetterTableGUI extends ilTable2GUI
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
        
        $this->setTitle($this->lng->txt("adn_information_letters_and_applications"));

        if (adnPerm::check(adnPerm::EP, adnPerm::WRITE)) {
            $this->addMultiCommand("confirmLettersDeletion", $this->lng->txt("delete"));
            $this->addColumn("", "", "1");
        }

        $this->addColumn($this->lng->txt("adn_title"), "file");
        $this->addColumn($this->lng->txt("actions"));
        
        $this->setDefaultOrderField("file");
        $this->setDefaultOrderDirection("asc");
        
        $this->setFormAction($this->ctrl->getFormAction($a_parent_obj));
        $this->setRowTemplate("tpl.letters_row.html", "Services/ADN/EP");
        
        $this->importData();
    }

    /**
     * Import data from DB
     */
    protected function importData()
    {
        include_once "Services/ADN/EP/classes/class.adnExamInfoLetter.php";
        $letters = adnExamInfoLetter::getAllLetters();

        $this->setData($letters);
        $this->setMaxCount(sizeof($letters));
    }
    
    /**
     * Fill table row
     *
     * @param array $a_set data array
     */
    protected function fillRow($a_set)
    {
        
        // actions...

        if (adnPerm::check(adnPerm::EP, adnPerm::WRITE)) {
            // checkbox for deletion
            $this->tpl->setCurrentBlock("cbox");
            $this->tpl->setVariable("VAL_ID", $a_set["id"]);
            $this->tpl->parseCurrentBlock();
        }
        
        if (adnPerm::check(adnPerm::EP, adnPerm::READ)) {
            $this->ctrl->setParameter($this->parent_obj, "ilt_id", $a_set["id"]);

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

            $this->ctrl->setParameter($this->parent_obj, "ilt_id", "");
        }

    
        // properties
        $this->tpl->setVariable("VAL_NAME", $a_set["file"]);
    }
}
