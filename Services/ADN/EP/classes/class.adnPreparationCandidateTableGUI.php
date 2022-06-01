<?php
/* Copyright (c) 2010 Leifos, GPL, see docs/LICENSE */

include_once("./Services/Table/classes/class.ilTable2GUI.php");

/**
 * Candidate table GUI class (preparation context)
 *
 * List all candidates
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.de>
 * @version $Id: class.adnPreparationCandidateTableGUI.php 27888 2011-02-28 11:09:28Z jluetzen $
 *
 * @ingroup ServicesADN
 */
class adnPreparationCandidateTableGUI extends ilTable2GUI
{
    /**
     * @var array<string, mixed>
     */
    protected array $map = [];

    /**
     * @var array<string, mixed>
     */
    protected array $filter;
    /**
     * @var array<string, string>
     */
    protected array $filter_options;

    protected bool $show_professionals;

    protected bool $show_prospects;

    /**
     * Constructor
     *
     * @param object $a_parent_obj parent gui object
     * @param string $a_parent_cmd parent default command
     * @param bool $a_show_professionals show certified professionals
     * @param bool $a_show_prospects show prospects
     */
    public function __construct(
        $a_parent_obj,
        $a_parent_cmd,
        $a_show_professionals = false,
        $a_show_prospects = false
    )
    {

        $this->show_professionals = (bool) $a_show_professionals;
        $this->show_prospects = (bool) $a_show_prospects;

        parent::__construct($a_parent_obj, $a_parent_cmd);

        $this->setId("adn_tbl_pcd");

        $this->setTitle($this->lng->txt("adn_candidates"));

        if (adnPerm::check(adnPerm::EP, adnPerm::WRITE)) {
            $this->addMultiCommand("confirmCandidatesDeletion", $this->lng->txt("adn_delete_candidates"));
            $this->addColumn("", "", 1);
        }

        $this->addColumn($this->lng->txt("adn_last_name"), "last_name");
        $this->addColumn($this->lng->txt("adn_first_name"), "first_name");
        $this->addColumn($this->lng->txt("adn_birthdate"), "birthdate");
        $this->addColumn($this->lng->txt("adn_citizenship"), "citizenship");
        $this->addColumn($this->lng->txt("adn_type_of_examination"), "subject_area");
        $this->addColumn($this->lng->txt("adn_registered_by"), "registered_by");
        $this->addColumn($this->lng->txt("adn_holdback_until"), "blocked_until");
        $this->addColumn($this->lng->txt("actions"));
        
        $this->setDefaultOrderField("last_name");
        $this->setDefaultOrderDirection("asc");

        include_once "Services/ADN/ED/classes/class.adnSubjectArea.php";
        $this->map["type"] = array("" => $this->lng->txt("adn_filter_none")) + adnSubjectArea::getAllAreas();

        include_once "Services/ADN/MD/classes/class.adnWMO.php";
        $this->map["registered_by"] = adnWMO::getWMOsSelect();

        include_once "Services/ADN/MD/classes/class.adnCountry.php";
        $this->map["citizenship"] = adnCountry::getCountriesSelect();

        $this->initFilter();

        $this->setFormAction($this->ctrl->getFormAction($a_parent_obj));
        $this->setRowTemplate("tpl.candidate_row.html", "Services/ADN/EP");

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
            if ($this->show_professionals || $this->show_prospects) {
                include_once "Services/ADN/ES/classes/class.adnCertificate.php";
                $all_valid = adnCertificate::getAllProfessionalsWithValidCertificates();
            }
            foreach ($candidates as $idx => $item) {
                // only if not candidate
                if (!$item["subject_area"]) {
                    if ($this->show_professionals || $this->show_prospects) {
                        // is professional?
                        if (in_array($item["id"], $all_valid) && !$this->show_professionals) {
                            unset($candidates[$idx]);
                            continue;
                        }
                        // is prospect?
                        elseif (!in_array($item["id"], $all_valid) && !$this->show_prospects) {
                            unset($candidates[$idx]);
                            continue;
                        }
                    } else {
                        unset($candidates[$idx]);
                        continue;
                    }
                }

                $candidates[$idx]["subject_area"] = $this->map["type"][$item["subject_area"]];

                // we cannot use intermal mapping because of archived values
                $candidates[$idx]["citizenship"] = adnCountry::lookupName($item["citizenship"]);
                $candidates[$idx]["registered_by"] =
                    adnWMO::lookupName($item["registered_by_wmo_id"]);
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

        $name = $this->addFilterItemByMetaType(
            "name",
            self::FILTER_TEXT,
            false,
            $this->lng->txt("adn_last_name")
        );
        $name->readFromSession();
        $this->filter["last_name"] = $name->getValue();

        $first_name = $this->addFilterItemByMetaType(
            "first_name",
            self::FILTER_TEXT,
            false,
            $this->lng->txt("adn_first_name")
        );
        $first_name->readFromSession();
        $this->filter["first_name"] = $first_name->getValue();

        $birthdate = $this->addFilterItemByMetaType(
            "birthdate",
            self::FILTER_DATE_RANGE,
            false,
            $this->lng->txt("adn_birthdate")
        );
        $birthdate->readFromSession();
        $this->filter["birthdate"] = $birthdate->getDate();
        
        $types = $this->addFilterItemByMetaType(
            "type",
            self::FILTER_SELECT,
            false,
            $this->lng->txt("adn_type_of_examination")
        );
        include_once "Services/ADN/ED/classes/class.adnSubjectArea.php";
        $this->filter_options["type"] = adnSubjectArea::getAllAreas();
        $all = array('' => $this->lng->txt('adn_filter_all')) + $this->filter_options["type"];
        $types->setOptions($all);
        $types->readFromSession();
        $this->filter["subject_area"] = $types->getValue();

        $wsd = $this->addFilterItemByMetaType(
            "registered_by",
            self::FILTER_SELECT,
            false,
            $this->lng->txt("adn_registered_by")
        );
        $wsd->setOptions(array(0 => $this->lng->txt("adn_filter_all")) + $this->map["registered_by"]);
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

        // actions...

        $this->ctrl->setParameter($this->parent_obj, "cd_id", $a_set["id"]);

        if (adnPerm::check(adnPerm::EP, adnPerm::WRITE)) {
            // edit
            $this->tpl->setCurrentBlock("action");
            $this->tpl->setVariable("TXT_CMD", $this->lng->txt("adn_edit_candidate"));
            $this->tpl->setVariable(
                "HREF_CMD",
                $this->ctrl->getLinkTarget($this->parent_obj, "editCandidate")
            );
            $this->tpl->parseCurrentBlock();

            // checkbox for deletion
            $this->tpl->setCurrentBlock("cbox");
            $this->tpl->setVariable("VAL_ID", $a_set["id"]);
            $this->tpl->parseCurrentBlock();
        }

        $this->ctrl->setParameter($this->parent_obj, "cd_id", "");
        
        
        // properties
        $this->tpl->setVariable("VAL_NAME", $a_set["last_name"]);
        $this->tpl->setVariable("VAL_FIRST_NAME", $a_set["first_name"]);
        $this->tpl->setVariable("VAL_CITIZENSHIP", $a_set["citizenship"]);

        $this->tpl->setVariable("VAL_BIRTHDATE", ilDatePresentation::formatDate(
            new ilDate($a_set["birthdate"], IL_CAL_DATE)
        ));

        $this->tpl->setVariable("VAL_TYPE", $a_set["subject_area"]);
        $this->tpl->setVariable("VAL_REGISTERED_BY", $a_set["registered_by"]);

        if ($a_set["blocked_until"]) {
            $this->tpl->setVariable("VAL_HOLDBACK_UNTIL", ilDatePresentation::formatDate(
                new ilDate($a_set["blocked_until"], IL_CAL_DATE)
            ));
        }
    }
}
