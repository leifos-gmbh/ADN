<?php
/* Copyright (c) 2010 Leifos, GPL, see docs/LICENSE */

/**
 * ADN information letter GUI class
 *
 * Letter list, forms and persistence
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @version $Id: class.adnInformationLetterGUI.php 27891 2011-02-28 11:46:53Z jluetzen $
 *
 * @ilCtrl_Calls adnInformationLetterGUI:
 *
 * @ingroup ServicesADN
 */
class adnInformationLetterGUI
{
    // current letter object
    protected $letter = null;
    
    /**
     * Constructor
     */
    public function __construct()
    {
        global $ilCtrl;

        // save letter ID through requests
        $ilCtrl->saveParameter($this, array("il_id"));
        
        $this->readInformationLetter();
    }
    
    /**
     * Execute command
     */
    public function executeCommand()
    {
        global $ilCtrl, $tpl, $lng;

        $tpl->setTitle($lng->txt("adn_ta") . " - " . $lng->txt("adn_ta_ils"));
        
        $next_class = $ilCtrl->getNextClass();
        
        // forward command to next gui class in control flow
        switch ($next_class) {
            // no next class:
            // this class is responsible to process the command
            default:
                $cmd = $ilCtrl->getCmd("listInformationLetters");
                
                switch ($cmd) {
                    // commands that need read permission
                    case "listInformationLetters":
                    case "downloadInformationLetter":
                        if (adnPerm::check(adnPerm::TA, adnPerm::READ)) {
                            $this->$cmd();
                        }
                        break;
                    
                    // commands that need write permission
                    case "addInformationLetter":
                    case "saveInformationLetter":
                    // case "editInformationLetter":
                    // case "updateInformationLetter":
                    case "confirmInformationLettersDeletion":
                    case "deleteInformationLetters":
                        if (adnPerm::check(adnPerm::TA, adnPerm::WRITE)) {
                            $this->$cmd();
                        }
                        break;
                    
                }
                break;
        }
    }
    
    /**
     * Read letter
     */
    protected function readInformationLetter()
    {
        if ((int) $_GET["il_id"] > 0) {
            include_once("./Services/ADN/TA/classes/class.adnInformationLetter.php");
            $this->letter = new adnInformationLetter((int) $_GET["il_id"]);
        }
    }
    
    /**
     * List letters
     */
    public function listInformationLetters()
    {
        global $tpl, $lng, $ilCtrl, $ilToolbar;

        if (adnPerm::check(adnPerm::TA, adnPerm::WRITE)) {
            $ilToolbar->addButton(
                $lng->txt("adn_add_information_letter"),
                $ilCtrl->getLinkTarget($this, "addInformationLetter")
            );
        }

        include_once("./Services/ADN/TA/classes/class.adnInformationLetterTableGUI.php");
        $table = new adnInformationLetterTableGUI($this, "listInformationLetters");

        $tpl->setContent($table->getHTML());
    }
    
    /**
     * Add new letter form
     *
     * @param ilPropertyFormGUI $a_form
     */
    protected function addInformationLetter(ilPropertyFormGUI $a_form = null)
    {
        global $tpl, $ilTabs, $ilCtrl, $lng;

        $ilTabs->setBackTarget(
            $lng->txt("back"),
            $ilCtrl->getLinkTarget($this, "listInformationLetters")
        );

        if (!$a_form) {
            $a_form = $this->initInformationLetterForm("create");
        }
        $tpl->setContent($a_form->getHTML());
    }
    
    /**
     * Init letter form
     *
     * @param string $a_mode form mode ("create" | "edit")
     * @return ilPropertyFormGUI
     */
    protected function initInformationLetterForm($a_mode = "edit")
    {
        global $lng, $ilCtrl;
        
        // get form object and add input fields
        include_once("Services/Form/classes/class.ilPropertyFormGUI.php");
        $form = new ilPropertyFormGUI();
        
        // name
        $name = new ilTextInputGUI($lng->txt("adn_name"), "name");
        $name->setMaxLength(200);
        $name->setRequired(true);
        $form->addItem($name);

        // file
        $file = new ilFileInputGUI($lng->txt("file"), "file");
        $file->setRequired(true);
        $form->addItem($file);

        if ($a_mode == "create") {
            // creation: save/cancel buttons and title
            $form->addCommandButton("saveInformationLetter", $lng->txt("save"));
            $form->addCommandButton("listInformationLetters", $lng->txt("cancel"));
            $form->setTitle($lng->txt("adn_add_information_letter"));
        } else {
            $name->setValue($this->letter->getName());
            
            // editing: update/cancel buttons and title
            $form->addCommandButton("updateInformationLetter", $lng->txt("save"));
            $form->addCommandButton("listInformationLetters", $lng->txt("cancel"));
            $form->setTitle($lng->txt("adn_edit_information_letter"));
        }
        
        $form->setFormAction($ilCtrl->getFormAction($this));

        return $form;
    }
    
    /**
     * Create new letter
     */
    protected function saveInformationLetter()
    {
        global $tpl, $lng, $ilCtrl;
        
        $form = $this->initInformationLetterForm("create");
        
        // check input
        if ($form->checkInput()) {
            // input ok: create new letter
            include_once("./Services/ADN/TA/classes/class.adnInformationLetter.php");
            $letter = new adnInformationLetter();
            $letter->setName($form->getInput("name"));

            $file = $form->getInput("file");
            $letter->importFile($file["tmp_name"], $file["name"]);
            
            if ($letter->save()) {
                // show success message and return to list
                ilUtil::sendSuccess($lng->txt("adn_information_letter_created"), true);
                $ilCtrl->redirect($this, "listInformationLetters");
            }
        }

        // input not valid: show form again
        $form->setValuesByPost();
        $this->addInformationLetter($form);
    }
    
    /**
     * Confirm letters deletion
     */
    protected function confirmInformationLettersDeletion()
    {
        global $ilCtrl, $tpl, $lng, $ilTabs;
        
        // check whether at least one item has been seleced
        if (!is_array($_POST["letter_id"]) || count($_POST["letter_id"]) == 0) {
            ilUtil::sendFailure($lng->txt("no_checkbox"), true);
            $ilCtrl->redirect($this, "listInformationLetters");
        } else {
            $ilTabs->setBackTarget(
                $lng->txt("back"),
                $ilCtrl->getLinkTarget($this, "listInformationLetters")
            );

            // display confirmation message
            include_once("./Services/Utilities/classes/class.ilConfirmationGUI.php");
            $cgui = new ilConfirmationGUI();
            $cgui->setFormAction($ilCtrl->getFormAction($this));
            $cgui->setHeaderText($lng->txt("adn_sure_delete_information_letters"));
            $cgui->setCancel($lng->txt("cancel"), "listInformationLetters");
            $cgui->setConfirm($lng->txt("delete"), "deleteInformationLetters");

            // list objects that should be deleted
            include_once("./Services/ADN/TA/classes/class.adnInformationLetter.php");
            foreach ($_POST["letter_id"] as $i) {
                $cgui->addItem("letter_id[]", $i, adnInformationLetter::lookupName($i));
            }
            
            $tpl->setContent($cgui->getHTML());
        }
    }
    
    /**
     * Delete letters
     */
    protected function deleteInformationLetters()
    {
        global $ilCtrl, $lng;
        
        include_once("./Services/ADN/TA/classes/class.adnInformationLetter.php");
        
        if (is_array($_POST["letter_id"])) {
            foreach ($_POST["letter_id"] as $i) {
                $letter = new adnInformationLetter($i);
                $letter->delete();
            }
        }
        ilUtil::sendSuccess($lng->txt("adn_information_letter_deleted"), true);
        $ilCtrl->redirect($this, "listInformationLetters");
    }

    /**
     * Download file
     */
    protected function downloadInformationLetter()
    {
        global $ilCtrl, $lng;
        
        $file = $this->letter->getFilePath() . $this->letter->getId();
        if (file_exists($file)) {
            ilUtil::deliverFile($file, $this->letter->getFileName());
        } else {
            ilUtil::sendFailure($lng->txt("adn_file_corrupt"), true);
            $ilCtrl->redirect($this, "listInformationLetters");
        }
    }
}
