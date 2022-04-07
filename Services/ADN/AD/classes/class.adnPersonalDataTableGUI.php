<?php
// cr-008 start
/* Copyright (c) 2010 Leifos, GPL, see docs/LICENSE */

include_once("./Services/Table/classes/class.ilTable2GUI.php");

/**
 * Personal data table GUI class
 *
 * List and filter personal data
 *
 * @author Alex Killing <killing@leifos.de>
 * @version $Id$
 *
 * @ingroup ServicesADN
 */
class adnPersonalDataTableGUI extends ilTable2GUI
{

    protected string $mode;
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
    public function __construct($a_parent_obj, $a_parent_cmd, $a_mode)
    {
        global $ilCtrl, $lng;

        $this->setId("adn_tbl_adpdm");
        parent::__construct($a_parent_obj, $a_parent_cmd);

        $this->mode = $a_mode;

        $this->setDisableFilterHiding(true);

        $this->setTitle($lng->txt("adn_ad_personal_data"));

        $this->addMultiCommand("delete", $lng->txt("delete"));

        $this->addColumn("", "", "1px", true);
        $this->addColumn($this->lng->txt("adn_last_name"), "last_name");
        $this->addColumn($this->lng->txt("adn_first_name"), "first_name");
        $this->addColumn($this->lng->txt("adn_birthdate"), "birthdate");
        $this->addColumn($this->lng->txt("adn_city"), "pa_city");
        $this->addColumn($this->lng->txt("adn_street"), "pa_street");
        $this->addColumn("ID", "id");
        $this->addColumn($this->lng->txt("adn_ad_wmo_created"), "wmo_id");
        $this->addColumn($this->lng->txt("adn_ad_created_date"), "create_date");
        $this->addColumn($this->lng->txt("adn_ad_last_certificate"), "last_certificate");
        $this->addColumn($this->lng->txt("actions"));
        
        $this->setDefaultOrderField("last_name");
        $this->setDefaultOrderDirection("asc");

        $this->setFormAction($ilCtrl->getFormAction($a_parent_obj));
        $this->setRowTemplate("tpl.personal_data_row.html", "Services/ADN/AD");

        $this->initFilter();
        $this->importData();
    }

    /**
     * Import data from DB
     */
    protected function importData()
    {
        include_once "Services/ADN/AD/classes/class.adnPersonalData.php";
        $data = adnPersonalData::getData($this->filter, $this->mode);
        $this->setData($data);
        //$this->setMaxCount(sizeof($users));
    }

    /**
     * Init filter
     */
    public function initFilter()
    {
        global $lng;

        if (!in_array($this->mode, array("cand", "cert"))) {

            // equal last name
            $name = $this->addFilterItemByMetaType(
                "equal_last_name",
                self::FILTER_CHECKBOX,
                false,
                $lng->txt("adn_ad_equal_last_name")
            );
            $name->readFromSession();
            $this->filter["equal"]["last_name"] = $name->getChecked();

            // equal birthdate
            $name = $this->addFilterItemByMetaType(
                "equal_birthdate",
                self::FILTER_CHECKBOX,
                false,
                $lng->txt("adn_ad_equal_birthdate")
            );
            $name->readFromSession();
            $this->filter["equal"]["birthdate"] = $name->getChecked();

            // equal city
            $name = $this->addFilterItemByMetaType(
                "equal_city",
                self::FILTER_CHECKBOX,
                false,
                $lng->txt("adn_ad_equal_city")
            );
            $name->readFromSession();
            $this->filter["equal"]["pa_city"] = $name->getChecked();

            // equal street
            $name = $this->addFilterItemByMetaType(
                "equal_street",
                self::FILTER_CHECKBOX,
                false,
                $lng->txt("adn_ad_equal_street")
            );
            $name->readFromSession();
            $this->filter["equal"]["pa_street"] = $name->getChecked();
        }

        // wmo
        /*			 see bug #15
        include_once("./Services/ADN/MD/classes/class.adnWMO.php");
        $options = array(
            "" => $lng->txt("adn_filter_all")
        );
        foreach (adnWMO::getAllWMOs(true) as $wmo)
        {
            $options[$wmo["id"]] = $wmo["name"];
        }
        $wmo = $this->addFilterItemByMetaType("registered_by", self::FILTER_SELECT, false,
            $lng->txt("adn_ad_wmo"));
        $wmo->setOptions($options);
        $wmo->readFromSession();
        $this->filter["registered_by"] = $wmo->getValue();
        */
    }

    /**
     * Fill table row
     *
     * @param array $a_set data array
     */
    protected function fillRow($a_set)
    {
        global $ilCtrl, $lng;

        $ilCtrl->setParameter($this->parent_obj, "pid", $a_set["id"]);
        $this->tpl->setCurrentBlock("action");
        $this->tpl->setVariable("HREF_CMD", $ilCtrl->getLinkTarget($this->parent_obj, "showPersonalDataDetails"));
        $this->tpl->setVariable("TXT_CMD", $lng->txt("adn_details"));
        $this->tpl->parseCurrentBlock();
        $ilCtrl->setParameter($this->parent_obj, "pid", $_GET["pid"]);

        $ilCtrl->setParameterByClass("adnCertificateGUI", "pid", $a_set["id"]);

        if ($a_set["foreign_cert_handed_in"]) {
            $this->tpl->setCurrentBlock("action");
            $this->tpl->setVariable("TXT_CMD", $lng->txt("adn_extend_certificate_foreign"));
            $this->tpl->setVariable("HREF_CMD", $ilCtrl->getLinkTargetByClass(array("adnBaseGUI", "adnCertifiedProfessionalGUI", "adnCertificateGUI"), "extendCertificate"));
            $this->tpl->parseCurrentBlock();
        }

        $ilCtrl->setParameterByClass("adnCertificateGUI", "pid", $_GET["id"]);

        // properties
        $this->tpl->setVariable("VAL_LAST_NAME", $a_set["last_name"]);
        $this->tpl->setVariable("VAL_FIRST_NAME", $a_set["first_name"]);
        $this->tpl->setVariable("VAL_BIRTHDATE", ilDatePresentation::formatDate(
            new ilDate($a_set["birthdate"], IL_CAL_DATE)
        ));
        $this->tpl->setVariable("VAL_CITY", $a_set["pa_city"]);
        $this->tpl->setVariable("VAL_STREET", $a_set["pa_street"] . " " . $a_set["pa_street_no"]);
        $this->tpl->setVariable("VAL_ID", $a_set["id"]);
        include_once("./Services/ADN/MD/classes/class.adnWMO.php");
        $this->tpl->setVariable("WMO", adnWMO::lookupName($a_set["registered_by_wmo_id"]));

        $this->tpl->setVariable("CREATE_DATE", ilDatePresentation::formatDate(
            new ilDate($a_set["create_date"], IL_CAL_DATETIME)
        ));
        $this->tpl->setVariable("LAST_CERTIFICATE", ilDatePresentation::formatDate(
            new ilDate($a_set["last_certificate"], IL_CAL_DATE)
        ));
    }
}
// cr-008 end
