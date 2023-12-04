<?php
/* Copyright (c) 2010 Leifos, GPL, see docs/LICENSE */

include_once("./Services/Table/classes/class.ilTable2GUI.php");

/**
 * ADN certificate table GUI class
 *
 * List all certificates
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.de>
 * @version $Id: class.adnCertificateTableGUI.php 30199 2011-08-08 15:07:04Z smeyer $
 *
 * @ingroup ServicesADN
 */
class adnCertificateTableGUI extends ilTable2GUI
{
    // [array] options for select filters (needed for value mapping)
    protected $filter_options;

    // [object] certified professional
    protected $cp;

    /**
     * Constructor
     *
     * @param object $a_parent_obj parent gui object
     * @param string $a_parent_cmd parent default command
     * @param int $a_cp_id professional id
     */
    public function __construct($a_parent_obj, $a_parent_cmd, $a_cp_id = 0)
    {
        global $ilCtrl, $lng;

        $this->setId("adn_tbl_crt");

        $this->cp_id = (int) $a_cp_id;
        if ($this->cp_id > 0) {
            include_once("./Services/ADN/ES/classes/class.adnCertifiedProfessional.php");
            $this->cp = new adnCertifiedProfessional($this->cp_id);
        }
        
        parent::__construct($a_parent_obj, $a_parent_cmd);

        // title
        if ($this->cp_id == 0) {
            $this->setTitle($lng->txt("adn_certificates"));
        } else {
            $this->setTitle($lng->txt("adn_certificates") . ": " .
                $this->cp->getLastname() . ", " . $this->cp->getFirstname());
        }

        // get wmo id of current user
        include_once("./Services/ADN/AD/classes/class.adnUser.php");
        $this->user_wmo = adnUser::lookupWmoId();

        // table columns
        $this->addColumn($this->lng->txt("adn_number"), "full_nr");
        $this->addColumn($this->lng->txt("adn_last_name"), "last_name");
        $this->addColumn($this->lng->txt("adn_first_name"), "first_name");
        $this->addColumn($this->lng->txt("adn_birthdate"), "birthdate");
        $this->addColumn($this->lng->txt("adn_valid_until"), "valid_until");
        if ($this->cp_id > 0) {
            $this->addColumn($this->lng->txt("adn_archived_on"), "last_update");
        }
        $this->addColumn($this->lng->txt('adn_certificate_type'), 'certificate_type');
        $this->addColumn($this->lng->txt('adn_certificate_status'), 'certificate_status');

        $this->addColumn($this->lng->txt("actions"));
        $this->setDefaultOrderField("full_nr");
        $this->setDefaultOrderDirection("asc");

        $this->setFormAction($ilCtrl->getFormAction($a_parent_obj));
        $this->setRowTemplate("tpl.certificates_row.html", "Services/ADN/CP");

        $this->importData();
    }

    /**
     * Import data from DB
     */
    protected function importData()
    {
        $include_invalids = $_SESSION["ct_ct_invalid"];
        $exclude_valids = false;
        if ($this->cp_id == 0) {
            $this->initFilter();
            $cert_filter = $this->filter;
        } else {
            $cert_filter = array("cp_professional_id" => $this->cp_id);
            $include_invalids = true;
            $exclude_valids = true;
        }

        include_once("./Services/ADN/ES/classes/class.adnCertificate.php");
        $certificates = adnCertificate::getAllCertificates(
            $cert_filter,
            $include_invalids,
            false,
            $exclude_valids
        );

        $this->setData($certificates);
        $this->setMaxCount(sizeof($certificates));
    }

    /**
     * Init filter
     */
    public function initFilter()
    {
        global $lng;

        $number = $this->addFilterItemByMetaType(
            "number",
            self::FILTER_TEXT,
            false,
            $lng->txt("adn_number")
        );
        $number->readFromSession();
        $this->filter["number"] = $number->getValue();

        $last_name = $this->addFilterItemByMetaType(
            "last_name",
            self::FILTER_TEXT,
            false,
            $lng->txt("adn_last_name")
        );
        $last_name->readFromSession();
        $this->filter["last_name"] = $last_name->getValue();

        $first_name = $this->addFilterItemByMetaType(
            "first_name",
            self::FILTER_TEXT,
            false,
            $lng->txt("adn_first_name")
        );
        $first_name->readFromSession();
        $this->filter["first_name"] = $first_name->getValue();
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
        $ilCtrl->setParameter($this->parent_obj, "ct_id", $a_set["id"]);

        $cert = new adnCertificate($a_set['id']);

        // details
        $this->tpl->setCurrentBlock("action");
        $this->tpl->setVariable("TXT_CMD", $lng->txt("adn_show_details"));
        if ($this->cp_id == 0 && $cert->getUuid() === '') {
            $this->tpl->setVariable(
                "HREF_CMD",
                $ilCtrl->getLinkTarget($this->parent_obj, "showCertificate")
            );
        } elseif ($this->cp_id == 0 && $cert->getUuid() !== '') {
            $this->tpl->setVariable(
                'HREF_CMD',
                $ilCtrl->getLinkTarget($this->parent_obj, 'showCard')
            );
        } else {
            $this->tpl->setVariable(
                "HREF_CMD",
                $ilCtrl->getLinkTarget($this->parent_obj, "showInvalidCertificate")
            );
        }
        $this->tpl->parseCurrentBlock();

        if ($this->cp_id == 0 && adnPerm::check(adnPerm::CP, adnPerm::WRITE)) {
            // cr-008 start
            include_once './Services/ADN/Report/classes/class.adnReportCertificate.php';
            if (
                $a_set["status"] == adnCertificate::STATUS_VALID &&
                !adnCertificate::isDuplicate($a_set['id']) &&
                !$a_set["is_extension"] &&
                $cert->getUuid() === ''
            ) {
                $this->tpl->setCurrentBlock("action");
                $this->tpl->setVariable("TXT_CMD", $lng->txt("adn_download_certificate"));
                $this->tpl->setVariable(
                    "HREF_CMD",
                    $ilCtrl->getLinkTarget($this->parent_obj, "downloadCertificate")
                );
                $this->tpl->parseCurrentBlock();
            }
            // cr-008 end

            // extend
            $this->tpl->setCurrentBlock("action");
            $this->tpl->setVariable("TXT_CMD", $lng->txt("adn_extend_certificate"));
            $this->tpl->setVariable(
                "HREF_CMD",
                $ilCtrl->getLinkTarget($this->parent_obj, "extendCertificate")
            );
            $this->tpl->parseCurrentBlock();
        }

        // only show these things for valid
        if (
            $this->cp_id == 0 &&
            $a_set["status"] == adnCertificate::STATUS_VALID
        ) {
            if (adnPerm::check(adnPerm::CP, adnPerm::WRITE)) {
                // duplicate
                if ($a_set["issued_by_wmo"] == $this->user_wmo) {
                    $this->tpl->setCurrentBlock("action");
                    $this->tpl->setVariable("TXT_CMD", $lng->txt("adn_duplicate_certificate"));
                    $this->tpl->setVariable(
                        "HREF_CMD",
                        $ilCtrl->getLinkTarget($this->parent_obj, "duplicateCertificate")
                    );
                    $this->tpl->parseCurrentBlock();
                }

                // generate invoice
                $this->tpl->setCurrentBlock("action");
                $this->tpl->setVariable("TXT_CMD", $lng->txt("adn_generate_invoice"));
                $this->tpl->setVariable(
                    "HREF_CMD",
                    $ilCtrl->getLinkTarget($this->parent_obj, "generateInvoice")
                );
                $this->tpl->parseCurrentBlock();

                // edit
                if ($cert->getUuid() === '') {
                    $this->tpl->setCurrentBlock("action");
                    $this->tpl->setVariable("TXT_CMD", $lng->txt("edit"));
                    $this->tpl->setVariable(
                        "HREF_CMD",
                        $ilCtrl->getLinkTarget($this->parent_obj, "edit")
                    );
                    $this->tpl->parseCurrentBlock();
                }
            }

            // download extend
            include_once './Services/ADN/Report/classes/class.adnReportCertificate.php';
            if (
                $a_set["is_extension"] &&
                adnReportCertificate::hasCertificate($a_set['id']) &&
                $cert->getUuid() === ''
            ) {
                $this->tpl->setCurrentBlock("action");
                $this->tpl->setVariable("TXT_CMD", $lng->txt("adn_download_extension"));
                $this->tpl->setVariable(
                    "HREF_CMD",
                    $ilCtrl->getLinkTarget($this->parent_obj, "downloadExtension")
                );
                $this->tpl->parseCurrentBlock();
            }

            // download duplicate
            if (
                adnCertificate::isDuplicate($a_set['id']) &&
                adnReportCertificate::hasCertificate($a_set['id']) &&
                $cert->getUuid() === ''
            ) {
                $this->tpl->setCurrentBlock("action");
                $this->tpl->setVariable("TXT_CMD", $lng->txt("adn_download_duplicate"));
                $this->tpl->setVariable(
                    "HREF_CMD",
                    $ilCtrl->getLinkTarget($this->parent_obj, "downloadDuplicate")
                );
                $this->tpl->parseCurrentBlock();
            }

            // download invoice
            include_once './Services/ADN/Report/classes/class.adnReportInvoice.php';
            if (adnReportInvoice::hasInvoice($a_set['id'])) {
                $this->tpl->setCurrentBlock("action");
                $this->tpl->setVariable("TXT_CMD", $lng->txt("adn_download_invoice"));
                $this->tpl->setVariable(
                    "HREF_CMD",
                    $ilCtrl->getLinkTarget($this->parent_obj, "downloadInvoice")
                );
                $this->tpl->parseCurrentBlock();
            }
        
            $ilCtrl->setParameter($this->parent_obj, "ct_id", "");

            // ... more certificates
            if (adnCertificate::countCertificatesForProfessional($a_set["cp_professional_id"]) > 1) {
                $ilCtrl->setParameter($this->parent_obj, "cp_id", $a_set["cp_professional_id"]);
                $this->tpl->setCurrentBlock("action");
                $this->tpl->setVariable("TXT_CMD", $lng->txt("adn_more_certificates"));
                $this->tpl->setVariable(
                    "HREF_CMD",
                    $ilCtrl->getLinkTarget($this->parent_obj, "showCertificatesOfProfessional")
                );
                $this->tpl->parseCurrentBlock();
                $ilCtrl->setParameter($this->parent_obj, "cp_id", "");
            }
        }

        if ($this->cp_id > 0) {
            // archived_on
            $this->tpl->setCurrentBlock("action");
            if ($a_set["status"] == adnCertificate::STATUS_INVALID) {
                $this->tpl->setVariable(
                    "VAL_ARCHIVED_ON",
                    ilDatePresentation::formatDate(
                        new ilDate($a_set["last_update"], IL_CAL_DATE)
                    )
                );
            } else {
                $this->tpl->setVariable(
                    "VAL_ARCHIVED_ON",
                    ilDatePresentation::formatDate(
                        new ilDate($a_set["valid_until"], IL_CAL_DATE)
                    )
                );
            }
            $this->tpl->parseCurrentBlock();
        }

        if ($cert->getUuid() === '') {
            $this->tpl->setVariable('VAL_CERTIFICATE_TYPE', $this->lng->txt('adn_certificate_type_paper'));
            $this->tpl->setVariable('VAL_CERTIFICATE_STATUS', '');
        } else {
            $this->tpl->setVariable('VAL_CERTIFICATE_TYPE', $this->lng->txt('adn_certificate_type_card'));
            $this->tpl->setVariable('VAL_CERTIFICATE_STATUS', $this->lng->txt('adn_certificate_status_' . $a_set['card_status']));
        }

        // properties
        $this->tpl->setVariable("VAL_NUMBER", $a_set["full_nr"]);
        $this->tpl->setVariable("VAL_LAST_NAME", $a_set["last_name"]);
        $this->tpl->setVariable("VAL_FIRST_NAME", $a_set["first_name"]);

        $this->tpl->setVariable("VAL_BIRTHDATE", ilDatePresentation::formatDate(
            new ilDate($a_set["birthdate"], IL_CAL_DATE)
        ));

        if ($this->cp_id == 0 && $a_set["status"] == adnCertificate::STATUS_INVALID) {
            $this->tpl->setVariable("VAL_VALID", "-");
        } else {
            $this->tpl->setVariable("VAL_VALID", ilDatePresentation::formatDate(
                new ilDate($a_set["valid_until"], IL_CAL_DATE)
            ));
        }
    }
}
