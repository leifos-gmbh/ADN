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

    protected ilCtrl $ctrl;
    protected ilGlobalTemplateInterface $tpl;
    protected ilToolbarGUI $toolbar;
    protected ilLanguage $lng;
    protected ilTabsGUI $tabs;
    
    /**
     * Constructor
     */
    public function __construct()
    {
        global $DIC;
        $this->ctrl = $DIC->ctrl();
        $this->tpl = $DIC->ui()->mainTemplate();
        $this->toolbar = $DIC->toolbar();
        $this->lng = $DIC->language();
        $this->tabs = $DIC->tabs();

        $this->wmo_id = (int) $_REQUEST["wmo_id"];
        
        // save office and facility ID through requests
        $this->ctrl->saveParameter($this, array("wmo_id"));
        $this->ctrl->saveParameter($this, array("ef_id"));
        
        $this->readExamFacility();
    }
    
    /**
     * Execute command
     */
    public function executeCommand()
    {
        
        $next_class = $this->ctrl->getNextClass();
        
        // forward command to next gui class in control flow
        switch ($next_class) {
            // no next class:
            // this class is responsible to process the command
            default:
                $cmd = $this->ctrl->getCmd("listExamFacilities");

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

        $this->tabs->setBackTarget($this->lng->txt("back"), $this->ctrl->getLinkTargetByClass("adnWMOGUI", "listWMOs"));

        if (adnPerm::check(adnPerm::MD, adnPerm::WRITE)) {
            $this->toolbar->addButton(
                $this->lng->txt("adn_add_exam_facility"),
                $this->ctrl->getLinkTarget($this, "addExamFacility")
            );
        }

        // table of countries
        include_once("./Services/ADN/MD/classes/class.adnExamFacilityTableGUI.php");
        $table = new adnExamFacilityTableGUI($this, "listExamFacilities", $this->wmo_id);
        
        // output table
        $this->tpl->setContent($table->getHTML());
    }

    /**
     * Add facility form
     *
     * @param ilPropertyFormGUI $a_form
     */
    protected function addExamFacility(ilPropertyFormGUI $a_form = null)
    {

        $this->tabs->setBackTarget($this->lng->txt("back"), $this->ctrl->getLinkTarget($this, "listExamFacilities"));

        if (!$a_form) {
            $a_form = $this->initExamFacilityForm(true);
        }
        $this->tpl->setContent($a_form->getHTML());
    }

    /**
     * Create new facility
     */
    protected function saveExamFacility()
    {

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
                ilUtil::sendSuccess($this->lng->txt("adn_exam_facility_created"), true);
                $this->ctrl->redirect($this, "listExamFacilities");
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

        $this->tabs->setBackTarget($this->lng->txt("back"), $this->ctrl->getLinkTarget($this, "listExamFacilities"));

        if (!$a_form) {
            $a_form = $this->initExamFacilityForm();
        }
        $this->tpl->setContent($a_form->getHTML());
    }

    /**
     * Update existing facility
     */
    protected function updateExamFacility()
    {

        $form = $this->initExamFacilityForm();
        if ($form->checkInput()) {
            $this->facility->setName($form->getInput("name"));
            $this->facility->setStreet($form->getInput("street"));
            $this->facility->setStreetNumber($form->getInput("streetno"));
            $this->facility->setZip($form->getInput("zip"));
            $this->facility->setCity($form->getInput("city"));

            if ($this->facility->update()) {
                ilUtil::sendSuccess($this->lng->txt("adn_exam_facility_updated"), true);
                $this->ctrl->redirect($this, "listExamFacilities");
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

        include_once "Services/Form/classes/class.ilPropertyFormGUI.php";
        $form = new ilPropertyFormGUI();
        $form->setFormAction($this->ctrl->getFormAction($this, "listExamFacilities"));

        include_once "Services/ADN/MD/classes/class.adnWMO.php";
        $form->setTitle($this->lng->txt("adn_exam_facility") . ": " . adnWMO::lookupName($this->wmo_id));

        $name = new ilTextInputGUI($this->lng->txt("adn_exam_facility_name"), "name");
        $name->setRequired(true);
        $name->setMaxLength(50);
        $form->addItem($name);

        $street = new ilTextInputGUI($this->lng->txt("adn_street"), "street");
        $street->setRequired(true);
        $street->setMaxLength(50);
        $form->addItem($street);

        $street_no = new ilTextInputGUI($this->lng->txt("adn_street_number"), "streetno");
        $street_no->setRequired(true);
        $street_no->setMaxLength(10);
        $street_no->setSize(10);
        $form->addItem($street_no);

        $zip = new ilTextInputGUI($this->lng->txt("adn_zip"), "zip");
        $zip->setRequired(true);
        $zip->setMaxLength(10);
        $zip->setSize(10);
        $form->addItem($zip);

        $city = new ilTextInputGUI($this->lng->txt("adn_city"), "city");
        $city->setRequired(true);
        $city->setMaxLength(50);
        $form->addItem($city);

        if ($a_create) {
            $form->addCommandButton("saveExamFacility", $this->lng->txt("save"));
        } else {
            $name->setValue($this->facility->getName());
            $street->setValue($this->facility->getStreet());
            $street_no->setValue($this->facility->getStreetNumber());
            $zip->setValue($this->facility->getZip());
            $city->setValue($this->facility->getCity());

            $form->addCommandButton("updateExamFacility", $this->lng->txt("save"));
        }
        $form->addCommandButton("listExamFacilities", $this->lng->txt("cancel"));

        return $form;
    }

    /**
     * Confirm deletion of facilities
     */
    public function confirmDeleteExamFacilities()
    {

        // check whether at least one item has been seleced
        if (!is_array($_POST["ef_id"]) || count($_POST["ef_id"]) == 0) {
            ilUtil::sendFailure($this->lng->txt("no_checkbox"), true);
            $this->ctrl->redirect($this, "listExamFacilities");
        } else {
            $this->tabs->setBackTarget(
                $this->lng->txt("back"),
                $this->ctrl->getLinkTarget($this, "listExamFacilities")
            );

            // display confirmation message
            include_once("./Services/Utilities/classes/class.ilConfirmationGUI.php");
            $cgui = new ilConfirmationGUI();
            $cgui->setFormAction($this->ctrl->getFormAction($this));
            $cgui->setHeaderText($this->lng->txt("adn_sure_delete_exam_facilities"));
            $cgui->setCancel($this->lng->txt("cancel"), "listExamFacilities");
            $cgui->setConfirm($this->lng->txt("delete"), "deleteExamFacilities");

            include_once("./Services/ADN/MD/classes/class.adnExamFacility.php");

            // list objects that should be deleted
            foreach ($_POST["ef_id"] as $i) {
                $cgui->addItem("ef_id[]", $i, adnExamFacility::lookupName($i));
            }

            $this->tpl->setContent($cgui->getHTML());
        }
    }

    /**
     * Delete facilities
     */
    protected function deleteExamFacilities()
    {

        include_once("./Services/ADN/MD/classes/class.adnExamFacility.php");

        if (is_array($_POST["ef_id"])) {
            foreach ($_POST["ef_id"] as $i) {
                $facility = new adnExamFacility($i);
                $facility->delete();
            }
        }
        ilUtil::sendSuccess($this->lng->txt("adn_exam_facility_deleted"), true);
        $this->ctrl->redirect($this, "listExamFacilities");
    }
}
