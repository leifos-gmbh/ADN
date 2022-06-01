<?php
/* Copyright (c) 2010 Leifos, GPL, see docs/LICENSE */

/**
 * ADN exam information letter GUI class
 *
 * List all files
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @version $Id: class.adnExamInfoLetterGUI.php 27888 2011-02-28 11:09:28Z jluetzen $
 *
 * @ilCtrl_Calls adnExamInfoLetterGUI:
 *
 * @ingroup ServicesADN
 */
class adnExamInfoLetterGUI
{
    // current letter object
    protected ?adnExamInfoLetter $letter = null;
    
    protected ilCtrl $ctrl;
    protected ilLanguage $lng;
    protected ilGlobalTemplateInterface $tpl;
    protected ilToolbarGUI $toolbar;
    protected ilTabsGUI $tabs;

    public function __construct()
    {
        global $DIC;
        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();
        $this->tpl = $DIC->ui()->mainTemplate();
        $this->toolbar = $DIC->toolbar();
        $this->tabs = $DIC->tabs();

        // save letter ID through requests
        $this->ctrl->saveParameter($this, array("ilt_id"));
        
        $this->readLetter();
    }
    
    /**
     * Execute command
     */
    public function executeCommand()
    {

        $this->tpl->setTitle($this->lng->txt("adn_ep") . " - " . $this->lng->txt("adn_ep_ils"));
        
        $next_class = $this->ctrl->getNextClass();
        
        // forward command to next gui class in control flow
        switch ($next_class) {
            // no next class:
            // this class is responsible to process the command
            default:
                $cmd = $this->ctrl->getCmd("listLetters");

                switch ($cmd) {
                    // commands that need read permission
                    case "listLetters":
                    case "downloadFile":
                        if (adnPerm::check(adnPerm::EP, adnPerm::READ)) {
                            $this->$cmd();
                        }
                        break;
                    
                    // commands that need write permission
                    case "saveLetter":
                    case "confirmLettersDeletion":
                    case "deleteLetters":
                        if (adnPerm::check(adnPerm::EP, adnPerm::WRITE)) {
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
    protected function readLetter()
    {
        if ((int) $_GET["ilt_id"] > 0) {
            include_once("./Services/ADN/EP/classes/class.adnExamInfoLetter.php");
            $this->letter = new adnExamInfoLetter((int) $_GET["ilt_id"]);
        }
    }

    /**
     * List letters
     */
    protected function listLetters()
    {
        
        if (adnPerm::check(adnPerm::EP, adnPerm::WRITE)) {
            include_once("./Services/Form/classes/class.ilFileInputGUI.php");
            $fi = new ilFileInputGUI($this->lng->txt("file"), "file");
            $this->toolbar->addInputItem($fi, true);
            $this->toolbar->setFormAction($this->ctrl->getFormAction($this, "saveLetter"), true);
            $this->toolbar->addFormButton($this->lng->txt("adn_add_information_letter_application"), "saveLetter");
        }

        include_once("./Services/ADN/EP/classes/class.adnExamInfoLetterTableGUI.php");
        $table = new adnExamInfoLetterTableGUI($this, "listLetters");

        $this->tpl->setContent($table->getHTML());
    }
    
    /**
     * Save uploaded letter
     */
    protected function saveLetter()
    {

        // check input
        if ($_FILES["file"]["tmp_name"]) {
            // input ok: create new letter
            include_once("./Services/ADN/EP/classes/class.adnExamInfoLetter.php");
            $letter = new adnExamInfoLetter();

            $letter->importFile($_FILES["file"]["tmp_name"], $_FILES["file"]["name"]);
            
            if ($letter->save()) {
                // show success message and return to list
                ilUtil::sendSuccess($this->lng->txt("adn_information_letter_created"), true);
                $this->ctrl->redirect($this, "listLetters");
            }
        }

        // input not valid: show form again
        ilUtil::sendFailure($this->lng->txt("adn_missing_file"));
        $this->listLetters();
    }
    
    /**
     * Confirm letters deletion
     */
    protected function confirmLettersDeletion()
    {
        
        // check whether at least one item has been seleced
        if (!is_array($_POST["letter_id"]) || count($_POST["letter_id"]) == 0) {
            ilUtil::sendFailure($this->lng->txt("no_checkbox"), true);
            $this->ctrl->redirect($this, "listLetters");
        } else {
            $this->tabs->setBackTarget(
                $this->lng->txt("back"),
                $this->ctrl->getLinkTarget($this, "listLetters")
            );
            
            // display confirmation message
            include_once("./Services/Utilities/classes/class.ilConfirmationGUI.php");
            $cgui = new ilConfirmationGUI();
            $cgui->setFormAction($this->ctrl->getFormAction($this));
            $cgui->setHeaderText($this->lng->txt("adn_sure_delete_information_letters"));
            $cgui->setCancel($this->lng->txt("cancel"), "listLetters");
            $cgui->setConfirm($this->lng->txt("delete"), "deleteLetters");

            // list objects that should be deleted
            include_once("./Services/ADN/EP/classes/class.adnExamInfoLetter.php");
            foreach ($_POST["letter_id"] as $i) {
                $cgui->addItem("letter_id[]", $i, adnExamInfoLetter::lookupName($i));
            }
            
            $this->tpl->setContent($cgui->getHTML());
        }
    }
    
    /**
     * Delete letters
     */
    protected function deleteLetters()
    {
        
        include_once("./Services/ADN/EP/classes/class.adnExamInfoLetter.php");
        
        if (is_array($_POST["letter_id"])) {
            foreach ($_POST["letter_id"] as $i) {
                $letter = new adnExamInfoLetter($i);
                $letter->delete();
            }
        }
        ilUtil::sendSuccess($this->lng->txt("adn_information_letter_deleted"), true);
        $this->ctrl->redirect($this, "listLetters");
    }

    /**
     * Download file
     */
    protected function downloadFile()
    {

        $file = $this->letter->getFilePath() . $this->letter->getId();
        if (file_exists($file)) {
            ilUtil::deliverFile($file, $this->letter->getFileName());
        } else {
            ilUtil::sendFailure($this->lng->txt("adn_file_corrupt"), true);
            $this->ctrl->redirect($this, "listLetters");
        }
    }
}
