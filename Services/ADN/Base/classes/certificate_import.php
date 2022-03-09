<?php

/**
 * Normalize data from certificate / professional source files into csv
 *
 * Only execute this if you know what you're doing
 */

exit();

$invalids = $res = array();

foreach (glob("G://ADN Sachkundigenverzeichnis/20110308_WSD/csv/*.csv") as $file) {
    // foreach(glob("G://ADN Sachkundigenverzeichnis/20110404_WSD_Ost_Update/csv/*.csv") as $file)
    readCSV($file, $res, $invalids);
}
foreach ($invalids as $column => $values) {
    echo "<br />" . $column . ": " . implode("; ", array_unique($values));
}
echo "<hr />";

$pros = array();
foreach ($res as $item) {
    $hash = strtolower($item["last_name"] . $item["first_name"] . $item["birthdate"]);
    $pros[$hash][] = $item;
}

$fp = fopen("test.csv", "w");

$header = array("Nachname", "Vorname", "Geburtsdatum", "Staatsangehörigkeit", "Strasse", "Hausnr",
    "PLZ", "Ort", "ADN-Nummer", "Art der Prüfung", "8.2.1.3 Trockengüterschiffe",
    "8.2.1.3 Tankschiffe",
    "8.2.1.5 Typ G (Gas)", "8.2.1.7 Typ C (Chemie)", "Ausstellungsjahr", "Ausgestellt am",
    "Ausgestellt durch",
    "Unterschrieben durch", "Gültig bis", "ZÜV", "Bemerkung", "Schulung", "Quelle");
foreach ($header as $idx => $col) {
    $header[$idx] = utf8_decode($col);
}
fputcsv($fp, $header, ";");

$ref = array("last_name", "first_name", "birthdate", "citizenship", "street", "hsno", "zip", "city",
    "adn_number", "type_of_exam", "type_dry", "type_tank", "type_gas", "type_chem", "year_issued",
    "issued_on", "wmo", "signature", "valid_until", "züv", "comment", "training","file");
ksort($pros);
foreach ($pros as $items) {
    foreach ($items as $item) {
        $row = array();
        foreach ($ref as $col) {
            $row[] = utf8_decode($item[$col]);
            unset($item[$col]);
        }
        fputcsv($fp, $row, ";");
    }
}

fclose($fp);


function readCSV($a_file, array &$res, array &$invalids)
{
    $column_map = array(
        "ADNR/Nummer" => "adn_number",
        "Ausstellungsjahr" => "year_issued",
        "Jahr der Ausstellung" => "year_issued",
        "Jahr" => "year_issued",
        "Name" => "last_name",
        "Vorname" => "first_name",
        "Geboren am" => "birthdate",
        "Staatsangehörigkeit1" => "citizenship",
        "Staatsangehörigkeit" => "citizenship",
        "Art der Prüfung" => "type_of_exam",
        "Art der Prüfung:" => "type_of_exam",
        "Straße" => "street",
        "Hausnummer" => "hsno",
        "Postleitzahl" => "zip",
        "Wohnort" => "city",
        "ZÜV-Nummer" => "züv",
        "Gültigkeit bis" => "valid_until",
        "Auststellungsdatum" => "issued_on",
        "Lehrgang von bis" => "training",
        "Name des Ausstellers" => "signature",
        "Bemerkungen" => "comment",
        "Nr. der Bescheinigung" => "adn_number",
        "Geburtsdatum" => "birthdate",
        "Staatsangehörigkeit (Länderkürzel)" => "citizenship",
        "8.2.1.3 Trockengüterschiffe" => "type_dry",
        "8.2.1.3 Tankschiffe" => "type_tank",
        "8.2.1.5 Typ G (Gas)" => "type_gas",
        "8.2.1.7 Typ C (Chemie)" => "type_chem",
        "Bescheinigung ist gültig bis" => "valid_until",
        "Ausstellungsdatum" => "issued_on",
        "ausgestellt durch" => "wmo",
        "Ausgestellt durch" => "signature",
        "Unterschrieben durch" => "signature"
    );

    $countries = array(
        "deutsch" => "DE",
        "Deutsch" => "DE",
        "D" => "DE",
        "DE" => "DE",
        "deuutsch" => "DE",
        "deusch" => "DE",
        "polnisch" => "PL",
        "PL" => "PL",
        "Pl" => "PL",
        "CZ" => "CZ",
        "CH" => "CH",
        "tschechisch" => "CZ",
        "türkisch" => "TR",
        "YU" => "YU", // ???
        "TR" => "TR",
        "ungarisch" => "HU",
        "rumänisch" => "RO",
        "niederländisch" => "NL",
        "bulgarisch" => "BG",
        "ukrainisch" => "UA",
        "F" => "F",
        "HR" => "HR",
        "slowakisch" => "SK",
        "HU" => "HU",
        "ROM" => "RO",
        "HUN" => "HU",
        "CR" => "HK",
        "Hun" => "HU",
        "SK" => "SK",
        "NL" => "NL",
        "A" => "AT",
        "UA" => "UA",
        "BG" => "BG",
        "Hu" => "HU",
        "RUS" => "RU",
        "I" => "IT",
        "B" => "BE",
        "HRV" => "HR",
        "R" => "RO",
        "E" => "ES",
        "BIH" => "BA",
        "Staatenlos" => "",
        "Sk" => "SK",
        "KAZ" => "KZ",
        "KZ" => "KZ",
        "L" => "LU",
        "Serbien" => "RS",
        "MK" => "MK",
        "d" => "DE",
        "Montenegro" => "ME",
        "N" => "NL",
        "Austria" => "AT",
        "Spanien" => "ES"
        );

    $wmos = array("WSD Nord", "WSD Nordwest", "WSD Mitte", "WSD West",
        "WSD Südwest", "WSD Süd", "WSD Ost");
    
    $cols = array();
    if (($handle = fopen($a_file, "r")) !== false) {
        while (($row = fgetcsv($handle, 1000, ";")) !== false) {
            if (!$cols) {
                foreach ($row as $idx => $col) {
                    if (trim($col)) {
                        $cols[$idx] = trim(utf8_encode($col));
                        if (!isset($column_map[$cols[$idx]])) {
                            var_dump($col);
                        }
                    }
                }
            } else {
                $item = array();
                foreach ($row as $idx => $value) {
                    $invalid = false;
                    $value = trim(utf8_encode($value));
                    if ($value !== "" && isset($cols[$idx])) {
                        switch ($column_map[$cols[$idx]]) {
                            case "adn_number":
                                if (!is_numeric($value)) {
                                    $value = str_replace("/G", "", $value);
                                    $value = str_replace("/ G", "", $value);
                                    $value = str_replace("/C", "", $value);
                                    $value = str_replace("/ C", "", $value);
                                    $value = str_replace("Nr.", "", $value);
                                    $value = str_replace("Nr", "", $value);
                                    $check = explode("/", $value);
                                    if (sizeof($check) == 2) {
                                        if ($check[1] > 2000) {
                                            $item["year_issued"] = $check[1];
                                        } else {
                                            $item["year_issued"] = (int) $check[1] + 2000;
                                        }
                                        $value = $check[0];
                                    }
                                }
                                if (!is_numeric($value) || !$value) {
                                    $invalid = true;
                                }
                                break;

                            case "year_issued":
                                $value = (int) $value + 2000;
                                break;

                            case "birthdate":
                            case "issued_on":
                            case "valid_until":
                                if ($value == "#Name?") {
                                    $value = null;
                                // $invalid = true;
                                } else {
                                    $check = explode(".", str_replace("..", ".", $value));
                                    if (@checkdate($check[1], $check[0], $check[2])) {
                                        $value = $check[2] . "-" . $check[1] . "-" . $check[0];
                                    } else {
                                        $invalid = true;
                                    }
                                }
                                break;

                            case "type_of_exam":
                                $value = strtolower($value);
                                if ($value != "w" && $value != "p") {
                                    $invalid = true;
                                }
                                break;

                            case "type_dry":
                            case "type_gas":
                            case "type_tank":
                            case "type_chem":
                                if (strtolower($value) == "x") {
                                    $value = true;
                                } elseif (!$value) {
                                    $value = false;
                                } else {
                                    $invalid = true;
                                }
                                break;

                            case "citizenship":
                                if (!isset($countries[$value])) {
                                    $invalid = true;
                                } else {
                                    $value = $countries[$value];
                                }
                                break;

                            case "wmo":
                                if (!in_array($value, $wmos)) {
                                    $invalid = true;
                                }
                                break;

                            default:
                                break;
                        }

                        if ($invalid) {
                            $invalids[$column_map[$cols[$idx]]][] = $value;
                            $value = "###" . $value;
                        }

                        $item[$column_map[$cols[$idx]]] = $value;
                    }
                }

                $item["file"] = basename($a_file);

                $res[] = $item;
            }
        }
    }
    fclose($handle);
}
