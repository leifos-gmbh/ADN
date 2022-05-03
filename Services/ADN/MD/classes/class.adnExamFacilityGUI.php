<?php
/* Copyright (c) 2010 Leifos, GPL, see docs/LICENSE */

/**
 * Exam facility GUI class
 *
 * Exam facility list, forms and persistence (parent wmo is mandatory)
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.de>
 * @version $Id: class.adnExamFacilityGUI.php 28757 2011-05-02 14:38:22Z jluetzen $
 *
 * @ilCtrl_Calls adnExamFacilityGUI:
 *
 * @ingroup ServicesADN
 */
class adnExamFacilityGUI
{
    // current wmo id
    protected int $wmo_id = 0;

    // current facility object
    protected ?adnExamFacility $facility = null;
    
    /**
     * Constructor
     */
    public function __construct()
    {
        global $ilCtrl;

        $this->wmo_id = (int) $_REQUEST["wmo_id"];
        
        // save office and facility ID through requests
        $ilCtrl->saveParameter($this, array("wmo_id"));
        $ilCtrl->saveParameter($this, array("ef_id"));
        
        $this->readExamFacility();
    }
    
    /**
     * Execute command
     */
    public function executeCommand()
    {
        global $ilCtrl;
        
        $next_class = $ilCtrl->getNextClass();
        
        // forward command to next gui class in control flow
        switch ($next_class) {
            // no next class:
            // this class is responsible to process the command
            default:
                $cmd = $ilCtrl->getCmd("listExamFacilities");

                switch ($cmd) {
                    // commands that need read permission
                    case "listExamFacilities":
                        if (adnPerm::check(adnPerm::MD, adnPerm::READ)) {
                            $this->$cmd();
                        }
                        break;
                    
                    // commands that need write permission
                    case "editExamFacility":
                    case "addExamFacility":
                    case "saveExamFacility":
                    case "updateExamFacility":
                    case "confirmDeleteExamFacilities":
                    case "deleteExamFacilities":
                        if (adnPerm::check(adnPerm::MD, adnPerm::WRITE)) {
                            $this->$cmd();
                        }
                        break;
                    
                }
                break;
        }
    }
    
    /**
     * Read facility
     */
    protected function readExamFacility()
    {
        if ((int) $_GET["ef_id"] > 0) {
            include_once("./Services/ADN/MD/classes/class.adnExamFacility.php");
            $this->facility = new adnExamFacility((int) $_GET["ef_id"]);
        }
    }
    
    /**
     * List all facilities
     */
    protected function listExamFacilities()
    {
        global $tpl, $ilCtrl, $ilToolbar, $lng, $ilTabs;

        $ilTabs->setBackTarget($lng->txt("back"), $ilCtrl->getLinkTargetByClass("adnWMOGUI", "listWMOs"));

        if (adnPerm::check(adnPerm::MD, adnPerm::WRITE)) {
            $ilToolbar->addButton(
                $lng->txt("adn_add_exam_facility"),
                $ilCtrl->getLinkTarget($this, "addExamFacility")
            );
        }

        // table of countries
        include_once("./Services/ADN/MD/classes/class.adnExamFacilityTableGUI.php");
        $table = new adnExamFacilityTableGUI($this, "listExamFacilities", $this->wmo_id);
        
        // output table
        $tpl->setContent($table->getHTML());
    }

    /**
     * Add facility form
     *
     * @param ilPropertyFormGUI $a_form
     */
    protected function addExamFacility(ilPropertyFormGUI $a_form = null)
    {
        global $tpl, $lng, $ilTabs, $ilCtrl;

        $ilTabs->setBackTarget($lng->txt("back"), $ilCtrl->getLinkTarget($this, "listExamFacilities"));

        if (!$a_form) {
            $a_form = $this->initExamFacilityForm(true);
        }
        $tpl->setContent($a_form->getHTML());
    }

    /**
     * Create new facility
     */
    protected function saveExamFacility()
    {
        global $tpl, $lng, $ilCtrl;

        $form = $this->initExamFacilityForm(true);
        if ($form->checkInput()) {
            include_once("./Services/ADN/MD/classes/class.adnExamFacility.php");
            $facility = new adnExamFacility();
            $facility->setWMO($this->wmo_id);
            $facility->setName($form->getInput("name"));
            $facility->setStreet($form->getInput("street"));
            $facility->setStreetNumber($form->getInput("streetno"));
            $facility->setZip($form->getInput("zip"));
            $facility->setCity($form->getInput("city"));
            if ($facility->save()) {
                ilUtil::sendSuccess($lng->txt("adn_exam_facility_created"), true);
                $ilCtrl->redirect($this, "listExamFacilities");
            }
        }
        
        $form->setValuesByPost();
        $this->addExamFacility($form);
    }

    /**
     * Edit facility form
     *
     * @param ilPropertyFormGUI $a_form
     */
    protected function editExamFacility(ilPropertyFormGUI $a_form = null)
    {
        global $tpl, $lng, $ilTabs, $ilCtrl;

        $ilTabs->setBackTarget($lng->txt("back"), $ilCtrl->getLinkTarget($this, "listExamFacilities"));

        if (!$a_form) {
            $a_form = $this->initExamFacilityForm();
        }
        $tpl->setContent($a_form->getHTML());
    }

    /**
     * Update existing facility
     */
    protected function updateExamFacility()
    {
        global $tpl, $lng, $ilCtrl;

        $form = $this->initExamFacilityForm();
        if ($form->checkInput()) {
            $this->facility->setName($form->getInput("name"));
            $this->facility->setStreet($form->getInput("street"));
            $this->facility->setStreetNumber($form->getInput("streetno"));
            $this->facility->setZip($form->getInput("zip"));
            $this->facility->setCity($form->getInput("city"));

            if ($this->facility->update()) {
                ilUtil::sendSuccess($lng->txt("adn_exam_facility_updated"), true);
                $ilCtrl->redirect($this, "listExamFacilities");
            }
        }

        $form->setValuesByPost();
        $this->editExamFacility($form);
    }

    /**
     * Build facility form
     * @return object
     */
    protected function initExamFacilityForm($a_create = false)
    {
        global  $lng, $ilCtrl;

        include_once "Services/Form/classes/class.ilPropertyFormGUI.php";
        $form = new ilPropertyFormGUI();
        $form->setFormAction($ilCtrl->getFormAction($this, "listExamFacilities"));

        include_once "Services/ADN/MD/classes/class.adnWMO.php";
        $form->setTitle($lng->txt("adn_exam_facility") . ": " . adnWMO::lookupName($this->wmo_id));

        $name = new ilTextInputGUI($lng->txt("adn_exam_facility_name"), "name");
        $name->setRequired(true);
        $name->setMaxLength(50);
        $form->addItem($name);

        $street = new ilTextInputGUI($lng->txt("adn_street"), "street");
        $street->setRequired(true);
        $street->setMaxLength(50);
        $form->addItem($street);

        $street_no = new ilTextInputGUI($lng->txt("adn_street_number"), "streetno");
        $street_no->setRequired(true);
        $street_no->setMaxLength(10);
        $street_no->setSize(10);
        $form->addItem($street_no);

        $zip = new ilTextInputGUI($lng->txt("adn_zip"), "zip");
        $zip->setRequired(true);
        $zip->setMaxLength(10);
        $zip->setSize(10);
        $form->addItem($zip);

        $city = new ilTextInputGUI($lng->txt("adn_city"), "city");
        $city->setRequired(true);
        $city->setMaxLength(50);
        $form->addItem($city);

        if ($a_create) {
            $form->addCommandButton("saveExamFacility", $lng->txt("save"));
        } else {
            $name->setValue($this->facility->getName());
            $street->setValue($this->facility->getStreet());
            $street_no->setValue($this->facility->getStreetNumber());
            $zip->setValue($this->facility->getZip());
            $city->setValue($this->facility->getCity());

            $form->addCommandButton("updateExamFacility", $lng->txt("save"));
        }
        $form->addCommandButton("listExamFacilities", $lng->txt("cancel"));

        return $form;
    }

    /**
     * Confirm deletion of facilities
     */
    public function confirmDeleteExamFacilities()
    {
        global $ilCtrl, $tpl, $lng, $ilTabs;

        // check whether at least one item has been seleced
        if (!is_array($_POST["ef_id"]) || count($_POST["ef_id"]) == 0) {
            ilUtil::sendFailure($lng->txt("no_checkbox"), true);
            $ilCtrl->redirect($this, "listExamFacilities");
        } else {
            $ilTabs->setBackTarget(
                $lng->txt("back"),
                $ilCtrl->getLinkTarget($this, "listExamFacilities")
            );

            // display confirmation message
            include_once("./Services/Utilities/classes/class.ilConfirmationGUI.php");
            $cgui = new ilConfirmationGUI();
            $cgui->setFormAction($ilCtrl->getFormAction($this));
            $cgui->setHeaderText($lng->txt("adn_sure_delete_exam_facilities"));
            $cgui->setCancel($lng->txt("cancel"), "listExamFacilities");
            $cgui->setConfirm($lng->txt("delete"), "deleteExamFacilities");

            include_once("./Services/ADN/MD/classes/class.adnExamFacility.php");

            // list objects that should be deleted
            foreach ($_POST["ef_id"] as $i) {
                $cgui->addItem("ef_id[]", $i, adnExamFacility::lookupName($i));
            }

            $tpl->setContent($cgui->getHTML());
        }
    }

    /**
     * Delete facilities
     */
    protected function deleteExamFacilities()
    {
        global $ilCtrl, $lng;

        include_once("./Services/ADN/MD/classes/class.adnExamFacility.php");

        if (is_array($_POST["ef_id"])) {
            foreach ($_POST["ef_id"] as $i) {
                $facility = new adnExamFacility($i);
                $facility->delete();
            }
        }
        ilUtil::sendSuccess($lng->txt("adn_exam_facility_deleted"), true);
        $ilCtrl->redirect($this, "listExamFacilities");
    }
}
