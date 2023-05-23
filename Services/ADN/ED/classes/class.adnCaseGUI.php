<?php
/* Copyright (c) 2010 Leifos, GPL, see docs/LICENSE */

include_once("./Services/ADN/ED/classes/class.adnSubjectArea.php");

/**
 * ADN case GUI class
 *
 * 3 simple case forms, no list
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @version $Id: class.adnCaseGUI.php 27873 2011-02-25 16:21:55Z jluetzen $
 *
 * @ilCtrl_Calls adnCaseGUI:
 *
 * @ingroup ServicesADN
 */
class adnCaseGUI
{
    // current type
    protected $type = null;

    // current subtype
    protected $butan = null;

    // current case object
    protected $case = null;
    
    /**
     * Constructor
     */
    public function __construct()
    {
        global $ilCtrl;

        // save case ID through requests
        $ilCtrl->saveParameter($this, array("cs_id"));
    }
    
    /**
     * Execute command
     */
    public function executeCommand()
    {
        global $ilCtrl, $lng, $tpl;

        $tpl->setTitle($lng->txt("adn_ed") . " - " . $lng->txt("adn_ed_cas"));
        adnIcon::setTitleIcon("ed_cas");
        
        $next_class = $ilCtrl->getNextClass();
        
        // forward command to next gui class in control flow
        switch ($next_class) {
            // no next class:
            // this class is responsible to process the command
            default:
                $cmd = $ilCtrl->getCmd("editCase");
                
                // determine type from cmd (gas|chem)
                $cmd = explode("Case", $cmd);
                $type = $cmd[1];
                $cmd = $cmd[0] . "Case";
                $this->butan = false;
                if (substr($type, 0, 3) == "Gas" || $type == "") {
                    $this->type = adnSubjectArea::GAS;

                    // butan or empty
                    if (substr($type, -5) == "Butan") {
                        $this->butan = true;
                    }
                } else {
                    $this->type = adnSubjectArea::CHEMICAL;
                }

                $this->setTabs();
                $this->readCase();

                switch ($cmd) {
                    // commands that need read permission
                    case "editCase":
                        if (adnPerm::check(adnPerm::ED, adnPerm::READ)) {
                            $this->$cmd();
                        }
                        break;

                    // commands that need write permission
                    case "updateCase":
                        if (adnPerm::check(adnPerm::ED, adnPerm::WRITE)) {
                            $this->$cmd();
                        }
                        break;
                }
                break;
        }
    }
    
    /**
     * Read case
     */
    protected function readCase()
    {
        $id = (int) $_GET["cs_id"];

        include_once("./Services/ADN/ED/classes/class.adnCase.php");
        if (!$id) {
            $id = adnCase::getIdByArea($this->type, $this->butan);
        }

        if ($id) {
            $this->case = new adnCase($id);

            // set type and butan from current case
            if ($this->case->getArea() == adnSubjectArea::GAS) {
                $this->type = adnSubjectArea::GAS;
            } else {
                $this->type = adnSubjectArea::CHEMICAL;
            }
            $this->butan = $this->case->getButan();
        }
    }

    /**
     * Edit case form
     *
     * @param ilPropertyFormGUI $a_form
     */
    protected function editCase(ilPropertyFormGUI $a_form = null)
    {
        global $tpl;

        if (!$a_form) {
            $a_form = $this->initCaseForm("edit");
        }
        $tpl->setContent($a_form->getHTML());
    }
    
    /**
     * Init case form (mind read-only mode)
     *
     * @return	ilPropertyFormGUI
     */
    protected function initCaseForm()
    {
        global $lng, $ilCtrl;
        
        // get form object and add input fields
        include_once("Services/Form/classes/class.ilPropertyFormGUI.php");
        $form = new ilPropertyFormGUI();

        // text
        if (adnPerm::check(adnPerm::ED, adnPerm::WRITE)) {
            $text = new ilTextAreaInputGUI($lng->txt("adn_text"), "text");
            $text->setRequired(true);
            $text->setCols(80);
            $text->setRows(20);
            $text->setSpecialCharacters(true, true);
            $text->setFormId($form->getId());
        } else {
            $text = new ilNonEditableValueGUI($lng->txt("adn_text"));
        }
        $text->setInfo(wordwrap($lng->txt("adn_case_placeholder_" . $this->type), 90, "<br />"));
        $form->addItem($text);

        if ($this->case) {
            $text->setValue($this->case->getText());
        }

        if (adnPerm::check(adnPerm::ED, adnPerm::WRITE)) {
            $cmd = "updateCase" . ucfirst($this->type);
            $cmd2 = "editCase" . ucfirst($this->type);
            if ($this->butan) {
                $cmd .= "Butan";
                $cmd2 .= "Butan";
            }
            $form->addCommandButton($cmd, $lng->txt("save"));
            $form->addCommandButton($cmd2, $lng->txt("cancel"));
            $form->setTitle($lng->txt("adn_case"));
        }
        
        $form->setFormAction($ilCtrl->getFormAction($this));

        return $form;
    }
    
    /**
     * Update case
     */
    protected function updateCase()
    {
        global $lng, $ilCtrl, $tpl;
        
        $form = $this->initCaseForm();

        $cmd = "editCase" . ucfirst($this->type);
        if ($this->butan) {
            $cmd .= "Butan";
        }
        
        // check input
        if ($form->checkInput()) {
            // perform update
            if (!$this->case) {
                include_once("./Services/ADN/ED/classes/class.adnCase.php");
                $case = new adnCase();
                $case->setArea($this->type);
                $case->setButan($this->butan);
                $case->setText($form->getInput("text"));
                if ($case->save()) {
                    ilUtil::sendSuccess($lng->txt("adn_case_created"), true);
                    $ilCtrl->redirect($this, $cmd);
                }
            } else {
                $this->case->setText($form->getInput("text"));
                if ($this->case->update()) {
                    // show success message and return to list
                    ilUtil::sendSuccess($lng->txt("adn_case_updated"), true);
                    $ilCtrl->redirect($this, $cmd);
                }
            }
        }
        
        // input not valid: show form again
        $form->setValuesByPost();
        $this->editCase($form);
    }
    
    /**
     * Set tabs
     */
    public function setTabs()
    {
        global $ilTabs, $lng, $txt, $ilCtrl;

        $ilTabs->addTab(
            adnSubjectArea::GAS . "_0",
            $lng->txt("adn_case_gas_empty"),
            $ilCtrl->getLinkTarget($this, "editCaseGas")
        );

        $ilTabs->addTab(
            adnSubjectArea::GAS . "_1",
            $lng->txt("adn_case_gas_butan"),
            $ilCtrl->getLinkTarget($this, "editCaseGasButan")
        );

        $ilTabs->addTab(
            adnSubjectArea::CHEMICAL . "_0",
            $lng->txt("adn_case_chem"),
            $ilCtrl->getLinkTarget($this, "editCaseChemicals")
        );

        $ilTabs->activateTab($this->type . "_" . (int) $this->butan);
    }
}
