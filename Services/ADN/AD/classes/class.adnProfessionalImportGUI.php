<?php
/* Copyright (c) 2010 Leifos, GPL, see docs/LICENSE */

/**
 * Certified professional import GUI class
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.de>
 * @version $Id: class.adnProfessionalImportGUI.php 45990 2013-11-05 11:49:10Z jluetzen $
 *
 * @ilCtrl_Calls adnProfessionalImportGUI:
 *
 * @ingroup ServicesADN
 */
class adnProfessionalImportGUI
{
    const COL_CERTIFICATE_NR = 0;
    const COL_CERTIFICATE_YEAR = 1;
    const COL_LAST_NAME = 2;
    const COL_FIRST_NAME = 3;
    const COL_BIRTHDATE = 4;
    const COL_CITIZENSHIP = 5;
    const COL_CERTIFICATE_DRY = 6;
    const COL_CERTIFICATE_TANK = 7;
    const COL_CERTIFICATE_GAS = 8;
    const COL_CERTIFICATE_CHEM = 9;
    const COL_CERTIFICATE_VALID_UNTIL = 10;
    const COL_CERTIFICATE_ISSUED_ON = 11;
    const COL_CERTIFICATE_ISSUED_BY_WMO = 12;
    const COL_CERTIFICATE_SIGNATURE = 13;
    
    // unused
    const COL_POSTAL_STREET = null;
    const COL_POSTAL_STREET_NUMBER = null;
    const COL_POSTAL_CODE = null;
    const COL_POSTAL_CITY = null;
    
    /**
     * Execute command
     */
    public function executeCommand()
    {
        global $ilCtrl, $lng, $tpl;
        
        $next_class = $ilCtrl->getNextClass();
        $tpl->setTitle($lng->txt("adn_md") . " - " . $lng->txt("adn_ad_icp"));
        adnIcon::setTitleIcon("ad_icp");


        // forward command to next gui class in control flow
        switch ($next_class) {
            // no next class:
            // this class is responsible to process the command
            default:
                $cmd = $ilCtrl->getCmd("importFile");

                switch ($cmd) {
                    // commands that need write permission
                    case "importFile":
                    case "confirmImport":
                    case "saveImport":
                        if (adnPerm::check(adnPerm::AD, adnPerm::WRITE)) {
                            $this->$cmd();
                        }
                        break;

                }
                break;
        }
    }
    
    /**
     * Import questions form
     *
     * @param ilPropertyFormGUI $a_form
     */
    protected function importFile(ilPropertyFormGUI $a_form = null)
    {
        global $tpl, $lng, $ilTabs, $ilCtrl;

        // remove "old" tmp files
        include_once "Services/ADN/ED/classes/class.adnQuestionExport.php";
        foreach (glob(adnQuestionExport::getFilePath() . "/tuf_*") as $file) {
            @unlink($file);
        }

        if (!$a_form) {
            $a_form = $this->initUploadForm();
        }
        $tpl->setContent($a_form->getHTML());
    }

    /**
     * Build upload form
     * @return object
     */
    protected function initUploadForm()
    {
        global  $lng, $ilCtrl;

        include_once "Services/Form/classes/class.ilPropertyFormGUI.php";
        $form = new ilPropertyFormGUI();
        $form->setTitle($lng->txt("adn_ad_icp"));
        $form->setFormAction($ilCtrl->getFormAction($this, "importFile"));

        include_once "Services/ADN/MD/classes/class.adnWMO.php";
        $wmo = new ilSelectInputGUI($lng->txt("adn_default_wmo"), "wmo");
        $wmo->setOptions(array("" => "-") + adnWMO::getWMOsSelect());
        $wmo->setRequired(true);
        $form->addItem($wmo);

        include_once "Services/ADN/MD/classes/class.adnCountry.php";
        $country = new ilSelectInputGUI($lng->txt("adn_default_country"), "country");
        $country->setOptions(array("" => "-") + adnCountry::getCountriesSelect());
        $country->setRequired(true);
        $form->addItem($country);

        $anon = new ilCheckboxInputGUI($lng->txt("adn_anonymize"), "anon");
        $form->addItem($anon);

        $limit = new ilNumberInputGUI($lng->txt("adn_limit"), "limit");
        $limit->setSize(4);
        $limit->setMaxLength(4);
        $form->addItem($limit);

        $file = new ilFileInputGUI($lng->txt("file"), "file");
        $file->setRequired(true);
        $file->setSuffixes(array("csv"));
        $form->addItem($file);

        $form->addCommandButton("confirmImport", $lng->txt("update"));
        $form->addCommandButton("importFile", $lng->txt("cancel"));

        return $form;
    }

    /**
     * Confirm import questions
     */
    protected function confirmImport()
    {
        global $tpl, $lng, $ilCtrl;

        $form = $this->initUploadForm();
        if ($form->checkInput()) {
            include_once "Services/ADN/ED/classes/class.adnQuestionExport.php";
            $file = $form->getInput("file");
            $target = adnQuestionExport::getFilePath() . "/tuf_" . md5(uniqid());
        
            if (move_uploaded_file($file["tmp_name"], $target)) {
                // init dry run
                $log = $this->importCSV(
                    $target,
                    $form->getInput("wmo"),
                    $form->getInput("country"),
                    $form->getInput("anon"),
                    $form->getInput("limit")
                );
                                                
                if ($log !== false) {
                    include_once("./Services/Utilities/classes/class.ilConfirmationGUI.php");
                    $cgui = new ilConfirmationGUI();
                    $cgui->setFormAction($ilCtrl->getFormAction($this));
                    $cgui->setHeaderText($lng->txt("adn_sure_import_mc_questions"));
                    $cgui->setCancel($lng->txt("cancel"), "importFile");
                    
                    $certs = $existing = $all = $errors_count = $notices_count = 0;
                    $has_errors = false;
                    foreach ($log as $item) {
                        // import does only work without any errors
                        if ($item["errors"]) {
                            $has_errors = true;
                            foreach ($item["errors"] as $line => $errors) {
                                $txt = "Zeile " . $line . ": " . implode(" | ", $errors) .
                                    '<div class="small" style="color:#888">' .
                                    implode(";", $item["org"][$line]) . "</div>";
                                
                                $errors_count++;
                            }
                            
                            $cgui->addItem("dummy_id[]", 1, $txt);
                        }
                        
                        // notices still allow import
                        if ($item["notices"]) {
                            foreach ($item["notices"] as $line => $notices) {
                                $txt = "Zeile " . $line . ": " . '<span style="color:#444">' . implode(" | ", $notices) . '</span>' .
                                    '<div class="small" style="color:#888">' .
                                    implode(";", $item["org"][$line]) . "</div>";
                                
                                $notices_count++;
                            }
                            
                            $cgui->addItem("dummy_id[]", 1, $txt);
                        }
                        
                        $all++;
                        $certs += sizeof($item["certificates"]);
                        if ($item["existing_id"]) {
                            $existing++;
                        }
                    }
                    
                    if (!$has_errors) {
                        $cgui->addItem(
                            "dummy_id[]",
                            1,
                            $lng->txt("adn_professional_import_new") . ": " .
                            ($all - $existing)
                        );
                        $cgui->addItem(
                            "dummy_id[]",
                            1,
                            $lng->txt("adn_professional_import_existing") . ": " . $existing
                        );
                        $cgui->addItem(
                            "dummy_id[]",
                            1,
                            $lng->txt("adn_certificates") . ": " . $certs
                        );
                        
                        $cgui->setConfirm($lng->txt("import"), "saveImport");
                        
                        // add form values for next request
                        $cgui->addHiddenItem("token", basename($target));
                        $cgui->addHiddenItem("wmo", $form->getInput("wmo"));
                        $cgui->addHiddenItem("country", $form->getInput("country"));
                        $cgui->addHiddenItem("anon", $form->getInput("anon"));
                        $cgui->addHiddenItem("limit", $form->getInput("limit"));
                    } else {
                        $cgui->addItem("dummy_id[]", 1, "Anmerkungen: " . $notices_count);
                        $cgui->addItem("dummy_id[]", 1, "Fehler: " . $errors_count);
                    }
                                        
                    $tpl->setContent($cgui->getHTML() .
                        '<div class="il_Description_no_margin">Ausstellungsjahr (Spalte ' . (self::COL_CERTIFICATE_YEAR + 1) .
                            ') wird beim Import ignoriert. Fehler verhindern den Import.</div>');
                    return;
                }
            }

            ilUtil::sendFailure($lng->txt("adn_professional_import_failed"));
        }

        $form->setValuesByPost();
        $this->importFile($form);
    }
    
    /**
     * Import questions
     */
    protected function saveImport()
    {
        global $tpl, $lng, $ilCtrl;

        $token = $_REQUEST["token"];
        if ($token) {
            include_once "Services/ADN/ED/classes/class.adnQuestionImport.php";
            $target = adnQuestionExport::getFilePath() . "/" . $token;
            if (file_exists($target)) {
                if ($this->importCSV(
                    $target,
                    $_REQUEST["wmo"],
                    $_REQUEST["country"],
                    $_REQUEST["anon"],
                    $_REQUEST["limit"],
                    false
                )) {
                    unlink($target);
                    ilUtil::sendSuccess($lng->txt("adn_professional_import_success"), true);
                    $ilCtrl->redirect($this, "importFile");
                }
            }
        }
        ilUtil::sendFailure($lng->txt("adn_professional_import_failed"), true);
        $ilCtrl->redirect($this, "importFile");
    }
    
    protected function checkDate($a_counter, $a_target, &$a_value, array &$a_pro, $a_allow_future = false)
    {
        // dd.mm.yyyy vs. yyyy-mm-dd
        if (substr($a_value, 2, 1) == ".") {
            $parts = explode(".", $a_value);
            $a_value = $parts[2] . "-" . $parts[1] . "-" .
                $parts[0];
        }

        $check = new ilDate($a_value, IL_CAL_DATE);
        if ($check->isNull() ||
            substr($a_value, 0, 4) < 1900 ||
            substr($a_value, 0, 4) > 2020) {
            $a_pro["errors"][$a_counter][] = "Datum " . $a_target . " (" . $a_value . ") ist ungültig.";
            $a_value = null;
        } elseif (!$a_allow_future) {
            if ($check->get(IL_CAL_DATE) > date("Y-m-d")) {
                $a_pro["errors"][$a_counter][] = "Datum " . $a_target . " (" . $a_value . ") ist in der Zukunft.";
                $a_value = null;
            }
        }
    }

    protected function parseRow($a_counter, $a_row, array &$a_mapping_data, array &$a_pros, array &$a_defaults)
    {
        // empty row
        if (!trim(implode("", $a_row))) {
            return;
        }
        
        // encoding
        foreach ($a_row as $idx => $value) {
            if (substr($value, 0, 3) == "###") {
                $a_row[$idx] = "";
            } else {
                $a_row[$idx] = trim(utf8_encode($value));
            }
        }
                    
        
        // professional data
                        
        // user column mapping
        $map = array(
            "last_name" => self::COL_LAST_NAME,
            "first_name" => self::COL_FIRST_NAME,
            "birthdate" => self::COL_BIRTHDATE,
            "citizenship" => self::COL_CITIZENSHIP,
            "postal_street" => self::COL_POSTAL_STREET,
            "postal_street_number" => self::COL_POSTAL_STREET_NUMBER,
            "postal_code" => self::COL_POSTAL_CODE,
            "postal_city" => self::COL_POSTAL_CITY
        );
        
        $mandatory = array("last_name", "first_name", "birthdate");
        
        foreach ($mandatory as $field) {
            if ($map[$field] === null) {
                exit("user column mapping incomplete: " . $field . " is mandatory.");
            }
        }
        
        $hash = $a_row[$map["last_name"]] . ", " . $a_row[$map["first_name"]];
        
        $a_pros[$hash]["org"][$a_counter] = $a_row;
        
        if (!isset($a_pros[$hash])) {
            $a_pros[$hash] = array();
        }
        foreach ($map as $target => $col) {
            // do not overwrite existing data
            if ($col !== null && !$a_pros[$hash][$target]) {
                if ($a_row[$col]) {
                    if ($target == "birthdate") {
                        $this->checkDate($a_counter, $target, $a_row[$col], $a_pros[$hash]);
                    }
                    $a_pros[$hash][$target] = $a_row[$col];
                }
            }
        }
        
        // mandatory fields
        foreach ($mandatory as $field) {
            if (!$a_pros[$hash][$field] && $a_pros[$hash][$field] !== null) {
                $a_pros[$hash]["errors"][$a_counter][] = $field . " sollte nicht leer sein.";
            }
        }

        // map countries (by code)
        if (!is_numeric($a_pros[$hash]["citizenship"])) {
            if (array_key_exists($a_pros[$hash]["citizenship"], $a_mapping_data["countries"])) {
                $a_pros[$hash]["citizenship"] = $a_mapping_data["countries"][$a_pros[$hash]["citizenship"]];
            } elseif ($a_defaults["country"]) {
                $a_pros[$hash]["notices"][$a_counter][] = "Land " . $a_pros[$hash]["citizenship"] . " ist ungültig, setze Default.";
                $a_pros[$hash]["citizenship"] = $a_defaults["country"];
            } else {
                $a_pros[$hash]["errors"][$a_counter][] = "Land " . $a_pros[$hash]["citizenship"] . " ist ungültig.";
                $a_pros[$hash]["citizenship"] = null;
            }
        }
        
        
        // certificates
        
        // certificate column mapping
        $cmap = array(
            "cert_dry" => self::COL_CERTIFICATE_DRY,
            "cert_tank" => self::COL_CERTIFICATE_TANK,
            "cert_gas" => self::COL_CERTIFICATE_GAS,
            "cert_chem" => self::COL_CERTIFICATE_CHEM,
            "cert_nr" => self::COL_CERTIFICATE_NR,
            "cert_year" => self::COL_CERTIFICATE_YEAR,
            "issued_on" => self::COL_CERTIFICATE_ISSUED_ON,
            "issued_wmo" => self::COL_CERTIFICATE_ISSUED_BY_WMO,
            "issued_signature" => self::COL_CERTIFICATE_SIGNATURE,
            "valid_until" => self::COL_CERTIFICATE_VALID_UNTIL
        );
        
        $mandatory = array("cert_nr", "cert_year", "issued_on", "issued_signature", "valid_until", "issued_wmo");
        
        foreach ($mandatory as $field) {
            if ($cmap[$field] === null) {
                exit("certificate column mapping incomplete: " . $field . " is mandatory.");
            }
        }
        
        // map wmo (needed for hash)
        $wmo = $a_row[$cmap["issued_wmo"]];
        if (!is_numeric($wmo)) {
            if (array_key_exists($wmo, $a_mapping_data["wmos"])) {
                $wmo = $a_mapping_data["wmos"][$wmo];
            } elseif ($a_defaults["wmo"]) {
                $a_pros[$hash]["notices"][$a_counter][] = "GDWS " . $wmo . " ist ungültig, setze Default.";
                $wmo = $a_defaults["wmo"];
            } else {
                $a_pros[$hash]["errors"][$a_counter][] = "GDWS " . $wmo . " ist ungültig.";
                $wmo = null;
            }
        }
        
        $chash = $wmo . "-" . $a_row[$cmap["cert_year"]] . "-" . str_pad($a_row[$cmap["cert_nr"]], 4, "0", STR_PAD_LEFT);

        // certificate data
        if (!isset($a_pros[$hash]["certificates"][$chash])) {
            $a_pros[$hash]["certificates"][$chash] = array();
        } else {
            $lines = array_keys($a_pros[$hash]["org"]);
            $a_pros[$hash]["errors"][$a_counter][] = "Zertifikat " . $chash . " (" .
                $a_mapping_data["wmos"][$wmo] . ") doppelt? [Zeilen: " . implode(", ", $lines) . "]";
        }
        foreach ($cmap as $target => $col) {
            // do not overwrite existing data
            if ($col !== null && !$a_pros[$hash]["certificates"][$chash][$target]) {
                if ($target == "issued_on" || $target == "valid_until") {
                    $this->checkDate($a_counter, $target, $a_row[$col], $a_pros[$hash], ($target == "valid_until"));
                }
                $a_pros[$hash]["certificates"][$chash][$target] = $a_row[$col];
            }
        }
        
        // see above
        $a_pros[$hash]["certificates"][$chash]["issued_wmo"] = $wmo;
        
        // mandatory fields
        foreach ($mandatory as $field) {
            if (!$a_pros[$hash]["certificates"][$chash][$field] && $a_pros[$hash]["certificates"][$chash][$field] !== null) {
                $a_pros[$hash]["errors"][$a_counter][] = $field . " sollte nicht leer sein.";
            }
        }
        
        if (!(bool) $a_pros[$hash]["certificates"][$chash]["cert_dry"] &&
            !(bool) $a_pros[$hash]["certificates"][$chash]["cert_tank"] &&
            !(bool) $a_pros[$hash]["certificates"][$chash]["cert_gas"] &&
            !(bool) $a_pros[$hash]["certificates"][$chash]["cert_chem"]) {
            $a_pros[$hash]["errors"][$a_counter][] = "Kein gültiger Zertifikatstyp.";
        }
        
        if (strlen($a_pros[$hash]["certificates"][$chash]["cert_year"]) < 4) {
            $a_pros[$hash]["certificates"][$chash]["cert_year"] = "2" . str_pad($a_pros[$hash]["certificates"][$chash]["cert_year"], 3, 0, STR_PAD_LEFT);
        }
        
        if ($a_pros[$hash]["certificates"][$chash]["cert_year"] > date("Y")) {
            $a_pros[$hash]["errors"][$a_counter][] = "Jahr cert_year (" . $a_pros[$hash]["certificates"][$chash]["cert_year"] . ") ist in der Zukunft.";
            $a_pros[$hash]["certificates"][$chash]["cert_year"] = null;
        }
        
        if ($a_pros[$hash]["certificates"][$chash]["cert_year"] &&
            $a_pros[$hash]["certificates"][$chash]["issued_on"] &&
            $a_pros[$hash]["certificates"][$chash]["cert_year"] != substr($a_pros[$hash]["certificates"][$chash]["issued_on"], 0, 4)) {
            $a_pros[$hash]["notices"][$a_counter][] = "Ausstellungsjahr " . $a_pros[$hash]["certificates"][$chash]["cert_year"] .
                " passt nicht zu Ausstellungsdatum " . $a_pros[$hash]["certificates"][$chash]["issued_on"];
        }
        
        // duplicate check
        if ($a_pros[$hash]["last_name"] &&
            $a_pros[$hash]["first_name"] &&
            $a_pros[$hash]["birthdate"]) {
            $old_id = adnCertifiedProfessional::isUserUnique(
                $a_pros[$hash]["last_name"],
                $a_pros[$hash]["first_name"],
                new ilDate($a_pros[$hash]["birthdate"], IL_CAL_DATE),
                null,
                true
            );
            if ($old_id) {
                $a_pros[$hash]["existing_id"] = $old_id;
                /*
                $a_pros[$hash]["notices"][$a_counter][] = "Sachkundiger ".$a_pros[$hash]["last_name"].
                    ", ".$a_pros[$hash]["first_name"].", ".$a_pros[$hash]["birthdate"]." ist bereits vorhanden.";
                */
            }
        }
        
        return $a_row;
    }
    
    protected function importProfessional($a_anonymize, array $pro, $a_default_wmo)
    {
        global $ilDB;
        
        // data used for anonymization
        $ano_last = array("Meier", "Müller", "Meyer", "Mayer", "Maier", "Schmitz", "Neumann",
            "Altmann", "Hermann", "Grundmann");
        $ano_first = array("Peter", "Carsten", "Ralf", "Stefan", "Oliver", "Sascha", "Alexander",
            "Manfred", "Gunther", "Siegfried");
        /*
        $ano_geo = array("Haupt", "Neben", "Wende", "Kreis", "Berg", "Tal", "Breiten",
            "Schmal", "Schnell", "Steil");
        */
                    
        $old_id = $pro["existing_id"];

        if ((bool) $a_anonymize) {
            $pro["last_name"] = $ano_last[array_rand($ano_last, 1)];
            $pro["first_name"] = $ano_first[array_rand($ano_first, 1)];
            /*
            $pro["postal_street"] = $ano_geo[array_rand($ano_geo, 1)]."strasse";
            $pro["postal_street_number"] = mt_rand(1, 200);
            $pro["postal_code"] = mt_rand(1, 90000);
            $pro["postal_city"] = $ano_geo[array_rand($ano_geo, 1)]."stadt";
            */
        }

        // create professional data object
        $obj = new adnCertifiedProfessional($old_id);
        $obj->setLastName($pro["last_name"]);
        $obj->setFirstName($pro["first_name"]);
        $obj->setBirthdate(new ilDate($pro["birthdate"], IL_CAL_DATE));
        $obj->setCitizenship($pro["citizenship"]);
        /*
        $obj->setPostalStreet($pro["postal_street"]);
        $obj->setPostalStreetNumber($pro["postal_street_number"]);
        $obj->setPostalCode($pro["postal_code"]);
        $obj->setPostalCity($pro["postal_city"]);
        */

        // only if new entry
        if (!$old_id) {
            $obj->setSalutation("m");
            $obj->setRegisteredBy($a_default_wmo);
            $saved = $obj->save();
        } else {
            $saved = $obj->update();
        }

        if ($saved) {
            $cid = $obj->getId();
            if ($pro["certificates"]) {
                // add certificates to professional
                foreach ($pro["certificates"] as $cert) {
                    $cobj = new adnCertificate();
                    $cobj->setCertifiedProfessionalId($cid);
                    $cobj->setNumber($cert["cert_nr"]);
                    $cobj->setType(adnCertificate::DRY_MATERIAL, $cert["cert_dry"]);
                    $cobj->setType(adnCertificate::TANK, $cert["cert_tank"]);
                    $cobj->setType(adnCertificate::GAS, $cert["cert_gas"]);
                    $cobj->setType(adnCertificate::CHEMICALS, $cert["cert_chem"]);
                    $cobj->setSignedBy($cert["issued_signature"]);
                    $cobj->setIssuedByWmo($cert["issued_wmo"]);
                    $cobj->setValidUntil(new ilDate($cert["valid_until"], IL_CAL_DATE));
                    $cobj->setIssuedOn(new ilDate($cert["issued_on"], IL_CAL_DATE));
                    $cobj->setStatus(adnCertificate::STATUS_INVALID);
                    $cobj->save(false);
                }

                $valid_cp_certificates = array();
                $set = $ilDB->query("SELECT id,valid_until FROM adn_es_certificate" .
                    " WHERE cp_professional_id = " . $ilDB->quote($cid, "integer"));
                while ($row = $ilDB->fetchAssoc($set)) {
                    if ($row["valid_until"] > date("Y-m-d")) {
                        $valid_cp_certificates[$row["id"]] = $row["valid_until"];
                    }
                }
                if (sizeof($valid_cp_certificates)) {
                    // set the status of all other certificates of the user to invalid
                    $ilDB->manipulate("UPDATE adn_es_certificate" .
                        " SET status = " . $ilDB->quote(adnCertificate::STATUS_INVALID, "integer") .
                        " WHERE cp_professional_id = " . $ilDB->quote($cid, "integer"));

                    // latest is to be valid
                    asort($valid_cp_certificates);
                    $valid_cp_certificate = array_pop(array_keys($valid_cp_certificates));
                    $ilDB->manipulate("UPDATE adn_es_certificate" .
                        " SET status = " . $ilDB->quote(adnCertificate::STATUS_VALID, "integer") .
                        " WHERE id = " . $ilDB->quote($valid_cp_certificate, "integer"));
                }
            }

            // set professional wmo to last certificate wmo
            if ($cobj->getIssuedByWmo()) {
                $obj->setRegisteredBy($cobj->getIssuedByWmo());
                $obj->update();
            }
        }
    }
    
    /**
     * Import certified professional and certificate data
     *
     * @param string $a_file
     * @param int $a_default_wmo
     * @param int $a_default_country
     * @param bool $a_anonymize
     * @param bool $a_dry_run
     * @param int $a_limit
     * @return array (all, existing)
     */
    protected function importCSV(
        $a_file,
        $a_default_wmo,
        $a_default_country,
        $a_anonymize = false,
        $a_limit = null,
        $a_dry_run = true
    )
    {
        include_once "Services/ADN/ES/classes/class.adnCertifiedProfessional.php";
        include_once "Services/ADN/ES/classes/class.adnCertificate.php";
        include_once "Services/ADN/ED/classes/class.adnSubjectArea.php";

        set_time_limit(0);

        // import complete file (only a few thousand lines)
        $raw = array();
        if (($handle = fopen($a_file, "r")) !== false) {
            while (($data = fgetcsv($handle, 1000, ";")) !== false) {
                $raw[] = $data;
            }
            fclose($handle);
        }

        // remove title row
        array_shift($raw);
        
        if ($raw) {
            $mapping = array();
                        
            // wmo code mapping
            $tmp = array(
                "1" => "Nord",
                "2" => "Nordwest",
                "3" => "Mitte",
                "4" => "West",
                "5" => "Südwest",
                "6" => "Süd",
                "7" => "Ost"
            );
            include_once "Services/ADN/MD/classes/class.adnWMO.php";
            $wmos = array();
            foreach (adnWMO::getAllWMOs() as $item) {
                $code = $item["code_nr"];
                if (array_key_exists($code, $tmp)) {
                    $core = $tmp[$code];
                    $mapping["wmos"]["WSD " . $core] = $item["id"];
                    $mapping["wmos"]["GDWS - Außenstelle " . $core . " -"] = $item["id"];
                }
            }
            
            // country code mapping
            include_once "Services/ADN/MD/classes/class.adnCountry.php";
            foreach (adnCountry::getAllCountries() as $item) {
                $mapping["countries"][$item["code"]] = $item["id"];
            }
                                
            $defaults = array(
                "country" => $a_default_country,
                "wmo" => $a_default_wmo
            );
            
            $pros = array();
            foreach ($raw as $idx => $item) {
                $this->parseRow($idx + 1, $item, $mapping, $pros, $defaults);
                                                            
                if ((int) $a_limit && sizeof($pros) == (int) $a_limit) {
                    break;
                }
            }
        }
        
        if (!$a_dry_run) {
            foreach ($pros as $pro) {
                $this->importProfessional($a_anonymize, $pro, $a_default_wmo);
            }
        }

        return $pros;
    }
}
