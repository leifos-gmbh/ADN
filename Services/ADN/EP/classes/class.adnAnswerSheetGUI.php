<?php
/* Copyright (c) 2010 Leifos, GPL, see docs/LICENSE */

include_once("./Services/ADN/ED/classes/class.adnExaminationQuestionGUI.php");

/**
 * Answer sheet GUI class
 *
 * Answer sheet list, forms and persistence
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.de>
 * @version $Id: class.adnAnswerSheetGUI.php 35407 2012-07-06 08:50:24Z jluetzen $
 *
 * @ilCtrl_Calls adnAnswerSheetGUI:
 *
 * @ingroup ServicesADN
 */
class adnAnswerSheetGUI
{
    // current sheet object
    protected ?adnAnswerSheet $sheet = null;

    protected ilCtrl $ctrl;
    protected ilLanguage $lng;
    protected ilGlobalTemplateInterface $tpl;
    protected ilTabsGUI $tabs;
    protected ilToolbarGUI $toolbar;
    
    /**
     * Constructor
     */
    public function __construct()
    {
        global $DIC;
        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();
        $this->tpl = $DIC->ui()->mainTemplate();
        $this->tabs = $DIC->tabs();
        $this->toolbar = $DIC->toolbar();
        
        // save sheet ID through requests
        $this->ctrl->saveParameter($this, array("sh_id"));
        $this->ctrl->saveParameter($this, array("arc"));

        $this->archived = (bool) $_REQUEST["arc"];
        
        $this->readSheet();
    }
    
    /**
     * Execute command
     */
    public function executeCommand()
    {

        $this->tpl->setTitle($this->lng->txt("adn_ep") . " - " . $this->lng->txt("adn_ep_ass"));
        
        $next_class = $this->ctrl->getNextClass();
        
        // forward command to next gui class in control flow
        switch ($next_class) {
            // no next class:
            // this class is responsible to process the command
            default:
                $cmd = $this->ctrl->getCmd("listEvents");
                
                switch ($cmd) {
                    // commands that need read permission
                    case "listEvents":
                    case "applyFilter":
                    case "resetFilter":
                    case "listSheets":
                    case "listQuestionsForSheet":
                    case "listAssignment":
                        if (adnPerm::check(adnPerm::EP, adnPerm::READ)) {
                            $this->$cmd();
                        }
                        break;
                    
                    // commands that need write permission
                    case "addMCSheet":
                    case "addCaseSheet":
                    case "addQuestionToSheet":
                    case "saveAddQuestions":
                    case "removeQuestionsFromSheet":
                    case "confirmSheetsDeletion":
                    case "deleteSheets":
                    case "saveSheetAssignment":
                    case "saveCaseGasSheet":
                    case "showCaseChemGoodsForm":
                    case "saveCaseChemSheet":
                    case 'generateSheets':
                    case 'downloadExaminationDocuments':
                        if (adnPerm::check(adnPerm::EP, adnPerm::WRITE)) {
                            $this->$cmd();
                        }
                        break;
                    
                }
                break;
        }
    }
    
    /**
     * Read sheet
     */
    protected function readSheet()
    {
        if ((int) $_GET["sh_id"] > 0) {
            include_once("./Services/ADN/EP/classes/class.adnAnswerSheet.php");
            $this->sheet = new adnAnswerSheet((int) $_GET["sh_id"]);
        }
    }
    
    /**
     * List all examination events (has to be selected first)
     */
    protected function listEvents()
    {

        $this->setEventTabs();

        // table of examination events
        include_once("./Services/ADN/ES/classes/class.adnExaminationEventTableGUI.php");
        $table = new adnExaminationEventTableGUI(
            $this,
            "listEvents",
            adnExaminationEventTableGUI::MODE_SHEET,
            $this->archived
        );
        
        // output table
        $this->tpl->setContent($table->getHTML());
    }

    /**
     * Apply filter settings (from table gui)
     */
    protected function applyFilter()
    {
        include_once("./Services/ADN/ES/classes/class.adnExaminationEventTableGUI.php");
        $table = new adnExaminationEventTableGUI(
            $this,
            "listEvents",
            adnExaminationEventTableGUI::MODE_SHEET,
            $this->archived
        );
        $table->resetOffset();
        $table->writeFilterToSession();

        $this->listEvents();
    }

    /**
     * Reset filter settings (from table gui)
     */
    protected function resetFilter()
    {
        include_once("./Services/ADN/ES/classes/class.adnExaminationEventTableGUI.php");
        $table = new adnExaminationEventTableGUI(
            $this,
            "listEvents",
            adnExaminationEventTableGUI::MODE_SHEET,
            $this->archived
        );
        $table->resetOffset();
        $table->resetFilter();

        $this->listEvents();
    }

    /**
     * Set event tabs
     */
    public function setEventTabs()
    {

        $this->ctrl->setParameter($this, "arc", "");

        $this->tabs->addTab(
            "current",
            $this->lng->txt("adn_current_examination_events"),
            $this->ctrl->getLinkTarget($this, "listEvents")
        );

        $this->ctrl->setParameter($this, "arc", "1");

        $this->tabs->addTab(
            "archived",
            $this->lng->txt("adn_archived_examination_events"),
            $this->ctrl->getLinkTarget($this, "listEvents")
        );

        $this->ctrl->setParameter($this, "arc", $this->archived);

        if ($this->archived) {
            $this->tabs->activateTab("archived");
        } else {
            $this->tabs->activateTab("current");
        }
    }

    /**
     * List answer sheets for event
     */
    protected function listSheets()
    {

        $this->tabs->setBackTarget($this->lng->txt("back"), $this->ctrl->getLinkTarget($this, "listEvents"));

        $event_id = (int) $_REQUEST["ev_id"];
        if (!$event_id) {
            return;
        }

        $this->ctrl->setParameter($this, "ev_id", $event_id);

        $this->tabs->addTab(
            "sht",
            $this->lng->txt("adn_answer_sheets"),
            $this->ctrl->getLinkTarget($this, "listSheets")
        );
        $this->tabs->addTab(
            "ass",
            $this->lng->txt("adn_assignment_generating"),
            $this->ctrl->getLinkTarget($this, "listAssignment")
        );

        $this->tabs->setTabActive("sht");

        // creation buttons (depend on archival status)
        if (!$this->archived && adnPerm::check(adnPerm::EP, adnPerm::WRITE)) {
            $this->toolbar->addButton(
                $this->lng->txt("adn_add_mc_answer_sheet"),
                $this->ctrl->getLinkTarget($this, "addMCSheet")
            );

            include_once "Services/ADN/EP/classes/class.adnExaminationEvent.php";
            include_once "Services/ADN/ED/classes/class.adnSubjectArea.php";
            $event = new adnExaminationEvent($event_id);
            if (adnSubjectArea::hasCasePart($event->getType())) {
                $this->toolbar->addButton(
                    $this->lng->txt("adn_add_case_answer_sheet"),
                    $this->ctrl->getLinkTarget($this, "addCaseSheet")
                );
            }
        }

        // table of examination events
        include_once("./Services/ADN/EP/classes/class.adnAnswerSheetTableGUI.php");
        $table = new adnAnswerSheetTableGUI($this, "listSheets", $event_id, $this->archived);

        // output table
        $this->tpl->setContent($table->getHTML());
    }

    /**
     * Generate MC Sheet
     *
     * As the questions are picked randomly, this method will create the complete sheet automatically
     */
    protected function addMCSheet()
    {
        
        $event_id = (int) $_GET["ev_id"];
        if (!$event_id) {
            return;
        }

        include_once "Services/ADN/EP/classes/class.adnExaminationEvent.php";
        $event = new adnExaminationEvent($event_id);
        include_once "Services/ADN/ED/classes/class.adnQuestionTargetNumbers.php";
        $sheet_questions = adnQuestionTargetNumbers::generateMCSheet($event->getType());

        // create sheet
        include_once "Services/ADN/EP/classes/class.adnAnswerSheet.php";
        $sheet = new adnAnswerSheet();
        $sheet->setEvent($event_id);
        $sheet->setType(adnAnswerSheet::TYPE_MC);
        $sheet->setGeneratedOn(new ilDate(time(), IL_CAL_UNIX));
        $sheet->setQuestions($sheet_questions);
        $sheet->save();

        ilUtil::sendSuccess($this->lng->txt("adn_answer_sheet_created"), true);
        $this->ctrl->setParameter($this, "sh_id", $sheet->getId());
        $this->ctrl->redirect($this, "listQuestionsForSheet");
    }

    /**
     * Add Case Sheet (MC/case switch)
     */
    protected function addCaseSheet()
    {

        $event_id = (int) $_GET["ev_id"];
        if (!$event_id) {
            return;
        }

        $this->ctrl->setParameter($this, "ev_id", $event_id);

        $this->tabs->setBackTarget(
            $this->lng->txt("back"),
            $this->ctrl->getLinkTarget($this, "listSheets")
        );

        include_once "Services/ADN/EP/classes/class.adnExaminationEvent.php";
        include_once "Services/ADN/ED/classes/class.adnSubjectArea.php";
        $event = new adnExaminationEvent($event_id);
        if ($event->getType() == adnSubjectArea::GAS) {
            $this->showCaseGasForm($event_id);
        } else {
            $this->showCaseChemLicenseForm($event);
        }
    }

    /**
     * 1st step case sheet gas (select case type and good in transit)
     *
     * @param int $a_event_id
     * @param ilPropertyFormGUI $a_form
     */
    protected function showCaseGasForm($a_event_id, ilPropertyFormGUI $a_form = null)
    {

        if (!$a_form) {
            $a_form = $this->initCaseGasForm($a_event_id);
        }

        $this->tpl->setContent($a_form->getHTML());
    }

    /**
     * Build gas case sheet form
     *
     * @param int $a_event_id
     * @return ilPropertyFormGUI
     */
    protected function initCaseGasForm($a_event_id)
    {
        
        include_once("Services/Form/classes/class.ilPropertyFormGUI.php");
        $form = new ilPropertyFormGUI();
        $form->setTitle($this->lng->txt("adn_add_case_answer_sheet"));
        $form->setFormAction($this->ctrl->getFormAction($this));

        $event = new ilHiddenInputGUI("ev_id");
        $event->setValue($a_event_id);
        $form->addItem($event);

        $case = new ilSelectInputGUI($this->lng->txt("adn_case"), "case");
        $case->setRequired(true);
        $case->setOptions(array(0 => $this->lng->txt("adn_empty"),
            1 => $this->lng->txt("adn_butan")));
        $form->addItem($case);

        include_once "Services/ADN/ED/classes/class.adnGoodInTransit.php";
        $good = new ilRadioGroupInputGUI($this->lng->txt("adn_good_in_transit_select"), "good");
        $good->setRequired(true);
        foreach (adnGoodInTransit::getGoodsSelect(adnGoodInTransit::TYPE_GAS) as
            $good_id => $good_caption) {
            $good->addOption(new ilRadioOption($good_caption, $good_id));
        }
        $form->addItem($good);

        $form->addCommandButton("saveCaseGasSheet", $this->lng->txt("save"));
        $form->addCommandButton("listSheets", $this->lng->txt("cancel"));

        return $form;
    }

    /**
     * Create gas case sheet
     *
     * This will just add an empty sheet, questions have to be assigned manually
     */
    protected function saveCaseGasSheet()
    {

        $event_id = (int) $_REQUEST["ev_id"];
        if (!$event_id) {
            return;
        }
        
        $form = $this->initCaseGasForm($event_id);
        if ($form->checkInput()) {
            include_once "Services/ADN/EP/classes/class.adnAnswerSheet.php";
            $sheet = new adnAnswerSheet();
            $sheet->setEvent($event_id);
            $sheet->setType(adnAnswerSheet::TYPE_CASE);
            $sheet->setNewGood($form->getInput("good"));
            $sheet->setButan($form->getInput("case"));
            $sheet->setGeneratedOn(new ilDate(time(), IL_CAL_UNIX));
            $sheet->save();

            ilUtil::sendSuccess($this->lng->txt("adn_answer_sheet_created"), true);
            $this->ctrl->setParameter($this, "sh_id", $sheet->getId());
            $this->ctrl->redirect($this, "listQuestionsForSheet");
        }

        $form->setValuesByPost();
        $this->showCaseGasForm($event_id, $form);
    }

    /**
     * 1st step add chem case sheet (select license)
     *
     * @param adnExaminationEvent $a_event
     * @param bool $a_invalid
     */
    protected function showCaseChemLicenseForm(adnExaminationEvent $a_event, $a_invalid = false)
    {

        include_once("Services/Form/classes/class.ilPropertyFormGUI.php");
        $form = new ilPropertyFormGUI();
        $form->setTitle($this->lng->txt("adn_add_case_answer_sheet"));
        $form->setFormAction($this->ctrl->getFormAction($this));

        $event = new ilHiddenInputGUI("ev_id");
        $event->setValue($a_event->getId());
        $form->addItem($event);

        include_once "Services/ADN/ED/classes/class.adnLicense.php";
        include_once "Services/ADN/ED/classes/class.adnSubjectArea.php";
        $lic = new ilRadioGroupInputGUI($this->lng->txt("adn_license"), "license");
        $lic->setRequired(true);
        foreach (adnLicense::getLicensesSelect(adnLicense::TYPE_CHEMICALS) as $lic_id => $lic_caption) {
            $lic->addOption(new ilRadioOption($lic_caption, $lic_id));
        }
        $form->addItem($lic);

        if ($a_invalid) {
            ilUtil::sendFailure($this->lng->txt("form_input_not_valid"));
            $lic->setAlert($this->lng->txt("msg_input_is_required"));
        }

        $form->addCommandButton("showCaseChemGoodsForm", $this->lng->txt("btn_next"));
        $form->addCommandButton("listSheets", $this->lng->txt("cancel"));

        $this->tpl->setContent($form->getHTML());
    }

    /**
     * 2nd step add chem case sheet (select matching goods for selected license)
     *
     * @param bool $a_invalid
     */
    protected function showCaseChemGoodsForm($a_invalid = false)
    {

        $event_id = (int) $_REQUEST["ev_id"];
        if (!$event_id) {
            return;
        }

        $this->ctrl->setParameter($this, "ev_id", $event_id);
        $this->tabs->setBackTarget(
            $this->lng->txt("back"),
            $this->ctrl->getLinkTarget($this, "listSheets")
        );

        include_once "Services/ADN/EP/classes/class.adnExaminationEvent.php";
        $event = new adnExaminationEvent($event_id);

        $license_id = (int) $_REQUEST["license"];
        if (!$license_id) {
            return $this->showCaseChemLicenseForm($event, true);
        }

        
        include_once("Services/Form/classes/class.ilPropertyFormGUI.php");
        $form = new ilPropertyFormGUI();
        $form->setTitle($this->lng->txt("adn_add_case_answer_sheet"));
        $form->setFormAction($this->ctrl->getFormAction($this));

        $evt = new ilHiddenInputGUI("ev_id");
        $evt->setValue($event->getId());
        $form->addItem($evt);

        $lic = new ilHiddenInputGUI("lic_id");
        $lic->setValue($license_id);
        $form->addItem($lic);

        include_once "Services/ADN/ED/classes/class.adnLicense.php";
        $license = new adnLicense($license_id);
        $good_ids = $license->getGoods();
        $goods = array();
        if ($good_ids) {
            include_once "Services/ADN/ED/classes/class.adnGoodInTransit.php";
            $all_goods = adnGoodInTransit::getGoodsSelect();
            foreach ($good_ids as $good_id) {
                // do not include archived goods
                if (array_key_exists($good_id, $all_goods)) {
                    $goods[$good_id] = $all_goods[$good_id];
                }
            }
        }

        $prev_good = new ilSelectInputGUI($this->lng->txt("adn_good_in_transit_previous"), "prev_good");
        $prev_good->setRequired(true);
        $prev_good->setOptions(array("" => $this->lng->txt("adn_no_previous_good")) + $goods);
        $form->addItem($prev_good);

        $new_good = new ilSelectInputGUI($this->lng->txt("adn_good_in_transit_select"), "new_good");
        $new_good->setRequired(true);
        $new_good->setOptions($goods);
        if (!sizeof($goods)) {
            $new_good->addCustomAttribute('style="width:100px"');
        }
        $form->addItem($new_good);

        if ($a_invalid) {
            ilUtil::sendFailure($this->lng->txt("form_input_not_valid"));
            $new_good->setAlert($this->lng->txt("msg_input_is_required"));
        }

        $form->addCommandButton("saveCaseChemSheet", $this->lng->txt("save"));
        $form->addCommandButton("listSheets", $this->lng->txt("cancel"));

        $this->tpl->setContent($form->getHTML());
    }

    /**
     * Create chem case sheet
     *
     * This will just add an empty sheet, questions have to be assigned manually
     */
    protected function saveCaseChemSheet()
    {

        $event_id = (int) $_REQUEST["ev_id"];
        $license_id = (int) $_REQUEST["lic_id"];
        if (!$event_id || !$license_id) {
            return;
        }

        $prev_good = (int) $_REQUEST["prev_good"];
        $new_good = (int) $_REQUEST["new_good"];
        if (!$new_good) {
            return $this->showCaseChemGoodsForm(true);
        }

        include_once "Services/ADN/EP/classes/class.adnAnswerSheet.php";
        $sheet = new adnAnswerSheet();
        $sheet->setEvent($event_id);
        $sheet->setType(adnAnswerSheet::TYPE_CASE);
        $sheet->setPreviousGood($prev_good);
        $sheet->setNewGood($new_good);
        $sheet->setLicense($license_id);
        $sheet->setGeneratedOn(new ilDate(time(), IL_CAL_UNIX));
        $sheet->save();

        ilUtil::sendSuccess($this->lng->txt("adn_answer_sheet_created"), true);
        $this->ctrl->setParameter($this, "sh_id", $sheet->getId());
        $this->ctrl->redirect($this, "listQuestionsForSheet");
    }

    /**
     * Build sheet title with all relevant attributes
     *
     * Attributes:
     * - sheet number
     * - event name
     * - case sheet: good, license, chemicals or gas/butan or gas/empty, case
     *
     * @return string
     */
    public function getFullSheetTitle()
    {
        
        include_once "Services/ADN/EP/classes/class.adnExaminationEvent.php";
        $title = $this->lng->txt("adn_answer_sheet") . " " .
            $this->sheet->getNumber() . ": " . adnExaminationEvent::lookupName($this->sheet->getEvent());

        $description = "";
        if ($this->sheet->getType() == adnAnswerSheet::TYPE_CASE) {
            $attributes = array();

            include_once "Services/ADN/ED/classes/class.adnGoodInTransit.php";
            $attributes[] = $this->lng->txt("adn_good_in_transit_select") . ": " .
                adnGoodInTransit::lookupName($this->sheet->getNewGood());

            include_once "Services/ADN/ED/classes/class.adnLicense.php";
            $license_id = $this->sheet->getLicense();
            if (!$license_id) {
                $license_name = array_pop(adnLicense::getAllLicenses(adnLicense::TYPE_GAS));
                $license_name = $license_name["name"];
            } else {
                $license_name = adnLicense::lookupName($license_id);
            }
            $attributes[] = $this->lng->txt("adn_license") . ": " . $license_name;

            include_once "Services/ADN/ED/classes/class.adnSubjectArea.php";
            include_once "Services/ADN/ED/classes/class.adnCase.php";
            $event = new adnExaminationEvent($this->sheet->getEvent());
            if ($event->getType() == adnSubjectArea::CHEMICAL) {
                $case = $this->lng->txt("adn_case_chem");
            } elseif ($this->sheet->getButan()) {
                $case = $this->lng->txt("adn_case_gas_butan");
            } else {
                $case = $this->lng->txt("adn_case_gas_empty");
            }
            $attributes[] = $this->lng->txt("adn_case") . ": " . $case .
                " (<a href=\"#\" onClick=\"JavaScript:document.getElementById('adn_case_text')." .
                "style.display = 'block';\">" . $this->lng->txt("show") . "<a>)";

            $description = implode(", ", $attributes);

            // add case text
            $case = adnCase::getIdByArea($event->getType(), $this->sheet->getButan());
            $case = new adnCase($case);
            $description .= "<div id=\"adn_case_text\" style=\"margin:10px; font-style:normal; " .
                "display:none;\">" . nl2br($case->getTranslatedText($this->sheet, $event)) . "</div>";
        }

        return array("title" => $title, "description" => $description);
    }

    /**
     * List all questions for sheet
     *
     * The question order depends on the (sub-)objective
     * This is a custom method and does not use any tree- or table-gui helper class
     */
    protected function listQuestionsForSheet()
    {

        if (!$this->sheet) {
            return;
        }

        $this->ctrl->setParameter($this, "ev_id", $this->sheet->getEvent());
        
        $this->tabs->setBackTarget($this->lng->txt("back"), $this->ctrl->getLinkTarget($this, "listSheets"));

        $mytpl = new ilTemplate("tpl.questions_group.html", true, true, "Services/ADN/EP");

        $title = $this->getFullSheetTitle();
        $mytpl->setVariable("VAL_TITLE", $title["title"]);
        if ($title["description"]) {
            $mytpl->setCurrentBlock("subtitle");
            $mytpl->setVariable("VAL_SUBTITLE", $title["description"]);
            $mytpl->parseCurrentBlock();
        }

        $read_only = (!adnPerm::check(adnPerm::EP, adnPerm::WRITE) || $this->archived);

        // top/bottom buttons
        if (!$read_only) {
            $mytpl->setCurrentBlock("remove1");
            $mytpl->setVariable("VAL_REMOVE_QUESTIONS", $this->lng->txt("adn_remove_questions"));
            $mytpl->parseCurrentBlock();
            
            $mytpl->setCurrentBlock("remove2");
            $mytpl->setVariable("VAL_REMOVE_QUESTIONS", $this->lng->txt("adn_remove_questions"));
            $mytpl->parseCurrentBlock();
        }

        $mytpl->setVariable("FORM_ACTION", $this->ctrl->getFormAction($this));
        
        $sheet_data = $this->sheet->getQuestionsInObjectiveOrder();
        $rows = $invalid_questions = 0;
        foreach ($sheet_data["objectives"] as $objective_number => $objective) {
            $id = $objective["id"];

            // mark invalid items
            if (!$objective["valid"]) {
                $objective["title"] = "<span style=\"color:red;\">" . $objective["title"] . "</span>";
            }
            
            $mytpl->setCurrentBlock("objective");
            $mytpl->setVariable("VAL_OBJECTIVE_ID", $id);
            $mytpl->setVariable("VAL_OBJECTIVE_NUMBER", $objective_number);
            $mytpl->setVariable("VAL_OBJECTIVE", $objective["title"]);

            // add questions link
            if (!$read_only && $objective["addable"]) {
                $mytpl->setCurrentBlock("objective_add");
                $this->ctrl->setParameter($this, "obj_id", $id);
                $mytpl->setVariable("VAL_OBJECTIVE_URL", $this->ctrl->getLinkTarget(
                    $this,
                    "addQuestionToSheet"
                ));
                $this->ctrl->setParameter($this, "obj_id", "");
                $mytpl->setVariable("VAL_OBJECTIVE_ACTION", $this->lng->txt("adn_add_questions"));
                $mytpl->parseCurrentBlock();
            }
            
            // get questions of objective
            $oquestions = array();
            foreach ($sheet_data["questions"] as $question_number => $question) {
                $question_id = $question["id"];
                
                if (isset($question["objective_id"]) && $question["objective_id"] ==
                    $objective_number) {
                    $oquestions[$question_number] = $question;
                }
            }

            $mytpl->parseCurrentBlock();

            $rows++;
            $mytpl->setCurrentBlock("row");
            $mytpl->setVariable("CSS_ROW", ($rows % 2) ? "tblrow1" : "tblrow2");
            $mytpl->parseCurrentBlock();

            // render questions
            if (sizeof($oquestions)) {
                ksort($oquestions);
                foreach ($oquestions as $question_number => $question) {
                    $question_id = $question["id"];
                    if (!$read_only) {
                        $mytpl->setCurrentBlock("cbox");
                        $mytpl->setVariable("VAL_QUESTION_ID", $question_id);
                        $mytpl->parseCurrentBlock();
                    }

                    // mark invalid items
                    if (!$question["valid"]) {
                        $invalid_questions++;
                        $question["text"] =
                            "<span style=\"color:red; text-decoration:line-through;\">" .
                            $question["text"] . "</span>";
                    }

                    $mytpl->setCurrentBlock("question");
                    $mytpl->setVariable("VAL_QUESTION_NUMBER", $question_number);
                    $mytpl->setVariable(
                        "VAL_QUESTION",
                        adnExaminationQuestionGUI::replaceBBCode($question["text"])
                    );
                    $mytpl->setVariable("VAL_QUESTION_MARGIN", 25);
                    $mytpl->parseCurrentBlock();
                    
                    $rows++;
                    $mytpl->setCurrentBlock("row");
                    $mytpl->setVariable("CSS_ROW", ($rows % 2) ? "tblrow1" : "tblrow2");
                    $mytpl->parseCurrentBlock();
                }
            }

            foreach ($sheet_data["subobjectives"] as $subobjective_number => $subobjective) {
                $subobjective_id = $subobjective["id"];

                // matching subobjectives for parent objective
                if ($subobjective["objective_id"] == $objective_number) {
                    // mark invalid items
                    if (!$subobjective["valid"]) {
                        $subobjective["title"] = "<span style=\"color:red;\">" .
                            $subobjective["title"] .
                            "</span>";
                    }

                    $mytpl->setCurrentBlock("subobjective");
                    $mytpl->setVariable("VAL_SUBOBJECTIVE_ID", $subobjective_id);
                    $mytpl->setVariable("VAL_SUBOBJECTIVE_NUMBER", $subobjective_number);
                    $mytpl->setVariable("VAL_SUBOBJECTIVE", $subobjective["title"]);

                    // add questions link
                    if (!$read_only && $subobjective["addable"]) {
                        $mytpl->setCurrentBlock("subobjective_add");
                        $this->ctrl->setParameter($this, "sobj_id", $subobjective_id);
                        $mytpl->setVariable(
                            "VAL_SUBOBJECTIVE_URL",
                            $this->ctrl->getLinkTarget($this, "addQuestionToSheet")
                        );
                        $this->ctrl->setParameter($this, "sobj_id", "");
                        $mytpl->setVariable(
                            "VAL_SUBOBJECTIVE_ACTION",
                            $this->lng->txt("adn_add_questions")
                        );
                        $mytpl->parseCurrentBlock();
                    }

                    // get questions of subobjective
                    $soquestions = array();
                    foreach ($sheet_data["questions"] as $question_number => $question) {
                        $question_id = $question["id"];

                        if (isset($question["subobjective_id"]) &&
                            $question["subobjective_id"] == $subobjective_number) {
                            $soquestions[$question_number] = $question;
                        }
                    }

                    $mytpl->parseCurrentBlock();

                    $rows++;
                    $mytpl->setCurrentBlock("row");
                    $mytpl->setVariable("CSS_ROW", ($rows % 2) ? "tblrow1" : "tblrow2");
                    $mytpl->parseCurrentBlock();

                    // render questions
                    if (sizeof($soquestions)) {
                        ksort($soquestions);
                        foreach ($soquestions as $question_number => $question) {
                            $question_id = $question["id"];

                            if (!$read_only) {
                                $mytpl->setCurrentBlock("cbox");
                                $mytpl->setVariable("VAL_QUESTION_ID", $question_id);
                                $mytpl->parseCurrentBlock();
                            }

                            // mark invalid items
                            if (!$question["valid"]) {
                                $invalid_questions++;
                                $question["text"] =
                                    "<span style=\"color:red; text-decoration:line-through;\">" .
                                    $question["text"] . "</span>";
                            }
                            
                            $mytpl->setCurrentBlock("question");
                            $mytpl->setVariable("VAL_QUESTION_NUMBER", $question_number);
                            $mytpl->setVariable("VAL_QUESTION", $question["text"]);
                            $mytpl->setVariable("VAL_QUESTION_MARGIN", 50);
                            $mytpl->parseCurrentBlock();
                        
                            $rows++;
                            $mytpl->setCurrentBlock("row");
                            $mytpl->setVariable("CSS_ROW", ($rows % 2) ? "tblrow1" : "tblrow2");
                            $mytpl->parseCurrentBlock();
                        }
                    }
                }
            }
        }

        // overall info
        $info = sprintf(
            $this->lng->txt("adn_sheet_question_status"),
            sizeof($sheet_data["questions"]),
            $sheet_data["target"]
        );

        // status info (invalid / too many / too few questions)

        if ($invalid_questions) {
            $info .= ", " . sprintf(
                $this->lng->txt("adn_sheet_question_status_invalid"),
                $invalid_questions
            );
        }
        
        if ($sheet_data["target"] == sizeof($sheet_data["questions"])) {
            if (!$invalid_questions) {
                ilUtil::sendInfo($info);
            } else {
                ilUtil::sendFailure($info);
            }
        } elseif ($sheet_data["target"] < sizeof($sheet_data["questions"])) {
            ilUtil::sendFailure($info . " - " . $this->lng->txt("adn_sheet_too_many"));
        } else {
            ilUtil::sendFailure($info . " - " . $this->lng->txt("adn_sheet_too_few"));
        }

        $this->tpl->setContent($mytpl->get());
    }

    /**
     * Remove question(s) from answer sheet (no confirmation needed, we just remove assignment)
     */
    protected function removeQuestionsFromSheet()
    {

        if (!$this->sheet) {
            return;
        }

        // check whether at least one item has been seleced
        if (!is_array($_POST["question_id"]) || count($_POST["question_id"]) == 0) {
            ilUtil::sendFailure($this->lng->txt("no_checkbox"), true);
            $this->ctrl->redirect($this, "listQuestionsForSheet");
        } else {
            $questions = $this->sheet->getQuestions();
            foreach ($questions as $idx => $question_id) {
                if (in_array($question_id, $_POST["question_id"])) {
                    unset($questions[$idx]);
                }
            }
            $this->sheet->setQuestions($questions);
            $this->sheet->update();
            
            ilUtil::sendSuccess($this->lng->txt("adn_sheet_questions_removed"), true);
            $this->ctrl->redirect($this, "listQuestionsForSheet");
        }
    }

    /**
     * Add question(s) to answer sheet GUI (list available questions)
     */
    protected function addQuestionToSheet()
    {

        if (!$this->sheet) {
            return;
        }

        $this->ctrl->setParameter($this, "obj_id", $_REQUEST["obj_id"]);
        $this->ctrl->setParameter($this, "sobj_id", $_REQUEST["sobj_id"]);

        $this->tabs->setBackTarget($this->lng->txt("back"), $this->ctrl->getLinkTarget(
            $this,
            "listQuestionsForSheet"
        ));

        // table of questions
        include_once("./Services/ADN/EP/classes/class.adnAnswerSheetQuestionTableGUI.php");
        $table = new adnAnswerSheetQuestionTableGUI(
            $this,
            "addQuestionToSheet",
            $this->sheet,
            $_REQUEST["obj_id"],
            $_REQUEST["sobj_id"]
        );

        // output table
        $this->tpl->setContent($table->getHTML());
    }

    /**
     * Add question(s) to answer sheet
     */
    protected function saveAddQuestions()
    {

        if (!$this->sheet) {
            return;
        }

        // check whether at least one item has been seleced
        if (!is_array($_POST["question_id"]) || count($_POST["question_id"]) == 0) {
            $this->ctrl->setParameter($this, "obj_id", $_REQUEST["obj_id"]);
            $this->ctrl->setParameter($this, "sobj_id", $_REQUEST["sobj_id"]);

            ilUtil::sendFailure($this->lng->txt("no_checkbox"), true);
            $this->ctrl->redirect($this, "addQuestionToSheet");
        } else {
            // check for sheet subjected mode (in this case target has to be selected)
            if ($_REQUEST["sobj_id"]) {
                include_once "Services/ADN/ED/classes/class.adnSubobjective.php";
                $sobj = new adnSubobjective($_REQUEST["sobj_id"]);
                $_REQUEST["obj_id"] = $sobj->getObjective();
            }
            include_once "Services/ADN/ED/classes/class.adnObjective.php";
            $obj = new adnObjective($_REQUEST["obj_id"]);
            if ($obj->isSheetSubjected()) {
                // show subjected objective selection screen
                if (!$_REQUEST["subj_map"]) {
                    return $this->showSheetSubjectedList($_POST["question_id"]);
                }
                // save selection
                else {
                    $map = $this->sheet->getQuestionMap();
                    foreach ($_REQUEST["subj_map"] as $question_id => $obj_id) {
                        $map[$question_id] = $obj_id;
                    }
                    $this->sheet->setQuestionMap($map);
                }
            }

            $questions = $this->sheet->getQuestions();
            foreach ($_POST["question_id"] as $question_id) {
                $questions[] = $question_id;
            }
            $this->sheet->setQuestions(array_unique($questions));
            $this->sheet->update();

            ilUtil::sendSuccess($this->lng->txt("adn_sheet_questions_added"), true);
            $this->ctrl->redirect($this, "listQuestionsForSheet");
        }
    }

    /**
     * Confirm sheets deletion
     */
    protected function confirmSheetsDeletion()
    {

        $event_id = (int) $_GET["ev_id"];
        if (!$event_id) {
            return;
        }

        $this->ctrl->setParameter($this, "ev_id", $event_id);

        // check whether at least one item has been seleced
        if (!is_array($_POST["sheet_id"]) || count($_POST["sheet_id"]) == 0) {
            ilUtil::sendFailure($this->lng->txt("no_checkbox"), true);
            $this->ctrl->redirect($this, "listSheets");
        } else {
            $this->tabs->setBackTarget(
                $this->lng->txt("back"),
                $this->ctrl->getLinkTarget($this, "listSheets")
            );
            
            // display confirmation message
            include_once("./Services/Utilities/classes/class.ilConfirmationGUI.php");
            $cgui = new ilConfirmationGUI();
            $cgui->setFormAction($this->ctrl->getFormAction($this));
            $cgui->setHeaderText($this->lng->txt("adn_sure_delete_answer_sheets"));
            $cgui->setCancel($this->lng->txt("cancel"), "listSheets");
            $cgui->setConfirm($this->lng->txt("delete"), "deleteSheets");

            // list objects that should be deleted
            include_once("./Services/ADN/EP/classes/class.adnAnswerSheet.php");
            foreach ($_POST["sheet_id"] as $i) {
                $cgui->addItem("sheet_id[]", $i, adnAnswerSheet::lookupName($i));
            }

            $this->tpl->setContent($cgui->getHTML());
        }
    }

    /**
     * Delete sheets
     */
    protected function deleteSheets()
    {

        $event_id = (int) $_REQUEST["ev_id"];
        if (!$event_id) {
            return;
        }

        $this->ctrl->setParameter($this, "ev_id", $event_id);

        include_once("./Services/ADN/EP/classes/class.adnAnswerSheet.php");

        if (is_array($_POST["sheet_id"])) {
            foreach ($_POST["sheet_id"] as $i) {
                $sheet = new adnAnswerSheet($i);
                $sheet->delete();
            }
        }
        ilUtil::sendSuccess($this->lng->txt("adn_answer_sheet_deleted"), true);
        $this->ctrl->redirect($this, "listSheets");
    }

    /**
     * List all assigned candidates to event
     */
    protected function listAssignment()
    {

        $this->tabs->setBackTarget($this->lng->txt("back"), $this->ctrl->getLinkTarget($this, "listEvents"));

        $event_id = (int) $_GET["ev_id"];
        if (!$event_id) {
            return;
        }

        $this->ctrl->setParameter($this, "ev_id", $event_id);

        $this->tabs->addTab(
            "sht",
            $this->lng->txt("adn_answer_sheets"),
            $this->ctrl->getLinkTarget($this, "listSheets")
        );
        $this->tabs->addTab(
            "ass",
            $this->lng->txt("adn_assignment_generating"),
            $this->ctrl->getLinkTarget($this, "listAssignment")
        );

        $this->tabs->setTabActive("ass");

        // table of questions
        include_once("./Services/ADN/EP/classes/class.adnAnswerSheetAssignmentTableGUI.php");
        $table = new adnAnswerSheetAssignmentTableGUI(
            $this,
            "listAssignment",
            $event_id,
            $this->archived
        );

        // output table
        $this->tpl->setContent($table->getHTML());
    }

    /**
     * Save candidates sheets assignment
     */
    protected function saveSheetAssignment($a_redirect = true)
    {

        $event_id = (int) $_GET["ev_id"];
        if (!$event_id) {
            return;
        }

        $this->ctrl->setParameter($this, "ev_id", $event_id);

        include_once "Services/ADN/EP/classes/class.adnAnswerSheetAssignment.php";
        include_once "Services/ADN/EC/classes/class.adnTest.php";
        $delete_failed = false;
        if (isset($_REQUEST['cnd']) && !empty($_REQUEST['cnd'])) {
            foreach ($_REQUEST["cnd"] as $candidate_id => $sheets) {
                $no_update = false;

                // check if candidate has given answers for assigned sheets
                $old = adnAnswerSheetAssignment::getSheetsSelect($candidate_id, $event_id);
                if (sizeof($old)) {
                    foreach ($old as $id => $sheet_id) {
                        // Delete deprecated reports
                        if (!in_array($sheet_id, (array) $_REQUEST['cnd'][$candidate_id])) {
                            include_once './Services/ADN/Report/classes/class.adnReportAnswerSheet.php';
                            adnReportAnswerSheet::deleteSheet($candidate_id, $sheet_id);
                        }

                        $assignment = new adnAnswerSheetAssignment($id);
                        if (adnTest::hasAnswered($id)) {
                            // when sheet is not selected anymore and existing answers: cancel
                            if (!in_array($assignment->getSheet(), $sheets)) {
                                $delete_failed = true;
                                $no_update = true;
                            }
                        }
                    }
                }

                if (!$no_update) {
                    // add new
                    foreach ($sheets as $sheet_id) {
                        if ($sheet_id) {
                            $assignment = new adnAnswerSheetAssignment(null, $candidate_id, $sheet_id);
                            if (!$assignment->getId()) {
                                $assignment->save();
                            } else {
                                unset($old[$assignment->getId()]);
                            }
                        }
                    }

                    // remove unused
                    if (sizeof($old)) {
                        foreach ($old as $id => $sheet_id) {
                            $assignment = new adnAnswerSheetAssignment($id);
                            $assignment->delete();
                        }
                    }
                }
            }
        } else {
            ilUtil::sendFailure($this->lng->txt("adn_assignment_save_fail_answered"), true);
            $this->ctrl->redirect($this, "listAssignment");
        }

        if ($delete_failed) {
            ilUtil::sendFailure($this->lng->txt("adn_assignment_delete_fail_answered"), true);
        }
        
        if ($a_redirect) {
            ilUtil::sendSuccess($this->lng->txt("adn_sheet_assignment_saved"), true);
            $this->ctrl->redirect($this, "listAssignment");
        }
        return true;
    }
    
    /**
     * Assign questions to objectives (subjected objective, e.g. E)
     *
     * @param array $a_question_ids
     */
    protected function showSheetSubjectedList(array $a_question_ids)
    {
        
        if (!$this->sheet) {
            return;
        }

        $this->ctrl->setParameter($this, "obj_id", $_REQUEST["obj_id"]);
        $this->ctrl->setParameter($this, "sobj_id", $_REQUEST["sobj_id"]);

        $this->tabs->setBackTarget($this->lng->txt("back"), $this->ctrl->getLinkTarget(
            $this,
            "addQuestionToSheet"
        ));

        // table of questions
        include_once("./Services/ADN/EP/classes/class.adnAnswerSheetQuestionTableGUI.php");
        $table = new adnAnswerSheetQuestionTableGUI(
            $this,
            "addQuestionToSheet",
            $this->sheet,
            $_REQUEST["obj_id"],
            $_REQUEST["sobj_id"],
            $a_question_ids
        );

        // output table
        $this->tpl->setContent($table->getHTML());
    }
    
    /**
     * Generate question sheet
     * @return void
     */
    protected function generateSheets()
    {

        $this->ctrl->saveParameter($this, 'ev_id');
        
        $this->saveSheetAssignment(false);
        
        // create report
        include_once './Services/ADN/Report/exceptions/class.adnReportException.php';
        try {
            include_once './Services/ADN/EP/classes/class.adnExaminationEvent.php';
            include_once("./Services/ADN/Report/classes/class.adnReportAnswerSheet.php");
            $report = new adnReportAnswerSheet(new adnExaminationEvent((int) $_REQUEST['ev_id']));
            $report->create();
        
            ilUtil::sendSuccess('Prüfbögen generiert', true);
            $this->ctrl->redirect($this, 'listAssignment');
            return true;
        } catch (adnReportException $e) {
            ilUtil::sendFailure($e->getMessage(), true);
            $this->ctrl->redirect($this, 'listAssignment');
        }
    }
    
    /**
     * Download examination documents
     * @return
     */
    protected function downloadExaminationDocuments()
    {
        
        if (!count((array) $_POST['candidate_id'])) {
            ilUtil::sendFailure($this->lng->txt("no_checkbox"), true);
            $this->ctrl->setParameter($this, "ev_id", (int) $_REQUEST['ev_id']);
            $this->ctrl->redirect($this, "listAssignment");
        }
        // create report
        include_once './Services/ADN/Report/exceptions/class.adnReportException.php';
        try {
            include_once './Services/ADN/EP/classes/class.adnExaminationEvent.php';
            include_once("./Services/ADN/Report/classes/class.adnReportAnswerSheet.php");
            $report = new adnReportAnswerSheet(new adnExaminationEvent((int) $_REQUEST['ev_id']));
            $report->setCandidates((array) $_POST['candidate_id']);
            $report->collectAllDocuments(!$this->archived);
            
            ilUtil::deliverFile($report->getOutfile(), 'Prüfungsbögen.pdf', 'application/pdf');
            return true;
        } catch (adnReportException $e) {
            ilUtil::sendFailure($e->getMessage(), true);
            $this->ctrl->saveParameter($this, 'ev_id');
            $this->ctrl->redirect($this, 'listAssignment');
        }
    }
}
