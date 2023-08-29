<?php
/* Copyright (c) 2010 Leifos, GPL, see docs/LICENSE */

include_once("./Services/Table/classes/class.ilTable2GUI.php");

/**
 * MC question export table GUI class
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.de>
 * @version $Id: class.adnMCQuestionExportTableGUI.php 27867 2011-02-25 09:35:47Z akill $
 *
 * @ingroup ServicesADN
 */
class adnMCQuestionExportTableGUI extends ilTable2GUI
{
    /**
     * Constructor
     *
     * @param object $a_parent_obj parent gui object
     * @param string $a_parent_cmd parent default command
     */
    public function __construct($a_parent_obj, $a_parent_cmd)
    {
        global $ilCtrl, $lng;

        parent::__construct($a_parent_obj, $a_parent_cmd);

        $this->setId("adn_tbl_admcx");

        $this->setTitle($lng->txt("adn_export_mc_questions"));
        
        $this->addColumn("", "");
        $this->addColumn($this->lng->txt("file"), "name");
        $this->addColumn($this->lng->txt("date"), "date_sort");
        $this->addColumn($this->lng->txt("actions"));
        
        $this->setDefaultOrderField("file");
        $this->setDefaultOrderDirection("asc");

        $this->setFormAction($ilCtrl->getFormAction($a_parent_obj));
        $this->setRowTemplate("tpl.file_row.html", "Services/ADN/AD");

        $this->addMultiCommand("confirmDeleteFiles", $lng->txt("delete"));

        $this->importData();
    }

    /**
     * Import data from DB
     */
    protected function importData()
    {
        include_once "Services/ADN/ED/classes/class.adnQuestionExport.php";
        $files = adnQuestionExport::getAllFiles();
        foreach ((array) $files as $idx => $item) {
            $files[$idx]['date'] = ilDatePresentation::formatDate($item['date']);
            $files[$idx]['date_sort'] = $item['date']->get(IL_CAL_UNIX);
        }
        $this->setData($files);
        $this->setMaxCount(sizeof($files));
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

        if (adnPerm::check(adnPerm::AD, adnPerm::WRITE)) {
            $ilCtrl->setParameter($this->parent_obj, "exf_id", $a_set["id"]);

            // edit
            $this->tpl->setCurrentBlock("action");
            $this->tpl->setVariable("TXT_CMD", $lng->txt("download"));
            $this->tpl->setVariable(
                "HREF_CMD",
                $ilCtrl->getLinkTarget($this->parent_obj, "downloadFile")
            );
            $this->tpl->parseCurrentBlock();

            $ilCtrl->setParameter($this->parent_obj, "exf_id", "");
        }

        // properties
        $this->tpl->setVariable("VAL_NAME", $a_set["name"]);
        $this->tpl->setVariable("VAL_DATE", $a_set["date"]);
        $this->tpl->setVariable("VAL_ID", $a_set["id"]);
    }
}
