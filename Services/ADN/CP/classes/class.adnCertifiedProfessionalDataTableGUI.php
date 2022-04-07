<?php
/* Copyright (c) 2010 Leifos, GPL, see docs/LICENSE */

include_once("./Services/Table/classes/class.ilTable2GUI.php");

/**
 * Professional data table GUI class
 *
 * List certified professionals
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.de>
 * @version $Id: class.adnCertifiedProfessionalDataTableGUI.php 27872 2011-02-25 15:42:09Z jluetzen $
 *
 * @ingroup ServicesADN
 */
class adnCertifiedProfessionalDataTableGUI extends ilTable2GUI
{
    /**
     * @var array<string, array<int, string>>
     */
    protected array $map;

    /**
     * @var array<string, mixed>
     */
    protected array $filter;

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

        $this->setId("adn_tbl_ctcpr");

        $this->setTitle($lng->txt("adn_certified_professionals_data"));

        $this->addColumn($this->lng->txt("adn_last_name"), "last_name");
        $this->addColumn($this->lng->txt("adn_first_name"), "first_name");
        $this->addColumn($this->lng->txt("adn_citizenship"), "citizenship");
        $this->addColumn($this->lng->txt("adn_registered_by"), "registered_by");
        $this->addColumn($this->lng->txt("actions"));
        
        $this->setDefaultOrderField("last_name");
        $this->setDefaultOrderDirection("asc");

        include_once "Services/ADN/MD/classes/class.adnWMO.php";
        $this->map["registered_by"] = adnWMO::getWMOsSelect();

        include_once "Services/ADN/MD/classes/class.adnCountry.php";
        $this->map["citizenship"] = adnCountry::getCountriesSelect();

        $this->initFilter();

        $this->setFormAction($ilCtrl->getFormAction($a_parent_obj));
        $this->setRowTemplate("tpl.professional_row.html", "Services/ADN/CP");

        $this->importData();
    }

    /**
     * Import data from DB
     */
    protected function importData()
    {
        include_once "Services/ADN/ES/classes/class.adnCertifiedProfessional.php";
        $candidates = adnCertifiedProfessional::getAllCandidates($this->filter);

        if (sizeof($candidates)) {
            include_once "Services/ADN/ES/classes/class.adnCertificate.php";
            $all_valid = adnCertificate::getAllProfessionalsWithValidCertificates();
            foreach ($candidates as $idx => $item) {
                // only if not candidate or prospect
                if ($item["subject_area"] || !in_array($item["id"], $all_valid)) {
                    unset($candidates[$idx]);
                    continue;
                }

                // we cannot use intermal mapping because of archived values
                $candidates[$idx]["citizenship"] = adnCountry::lookupName($item["citizenship"]);
                $candidates[$idx]["registered_by"] = adnWMO::lookupName($item["registered_by_wmo_id"]);
            }
        }

        $this->setData($candidates);
        $this->setMaxCount(sizeof($candidates));
    }
    
    /**
     * Init filter
     */
    public function initFilter()
    {
        global $lng;

        $name = $this->addFilterItemByMetaType(
            "name",
            self::FILTER_TEXT,
            false,
            $lng->txt("adn_last_name")
        );
        $name->readFromSession();
        $this->filter["last_name"] = $name->getValue();

        $wsd = $this->addFilterItemByMetaType(
            "registered_by",
            self::FILTER_SELECT,
            false,
            $lng->txt("adn_registered_by")
        );
        $wsd->setOptions(array(0 => $lng->txt("adn_filter_all")) + $this->map["registered_by"]);
        $wsd->readFromSession();
        $this->filter["registered_by"] = $wsd->getValue();
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

        if (adnPerm::check(adnPerm::EP, adnPerm::WRITE)) {
            $ilCtrl->setParameter($this->parent_obj, "ct_cpr", $a_set["id"]);

            // edit
            $this->tpl->setCurrentBlock("action");
            $this->tpl->setVariable("TXT_CMD", $lng->txt("adn_edit_professional"));
            $this->tpl->setVariable(
                "HREF_CMD",
                $ilCtrl->getLinkTarget($this->parent_obj, "editProfessional")
            );
            $this->tpl->parseCurrentBlock();

            $ilCtrl->setParameter($this->parent_obj, "ct_cpr", "");
        }

        // properties
        $this->tpl->setVariable("VAL_NAME", $a_set["last_name"]);
        $this->tpl->setVariable("VAL_FIRST_NAME", $a_set["first_name"]);
        $this->tpl->setVariable("VAL_CITIZENSHIP", $a_set["citizenship"]);
        $this->tpl->setVariable("VAL_REGISTERED_BY", $a_set["registered_by"]);
    }
}
