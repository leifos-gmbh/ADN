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
    protected $letter = null;
    
    /**
     * Constructor
     */
    public function __construct()
    {
        global $ilCtrl;

        // save letter ID through requests
        $ilCtrl->saveParameter($this, array("ilt_id"));
        
        $this->readLetter();
    }
    
    /**
     * Execute command
     */
    public function executeCommand()
    {
        global $ilCtrl, $lng, $tpl;

        $tpl->setTitle($lng->txt("adn_ep") . " - " . $lng->txt("adn_ep_ils"));
        
        $next_class = $ilCtrl->getNextClass();
        
        // forward command to next gui class in control flow
        switch ($next_class) {
            // no next class:
            // this class is responsible to process the command
            default:
                $cmd = $ilCtrl->getCmd("listLetters");

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
        global $tpl, $lng, $ilCtrl, $ilToolbar;
        
        if (adnPerm::check(adnPerm::EP, adnPerm::WRITE)) {
            include_once("./Services/Form/classes/class.ilFileInputGUI.php");
            $fi = new ilFileInputGUI($lng->txt("file"), "file");
            $ilToolbar->addInputItem($fi, true);
            $ilToolbar->setFormAction($ilCtrl->getFormAction($this, "saveLetter"), true);
            $ilToolbar->addFormButton($lng->txt("adn_add_information_letter_application"), "saveLetter");
        }

        include_once("./Services/ADN/EP/classes/class.adnExamInfoLetterTableGUI.php");
        $table = new adnExamInfoLetterTableGUI($this, "listLetters");

        $tpl->setContent($table->getHTML());
    }
    
    /**
     * Save uploaded letter
     */
    protected function saveLetter()
    {
        global $tpl, $lng, $ilCtrl;

        // check input
        if ($_FILES["file"]["tmp_name"]) {
            // input ok: create new letter
            include_once("./Services/ADN/EP/classes/class.adnExamInfoLetter.php");
            $letter = new adnExamInfoLetter();

            $letter->importFile($_FILES["file"]["tmp_name"], $_FILES["file"]["name"]);
            
            if ($letter->save()) {
                // show success message and return to list
                ilUtil::sendSuccess($lng->txt("adn_information_letter_created"), true);
                $ilCtrl->redirect($this, "listLetters");
            }
        }

        // input not valid: show form again
        ilUtil::sendFailure($lng->txt("adn_missing_file"));
        $this->listLetters();
    }
    
    /**
     * Confirm letters deletion
     */
    protected function confirmLettersDeletion()
    {
        global $ilCtrl, $tpl, $lng, $ilTabs;
        
        // check whether at least one item has been seleced
        if (!is_array($_POST["letter_id"]) || count($_POST["letter_id"]) == 0) {
            ilUtil::sendFailure($lng->txt("no_checkbox"), true);
            $ilCtrl->redirect($this, "listLetters");
        } else {
            $ilTabs->setBackTarget(
                $lng->txt("back"),
                $ilCtrl->getLinkTarget($this, "listLetters")
            );
            
            // display confirmation message
            include_once("./Services/Utilities/classes/class.ilConfirmationGUI.php");
            $cgui = new ilConfirmationGUI();
            $cgui->setFormAction($ilCtrl->getFormAction($this));
            $cgui->setHeaderText($lng->txt("adn_sure_delete_information_letters"));
            $cgui->setCancel($lng->txt("cancel"), "listLetters");
            $cgui->setConfirm($lng->txt("delete"), "deleteLetters");

            // list objects that should be deleted
            include_once("./Services/ADN/EP/classes/class.adnExamInfoLetter.php");
            foreach ($_POST["letter_id"] as $i) {
                $cgui->addItem("letter_id[]", $i, adnExamInfoLetter::lookupName($i));
            }
            
            $tpl->setContent($cgui->getHTML());
        }
    }
    
    /**
     * Delete letters
     */
    protected function deleteLetters()
    {
        global $ilCtrl, $lng;
        
        include_once("./Services/ADN/EP/classes/class.adnExamInfoLetter.php");
        
        if (is_array($_POST["letter_id"])) {
            foreach ($_POST["letter_id"] as $i) {
                $letter = new adnExamInfoLetter($i);
                $letter->delete();
            }
        }
        ilUtil::sendSuccess($lng->txt("adn_information_letter_deleted"), true);
        $ilCtrl->redirect($this, "listLetters");
    }

    /**
     * Download file
     */
    protected function downloadFile()
    {
        global $ilCtrl, $lng;

        $file = $this->letter->getFilePath() . $this->letter->getId();
        if (file_exists($file)) {
            ilUtil::deliverFile($file, $this->letter->getFileName());
        } else {
            ilUtil::sendFailure($lng->txt("adn_file_corrupt"), true);
            $ilCtrl->redirect($this, "listLetters");
        }
    }
}
