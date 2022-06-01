<?php
/* Copyright (c) 2010 Leifos, GPL, see docs/LICENSE */

include_once "Services/ADN/ED/classes/class.adnGoodInTransit.php";

/**
 * ADN good in transit GUI class
 *
 * Good in transit forms and persistence
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @version $Id: class.adnGoodInTransitGUI.php 27883 2011-02-27 19:30:41Z akill $
 *
 * @ilCtrl_Calls adnGoodInTransitGUI: adnGoodInTransitCategoryGUI
 *
 * @ingroup ServicesADN
 */
class adnGoodInTransitGUI
{
    // current type
    protected string $type = '';

    // current good object
    protected ?adnGoodInTransit $good = null;

    protected ilCtrl $ctrl;
    protected ilLanguage $lng;
    protected ilGlobalTemplateInterface $tpl;
    protected ilToolbarGUI $toolbar;
    protected ilTabsGUI $tabs;
    
    /**
     * Constructor
     */
    public function __construct()
    {
        global $DIC;
        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();
        $this->tpl = $DIC->ui()->mainTemplate();
        $this->toolbar = $DIC->toolbar();
        $this->tabs = $DIC->tabs();

        // save good ID through requests
        $this->ctrl->saveParameter($this, array("gd_id"));
        
        $this->readGood();
    }
    
    /**
     * Execute command
     */
    public function executeCommand()
    {

        $this->tpl->setTitle($this->lng->txt("adn_ed") . " - " . $this->lng->txt("adn_ed_gts"));
        
        $next_class = $this->ctrl->getNextClass();
        
        // forward command to next gui class in control flow
        switch ($next_class) {
            case "adngoodintransitcategorygui":
                include_once("./Services/ADN/ED/classes/class.adnGoodInTransitCategoryGUI.php");
                $ct_gui = new adnGoodInTransitCategoryGUI();
                $this->ctrl->forwardCommand($ct_gui);
                break;

            // no next class:
            // this class is responsible to process the command
            default:
                $cmd = $this->ctrl->getCmd("listGasGoods");

                // determine type from cmd (Gas|Chem)
                if (strstr($cmd, "Gas")) {
                    $this->type = "Gas";
                    $cmd = str_replace("Gas", "", $cmd);
                } elseif (strstr($cmd, "Chem")) {
                    $this->type = "Chem";
                    $cmd = str_replace("Chem", "", $cmd);
                }
                
                $this->setTabs($cmd);

                switch ($cmd) {
                    // commands that need read permission
                    case "listGoods":
                    case "downloadFile":
                        if (adnPerm::check(adnPerm::ED, adnPerm::READ)) {
                            $this->$cmd();
                        }
                        break;
                    
                    // commands that need write permission
                    case "addGood":
                    case "saveGood":
                    case "editGood":
                    case "updateGood":
                    case "confirmGoodsDeletion":
                    case "deleteGoods":
                        if (adnPerm::check(adnPerm::ED, adnPerm::WRITE)) {
                            $this->$cmd();
                        }
                        break;
                    
                }
                break;
        }
    }
    
    /**
     * Read good
     */
    protected function readGood()
    {
        if ((int) $_GET["gd_id"] > 0) {
            include_once("./Services/ADN/ED/classes/class.adnGoodInTransit.php");
            $this->good = new adnGoodInTransit((int) $_GET["gd_id"]);

            // set type from current good
            if ($this->good->getType() == adnGoodInTransit::TYPE_GAS) {
                $this->type = "Gas";
            } else {
                $this->type = "Chem";
            }
        }
    }

    /**
     * List goods
     */
    protected function listGoods()
    {

        if (adnPerm::check(adnPerm::ED, adnPerm::WRITE)) {
            $this->toolbar->addButton(
                $this->lng->txt("adn_add_good_in_transit"),
                $this->ctrl->getLinkTarget($this, "add" . $this->type . "Good")
            );
        }

        include_once("./Services/ADN/ED/classes/class.adnGoodInTransitTableGUI.php");
        $table = new adnGoodInTransitTableGUI(
            $this,
            "list" . $this->type . "Goods",
            $this->typeToConst(),
            $this->type
        );

        $this->tpl->setContent($table->getHTML());
    }
    
    /**
     * Add new good form
     *
     * @param ilPropertyFormGUI $a_form
     */
    protected function addGood(ilPropertyFormGUI $a_form = null)
    {

        $this->tabs->setBackTarget($this->lng->txt("back"), $this->ctrl->getLinkTarget(
            $this,
            "list" . $this->type . "Goods"
        ));

        if (!$a_form) {
            $a_form = $this->initGoodForm("create");
        }
        $this->tpl->setContent($a_form->getHTML());
    }
    
    /**
     * Edit good form
     *
     * @param ilPropertyFormGUI $a_form
     */
    protected function editGood(ilPropertyFormGUI $a_form = null)
    {

        $this->tabs->setBackTarget($this->lng->txt("back"), $this->ctrl->getLinkTarget(
            $this,
            "list" . $this->type . "Goods"
        ));

        if (!$a_form) {
            $a_form = $this->initGoodForm("edit");
        }
        $this->tpl->setContent($a_form->getHTML());
    }
    
    /**
     * Init good form
     *
     * @param string $a_mode form mode ("create" | "edit")
     * @return ilPropertyFormGUI
     */
    protected function initGoodForm($a_mode = "edit")
    {
        
        // get form object and add input fields
        include_once("Services/Form/classes/class.ilPropertyFormGUI.php");
        $form = new ilPropertyFormGUI();

        // number
        $number = new ilNumberInputGUI($this->lng->txt("adn_un_nr"), "number");
        $number->setRequired(true);
        $number->setSize(4);
        $number->setMaxLength(4);
        $form->addItem($number);
        
        // name
        $name = new ilTextInputGUI($this->lng->txt("adn_name"), "name");
        $name->setRequired(true);
        $name->setMaxLength(200);
        $form->addItem($name);

        // categories (foreign key, but usage is checked - so no orphans)
        include_once "Services/ADN/ED/classes/class.adnGoodInTransitCategory.php";
        $options = adnGoodInTransitCategory::getCategoriesSelect($this->typetoConst());
        if ($options) {
            $cats = new ilRadioGroupInputGUI($this->lng->txt("adn_good_in_transit_category"), "cat");
            $cat_none = new ilRadioOption($this->lng->txt("adn_no_good_in_transit_category"), 0);
            $cats->addOption($cat_none);
            foreach ($options as $cat_id => $cat_name) {
                $cats->addOption(new ilRadioOption($cat_name, $cat_id));
            }
            $form->addItem($cats);
        }

        // available only with chemicals
        if ($this->type == "Chem") {
            // class
            $class = new ilTextInputGUI($this->lng->txt("adn_class"), "class");
            $class->setMaxLength(5);
            $class->setSize(5);
            $class->setRequired(true);
            $form->addItem($class);

            // class code
            $ccode = new ilTextInputGUI($this->lng->txt("adn_class_code"), "ccode");
            $ccode->setMaxLength(5);
            $ccode->setSize(5);
            $ccode->setRequired(true);
            $form->addItem($ccode);

            // packing group
            $packing = new ilTextInputGUI($this->lng->txt("adn_packing_group"), "packing");
            $packing->setMaxLength(5);
            $packing->setSize(5);
            $packing->setRequired(true);
            $form->addItem($packing);
        }

        // file
        $file = new ilFileInputGUI($this->lng->txt("adn_material_file"), "file");
        $file->setSuffixes(array("pdf"));
        $file->setALlowDeletion(true);
        $form->addItem($file);

        if ($a_mode == "create") {
            // creation: save/cancel buttons and title
            $form->addCommandButton("save" . $this->type . "Good", $this->lng->txt("save"));
            $form->addCommandButton("list" . $this->type . "Goods", $this->lng->txt("cancel"));
            $form->setTitle($this->lng->txt("adn_add_good_in_transit"));
        } else {
            $number->setValue($this->good->getNumber());
            $name->setValue($this->good->getName());
            $file->setValue($this->good->getFileName());
            if ($cats) {
                $cats->setValue($this->good->getCategory());
            }

            // available only with chemicals
            if ($this->type == "Chem") {
                $class->setValue($this->good->getClass());
                $ccode->setValue($this->good->getClassCode());
                $packing->setValue($this->good->getPackingGroup());
            }
            
            // editing: update/cancel buttons and title
            $form->addCommandButton("update" . $this->type . "Good", $this->lng->txt("save"));
            $form->addCommandButton("list" . $this->type . "Goods", $this->lng->txt("cancel"));
            $form->setTitle($this->lng->txt("adn_edit_good_in_transit"));
        }
        
        $form->setFormAction($this->ctrl->getFormAction($this));

        return $form;
    }
    
    /**
     * Create new good
     */
    protected function saveGood()
    {
        
        $form = $this->initGoodForm("create");
        
        // check input
        if ($form->checkInput()) {
            // input ok: create new good
            include_once("./Services/ADN/ED/classes/class.adnGoodInTransit.php");
            $good = new adnGoodInTransit();
            $good->setType($this->typeToConst());
            $good->setNumber($form->getInput("number"));
            $good->setName($form->getInput("name"));
            $good->setCategory($form->getInput("cat"));

            // available only with chemicals
            if ($this->type == "Chem") {
                $good->setClass($form->getInput("class"));
                $good->setClassCode($form->getInput("ccode"));
                $good->setPackingGroup($form->getInput("packing"));
            }

            // upload?
            $file = $form->getInput("file");
            $good->importFile($file["tmp_name"], $file["name"]);
            
            if ($good->save()) {
                // show success message and return to list
                ilUtil::sendSuccess($this->lng->txt("adn_good_in_transit_created"), true);
                $this->ctrl->redirect($this, "list" . $this->type . "Goods");
            }
        }

        // input not valid: show form again
        $form->setValuesByPost();
        $this->addGood($form);
    }
    
    /**
     * Update good
     */
    protected function updateGood()
    {
        
        $form = $this->initGoodForm("edit");
        
        // check input
        if ($form->checkInput()) {
            // perform update
            $this->good->setNumber($form->getInput("number"));
            $this->good->setName($form->getInput("name"));
            $this->good->setCategory($form->getInput("cat"));

            // available only with chemicals
            if ($this->type == "Chem") {
                $this->good->setClass($form->getInput("class"));
                $this->good->setClassCode($form->getInput("ccode"));
                $this->good->setPackingGroup($form->getInput("packing"));
            }

            // remove existing file
            if ($form->getInput("file_delete")) {
                $this->good->setFileName(null);
                $this->good->removeFile($this->good->getId());
            }

            // upload?
            $file = $form->getInput("file");
            $this->good->importFile($file["tmp_name"], $file["name"]);
            
            if ($this->good->update()) {
                // show success message and return to list
                ilUtil::sendSuccess($this->lng->txt("adn_good_in_transit_updated"), true);
                $this->ctrl->redirect($this, "list" . $this->type . "Goods");
            }
        }
        
        // input not valid: show form again
        $form->setValuesByPost();
        $this->editGood($form);
    }
    
    /**
     * Confirm goods deletion
     */
    protected function confirmGoodsDeletion()
    {
        
        // check whether at least one item has been seleced
        if (!is_array($_POST["good_id"]) || count($_POST["good_id"]) == 0) {
            ilUtil::sendFailure($this->lng->txt("no_checkbox"), true);
            $this->ctrl->redirect($this, "list" . $this->type . "Goods");
        } else {
            $this->tabs->setBackTarget(
                $this->lng->txt("back"),
                $this->ctrl->getLinkTarget($this, "list" . $this->type . "Goods")
            );

            // display confirmation message
            include_once("./Services/Utilities/classes/class.ilConfirmationGUI.php");
            $cgui = new ilConfirmationGUI();
            $cgui->setFormAction($this->ctrl->getFormAction($this));
            $cgui->setHeaderText($this->lng->txt("adn_sure_delete_goods_in_transit"));
            $cgui->setCancel($this->lng->txt("cancel"), "list" . $this->type . "Goods");
            $cgui->setConfirm($this->lng->txt("delete"), "delete" . $this->type . "Goods");

            // list objects that should be deleted
            include_once("./Services/ADN/ED/classes/class.adnGoodInTransit.php");
            foreach ($_POST["good_id"] as $i) {
                $cgui->addItem("good_id[]", $i, adnGoodInTransit::lookupName($i));
            }
            
            $this->tpl->setContent($cgui->getHTML());
        }
    }
    
    /**
     * Delete goods
     */
    protected function deleteGoods()
    {
        
        include_once("./Services/ADN/ED/classes/class.adnGoodInTransit.php");
        
        if (is_array($_POST["good_id"])) {
            foreach ($_POST["good_id"] as $i) {
                $good = new adnGoodInTransit($i);
                $good->delete();
            }
        }
        ilUtil::sendSuccess($this->lng->txt("adn_good_in_transit_deleted"), true);
        $this->ctrl->redirect($this, "list" . $this->type . "Goods");
    }

    /**
     * Download file
     */
    protected function downloadFile()
    {

        $file = $this->good->getFilePath() . $this->good->getId();
        if (file_exists($file)) {
            ilUtil::deliverFile($file, $this->good->getFileName());
        } else {
            ilUtil::sendFailure($this->lng->txt("adn_file_corrupt"), true);
            $this->ctrl->redirect($this, "listGoods");
        }
    }

    /**
     * Set tabs
     *
     * @param string $a_activate
     */
    public function setTabs($a_activate)
    {

        $this->tabs->addTab(
            "gas",
            $this->lng->txt("adn_goods_in_transit_gas"),
            $this->ctrl->getLinkTarget($this, "listGasGoods")
        );

        $this->tabs->addTab(
            "chem",
            $this->lng->txt("adn_goods_in_transit_chemicals"),
            $this->ctrl->getLinkTarget($this, "listChemGoods")
        );

        $this->tabs->activateTab(strtolower($this->type));

        if ($this->type == "Gas") {
            $this->tabs->addSubTab(
                "gas_goods",
                $this->lng->txt("adn_goods_in_transit"),
                $this->ctrl->getLinkTarget($this, "listGasGoods")
            );

            $this->tabs->addSubTab(
                "gas_cats",
                $this->lng->txt("adn_good_in_transit_categories"),
                $this->ctrl->getLinkTargetByClass("adngoodintransitcategorygui", "listGasCategories")
            );

            $this->tabs->activateSubTab("gas_goods");
        } else {
            $this->tabs->addSubTab(
                "chem_goods",
                $this->lng->txt("adn_goods_in_transit"),
                $this->ctrl->getLinkTarget($this, "listChemGoods")
            );

            $this->tabs->addSubTab(
                "chem_cats",
                $this->lng->txt("adn_good_in_transit_categories"),
                $this->ctrl->getLinkTargetByClass("adngoodintransitcategorygui", "listChemCategories")
            );

            $this->tabs->activateSubTab("chem_goods");
        }
    }

    /**
     * Convert internal type (string-based) to application class constant
     *
     * @return int
     */
    protected function typeToConst()
    {
        if ($this->type == "Gas") {
            return adnGoodInTransit::TYPE_GAS;
        } else {
            return adnGoodInTransit::TYPE_CHEMICALS;
        }
    }
}
