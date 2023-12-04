<?php
/* Copyright (c) 2010 Leifos, GPL, see docs/LICENSE */

include_once("./Services/Table/classes/class.ilTable2GUI.php");

/**
 * Certified professional directory table GUI class
 *
 * List certified professionals
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.de>
 * @version $Id: class.adnCertifiedProfessionalDirectoryTableGUI.php 31050 2011-10-09 10:38:06Z smeyer $
 *
 * @ingroup ServicesADN
 */
class adnCertifiedProfessionalDirectoryTableGUI extends ilTable2GUI
{
    // [array] options for select filters (needed for value mapping)
    protected $filter_options;

    // [ilDateTime]
    protected $date_from;

    // [ilDateTime]
    protected $date_to;

    // [int]
    protected $wmo;

    protected $distinct_wmo_ids = array();

    /**
     * Constructor
     *
     * @param object $a_parent_obj parent gui object
     * @param string $a_parent_cmd parent default command
     * @param ilDate $a_date_from from data
     * @param ilDate $a_date_to to date
     * @param int $a_wmo current wmo
     */
    public function __construct($a_parent_obj, $a_parent_cmd, ilDate $a_date_from, ilDate $a_date_to, $a_wmo)
    {
        global $ilCtrl, $lng;

        $this->setId("adn_tbl_cpd");

        parent::__construct($a_parent_obj, $a_parent_cmd);

        $this->date_from = $a_date_from;
        $this->date_to = $a_date_to;
        $this->wmo = (int) $a_wmo;

        $title = $lng->txt("adn_certified_professional_directory") . ": " .
            ilDatePresentation::formatDate($this->date_from) . " - " .
            ilDatePresentation::formatDate($this->date_to);
        if ($this->wmo) {
            include_once "Services/ADN/MD/classes/class.adnWMO.php";
            $title .= ", " . adnWMO::lookupName($this->wmo);
        }
        $this->setTitle($title);
        
        $this->addColumn($this->lng->txt("adn_number"), "full_nr_sort");
        $this->addColumn($this->lng->txt("adn_last_name"), "last_name");
        $this->addColumn($this->lng->txt("adn_first_name"), "first_name");
        $this->addColumn($this->lng->txt("adn_birthdate"), "birthdate");
        $this->addColumn($this->lng->txt("adn_citizenship"), "citizenship");
        $this->addColumn($this->lng->txt("adn_type"));
        $this->addColumn($this->lng->txt("adn_valid_until"), "valid_until");
        $this->addColumn($this->lng->txt("adn_issued_on"), "issued_on");
        $this->addColumn($this->lng->txt("adn_signed_by"), "signed_by");

        $this->setDefaultOrderField("number");
        $this->setDefaultOrderDirection("asc");

        include_once "Services/ADN/MD/classes/class.adnCountry.php";
        $this->map["citizenship"] = adnCountry::getCountriesSelect();

        include_once "Services/ADN/ES/classes/class.adnCertificate.php";
        $this->map["type"] = adnCertificate::getCertificateTypes();

        $this->setFormAction($ilCtrl->getFormAction($a_parent_obj));
        $this->setRowTemplate("tpl.directory_row.html", "Services/ADN/CP");
        $this->setExportFormats([self::EXPORT_CSV]);

        $this->importData();
    }

    /**
     * Get distinct wmo ids of directory
     * @return array
     */
    public function getWmoIds()
    {
        asort($this->distinct_wmo_ids);
        return (array) $this->distinct_wmo_ids;
    }

    /**
     * Import data from DB
     */
    protected function importData()
    {
        include_once "Services/ADN/ES/classes/class.adnCertificate.php";
        $professionals = adnCertificate::getProfessionalDirectory(
            $this->date_from,
            $this->date_to,
            $this->wmo
        );

        // value mapping (to have correct sorting)
        if (sizeof($professionals)) {
            foreach ($professionals as $idx => $item) {
                $professionals[$idx]["citizenship"] = $this->map["citizenship"][$item["citizenship"]];

                $this->distinct_wmo_ids[$item['wmo_id']] = $item['code_nr'];

                $types = array();
                foreach ($this->map["type"] as $column => $caption) {
                    if ($item["type_" . $column]) {
                        $types[] = $caption;
                    }
                }

                $full_number = explode('-', $item['full_nr']);
                $professionals[$idx]['full_nr_sort'] = $full_number[2] . $full_number[1] . $full_number[0];
                $professionals[$idx]["type"] = implode(", ", $types);
            }
        }
        $this->setData($professionals);
        $this->setMaxCount(sizeof($professionals));
    }

    /**
     * Fill table row
     *
     * @param array $a_set data array
     */
    protected function fillRow($a_set)
    {
        global $lng, $ilCtrl;

        // properties
        $this->tpl->setVariable("VAL_ID", $a_set["id"]);
        $this->tpl->setVariable("VAL_NUMBER", $a_set["full_nr"]);
        $this->tpl->setVariable("VAL_LAST_NAME", $a_set["last_name"]);
        $this->tpl->setVariable("VAL_FIRST_NAME", $a_set["first_name"]);
        $this->tpl->setVariable("VAL_CITIZENSHIP", $a_set["citizenship"]);
        $this->tpl->setVariable("VAL_TYPE", $a_set["type"]);
        $this->tpl->setVariable("VAL_SIGNED_BY", $a_set["signed_by"]);
        
        $this->tpl->setVariable("VAL_BIRTHDATE", ilDatePresentation::formatDate(
            new ilDate($a_set["birthdate"], IL_CAL_DATE)
        ));
    
        $this->tpl->setVariable("VAL_ISSUED_ON", ilDatePresentation::formatDate(
            new ilDate($a_set["issued_on"], IL_CAL_DATE)
        ));

        $this->tpl->setVariable("VAL_VALID_UNTIL", ilDatePresentation::formatDate(
            new ilDate($a_set["valid_until"], IL_CAL_DATE)
        ));
    }

    protected function fillRowCSV($a_csv, $a_set)
    {
        $a_csv->addColumn($a_set["full_nr"]);
        $a_csv->addColumn($a_set["last_name"]);
        $a_csv->addColumn($a_set["first_name"]);
        $a_csv->addColumn(
            ilDatePresentation::formatDate(
                new ilDate($a_set['birthdate'], IL_CAL_DATE))
        );
        $a_csv->addColumn($a_set["citizenship"]);
        $a_csv->addColumn($a_set["type"]);
        $a_csv->addColumn(
            ilDatePresentation::formatDate(
                new ilDate($a_set['valid_until'], IL_CAL_DATE))
        );
        $a_csv->addColumn(
            ilDatePresentation::formatDate(
                new ilDate($a_set['issued_on'], IL_CAL_DATE))
        );
        $a_csv->addColumn($a_set["signed_by"]);
        $a_csv->addRow();
    }
}
