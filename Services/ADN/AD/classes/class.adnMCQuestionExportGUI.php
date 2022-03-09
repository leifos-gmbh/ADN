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
                $cmd = $ilCtrl->getCmd("listFiles");

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
        global $tpl, $ilCtrl, $ilToolbar, $lng, $ilTabs;

        // create export / import file
        if (adnPerm::check(adnPerm::AD, adnPerm::WRITE)) {
            $ilToolbar->addButton(
                $lng->txt("adn_create_mc_export"),
                $ilCtrl->getLinkTarget($this, "exportFile")
            );
            $ilToolbar->addButton(
                $lng->txt("adn_import_mc_questions"),
                $ilCtrl->getLinkTarget($this, "importFile")
            );
        }

        // table of countries
        include_once("./Services/ADN/AD/classes/class.adnMCQuestionExportTableGUI.php");
        $table = new adnMCQuestionExportTableGUI($this, "listFiles");
        
        // output table
        $tpl->setContent($table->getHTML());
    }

    /**
     * Build new export file
     */
    protected function exportFile()
    {
        global $ilCtrl, $lng;

        include_once "Services/ADN/ED/classes/class.adnQuestionExport.php";
        $export = new adnQuestionExport();
        $export->buildExport();

        ilUtil::sendSuccess($lng->txt("adn_mc_export_success"), true);
        $ilCtrl->redirect($this, "listFiles");
    }

    /**
     * Import questions form
     *
     * @param ilPropertyFormGUI $a_form
     */
    protected function importFile(ilPropertyFormGUI $a_form = null)
    {
        global $tpl, $lng, $ilTabs, $ilCtrl;

        // remove "old" tmp files
        include_once "Services/ADN/ED/classes/class.adnQuestionExport.php";
        foreach (glob(adnQuestionExport::getFilePath() . "/tuf_*") as $file) {
            @unlink($file);
        }

        $ilTabs->setBackTarget($lng->txt("back"), $ilCtrl->getLinkTarget($this, "listFiles"));

        if (!$a_form) {
            $a_form = $this->initUploadForm();
        }
        $tpl->setContent($a_form->getHTML());
    }

    /**
     * Build upload form
     * @return object
     */
    protected function initUploadForm()
    {
        global  $lng, $ilCtrl;

        include_once "Services/Form/classes/class.ilPropertyFormGUI.php";
        $form = new ilPropertyFormGUI();
        $form->setTitle($lng->txt("adn_import_mc_questions"));
        $form->setFormAction($ilCtrl->getFormAction($this, "listFiles"));

        $opts = new ilCheckboxGroupInputGUI($lng->txt("adn_import_include"), "opts");
        $opts->setRequired(true);
        $form->addItem($opts);

        $mc = new ilCheckboxOption($lng->txt("adn_include_mc_questions"), "mc");
        $opts->addOption($mc);

        $case = new ilCheckboxOption($lng->txt("adn_include_case_questions"), "case");
        $opts->addOption($case);

        $obj = new ilCheckboxOption($lng->txt("adn_include_objectives"), "obj");
        $opts->addOption($obj);

        $tgt = new ilCheckboxOption($lng->txt("adn_include_target_numbers"), "tgt");
        $opts->addOption($tgt);

        $goods = new ilCheckboxOption($lng->txt("adn_include_goods"), "goods");
        $opts->addOption($goods);

        $del = new ilCheckboxInputGUI($lng->txt("adn_delete_old_data"), "delall");
        $form->addItem($del);

        $update = new ilCheckboxInputGUI($lng->txt("adn_enable_updates"), "update");
        $form->addItem($update);

        $file = new ilFileInputGUI($lng->txt("file"), "file");
        $file->setRequired(true);
        $file->setSuffixes(array("zip"));
        $form->addItem($file);

        $form->addCommandButton("confirmImport", $lng->txt("update"));
        $form->addCommandButton("listFiles", $lng->txt("cancel"));

        return $form;
    }

    /**
     * Confirm import questions
     */
    protected function confirmImport()
    {
        global $tpl, $lng, $ilCtrl;

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
                    $cgui->setFormAction($ilCtrl->getFormAction($this));
                    $cgui->setHeaderText($lng->txt("adn_sure_import_mc_questions"));
                    $cgui->setCancel($lng->txt("cancel"), "listFiles");
                    $cgui->setConfirm($lng->txt("import"), "saveImport");

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
                                    $lng->txt("adn_mc_import_" . $type . "_valid") . ": " . sizeof($data["valid"])
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
                                    $lng->txt("adn_mc_import_" . $type . "_invalid") . ": " . $invalid
                                );
                            }
                        }
                    }

                    $tpl->setContent($cgui->getHTML());
                    return;
                }
            }

            ilUtil::sendFailure($lng->txt("adn_mc_questions_import_failed"));
        }

        $form->setValuesByPost();
        $this->importFile($form);
    }

    /**
     * Import questions
     */
    protected function saveImport()
    {
        global $tpl, $lng, $ilCtrl;

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
                    ilUtil::sendSuccess($lng->txt("adn_mc_questions_import_success"), true);
                    $ilCtrl->redirect($this, "listFiles");
                }
            }
        }
        ilUtil::sendFailure($lng->txt("adn_mc_questions_import_failed"), true);
        $ilCtrl->redirect($this, "listFiles");
    }

    /**
     * Confirm deletion of export files
     */
    public function confirmDeleteFiles()
    {
        global $ilCtrl, $tpl, $lng, $ilTabs;

        // check whether at least one item has been seleced
        if (!is_array($_POST["file_id"]) || count($_POST["file_id"]) == 0) {
            ilUtil::sendFailure($lng->txt("no_checkbox"), true);
            $ilCtrl->redirect($this, "listFiles");
        } else {
            $ilTabs->setBackTarget(
                $lng->txt("back"),
                $ilCtrl->getLinkTarget($this, "listFiles")
            );

            // display confirmation message
            include_once("./Services/Utilities/classes/class.ilConfirmationGUI.php");
            $cgui = new ilConfirmationGUI();
            $cgui->setFormAction($ilCtrl->getFormAction($this));
            $cgui->setHeaderText($lng->txt("adn_sure_delete_export_files"));
            $cgui->setCancel($lng->txt("cancel"), "listFiles");
            $cgui->setConfirm($lng->txt("delete"), "deleteFiles");

            include_once("./Services/ADN/ED/classes/class.adnQuestionExport.php");

            // list objects that should be deleted
            foreach ($_POST["file_id"] as $i) {
                $cgui->addItem("file_id[]", $i, adnQuestionExport::lookupName($i));
            }

            $tpl->setContent($cgui->getHTML());
        }
    }

    /**
     * Delete files
     */
    protected function deleteFiles()
    {
        global $ilCtrl, $lng;

        if (is_array($_POST["file_id"])) {
            include_once("./Services/ADN/ED/classes/class.adnQuestionExport.php");
            foreach ($_POST["file_id"] as $i) {
                adnQuestionExport::delete($i);
            }
        }
        ilUtil::sendSuccess($lng->txt("adn_export_file_deleted"), true);
        $ilCtrl->redirect($this, "listFiles");
    }

    /**
     * Download export file
     */
    protected function downloadFile()
    {
        global $ilCtrl, $lng;

        include_once("./Services/ADN/ED/classes/class.adnQuestionExport.php");
        $file = adnQuestionExport::getFilePath() . "/" . $_REQUEST["exf_id"];
        if (file_exists($file)) {
            ilUtil::deliverFile($file, adnQuestionExport::lookupName($_REQUEST["exf_id"], true));
        } else {
            ilUtil::sendFailure($lng->txt("adn_file_corrupt"), true);
            $ilCtrl->redirect($this, "listFiles");
        }
    }
}
