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
    protected ?adnCharacter $character = null;

    protected ilCtrl $ctrl;
    protected ilGlobalTemplate $tpl;
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

        // save character ID through requests
        $this->ctrl->saveParameter($this, array("chr_id"));
        
        $this->readCharacter();
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
                $cmd = $this->ctrl->getCmd("listCharacters");

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

        // add button
        if (adnPerm::check(adnPerm::AD, adnPerm::WRITE)) {
            $this->toolbar->addButton(
                $this->lng->txt("adn_add_character"),
                $this->ctrl->getLinkTarget($this, "addCharacter")
            );
        }

        // table of countries
        include_once("./Services/ADN/AD/classes/class.adnCharacterTableGUI.php");
        $table = new adnCharacterTableGUI($this, "listCharacters");
        
        // output table
        $this->tpl->setContent($table->getHTML());
    }

    /**
     * Add character form
     *
     * @param ilPropertyFormGUI $a_form
     */
    protected function addCharacter(ilPropertyFormGUI $a_form = null)
    {

        $this->tabs->setBackTarget($this->lng->txt("back"), $this->ctrl->getLinkTarget($this, "listCharacters"));

        if (!$a_form) {
            $a_form = $this->initCharacterForm(true);
        }
        $this->tpl->setContent($a_form->getHTML());
    }

    /**
     * Create new character
     */
    protected function saveCharacter()
    {

        $form = $this->initCharacterForm(true);
        if ($form->checkInput()) {
            include_once("./Services/ADN/AD/classes/class.adnCharacter.php");
            $character = new adnCharacter();
            $character->setName($form->getInput("name"));
            $character->setCode($form->getInput("code"));
            
            if ($character->save()) {
                ilUtil::sendSuccess($this->lng->txt("adn_character_created"), true);
                $this->ctrl->redirect($this, "listCharacters");
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

        $this->tabs->setBackTarget($this->lng->txt("back"), $this->ctrl->getLinkTarget($this, "listCharacters"));

        if (!$a_form) {
            $a_form = $this->initCharacterForm();
        }
        $this->tpl->setContent($a_form->getHTML());
    }

    /**
     * Update existing character
     */
    protected function updateCharacter()
    {

        $form = $this->initCharacterForm();
        if ($form->checkInput()) {
            $this->character->setName($form->getInput("name"));
            $this->character->setCode($form->getInput("code"));
            
            if ($this->character->update()) {
                ilUtil::sendSuccess($this->lng->txt("adn_character_updated"), true);
                $this->ctrl->redirect($this, "listCharacters");
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

        include_once "Services/Form/classes/class.ilPropertyFormGUI.php";
        $form = new ilPropertyFormGUI();
        $form->setTitle($this->lng->txt("adn_character"));
        $form->setFormAction($this->ctrl->getFormAction($this, "listCharacters"));

        $name = new ilTextInputGUI($this->lng->txt("adn_char"), "name");
        $name->setSize(1);
        $name->setMaxLength(1);
        $form->addItem($name);

        $code = new ilNumberInputGUI($this->lng->txt("adn_char_code"), "code");
        $code->setSize(5);
        $code->setMaxLength(5);
        $form->addItem($code);

        if ($a_create) {
            $form->addCommandButton("saveCharacter", $this->lng->txt("save"));
        } else {
            $name->setValue($this->character->getName());
            $code->setValue($this->character->getCode());

            $form->addCommandButton("updateCharacter", $this->lng->txt("save"));
        }
        $form->addCommandButton("listCharacters", $this->lng->txt("cancel"));

        return $form;
    }

    /**
     * Confirm deletion of characters
     */
    public function confirmDeleteCharacters()
    {

        // check whether at least one item has been seleced
        if (!is_array($_POST["chr_id"]) || count($_POST["chr_id"]) == 0) {
            ilUtil::sendFailure($this->lng->txt("no_checkbox"), true);
            $this->ctrl->redirect($this, "listCharacters");
        } else {
            $this->tabs->setBackTarget(
                $this->lng->txt("back"),
                $this->ctrl->getLinkTarget($this, "listCharacters")
            );

            // display confirmation message
            include_once("./Services/Utilities/classes/class.ilConfirmationGUI.php");
            $cgui = new ilConfirmationGUI();
            $cgui->setFormAction($this->ctrl->getFormAction($this));
            $cgui->setHeaderText($this->lng->txt("adn_sure_delete_characters"));
            $cgui->setCancel($this->lng->txt("cancel"), "listCharacters");
            $cgui->setConfirm($this->lng->txt("delete"), "deleteCharacters");

            include_once("./Services/ADN/AD/classes/class.adnCharacter.php");

            // list objects that should be deleted
            foreach ($_POST["chr_id"] as $i) {
                $cgui->addItem("chr_id[]", $i, adnCharacter::lookupName($i));
            }

            $this->tpl->setContent($cgui->getHTML());
        }
    }

    /**
     * Delete characters
     */
    protected function deleteCharacters()
    {

        include_once("./Services/ADN/AD/classes/class.adnCharacter.php");

        if (is_array($_POST["chr_id"])) {
            foreach ($_POST["chr_id"] as $i) {
                $character = new adnCharacter($i);
                $character->delete();
            }
        }
        ilUtil::sendSuccess($this->lng->txt("adn_character_deleted"), true);
        $this->ctrl->redirect($this, "listCharacters");
    }
}
