<?php
/* Copyright (c) 2010 Leifos, GPL, see docs/LICENSE */

/**
 * Special character GUI class
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.de>
 * @version $Id: class.adnCharacterGUI.php 28757 2011-05-02 14:38:22Z jluetzen $
 *
 * @ilCtrl_Calls adnCharacterGUI:
 *
 * @ingroup ServicesADN
 */
class adnCharacterGUI
{
    // current character object
    protected $character = null;
    
    /**
     * Constructor
     */
    public function __construct()
    {
        global $ilCtrl;

        // save character ID through requests
        $ilCtrl->saveParameter($this, array("chr_id"));
        
        $this->readCharacter();
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
                $cmd = $ilCtrl->getCmd("listCharacters");

                switch ($cmd) {
                    // commands that need read permission
                    case "listCharacters":
                        if (adnPerm::check(adnPerm::AD, adnPerm::READ)) {
                            $this->$cmd();
                        }
                        break;
                    
                    // commands that need write permission
                    case "editCharacter":
                    case "addCharacter":
                    case "saveCharacter":
                    case "updateCharacter":
                    case "confirmDeleteCharacters":
                    case "deleteCharacters":
                        if (adnPerm::check(adnPerm::AD, adnPerm::WRITE)) {
                            $this->$cmd();
                        }
                        break;
                    
                }
                break;
        }
    }
    
    /**
     * Read character
     */
    protected function readCharacter()
    {
        if ((int) $_GET["chr_id"] > 0) {
            include_once("./Services/ADN/AD/classes/class.adnCharacter.php");
            $this->character = new adnCharacter((int) $_GET["chr_id"]);
        }
    }
    
    /**
     * List all characters
     */
    protected function listCharacters()
    {
        global $tpl, $ilCtrl, $ilToolbar, $lng, $ilTabs;

        // add button
        if (adnPerm::check(adnPerm::AD, adnPerm::WRITE)) {
            $ilToolbar->addButton(
                $lng->txt("adn_add_character"),
                $ilCtrl->getLinkTarget($this, "addCharacter")
            );
        }

        // table of countries
        include_once("./Services/ADN/AD/classes/class.adnCharacterTableGUI.php");
        $table = new adnCharacterTableGUI($this, "listCharacters");
        
        // output table
        $tpl->setContent($table->getHTML());
    }

    /**
     * Add character form
     *
     * @param ilPropertyFormGUI $a_form
     */
    protected function addCharacter(ilPropertyFormGUI $a_form = null)
    {
        global $tpl, $lng, $ilTabs, $ilCtrl;

        $ilTabs->setBackTarget($lng->txt("back"), $ilCtrl->getLinkTarget($this, "listCharacters"));

        if (!$a_form) {
            $a_form = $this->initCharacterForm(true);
        }
        $tpl->setContent($a_form->getHTML());
    }

    /**
     * Create new character
     */
    protected function saveCharacter()
    {
        global $tpl, $lng, $ilCtrl;

        $form = $this->initCharacterForm(true);
        if ($form->checkInput()) {
            include_once("./Services/ADN/AD/classes/class.adnCharacter.php");
            $character = new adnCharacter();
            $character->setName($form->getInput("name"));
            $character->setCode($form->getInput("code"));
            
            if ($character->save()) {
                ilUtil::sendSuccess($lng->txt("adn_character_created"), true);
                $ilCtrl->redirect($this, "listCharacters");
            }
        }

        // reload if invalid
        $form->setValuesByPost();
        $this->addCharacter($form);
    }

    /**
     * Edit character form
     *
     * @param ilPropertyFormGUI $a_form
     */
    protected function editCharacter(ilPropertyFormGUI $a_form = null)
    {
        global $tpl, $lng, $ilTabs, $ilCtrl;

        $ilTabs->setBackTarget($lng->txt("back"), $ilCtrl->getLinkTarget($this, "listCharacters"));

        if (!$a_form) {
            $a_form = $this->initCharacterForm();
        }
        $tpl->setContent($a_form->getHTML());
    }

    /**
     * Update existing character
     */
    protected function updateCharacter()
    {
        global $tpl, $lng, $ilCtrl;

        $form = $this->initCharacterForm();
        if ($form->checkInput()) {
            $this->character->setName($form->getInput("name"));
            $this->character->setCode($form->getInput("code"));
            
            if ($this->character->update()) {
                ilUtil::sendSuccess($lng->txt("adn_character_updated"), true);
                $ilCtrl->redirect($this, "listCharacters");
            }
        }

        // reload if invalid
        $form->setValuesByPost();
        $this->editCharacter($form);
    }

    /**
     * Build character form
     *
     * @return ilPropertyFormGUI
     */
    protected function initCharacterForm($a_create = false)
    {
        global $lng, $ilCtrl;

        include_once "Services/Form/classes/class.ilPropertyFormGUI.php";
        $form = new ilPropertyFormGUI();
        $form->setTitle($lng->txt("adn_character"));
        $form->setFormAction($ilCtrl->getFormAction($this, "listCharacters"));

        $name = new ilTextInputGUI($lng->txt("adn_char"), "name");
        $name->setSize(1);
        $name->setMaxLength(1);
        $form->addItem($name);

        $code = new ilNumberInputGUI($lng->txt("adn_char_code"), "code");
        $code->setSize(5);
        $code->setMaxLength(5);
        $form->addItem($code);

        if ($a_create) {
            $form->addCommandButton("saveCharacter", $lng->txt("save"));
        } else {
            $name->setValue($this->character->getName());
            $code->setValue($this->character->getCode());

            $form->addCommandButton("updateCharacter", $lng->txt("save"));
        }
        $form->addCommandButton("listCharacters", $lng->txt("cancel"));

        return $form;
    }

    /**
     * Confirm deletion of characters
     */
    public function confirmDeleteCharacters()
    {
        global $ilCtrl, $tpl, $lng, $ilTabs;

        // check whether at least one item has been seleced
        if (!is_array($_POST["chr_id"]) || count($_POST["chr_id"]) == 0) {
            ilUtil::sendFailure($lng->txt("no_checkbox"), true);
            $ilCtrl->redirect($this, "listCharacters");
        } else {
            $ilTabs->setBackTarget(
                $lng->txt("back"),
                $ilCtrl->getLinkTarget($this, "listCharacters")
            );

            // display confirmation message
            include_once("./Services/Utilities/classes/class.ilConfirmationGUI.php");
            $cgui = new ilConfirmationGUI();
            $cgui->setFormAction($ilCtrl->getFormAction($this));
            $cgui->setHeaderText($lng->txt("adn_sure_delete_characters"));
            $cgui->setCancel($lng->txt("cancel"), "listCharacters");
            $cgui->setConfirm($lng->txt("delete"), "deleteCharacters");

            include_once("./Services/ADN/AD/classes/class.adnCharacter.php");

            // list objects that should be deleted
            foreach ($_POST["chr_id"] as $i) {
                $cgui->addItem("chr_id[]", $i, adnCharacter::lookupName($i));
            }

            $tpl->setContent($cgui->getHTML());
        }
    }

    /**
     * Delete characters
     */
    protected function deleteCharacters()
    {
        global $ilCtrl, $lng;

        include_once("./Services/ADN/AD/classes/class.adnCharacter.php");

        if (is_array($_POST["chr_id"])) {
            foreach ($_POST["chr_id"] as $i) {
                $character = new adnCharacter($i);
                $character->delete();
            }
        }
        ilUtil::sendSuccess($lng->txt("adn_character_deleted"), true);
        $ilCtrl->redirect($this, "listCharacters");
    }
}
