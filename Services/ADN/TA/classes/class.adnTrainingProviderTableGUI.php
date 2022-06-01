<?php
/* Copyright (c) 2010 Leifos, GPL, see docs/LICENSE */

include_once("./Services/Table/classes/class.ilTable2GUI.php");

/**
 * ADN training provider table GUI class
 *
 * List all providers
 *
 * @author Alex Killing <killing@leifos.de>
 * @version $Id: class.adnTrainingProviderTableGUI.php 27891 2011-02-28 11:46:53Z jluetzen $
 *
 * @ingroup ServicesADN
 */
class adnTrainingProviderTableGUI extends ilTable2GUI
{
    /**
     * Constructor
     *
     * @param object $a_parent_obj parent gui object
     * @param string $a_parent_cmd parent default command
     */
    public function __construct($a_parent_obj, $a_parent_cmd)
    {
        
        parent::__construct($a_parent_obj, $a_parent_cmd);
        include_once("./Services/ADN/TA/classes/class.adnTrainingProvider.php");

        $this->setId("adn_ta_prov");
        
        $this->setTitle($this->lng->txt("adn_training_providers"));

        if (adnPerm::check(adnPerm::TA, adnPerm::WRITE)) {
            $this->addMultiCommand("confirmTrainingProviderDeletion", $this->lng->txt("delete"));
            $this->addColumn("", "", "1");
        }

        $this->addColumn($this->lng->txt("adn_company_name"), "name");
        $this->addColumn($this->lng->txt("adn_approved_types_of_training"));
        $this->addColumn($this->lng->txt("adn_training_facilities"));
        $this->addColumn($this->lng->txt("actions"));
        
        $this->setDefaultOrderField("name");
        $this->setDefaultOrderDirection("asc");

        $this->setFormAction($this->ctrl->getFormAction($a_parent_obj));
        $this->setRowTemplate("tpl.training_providers_row.html", "Services/ADN/TA");
        
        $this->importData();
    }

    /**
     * Import data from DB
     */
    protected function importData()
    {
        include_once "Services/ADN/TA/classes/class.adnTrainingProvider.php";
        $providers = adnTrainingProvider::getAllTrainingProviders();

        // value mapping (to have correct sorting)
        if (sizeof($providers)) {
            include_once("./Services/ADN/TA/classes/class.adnTypesOfTraining.php");
            include_once("./Services/ADN/TA/classes/class.adnTrainingFacility.php");
            $all_types = adnTypesOfTraining::getAllTypes();

            foreach ($providers as $idx => $item) {
                $types = array();
                if ($item["app_types"]) {
                    foreach ($item["app_types"] as $type) {
                        $types[] = $all_types[$type];
                    }
                }
                $providers[$idx]["types"] = implode(", ", $types);

                $facilities = array();
                $all_facilities = adnTrainingFacility::getAllTrainingFacilities($item["id"]);
                if ($all_facilities) {
                    foreach ($all_facilities as $set) {
                        $facilities[] = $set["name"];
                    }
                }
                $providers[$idx]["facilities"] = implode(", ", $facilities);
            }
        }

        $this->setData($providers);
        $this->setMaxCount(sizeof($providers));
    }

    /**
     * Fill table row
     *
     * @param array $a_set data array
     */
    protected function fillRow($a_set)
    {
        
        // actions...

        $this->ctrl->setParameter($this->parent_obj, "tp_id", $a_set["id"]);

        if (adnPerm::check(adnPerm::TA, adnPerm::WRITE)) {
            // ...edit
            $this->tpl->setCurrentBlock("action");
            $this->tpl->setVariable(
                "TXT_CMD",
                $this->lng->txt("adn_edit_contact_data")
            );
            $this->tpl->setVariable(
                "HREF_CMD",
                $this->ctrl->getLinkTarget($this->parent_obj, "editTrainingProvider")
            );
            $this->tpl->parseCurrentBlock();
            $this->tpl->setCurrentBlock("action");
            $this->tpl->setVariable(
                "TXT_CMD",
                $this->lng->txt("adn_edit_types_of_training")
            );
            $this->tpl->setVariable(
                "HREF_CMD",
                $this->ctrl->getLinkTarget($this->parent_obj, "listTrainingTypes")
            );
            $this->tpl->parseCurrentBlock();

            // ... edit instructors
            $this->tpl->setCurrentBlock("action");
            $this->tpl->setVariable("TXT_CMD", $this->lng->txt("adn_edit_instructors"));
            $this->tpl->setVariable(
                "HREF_CMD",
                $this->ctrl->getLinkTargetByClass("adninstructorgui", "listInstructors")
            );
            $this->tpl->parseCurrentBlock();

            // ... edit facilities
            $this->tpl->setCurrentBlock("action");
            $this->tpl->setVariable("TXT_CMD", $this->lng->txt("adn_edit_training_facilities"));
            $this->tpl->setVariable(
                "HREF_CMD",
                $this->ctrl->getLinkTargetByClass("adntrainingfacilitygui", "listTrainingFacilities")
            );
            $this->tpl->parseCurrentBlock();

            // checkbox for deletion
            $this->tpl->setCurrentBlock("cbox");
            $this->tpl->setVariable("VAL_ID", $a_set["id"]);
            $this->tpl->parseCurrentBlock();
        }

        if (adnPerm::check(adnPerm::TA, adnPerm::READ)) {
            if (!adnPerm::check(adnPerm::TA, adnPerm::WRITE)) {
                // ... show details
                $this->tpl->setCurrentBlock("action");
                $this->tpl->setVariable("TXT_CMD", $this->lng->txt("adn_show_details"));
                $this->tpl->setVariable(
                    "HREF_CMD",
                    $this->ctrl->getLinkTarget($this->parent_obj, "showTrainingProvider")
                );
                $this->tpl->parseCurrentBlock();
            }

            $this->ctrl->setParameter($this->parent_obj, "istp", "1");

            // ... training events
            $this->tpl->setCurrentBlock("action");
            $this->tpl->setVariable("TXT_CMD", $this->lng->txt("adn_training_events"));
            $this->tpl->setVariable(
                "HREF_CMD",
                $this->ctrl->getLinkTargetByClass("adntrainingeventgui", "listTrainingEvents")
            );
            $this->tpl->parseCurrentBlock();

            $this->ctrl->setParameter($this->parent_obj, "istp", "");
        }

        $this->ctrl->setParameter($this->parent_obj, "tp_id", "");


        // properties
        $this->tpl->setVariable("VAL_NAME", $a_set["name"]);
        $this->tpl->setVariable("VAL_APP_TYPES", $a_set["types"]);
        $this->tpl->setVariable("VAL_FACILITIES", $a_set["facilities"]);
    }
}
