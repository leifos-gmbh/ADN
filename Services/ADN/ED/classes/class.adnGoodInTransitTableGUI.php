<?php
/* Copyright (c) 2010 Leifos, GPL, see docs/LICENSE */

include_once("./Services/Table/classes/class.ilTable2GUI.php");
include_once "Services/ADN/ED/classes/class.adnGoodInTransit.php";

/**
 * ADN good in transit table GUI class
 *
 * List all goods (either gas or chemicals)
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @version $Id: class.adnGoodInTransitTableGUI.php 27873 2011-02-25 16:21:55Z jluetzen $
 *
 * @ingroup ServicesADN
 */
class adnGoodInTransitTableGUI extends ilTable2GUI
{
    protected int $type = 0;

    /**
     * Constructor
     *
     * @param object $a_parent_obj parent gui object
     * @param string $a_parent_cmd parent default command
     * @param int $a_type parent type
     * @param string $a_org_type parent type string
     */
    public function __construct($a_parent_obj, $a_parent_cmd, $a_type, $a_org_type)
    {

        $this->type = $a_type;

        $this->setId("adn_ed_git");

        parent::__construct($a_parent_obj, $a_parent_cmd);
        
        $this->setTitle($this->lng->txt("adn_goods_in_transit"));

        if (adnPerm::check(adnPerm::ED, adnPerm::WRITE)) {
            $this->addMultiCommand("confirm" . $a_org_type . "GoodsDeletion", $this->lng->txt("delete"));
            $this->addColumn("", "", "1");
        }
        
        $this->addColumn($this->lng->txt("adn_un_nr"), "un_nr");
        $this->addColumn($this->lng->txt("adn_name"), "name");
        $this->addColumn($this->lng->txt("adn_good_in_transit_category"), "category");

        if ($this->type == adnGoodInTransit::TYPE_CHEMICALS) {
            $this->addColumn($this->lng->txt("adn_class"), "class");
            $this->addColumn($this->lng->txt("adn_class_code"), "class_code");
            $this->addColumn($this->lng->txt("adn_packing_group"), "packing_group");
        }
        
        $this->addColumn($this->lng->txt("actions"));
        
        $this->setDefaultOrderField("number");
        $this->setDefaultOrderDirection("asc");
        
        $this->setFormAction($this->ctrl->getFormAction($a_parent_obj));
        $this->setRowTemplate("tpl.goods_row.html", "Services/ADN/ED");
        
        $this->importData();
    }

    /**
     * Import data from DB
     */
    protected function importData()
    {
        $goods = adnGoodInTransit::getAllGoods($this->type);

        // value mapping (to have correct sorting)
        if (sizeof($goods)) {
            include_once "Services/ADN/ED/classes/class.adnGoodInTransitCategory.php";
            foreach ($goods as $idx => $item) {
                if ($item["ed_good_category_id"]) {
                    $goods[$idx]["category"] =
                        adnGoodInTransitCategory::lookupName($item["ed_good_category_id"]);
                }
            }
        }

        $this->setData($goods);
        $this->setMaxCount(sizeof($goods));
    }
    
    /**
     * Fill table row
     *
     * @param array $a_set data array
     */
    protected function fillRow($a_set)
    {
        
        // actions...

        $this->ctrl->setParameter($this->parent_obj, "gd_id", $a_set["id"]);
        
        if (adnPerm::check(adnPerm::ED, adnPerm::WRITE)) {
            // ...edit
            $this->tpl->setCurrentBlock("action");
            $this->tpl->setVariable(
                "TXT_CMD",
                $this->lng->txt("edit")
            );
            $this->tpl->setVariable(
                "HREF_CMD",
                $this->ctrl->getLinkTarget($this->parent_obj, "editGood")
            );
            $this->tpl->parseCurrentBlock();

            // checkbox for deletion
            $this->tpl->setCurrentBlock("cbox");
            $this->tpl->setVariable("VAL_ID", $a_set["id"]);
            $this->tpl->parseCurrentBlock();
        }

        if (adnPerm::check(adnPerm::ED, adnPerm::READ)) {
            // download
            if ($a_set["material_file"]) {
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

        $this->ctrl->setParameter($this->parent_obj, "gd_id", "");

        // properties
        $this->tpl->setVariable("VAL_NUMBER", $a_set["un_nr"]);
        $this->tpl->setVariable("VAL_NAME", $a_set["name"]);
        $this->tpl->setVariable("VAL_CATEGORY", $a_set["category"]);

        if ($this->type == adnGoodInTransit::TYPE_CHEMICALS) {
            $this->tpl->setCurrentBlock("chem_attr");
            $this->tpl->setVariable("VAL_CLASS", $a_set["class"]);
            $this->tpl->setVariable("VAL_CLASS_CODE", $a_set["class_code"]);
            $this->tpl->setVariable("VAL_PACKING_GROUP", $a_set["packing_group"]);
            $this->tpl->parseCurrentBlock();
        }
    }
}
