<?php
/* Copyright (c) 2010 Leifos, GPL, see docs/LICENSE */

/**
 * MC question export GUI class
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.de>
 * @version $Id: class.adnMCQuestionExportGUI.php 27867 2011-02-25 09:35:47Z akill $
 *
 * @ilCtrl_Calls adnMCQuestionExportGUI:
 *
 * @ingroup ServicesADN
 */
class adnMCQuestionExportGUI
{
    protected ilCtrl $ctrl;
    protected ilGlobalTemplateInterface $tpl;
    protected ilToolbarGUI $toolbar;
    protected ilLanguage $lng;
    protected ilTabsGUI $tabs;

    public function __construct()
    {
        global $DIC;

        $this->ctrl = $DIC->ctrl();
        $this->tpl = $DIC->ui()->mainTemplate();
        $this->toolbar = $DIC->toolbar();
        $this->lng = $DIC->language();
        $this->tabs = $DIC->tabs();
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
                $cmd = $this->ctrl->getCmd("listFiles");

                switch ($cmd) {
                    // commands that need write permission
                    case "listFiles":
                    case "exportFile":
                    case "downloadFile":
                    case "confirmDeleteFiles":
                    case "deleteFiles":
                    case "importFile":
                    case "confirmImport":
                    case "saveImport":
                        if (adnPerm::check(adnPerm::AD, adnPerm::WRITE)) {
                            $this->$cmd();
                        }
                        break;

                }
                break;
        }
    }
    
    /**
     * List all export files
     */
    protected function listFiles()
    {

        // create export / import file
        if (adnPerm::check(adnPerm::AD, adnPerm::WRITE)) {
            $this->toolbar->addButton(
                $this->lng->txt("adn_create_mc_export"),
                $this->ctrl->getLinkTarget($this, "exportFile")
            );
            $this->toolbar->addButton(
                $this->lng->txt("adn_import_mc_questions"),
                $this->ctrl->getLinkTarget($this, "importFile")
            );
        }

        // table of countries
        include_once("./Services/ADN/AD/classes/class.adnMCQuestionExportTableGUI.php");
        $table = new adnMCQuestionExportTableGUI($this, "listFiles");
        
        // output table
        $this->tpl->setContent($table->getHTML());
    }

    /**
     * Build new export file
     */
    protected function exportFile()
    {

        include_once "Services/ADN/ED/classes/class.adnQuestionExport.php";
        $export = new adnQuestionExport();
        $export->buildExport();

        ilUtil::sendSuccess($this->lng->txt("adn_mc_export_success"), true);
        $this->ctrl->redirect($this, "listFiles");
    }

    /**
     * Import questions form
     *
     * @param ilPropertyFormGUI $a_form
     */
    protected function importFile(ilPropertyFormGUI $a_form = null)
    {

        // remove "old" tmp files
        include_once "Services/ADN/ED/classes/class.adnQuestionExport.php";
        foreach (glob(adnQuestionExport::getFilePath() . "/tuf_*") as $file) {
            @unlink($file);
        }

        $this->tabs->setBackTarget($this->lng->txt("back"), $this->ctrl->getLinkTarget($this, "listFiles"));

        if (!$a_form) {
            $a_form = $this->initUploadForm();
        }
        $this->tpl->setContent($a_form->getHTML());
    }

    /**
     * Build upload form
     * @return object
     */
    protected function initUploadForm()
    {

        include_once "Services/Form/classes/class.ilPropertyFormGUI.php";
        $form = new ilPropertyFormGUI();
        $form->setTitle($this->lng->txt("adn_import_mc_questions"));
        $form->setFormAction($this->ctrl->getFormAction($this, "listFiles"));

        $opts = new ilCheckboxGroupInputGUI($this->lng->txt("adn_import_include"), "opts");
        $opts->setRequired(true);
        $form->addItem($opts);

        $mc = new ilCheckboxOption($this->lng->txt("adn_include_mc_questions"), "mc");
        $opts->addOption($mc);

        $case = new ilCheckboxOption($this->lng->txt("adn_include_case_questions"), "case");
        $opts->addOption($case);

        $obj = new ilCheckboxOption($this->lng->txt("adn_include_objectives"), "obj");
        $opts->addOption($obj);

        $tgt = new ilCheckboxOption($this->lng->txt("adn_include_target_numbers"), "tgt");
        $opts->addOption($tgt);

        $goods = new ilCheckboxOption($this->lng->txt("adn_include_goods"), "goods");
        $opts->addOption($goods);

        $del = new ilCheckboxInputGUI($this->lng->txt("adn_delete_old_data"), "delall");
        $form->addItem($del);

        $update = new ilCheckboxInputGUI($this->lng->txt("adn_enable_updates"), "update");
        $form->addItem($update);

        $file = new ilFileInputGUI($this->lng->txt("file"), "file");
        $file->setRequired(true);
        $file->setSuffixes(array("zip"));
        $form->addItem($file);

        $form->addCommandButton("confirmImport", $this->lng->txt("update"));
        $form->addCommandButton("listFiles", $this->lng->txt("cancel"));

        return $form;
    }

    /**
     * Confirm import questions
     */
    protected function confirmImport()
    {

        $form = $this->initUploadForm();
        if ($form->checkInput()) {
            include_once "Services/ADN/ED/classes/class.adnQuestionImport.php";
            $export = new adnQuestionImport();
            $file = $form->getInput("file");
            $target = adnQuestionExport::getFilePath() . "/tuf_" . md5(uniqid());
        
            if (move_uploaded_file($file["tmp_name"], $target)) {
                // init dry run
                $opts = $form->getInput("opts");
                $log = $export->processImport(
                    $target,
                    in_array("mc", $opts),
                    in_array("case", $opts),
                    in_array("obj", $opts),
                    in_array("tgt", $opts),
                    in_array("goods", $opts),
                    (bool) $form->getInput("delall"),
                    (bool) $form->getInput("update"),
                    true
                );

                if ($log !== false) {
                    include_once("./Services/Utilities/classes/class.ilConfirmationGUI.php");
                    $cgui = new ilConfirmationGUI();
                    $cgui->setFormAction($this->ctrl->getFormAction($this));
                    $cgui->setHeaderText($this->lng->txt("adn_sure_import_mc_questions"));
                    $cgui->setCancel($this->lng->txt("cancel"), "listFiles");
                    $cgui->setConfirm($this->lng->txt("import"), "saveImport");

                    // add form values for next request
                    $cgui->addHiddenItem("token", basename($target));
                    if (in_array("mc", $opts)) {
                        $cgui->addHiddenItem("opts[]", "mc");
                    }
                    if (in_array("case", $opts)) {
                        $cgui->addHiddenItem("opts[]", "case");
                    }
                    if (in_array("obj", $opts)) {
                        $cgui->addHiddenItem("opts[]", "obj");
                    }
                    if (in_array("tgt", $opts)) {
                        $cgui->addHiddenItem("opts[]", "tgt");
                    }
                    if (in_array("goods", $opts)) {
                        $cgui->addHiddenItem("opts[]", "goods");
                    }
                    $cgui->addHiddenItem("delall", $form->getInput("delall"));
                    $cgui->addHiddenItem("update", $form->getInput("update"));

                    // display log for confirmation
                    if (sizeof($log)) {
                        foreach ($log as $type => $data) {
                            // valid: summary
                            if ($data["valid"]) {
                                $cgui->addItem(
                                    "dummy_id[]",
                                    1,
                                    $this->lng->txt("adn_mc_import_" . $type . "_valid") . ": " . sizeof($data["valid"])
                                );
                            }
                            // invalid: show items
                            if ($data["invalid"]) {
                                // more than 10 items: truncate
                                if (sizeof($data["invalid"]) > 10) {
                                    $invalid = array_slice($data["invalid"], 0, 10);
                                    $invalid = sizeof($data["invalid"]) . " (" . implode(", ", $invalid) . " [...])";
                                }
                                // less: show all
                                else {
                                    $invalid = implode(", ", $data["invalid"]);
                                }

                                $cgui->addItem(
                                    "dummy_id[]",
                                    1,
                                    $this->lng->txt("adn_mc_import_" . $type . "_invalid") . ": " . $invalid
                                );
                            }
                        }
                    }

                    $this->tpl->setContent($cgui->getHTML());
                    return;
                }
            }

            ilUtil::sendFailure($this->lng->txt("adn_mc_questions_import_failed"));
        }

        $form->setValuesByPost();
        $this->importFile($form);
    }

    /**
     * Import questions
     */
    protected function saveImport()
    {

        $token = $_REQUEST["token"];
        if ($token) {
            include_once "Services/ADN/ED/classes/class.adnQuestionImport.php";
            $export = new adnQuestionImport();
            $target = adnQuestionExport::getFilePath() . "/" . $token;
            if (file_exists($target)) {
                $opts = $_REQUEST["opts"];
                if ($export->processImport(
                    $target,
                    in_array("mc", $opts),
                    in_array("case", $opts),
                    in_array("obj", $opts),
                    in_array("tgt", $opts),
                    in_array("goods", $opts),
                    $_REQUEST["delall"],
                    $_REQUEST["update"],
                    false
                )) {
                    unlink($target);
                    ilUtil::sendSuccess($this->lng->txt("adn_mc_questions_import_success"), true);
                    $this->ctrl->redirect($this, "listFiles");
                }
            }
        }
        ilUtil::sendFailure($this->lng->txt("adn_mc_questions_import_failed"), true);
        $this->ctrl->redirect($this, "listFiles");
    }

    /**
     * Confirm deletion of export files
     */
    public function confirmDeleteFiles()
    {

        // check whether at least one item has been seleced
        if (!is_array($_POST["file_id"]) || count($_POST["file_id"]) == 0) {
            ilUtil::sendFailure($this->lng->txt("no_checkbox"), true);
            $this->ctrl->redirect($this, "listFiles");
        } else {
            $this->tabs->setBackTarget(
                $this->lng->txt("back"),
                $this->ctrl->getLinkTarget($this, "listFiles")
            );

            // display confirmation message
            include_once("./Services/Utilities/classes/class.ilConfirmationGUI.php");
            $cgui = new ilConfirmationGUI();
            $cgui->setFormAction($this->ctrl->getFormAction($this));
            $cgui->setHeaderText($this->lng->txt("adn_sure_delete_export_files"));
            $cgui->setCancel($this->lng->txt("cancel"), "listFiles");
            $cgui->setConfirm($this->lng->txt("delete"), "deleteFiles");

            include_once("./Services/ADN/ED/classes/class.adnQuestionExport.php");

            // list objects that should be deleted
            foreach ($_POST["file_id"] as $i) {
                $cgui->addItem("file_id[]", $i, adnQuestionExport::lookupName($i));
            }

            $this->tpl->setContent($cgui->getHTML());
        }
    }

    /**
     * Delete files
     */
    protected function deleteFiles()
    {

        if (is_array($_POST["file_id"])) {
            include_once("./Services/ADN/ED/classes/class.adnQuestionExport.php");
            foreach ($_POST["file_id"] as $i) {
                adnQuestionExport::delete($i);
            }
        }
        ilUtil::sendSuccess($this->lng->txt("adn_export_file_deleted"), true);
        $this->ctrl->redirect($this, "listFiles");
    }

    /**
     * Download export file
     */
    protected function downloadFile()
    {

        include_once("./Services/ADN/ED/classes/class.adnQuestionExport.php");
        $file = adnQuestionExport::getFilePath() . "/" . $_REQUEST["exf_id"];
        if (file_exists($file)) {
            ilUtil::deliverFile($file, adnQuestionExport::lookupName($_REQUEST["exf_id"], true));
        } else {
            ilUtil::sendFailure($this->lng->txt("adn_file_corrupt"), true);
            $this->ctrl->redirect($this, "listFiles");
        }
    }
}
