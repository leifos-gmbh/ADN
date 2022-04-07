<?php
/* Copyright (c) 2010 Leifos, GPL, see docs/LICENSE */

include_once "Services/ADN/ED/classes/class.adnLicense.php";

/**
 * ADN license GUI class
 *
 * License forms and persistence
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @version $Id: class.adnLicenseGUI.php 27883 2011-02-27 19:30:41Z akill $
 *
 * @ilCtrl_Calls adnLicenseGUI:
 *
 * @ingroup ServicesADN
 */
class adnLicenseGUI
{
    // current type
    protected string $type = '';

    // current license object
    protected ?adnLicense $license = null;
    
    /**
     * Constructor
     */
    public function __construct()
    {
        global $ilCtrl;

        // save license ID through requests
        $ilCtrl->saveParameter($this, array("lcs_id"));
        
        $this->readLicense();
    }
    
    /**
     * Execute command
     */
    public function executeCommand()
    {
        global $ilCtrl, $lng, $tpl;

        $tpl->setTitle($lng->txt("adn_ed") . " - " . $lng->txt("adn_ed_lic"));
        
        $next_class = $ilCtrl->getNextClass();
        
        // forward command to next gui class in control flow
        switch ($next_class) {
            // no next class:
            // this class is responsible to process the command
            default:
                $cmd = $ilCtrl->getCmd("listLicenses");

                // determine type from cmd (1|2)
                if (!stristr($cmd, "Gas")) {
                    $this->type = adnLicense::TYPE_CHEMICALS;
                    $cmd = str_replace("Chem", "", $cmd);
                } else {
                    $this->type = adnLicense::TYPE_GAS;
                    $cmd = str_replace("Gas", "", $cmd);
                }
                
                $this->setTabs();

                switch ($cmd) {
                    // commands that need read permission
                    case "listLicenses":
                    case "downloadFile":
                        if (adnPerm::check(adnPerm::ED, adnPerm::READ)) {
                            $this->$cmd();
                        }
                        break;
                    
                    // commands that need write permission
                    case "addLicense":
                    case "saveLicense":
                    case "editLicense":
                    case "updateLicense":
                    case "confirmLicensesDeletion":
                    case "deleteLicenses":
                        if (adnPerm::check(adnPerm::ED, adnPerm::WRITE)) {
                            $this->$cmd();
                        }
                        break;
                    
                }
                break;
        }
    }
    
    /**
     * Read license
     */
    protected function readLicense()
    {
        if ((int) $_GET["lcs_id"] > 0) {
            include_once("./Services/ADN/ED/classes/class.adnLicense.php");
            $this->license = new adnLicense((int) $_GET["lcs_id"]);
            $this->type = $this->license->getType();
        }
    }

    /**
     * List liceneses
     */
    protected function listLicenses()
    {
        global $tpl, $lng, $ilCtrl, $ilToolbar;

        include_once("./Services/ADN/ED/classes/class.adnLicenseTableGUI.php");
        $table = new adnLicenseTableGUI($this, $this->getLink("listLicenses"), $this->type);

        if (adnPerm::check(adnPerm::ED, adnPerm::WRITE)) {
            if ($this->type == adnLicense::TYPE_CHEMICALS) {
                $ilToolbar->addButton(
                    $lng->txt("adn_add_license"),
                    $ilCtrl->getLinkTarget($this, $this->getLink("addLicense"))
                );
            } elseif (!$table->getData()) {
                $ilToolbar->addButton(
                    $lng->txt("adn_add_gas_license"),
                    $ilCtrl->getLinkTarget($this, $this->getLink("addLicense"))
                );
            }
        }

        $tpl->setContent($table->getHTML());
    }
    
    /**
     * Add new license form
     *
     * @param ilPropertyFormGUI $a_form
     */
    protected function addLicense(ilPropertyFormGUI $a_form = null)
    {
        global $tpl, $ilTabs, $ilCtrl, $lng;

        $ilTabs->setBackTarget($lng->txt("back"), $ilCtrl->getLinkTarget(
            $this,
            $this->getLink("listLicenses")
        ));

        if (!$a_form) {
            $a_form = $this->initLicenseForm("create");
        }
        $tpl->setContent($a_form->getHTML());
    }
    
    /**
     * Edit license form
     *
     * @param ilPropertyFormGUI $a_form
     */
    protected function editLicense(ilPropertyFormGUI $a_form = null)
    {
        global $tpl, $ilTabs, $ilCtrl, $lng;

        $ilTabs->setBackTarget($lng->txt("back"), $ilCtrl->getLinkTarget(
            $this,
            $this->getLink("listLicenses")
        ));

        if (!$a_form) {
            $a_form = $this->initLicenseForm("edit");
        }
        $tpl->setContent($a_form->getHTML());
    }
    
    /**
     * Init license form
     *
     * @param string $a_mode form mode ("create" | "edit")
     * @return ilPropertyFormGUI
     */
    protected function initLicenseForm($a_mode = "edit")
    {
        global $lng, $ilCtrl;
        
        // get form object and add input fields
        include_once("Services/Form/classes/class.ilPropertyFormGUI.php");
        $form = new ilPropertyFormGUI();
        
        // name
        $name = new ilTextInputGUI($lng->txt("adn_title"), "name");
        $name->setRequired(true);
        $name->setMaxLength(200);
        $form->addItem($name);

        if ($this->type == adnLicense::TYPE_CHEMICALS) {
            // goods (foreign key)
            $goods = null;
            if ($a_mode != "create") {
                $goods = $this->license->getGoods();
            }
            include_once "Services/ADN/ED/classes/class.adnGoodInTransit.php";
            $goods = adnGoodInTransit::getGoodsSelect(
                adnGoodInTransit::TYPE_CHEMICALS,
                null,
                $goods
            );
            if ($goods) {
                $specific = new ilCheckboxGroupInputGUI(
                    $lng->txt("adn_goods_in_transit"),
                    "goods"
                );
                $specific->setRequired(true);
                $form->addItem($specific);
                foreach ($goods as $good_id => $good_name) {
                    $box = new ilCheckboxOption($good_name, $good_id);
                    $specific->addOption($box);
                }
            }
        }

        // file
        $file = new ilFileInputGUI($lng->txt("file"), "file");
        $file->setSuffixes(array("pdf"));
        $file->setALlowDeletion(true);
        $form->addItem($file);

        if ($a_mode == "create") {
            // creation: save/cancel buttons and title
            $form->addCommandButton($this->getLink("saveLicense"), $lng->txt("save"));
            $form->addCommandButton($this->getLink("listLicenses"), $lng->txt("cancel"));
            $form->setTitle($lng->txt("adn_add_license"));
        } else {
            $name->setValue($this->license->getName());
            $file->setValue($this->license->getFileName());

            if ($this->type == adnLicense::TYPE_CHEMICALS) {
                $specific->setValue($this->license->getGoods());
            }
            
            // editing: update/cancel buttons and title
            $form->addCommandButton($this->getLink("updateLicense"), $lng->txt("save"));
            $form->addCommandButton($this->getLink("listLicenses"), $lng->txt("cancel"));
            $form->setTitle($lng->txt("adn_edit_license"));
        }
        
        $form->setFormAction($ilCtrl->getFormAction($this));

        return $form;
    }
    
    /**
     * Create new license
     */
    protected function saveLicense()
    {
        global $tpl, $lng, $ilCtrl;
        
        $form = $this->initLicenseForm("create");
        
        // check input
        if ($form->checkInput()) {
            // input ok: create new license
            include_once("./Services/ADN/ED/classes/class.adnLicense.php");
            $license = new adnLicense();
            $license->setType($this->type);
            $license->setName($form->getInput("name"));

            if ($this->type == adnLicense::TYPE_CHEMICALS) {
                $license->setGoods($form->getInput("goods"));
            }

            // upload?
            $file = $form->getInput("file");
            $license->importFile($file["tmp_name"], $file["name"]);
            
            if ($license->save()) {
                // show success message and return to list
                ilUtil::sendSuccess($lng->txt("adn_license_created"), true);
                $ilCtrl->redirect($this, $this->getLink("listLicenses"));
            }
        }

        // input not valid: show form again
        $form->setValuesByPost();
        $this->addLicense($form);
    }
    
    /**
     * Update license
     */
    protected function updateLicense()
    {
        global $lng, $ilCtrl, $tpl;
        
        $form = $this->initLicenseForm("edit");
        
        // check input
        if ($form->checkInput()) {
            // perform update
            $this->license->setName($form->getInput("name"));

            if ($this->type == adnLicense::TYPE_CHEMICALS) {
                $this->license->setGoods($form->getInput("goods"));
            }

            // delete existing file
            if ($form->getInput("file_delete")) {
                $this->license->setFileName(null);
                $this->license->removeFile($this->license->getId());
            }

            // upload?
            $file = $form->getInput("file");
            $this->license->importFile($file["tmp_name"], $file["name"]);
            
            if ($this->license->update()) {
                // show success message and return to list
                ilUtil::sendSuccess($lng->txt("adn_license_updated"), true);
                $ilCtrl->redirect($this, $this->getLink("listLicenses"));
            }
        }
        
        // input not valid: show form again
        $form->setValuesByPost();
        $this->editLicense($form);
    }
    
    /**
     * Confirm licenses deletion
     */
    protected function confirmLicensesDeletion()
    {
        global $ilCtrl, $tpl, $lng, $ilTabs;
        
        // check whether at least one item has been seleced
        if (!is_array($_POST["license_id"]) || count($_POST["license_id"]) == 0) {
            ilUtil::sendFailure($lng->txt("no_checkbox"), true);
            $ilCtrl->redirect($this, $this->getLink("listLicenses"));
        } else {
            $ilTabs->setBackTarget(
                $lng->txt("back"),
                $ilCtrl->getLinkTarget($this, "listLicenses")
            );

            // display confirmation message
            include_once("./Services/Utilities/classes/class.ilConfirmationGUI.php");
            $cgui = new ilConfirmationGUI();
            $cgui->setFormAction($ilCtrl->getFormAction($this));
            $cgui->setHeaderText($lng->txt("adn_sure_delete_licenses"));
            $cgui->setCancel($lng->txt("cancel"), $this->getLink("listLicenses"));
            $cgui->setConfirm($lng->txt("delete"), $this->getLink("deleteLicenses"));

            // list objects that should be deleted
            include_once("./Services/ADN/ED/classes/class.adnLicense.php");
            foreach ($_POST["license_id"] as $i) {
                $cgui->addItem("license_id[]", $i, adnLicense::lookupName($i));
            }
            
            $tpl->setContent($cgui->getHTML());
        }
    }
    
    /**
     * Delete licenses
     */
    protected function deleteLicenses()
    {
        global $ilCtrl, $lng;
        
        include_once("./Services/ADN/ED/classes/class.adnLicense.php");
        
        if (is_array($_POST["license_id"])) {
            foreach ($_POST["license_id"] as $i) {
                $license = new adnLicense($i);
                $license->delete();
            }
        }
        ilUtil::sendSuccess($lng->txt("adn_license_deleted"), true);
        $ilCtrl->redirect($this, $this->getLink("listLicenses"));
    }

    /**
     * Download file
     */
    protected function downloadFile()
    {
        global $ilCtrl, $lng;

        $file = $this->license->getFilePath() . $this->license->getId();
        if (file_exists($file)) {
            ilUtil::deliverFile($file, $this->license->getFileName());
        } else {
            ilUtil::sendFailure($lng->txt("adn_file_corrupt"), true);
            $ilCtrl->redirect($this, $this->getLink("listLicenses"));
        }
    }

    /**
     * Set tabs
     */
    public function setTabs()
    {
        global $ilTabs, $lng, $txt, $ilCtrl;

        $ilTabs->addTab(
            adnLicense::TYPE_CHEMICALS,
            $lng->txt("adn_licenses_chem"),
            $ilCtrl->getLinkTarget($this, "listLicensesChem")
        );

        $ilTabs->addTab(
            adnLicense::TYPE_GAS,
            $lng->txt("adn_licenses_gas"),
            $ilCtrl->getLinkTarget($this, "listLicensesGas")
        );

        $ilTabs->activateTab($this->type);
    }

    /**
     * Add current type to link
     *
     * @param string $a_cmd
     * @return string
     */
    public function getLink($a_cmd)
    {
        if ($this->type == adnLicense::TYPE_CHEMICALS) {
            return $a_cmd . "Chem";
        } else {
            return $a_cmd . "Gas";
        }
    }
}
