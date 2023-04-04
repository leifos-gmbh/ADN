<?php
/* Copyright (c) 2010 Leifos, GPL, see docs/LICENSE */

include_once("./Services/Table/classes/class.ilTable2GUI.php");

/**
 * Assignment table GUI class (answer sheet context)
 *
 * List all candidates for an examination event, sheets can be assigned by dropdowns
 * If the sheets have already been generated, the respective dates will be displayed
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.de>
 * @version $Id: class.adnAnswerSheetAssignmentTableGUI.php 27887 2011-02-28 10:54:39Z jluetzen $
 *
 * @ingroup ServicesADN
 */
class adnAnswerSheetAssignmentTableGUI extends ilTable2GUI
{
    // [array] captions for foreign keys
    protected $map;

    // [adnExaminationEvent] examination event
    protected $event;

    // [bool]
    protected $has_case_part;

    // [bool] examination event in the past?
    protected $event_done;
    
    /**
     * Constructor
     *
     * @param object $a_parent_obj parent gui object
     * @param string $a_parent_cmd parent default command
     * @param int $a_event_id examination event id
     * @param bool $a_archived current or past event
     */
    public function __construct($a_parent_obj, $a_parent_cmd, $a_event_id, $a_archived = false)
    {
        global $ilCtrl, $lng;

        parent::__construct($a_parent_obj, $a_parent_cmd);

        $this->setId("adn_tbl_pcd");

        include_once "Services/ADN/EP/classes/class.adnExaminationEvent.php";
        $this->event = new adnExaminationEvent((int) $a_event_id);
        $this->event_done = (bool) $a_archived;
        
        $this->setTitle($lng->txt("adn_answer_sheet_assignment") . ": " .
            adnExaminationEvent::lookupName((int) $a_event_id));

        if (adnPerm::check(adnPerm::EP, adnPerm::WRITE)) {
            if (!$this->event_done) {
                $this->addCommandButton("saveSheetAssignment", $lng->txt("adn_save_assignment"));
                $this->addCommandButton("generateSheets", $lng->txt("adn_generate_sheets"));
            }

            $this->addMultiCommand(
                "downloadExaminationDocuments",
                $lng->txt("adn_download_answer_scoring_material")
            );
            $this->addColumn("", "", 1);
        }
        
        $this->addColumn($this->lng->txt("adn_last_name"), "name");
        $this->addColumn($this->lng->txt("adn_first_name"), "first_name");
        $this->addColumn($this->lng->txt("adn_permanent_address"), "address");
        $this->addColumn($this->lng->txt("adn_assigned_sheets"), "assigned");
        $this->addColumn($this->lng->txt("adn_generated_sheets"), "generated");

        $this->setDefaultOrderField("name");
        $this->setDefaultOrderDirection("asc");

        include_once "Services/ADN/MD/classes/class.adnCountry.php";
        $this->map["country"] = adnCountry::getCountriesSelect();

        include_once "Services/ADN/EP/classes/class.adnAnswerSheet.php";
        $this->map["sheets_split"] = adnAnswerSheet::getSheetsSelect($this->event->getId(), true);
        $this->map["sheets"] = adnAnswerSheet::getSheetsSelect($this->event->getId());
        
        $this->setFormAction($ilCtrl->getFormAction($a_parent_obj));
        $this->setRowTemplate("tpl.assignment_row.html", "Services/ADN/EP");

        $this->importData();

        $this->has_case_part = adnSubjectArea::hasCasePart($this->event->getType());
    }

    /**
     * Get mapping data
     *
     * @return array
     */
    public function getMap()
    {
        return $this->map;
    }

    /**
     * Import data from DB
     */
    protected function importData()
    {
        include_once "Services/ADN/EP/classes/class.adnAssignment.php";
        $assignments = adnAssignment::getAllAssignments(array("event_id" => $this->event->getId()));
        if ($assignments) {
            include_once "Services/ADN/ES/classes/class.adnCertifiedProfessional.php";
            include_once "Services/ADN/EP/classes/class.adnAnswerSheetAssignment.php";
            $data = array();
            foreach ($assignments as $item) {
                $user = new adnCertifiedProfessional($item["cp_professional_id"]);

                $this->map["candidates"][$user->getId()] = $user->getLastName() . ", " .
                    $user->getFirstName();

                $data[] = array("id" => $user->getId(),
                    "last_name" => $user->getLastName(),
                    "first_name" => $user->getFirstName(),
                    "street" => $user->getPostalStreet(),
                    "house_number" => $user->getPostalStreetNumber(),
                    "zip" => $user->getPostalCode(),
                    "city" => $user->getPostalCity(),
                    "country" => $this->map["country"][$user->getPostalCountry()],
                    "assigned" => adnAnswerSheetAssignment::getSheetsSelect(
                        $user->getId(),
                        $this->event->getId()
                    )
                    );
            }

            $this->setData($data);
            $this->setMaxCount(sizeof($data));
        }
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
        $this->tpl->setVariable("VAL_NAME", $a_set["last_name"]);
        $this->tpl->setVariable("VAL_FIRST_NAME", $a_set["first_name"]);

        $this->tpl->setVariable("VAL_STREET", $a_set["street"]);
        $this->tpl->setVariable("VAL_HNO", $a_set["house_number"]);
        $this->tpl->setVariable("VAL_ZIP", $a_set["zip"]);
        $this->tpl->setVariable("VAL_CITY", $a_set["city"]);
        $this->tpl->setVariable("VAL_COUNTRY", $a_set["country"]);

        // select
        if (adnPerm::check(adnPerm::EP, adnPerm::WRITE)) {
            $this->tpl->setCurrentBlock("cbox");
            $this->tpl->setVariable("VAL_ID", $a_set["id"]);
            $this->tpl->parseCurrentBlock();

            if (!$this->event_done) {
                if ($this->has_case_part) {
                    $selects = array(adnAnswerSheet::TYPE_MC, adnAnswerSheet::TYPE_CASE);
                } else {
                    $selects = array(adnAnswerSheet::TYPE_MC);
                }
                foreach ($selects as $type) {
                    $this->tpl->setCurrentBlock("assigned_option");
                    $this->tpl->setVariable(
                        "VAL_ASSIGNED_OPTION_TITLE",
                        $this->lng->txt("adn_no_assignment")
                    );
                    $this->tpl->setVariable("VAL_ASSIGNED_OPTION_ID", 0);
                    $this->tpl->parseCurrentBlock();

                    if ($this->map["sheets_split"][$type]) {
                        foreach ($this->map["sheets_split"][$type] as $id => $caption) {
                            $this->tpl->setVariable("VAL_ASSIGNED_OPTION_TITLE", $caption);
                            $this->tpl->setVariable("VAL_ASSIGNED_OPTION_ID", $id);
                            if (in_array($id, $a_set["assigned"])) {
                                $this->tpl->setVariable(
                                    "VAL_ASSIGNED_OPTION_STATUS",
                                    " selected=\selected\""
                                );
                            }
                            $this->tpl->parseCurrentBlock();
                        }
                    }

                    $this->tpl->setCurrentBlock("assigned");
                    $this->tpl->setVariable("VAL_ASSIGNED_ID", $a_set["id"]);
                    $this->tpl->parseCurrentBlock();
                }
            }
            // read-only
            elseif ($a_set["assigned"]) {
                $all = [];
                foreach ($a_set["assigned"] as $sheet_id) {
                    $all[] = $this->map["sheets"][$sheet_id];
                }
                $this->tpl->setCurrentBlock("assigned_static");
                $this->tpl->setVariable("VAL_ASSIGNED_INFO", implode(", ", $all));
                $this->tpl->parseCurrentBlock();
            }
        }

        if ($a_set["assigned"]) {
            include_once './Services/ADN/Report/classes/class.adnReportAnswerSheet.php';
            foreach ($a_set["assigned"] as $assigned) {
                if ($mtime = adnReportAnswerSheet::lookupCandidateSheetGenerated(
                    $a_set['id'],
                    $assigned
                )) {
                    $this->tpl->setCurrentBlock("generated");
                    $this->tpl->setVariable("VAL_GENERATED_NUMBER", $this->map['sheets'][$assigned]);
                    $this->tpl->setVariable(
                        "VAL_GENERATED_DATE",
                        ilDatePresentation::formatDate(new ilDate($mtime, IL_CAL_UNIX))
                    );
                    $this->tpl->parseCurrentBlock();
                }
            }
        }
    }
}
