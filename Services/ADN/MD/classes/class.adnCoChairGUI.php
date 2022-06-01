<?php
/* Copyright (c) 2010 Leifos, GPL, see docs/LICENSE */

/**
 * Co-chair GUI class
 *
 * Co-chair list, forms and persistence (parent wmo is mandatory)
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.de>
 * @version $Id: class.adnCoChairGUI.php 28757 2011-05-02 14:38:22Z jluetzen $
 *
 * @ilCtrl_Calls adnCoChairGUI:
 *
 * @ingroup ServicesADN
 */
class adnCoChairGUI
{
    // current wmo id
    protected int $wmo_id;

    // current cochair object
    protected ?adnCoChair $cochair = null;

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
        
        // save office and cochair ID through requests
        $this->ctrl->saveParameter($this, array("wmo_id"));
        $this->ctrl->saveParameter($this, array("cch_id"));
        
        $this->readCoChair();
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
                $cmd = $this->ctrl->getCmd("listCoChairs");

                switch ($cmd) {
                    // commands that need read permission
                    case "listCoChairs":
                        if (adnPerm::check(adnPerm::MD, adnPerm::READ)) {
                            $this->$cmd();
                        }
                        break;
                    
                    // commands that need write permission
                    case "editCoChair":
                    case "addCoChair":
                    case "saveCoChair":
                    case "updateCoChair":
                    case "confirmDeleteCoChairs":
                    case "deleteCoChairs":
                        if (adnPerm::check(adnPerm::MD, adnPerm::WRITE)) {
                            $this->$cmd();
                        }
                        break;
                    
                }
                break;
        }
    }
    
    /**
     * Read cochair
     */
    protected function readCoChair()
    {
        if ((int) $_GET["cch_id"] > 0) {
            include_once("./Services/ADN/MD/classes/class.adnCoChair.php");
            $this->cochair = new adnCoChair((int) $_GET["cch_id"]);
        }
    }
    
    /**
     * List all cochairs
     */
    protected function listCoChairs()
    {

        $this->tabs->setBackTarget($this->lng->txt("back"), $this->ctrl->getLinkTargetByClass("adnWMOGUI", "listWMOs"));

        if (adnPerm::check(adnPerm::MD, adnPerm::WRITE)) {
            $this->toolbar->addButton(
                $this->lng->txt("adn_add_cochair"),
                $this->ctrl->getLinkTarget($this, "addCoChair")
            );
        }

        // table of countries
        include_once("./Services/ADN/MD/classes/class.adnCoChairTableGUI.php");
        $table = new adnCoChairTableGUI($this, "listCoChairs", $this->wmo_id);
        
        // output table
        $this->tpl->setContent($table->getHTML());
    }

    /**
     * Add cochair form
     *
     * @param ilPropertyFormGUI $a_form
     */
    protected function addCoChair(ilPropertyFormGUI $a_form = null)
    {

        $this->tabs->setBackTarget($this->lng->txt("back"), $this->ctrl->getLinkTarget($this, "listCoChairs"));

        if (!$a_form) {
            $a_form = $this->initCoChairForm(true);
        }
        $this->tpl->setContent($a_form->getHTML());
    }

    /**
     * Create new cochair
     */
    protected function saveCoChair()
    {

        $form = $this->initCoChairForm(true);
        if ($form->checkInput()) {
            include_once("./Services/ADN/MD/classes/class.adnCoChair.php");
            $cochair = new adnCoChair();
            $cochair->setWMO($this->wmo_id);
            $cochair->setSalutation($form->getInput("salutation"));
            $cochair->setName($form->getInput("name"));
            if ($cochair->save()) {
                ilUtil::sendSuccess($this->lng->txt("adn_cochair_created"), true);
                $this->ctrl->redirect($this, "listCoChairs");
            }
        }
        
        $form->setValuesByPost();
        $this->addCoChair($form);
    }

    /**
     * Edit cochair form
     *
     * @param ilPropertyFormGUI $a_form
     */
    protected function editCoChair(ilPropertyFormGUI $a_form = null)
    {

        $this->tabs->setBackTarget($this->lng->txt("back"), $this->ctrl->getLinkTarget($this, "listCoChairs"));

        if (!$a_form) {
            $a_form = $this->initCoChairForm();
        }
        $this->tpl->setContent($a_form->getHTML());
    }

    /**
     * Update existing cochair
     */
    protected function updateCoChair()
    {

        $form = $this->initCoChairForm();
        if ($form->checkInput()) {
            $this->cochair->setSalutation($form->getInput("salutation"));
            $this->cochair->setName($form->getInput("name"));
            if ($this->cochair->update()) {
                ilUtil::sendSuccess($this->lng->txt("adn_cochair_updated"), true);
                $this->ctrl->redirect($this, "listCoChairs");
            }
        }

        $form->setValuesByPost();
        $this->editCoChair($form);
    }

    /**
     * Build cochair form
     * @return object
     */
    protected function initCoChairForm($a_create = false)
    {

        include_once "Services/Form/classes/class.ilPropertyFormGUI.php";
        $form = new ilPropertyFormGUI();
        $form->setFormAction($this->ctrl->getFormAction($this, "listCoChairs"));

        include_once "Services/ADN/MD/classes/class.adnWMO.php";
        $form->setTitle($this->lng->txt("adn_cochair") . ": " . adnWMO::lookupName($this->wmo_id));
        
        $salutation = new ilSelectInputGUI($this->lng->txt("adn_salutation"), "salutation");
        $options = array("f" => $this->lng->txt("adn_salutation_f"),
            "m" => $this->lng->txt("adn_salutation_m"));
        $salutation->setOptions($options);
        $salutation->setRequired(true);
        $form->addItem($salutation);

        $name = new ilTextInputGUI($this->lng->txt("adn_last_name"), "name");
        $name->setRequired(true);
        $name->setMaxLength(50);
        $form->addItem($name);

        if ($a_create) {
            $form->addCommandButton("saveCoChair", $this->lng->txt("save"));
        } else {
            $salutation->setValue($this->cochair->getSalutation());
            $name->setValue($this->cochair->getName());

            $form->addCommandButton("updateCoChair", $this->lng->txt("save"));
        }
        $form->addCommandButton("listCoChairs", $this->lng->txt("cancel"));

        return $form;
    }

    /**
     * Confirm deletion of cochairs
     */
    public function confirmDeleteCoChairs()
    {

        // check whether at least one item has been seleced
        if (!is_array($_POST["cch_id"]) || count($_POST["cch_id"]) == 0) {
            ilUtil::sendFailure($this->lng->txt("no_checkbox"), true);
            $this->ctrl->redirect($this, "listCoChairs");
        } else {
            $this->tabs->setBackTarget(
                $this->lng->txt("back"),
                $this->ctrl->getLinkTarget($this, "listCoChairs")
            );

            // display confirmation message
            include_once("./Services/Utilities/classes/class.ilConfirmationGUI.php");
            $cgui = new ilConfirmationGUI();
            $cgui->setFormAction($this->ctrl->getFormAction($this));
            $cgui->setHeaderText($this->lng->txt("adn_sure_delete_cochairs"));
            $cgui->setCancel($this->lng->txt("cancel"), "listCoChairs");
            $cgui->setConfirm($this->lng->txt("delete"), "deleteCoChairs");

            include_once("./Services/ADN/MD/classes/class.adnCoChair.php");

            // list objects that should be deleted
            foreach ($_POST["cch_id"] as $i) {
                $cgui->addItem("cch_id[]", $i, adnCoChair::lookupName($i));
            }

            $this->tpl->setContent($cgui->getHTML());
        }
    }

    /**
     * Delete cochairs
     */
    protected function deleteCoChairs()
    {

        include_once("./Services/ADN/MD/classes/class.adnCoChair.php");

        if (is_array($_POST["cch_id"])) {
            foreach ($_POST["cch_id"] as $i) {
                $cochair = new adnCoChair($i);
                $cochair->delete();
            }
        }
        ilUtil::sendSuccess($this->lng->txt("adn_cochair_deleted"), true);
        $this->ctrl->redirect($this, "listCoChairs");
    }
}
