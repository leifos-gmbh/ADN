<?php
/* Copyright (c) 2010 Leifos, GPL, see docs/LICENSE */

include_once "Services/ADN/ED/classes/class.adnGoodInTransit.php";

/**
 * ADN good in transit category GUI class
 *
 * Goods in transit category forms and persistence
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @version $Id: class.adnGoodInTransitCategoryGUI.php 27883 2011-02-27 19:30:41Z akill $
 *
 * @ilCtrl_Calls adnGoodInTransitCategoryGUI:
 *
 * @ingroup ServicesADN
 */
class adnGoodInTransitCategoryGUI
{
    // current type
    protected string $type = '';

    // current category object
    protected ?adnGoodInTransitCategory $category = null;

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


        // save category ID through requests
        $this->ctrl->saveParameter($this, array("gct_id"));
        
        $this->readCategory();
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
                $cmd = $this->ctrl->getCmd("listGasCategories");

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
                    case "listCategories":
                        if (adnPerm::check(adnPerm::ED, adnPerm::READ)) {
                            $this->$cmd();
                        }
                        break;
                    
                    // commands that need write permission
                    case "addCategory":
                    case "saveCategory":
                    case "editCategory":
                    case "updateCategory":
                    case "confirmCategoriesDeletion":
                    case "deleteCategories":
                        if (adnPerm::check(adnPerm::ED, adnPerm::WRITE)) {
                            $this->$cmd();
                        }
                        break;
                    
                }
                break;
        }
    }
    
    /**
     * Read good category
     */
    protected function readCategory()
    {
        if ((int) $_GET["gct_id"] > 0) {
            include_once("./Services/ADN/ED/classes/class.adnGoodInTransitCategory.php");
            $this->category = new adnGoodInTransitCategory((int) $_GET["gct_id"]);

            // set type from current category
            if ($this->category->getType() == adnGoodInTransitCategory::TYPE_GAS) {
                $this->type = "Gas";
            } else {
                $this->type = "Chem";
            }
        }
    }

    /**
     * List categories
     */
    protected function listCategories()
    {

        if (adnPerm::check(adnPerm::ED, adnPerm::WRITE)) {
            $this->toolbar->addButton(
                $this->lng->txt("adn_add_good_in_transit_category"),
                $this->ctrl->getLinkTarget($this, "add" . $this->type . "Category")
            );
        }

        include_once("./Services/ADN/ED/classes/class.adnGoodInTransitCategoryTableGUI.php");
        $table = new adnGoodInTransitCategoryTableGUI(
            $this,
            "list" . $this->type . "Categories",
            $this->typeToConst(),
            $this->type
        );

        $this->tpl->setContent($table->getHTML());
    }
    
    /**
     * Add new category form
     *
     * @param ilPropertyFormGUI $a_form
     */
    protected function addCategory(ilPropertyFormGUI $a_form = null)
    {

        $this->tabs->setBackTarget($this->lng->txt("back"), $this->ctrl->getLinkTarget($this, "list" . $this->type .
            "Categories"));

        if (!$a_form) {
            $a_form = $this->initCategoryForm("create");
        }
        $this->tpl->setContent($a_form->getHTML());
    }
    
    /**
     * Edit category form
     *
     * @param ilPropertyFormGUI $a_form
     */
    protected function editCategory(ilPropertyFormGUI $a_form = null)
    {

        $this->tabs->setBackTarget($this->lng->txt("back"), $this->ctrl->getLinkTarget($this, "list" . $this->type .
            "Categories"));

        if (!$a_form) {
            $a_form = $this->initCategoryForm("edit");
        }
        $this->tpl->setContent($a_form->getHTML());
    }
    
    /**
     * Init category form
     *
     * @param string $a_mode form mode ("create" | "edit")
     * @return ilPropertyFormGUI
     */
    protected function initCategoryForm($a_mode = "edit")
    {
        
        // get form object and add input fields
        include_once("Services/Form/classes/class.ilPropertyFormGUI.php");
        $form = new ilPropertyFormGUI();
        
        // name
        $name = new ilTextInputGUI($this->lng->txt("adn_name"), "name");
        $name->setRequired(true);
        $name->setMaxLength(200);
        $form->addItem($name);

        if ($a_mode == "create") {
            // creation: save/cancel buttons and title
            $form->addCommandButton("save" . $this->type . "Category", $this->lng->txt("save"));
            $form->addCommandButton("list" . $this->type . "Categories", $this->lng->txt("cancel"));
            $form->setTitle($this->lng->txt("adn_add_good_in_transit_category"));
        } else {
            $name->setValue($this->category->getName());
            
            // editing: update/cancel buttons and title
            $form->addCommandButton("update" . $this->type . "Category", $this->lng->txt("save"));
            $form->addCommandButton("list" . $this->type . "Categories", $this->lng->txt("cancel"));
            $form->setTitle($this->lng->txt("adn_edit_good_in_transit_category"));
        }
        
        $form->setFormAction($this->ctrl->getFormAction($this));

        return $form;
    }
    
    /**
     * Create new category
     */
    protected function saveCategory()
    {
        
        $form = $this->initCategoryForm("create");
        
        // check input
        if ($form->checkInput()) {
            // input ok: create new category
            include_once("./Services/ADN/ED/classes/class.adnGoodInTransitCategory.php");
            $cat = new adnGoodInTransitCategory();
            $cat->setName($form->getInput("name"));
            $cat->setType($this->typeToConst());

            if ($cat->save()) {
                // show success message and return to list
                ilUtil::sendSuccess($this->lng->txt("adn_good_in_transit_category_created"), true);
                $this->ctrl->redirect($this, "list" . $this->type . "Categories");
            }
        }

        // input not valid: show form again
        $form->setValuesByPost();
        $this->addCategory($form);
    }
    
    /**
     * Update category
     */
    protected function updateCategory()
    {
        
        $form = $this->initCategoryForm("edit");
        
        // check input
        if ($form->checkInput()) {
            // perform update
            $this->category->setName($form->getInput("name"));
            
            if ($this->category->update()) {
                // show success message and return to list
                ilUtil::sendSuccess($this->lng->txt("adn_good_in_transit_category_updated"), true);
                $this->ctrl->redirect($this, "list" . $this->type . "Categories");
            }
        }
        
        // input not valid: show form again
        $form->setValuesByPost();
        $this->editCategory($form);
    }
    
    /**
     * Confirm categories deletion
     */
    protected function confirmCategoriesDeletion()
    {
        
        // check whether at least one item has been seleced
        if (!is_array($_POST["category_id"]) || count($_POST["category_id"]) == 0) {
            ilUtil::sendFailure($this->lng->txt("no_checkbox"), true);
            $this->ctrl->redirect($this, "list" . $this->type . "Categories");
        } else {
            $this->tabs->setBackTarget(
                $this->lng->txt("back"),
                $this->ctrl->getLinkTarget($this, "list" . $this->type . "Categories")
            );

            // display confirmation message
            include_once("./Services/Utilities/classes/class.ilConfirmationGUI.php");
            $cgui = new ilConfirmationGUI();
            $cgui->setFormAction($this->ctrl->getFormAction($this));
            $cgui->setHeaderText($this->lng->txt("adn_sure_delete_good_in_transit_categories"));
            $cgui->setCancel($this->lng->txt("cancel"), "list" . $this->type . "Categories");
            $cgui->setConfirm($this->lng->txt("delete"), "delete" . $this->type . "Categories");

            // list objects that should be deleted
            include_once("./Services/ADN/ED/classes/class.adnGoodInTransitCategory.php");
            foreach ($_POST["category_id"] as $i) {
                $cgui->addItem("category_id[]", $i, adnGoodInTransitCategory::lookupName($i));
            }
            
            $this->tpl->setContent($cgui->getHTML());
        }
    }
    
    /**
     * Delete categories
     */
    protected function deleteCategories()
    {

        include_once("./Services/ADN/ED/classes/class.adnGoodInTransitCategory.php");

        $has_failed = false;
        if (is_array($_POST["category_id"])) {
            foreach ($_POST["category_id"] as $i) {
                // can fail because category must not be use
                $cat = new adnGoodInTransitCategory($i);
                if (!$cat->delete()) {
                    $has_failed = true;
                }
            }
        }
        if (!$has_failed) {
            ilUtil::sendSuccess($this->lng->txt("adn_good_in_transit_category_deleted"), true);
        } else {
            ilUtil::sendFailure($this->lng->txt("adn_good_in_transit_category_not_deleted"), true);
        }
        $this->ctrl->redirect($this, "list" . $this->type . "Categories");
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
            $this->ctrl->getLinkTargetByClass("adngoodintransitgui", "listGasGoods")
        );

        $this->tabs->addTab(
            "chem",
            $this->lng->txt("adn_goods_in_transit_chemicals"),
            $this->ctrl->getLinkTargetByClass("adngoodintransitgui", "listChemGoods")
        );

        $this->tabs->activateTab(strtolower($this->type));

        if ($this->type == "Gas") {
            $this->tabs->addSubTab(
                "gas_goods",
                $this->lng->txt("adn_goods_in_transit"),
                $this->ctrl->getLinkTargetByClass("adngoodintransitgui", "listGasGoods")
            );

            $this->tabs->addSubTab(
                "gas_cats",
                $this->lng->txt("adn_good_in_transit_categories"),
                $this->ctrl->getLinkTarget($this, "listGasCategories")
            );

            $this->tabs->activateSubTab("gas_cats");
        } else {
            $this->tabs->addSubTab(
                "chem_goods",
                $this->lng->txt("adn_goods_in_transit"),
                $this->ctrl->getLinkTargetByClass("adngoodintransitgui", "listChemGoods")
            );

            $this->tabs->addSubTab(
                "chem_cats",
                $this->lng->txt("adn_good_in_transit_categories"),
                $this->ctrl->getLinkTarget($this, "listChemCategories")
            );

            $this->tabs->activateSubTab("chem_cats");
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
