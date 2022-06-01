<?php
/* Copyright (c) 2010 Leifos, GPL, see docs/LICENSE */

/**
 * Scoring GUI class. This class mainly includes the form to create/edit certificates after
 * and exam has been taken.
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.de>
 * @version $Id: class.adnCertificateScoringGUI.php 58885 2015-04-16 14:38:44Z fwolf $
 *
 * @ilCtrl_Calls adnCertificateScoringGUI:
 *
 * @ingroup ServicesADN
 */
class adnCertificateScoringGUI
{
    protected ilCtrl $ctrl;
    protected ilGlobalTemplateInterface $tpl;
    protected ilLanguage $lng;
    protected ilTabsGUI $tabs;
    protected ilObjUser $user;
    /**
     * Constructor
     */
    public function __construct()
    {
        global $DIC;
        $this->ctrl = $DIC->ctrl();
        $this->tpl = $DIC->ui()->mainTemplate();
        $this->lng = $DIC->language();
        $this->tabs = $DIC->tabs();
        $this->user = $DIC->user();
        
        // save certificate ID through requests cr-008 added cd_id
        $this->ctrl->saveParameter($this, array("ct_id", "ev_id", "ass_id", "cd_id"));

    }
    
    /**
     * Execute command
     */
    public function executeCommand()
    {

        $this->tpl->setTitle($this->lng->txt("adn_es") . " - " .
            $this->lng->txt("adn_es_cts"));

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
                    case "listCandidates":
                    case "applyFilter":
                    case "resetFilter":
                    case "downloadCertificates":
                        if (adnPerm::check(adnPerm::ES, adnPerm::READ)) {
                            $this->$cmd();
                        }
                        break;
                    
                    // commands that need write permission
                    case "editCertificate":
                    case "createCertificate":
                    case "saveCertificate":
                    case "updateCertificate":
                    case "confirmSaveCertificate":
                        if (adnPerm::check(adnPerm::ES, adnPerm::WRITE)) {
                            $this->$cmd();
                        }
                        break;
                    
                }
                break;
        }
    }

    
    /**
     * List all examination events
     */
    protected function listEvents()
    {

        // table of examination events
        include_once("./Services/ADN/ES/classes/class.adnExaminationEventTableGUI.php");
        $table = new adnExaminationEventTableGUI(
            $this,
            "listEvents",
            adnExaminationEventTableGUI::MODE_CERTIFICATE,
            true
        );
        
        // output table
        $this->tpl->setContent($table->getHTML());
    }

    /**
     * Apply filter settings
     */
    protected function applyFilter()
    {
        include_once("./Services/ADN/ES/classes/class.adnExaminationEventTableGUI.php");
        $table = new adnExaminationEventTableGUI(
            $this,
            "listEvents",
            adnExaminationEventTableGUI::MODE_CERTIFICATE,
            true
        );
        $table->resetOffset();
        $table->writeFilterToSession();

        $this->listEvents();
    }

    /**
     * Reset filter settings
     */
    protected function resetFilter()
    {
        include_once("./Services/ADN/ES/classes/class.adnExaminationEventTableGUI.php");
        $table = new adnExaminationEventTableGUI(
            $this,
            "listEvents",
            adnExaminationEventTableGUI::MODE_CERTIFICATE,
            true
        );
        $table->resetOffset();
        $table->resetFilter();

        $this->listEvents();
    }

    /**
     * List all candiates for event
     */
    protected function listCandidates()
    {

        $this->tabs->setBackTarget($this->lng->txt("back"), $this->ctrl->getLinkTarget($this, "listEvents"));

        $event_id = (int) $_GET["ev_id"];

        // cr-008 start
        if ($event_id == 0) {
            $this->ctrl->redirectByClass(array("adnBaseGUI", "adncertifiedprofessionalgui", "adnPersonalDataMaintenanceGUI"), "listPersonalData");
        }
        // cr-008 end

        $this->ctrl->setParameter($this, "ev_id", $event_id);

        // table of candidates
        include_once("./Services/ADN/ES/classes/class.adnCertificateCandidateTableGUI.php");
        $table = new adnCertificateCandidateTableGUI($this, "listCandidates", $event_id);

        // output table
        $this->tpl->setContent($table->getHTML());
    }

    /**
     * Create new certificate
     *
     * @param ilPropertyFormGUI $a_form
     */
    protected function createCertificate(ilPropertyFormGUI $a_form = null)
    {

        $event_id = (int) $_GET["ev_id"];
        $candidate_id = (int) $_GET["cd_id"];

        $this->ctrl->setParameter($this, "ev_id", $event_id);

        $this->tabs->setBackTarget($this->lng->txt("back"), $this->ctrl->getLinkTarget($this, "listCandidates"));

        $this->ctrl->setParameter($this, "cd_id", $candidate_id);

        if (!$a_form) {
            $a_form = $this->initCertificateForm();
        }
        $this->tpl->setContent($a_form->getHTML());
    }

    /**
     * Edit certificate for candidate and event
     *
     * @param ilPropertyFormGUI $a_form
     */
    protected function editCertificate(ilPropertyFormGUI $a_form = null)
    {

        $event_id = (int) $_GET["ev_id"];
        $candidate_id = (int) $_GET["cd_id"];

        $this->ctrl->setParameter($this, "ev_id", $event_id);

        $this->tabs->setBackTarget($this->lng->txt("back"), $this->ctrl->getLinkTarget($this, "listCandidates"));

        $this->ctrl->setParameter($this, "cd_id", $candidate_id);

        if (!$a_form) {
            $a_form = $this->initCertificateForm("edit");
        }
        $this->tpl->setContent($a_form->getHTML());
    }

    /**
     * Init certificate form
     *
     * @param string $a_mode create | edit
     * @param bool $a_final_confirmation
     * @return ilPropertyFormGUI
     */
    protected function initCertificateForm($a_mode = "create", $a_final_confirmation = false)
    {

        // assignment
        $assignment_id = (int) $_GET["ass_id"];
        // cr-008 start
        if ($assignment_id > 0) {
            include_once("./Services/ADN/EP/classes/class.adnAssignment.php");
            $assignment = new adnAssignment($assignment_id);
            $this->ctrl->setParameter($this, "ass_id", $assignment_id);

            // candidate
            $candidate_id = $assignment->getUser();
            include_once("./Services/ADN/ES/classes/class.adnCertifiedProfessional.php");
            $candidate = new adnCertifiedProfessional($candidate_id);
        } else {
            include_once("./Services/ADN/ES/classes/class.adnCertifiedProfessional.php");
            $candidate = new adnCertifiedProfessional($_GET["cd_id"]);
        }
        // cr-008 end

        // certificate
        if ($a_mode == "edit") {
            $certificate_id = (int) $_GET["ct_id"];
            include_once("./Services/ADN/ES/classes/class.adnCertificate.php");
            $certificate = new adnCertificate($certificate_id);
        }

        include_once "Services/Form/classes/class.ilPropertyFormGUI.php";
        $form = new ilPropertyFormGUI();
        $form->setFormAction($this->ctrl->getFormAction($this, "listCandidates"));

        // title
        // cr-008 start
        if ($assignment_id > 0) {
            include_once("./Services/ADN/EP/classes/class.adnExaminationEvent.php");
            $title = "<div class=\"small\" style=\"font-weight:normal; margin-bottom:5px;\">" .
                adnExaminationEvent::lookupName($assignment->getEvent()) . "</div>";
        }
        // cr-008 end

        if ($a_mode == "create") {
            $title .= $this->lng->txt("adn_create_certificate");
        } else {
            $title .= $this->lng->txt("adn_edit_certificate");
        }
        $title .= ": " . $candidate->getLastname() . ", " . $candidate->getFirstname();
        $form->setTitle($title);

        // certificate number
        if ($a_mode == "edit") {
            $ne = new ilNonEditableValueGUI($this->lng->txt("adn_number"), "");
            $ne->setValue($certificate->getFullCertificateNumber());
            $form->addItem($ne);
        } else {
            include_once("./Services/ADN/AD/classes/class.adnUser.php");
            $ne = new ilNonEditableValueGUI($this->lng->txt("adn_number"), "cert_nr");
            include_once("./Services/ADN/ES/classes/class.adnCertificate.php");
            $ne->setValue(
                adnCertificate::_getFullCertificateNumber(
                    adnUser::lookupWmoId(),
                    adnCertificate::_determineNextNumber(
                        adnUser::lookupWmoId(),
                        new ilDate(ilUtil::now(), IL_CAL_DATE)
                    ),
                    new ilDate(ilUtil::now(), IL_CAL_DATE)
                )
            );
            $form->addItem($ne);
        }

        // certificate type
        $type = new ilCheckboxGroupInputGUI(
            $this->lng->txt("adn_type_of_cert"),
            "cert_type"
        );
        include_once "Services/ADN/ES/classes/class.adnCertificate.php";
        $values = array();
        foreach (adnCertificate::getCertificateTypes() as $id => $caption) {
            $cb = new ilCheckboxOption($caption, $id);
            $type->addOption($cb);
            if ($a_mode == "edit") {
                if ($certificate->getType($id)) {
                    $values[] = $id;
                }
            }
        }
        if ($a_mode == "edit") {
            $type->setValue($values);
        }
        $type->setRequired(true);
        $form->addItem($type);

        // issued by wmo
        include_once("./Services/ADN/MD/classes/class.adnWMO.php");
        $wmos = adnWMO::getAllWMOs();
        $options = array();
        foreach ($wmos as $wmo) {
            $options[$wmo["id"]] = $wmo["name"];
        }
        $wmo = new ilSelectInputGUI($this->lng->txt("adn_issued_by"), "issued_by_wmo");
        $wmo->setOptions($options);
        include_once("./Services/ADN/AD/classes/class.adnUser.php");
        if ($a_mode == "edit") {
            $wmo->setValue($certificate->getIssuedByWmo());
            $form->addItem($form->getReadOnlyItem($wmo));
        } else {
            $wmo->setValue(adnUser::lookupWmoId());
            $form->addItem($wmo);
            $wmo->setRequired(true);
        }

        // issued on
        if ($a_mode == "edit") {
            $ne = new ilNonEditableValueGUI($this->lng->txt("adn_issued_on"), "");
            $c = $certificate->getIssuedOn();
            $ne->setValue(ilDatePresentation::formatDate(
                $certificate->getIssuedOn(),
                IL_CAL_DATE
            ));
            $form->addItem($ne);
        } else {
            $issued_on = new ilDateTimeInputGUI($this->lng->txt("adn_issued_on"), "issued_on");
            $issued_on->setRequired(true);
            $form->addItem($issued_on);
        }

        // valid until
        $valid_until = new ilDateTimeInputGUI($this->lng->txt("adn_valid_until"), "valid_until");
        $valid_until->setRequired(true);
        if ($a_mode == "create") {
            $vu_date = new ilDateTime(time(), IL_CAL_UNIX);
            $vu_date->increment(IL_CAL_YEAR, 5);
            $vu_date->increment(IL_CAL_DAY, -1);
            $valid_until->setDate($vu_date);
        }
        $form->addItem($valid_until);

        // signed by
        $signed_by = new ilTextInputGUI($this->lng->txt("adn_signed_by"), "signed_by");
        $signed_by->setRequired(true);
        $form->addItem($signed_by);

        if ($a_mode == "edit") {
            //$wmo->setValue($certificate->getIssuedByWmo());
            $valid_until->setDate($certificate->getValidUntil());
            $signed_by->setValue($certificate->getSignedBy());
        } else {
            $signed_by->setValue($this->user->getLastname() . ", " . $this->user->getFirstname());
        }

        // status
        if ($a_mode == "edit") {
            // status
            $status = new ilNonEditableValueGUI($this->lng->txt("adn_status"), "");
            $form->addItem($status);

            // if certificate is not valid anymore, overwrite status
            $today = new ilDate(time(), IL_CAL_UNIX);
            $today = $today->get(IL_CAL_DATE);
            if ($certificate->getValidUntil()->get(IL_CAL_DATE) < $today) {
                $status->setValue($this->lng->txt("adn_invalid"));
            } elseif ($certificate->getStatus() == adnCertificate::STATUS_INVALID) {
                $status->setValue($this->lng->txt("adn_invalid"));
            } else {
                $status->setValue($this->lng->txt("adn_valid"));
            }
        }


        // form buttons
        if ($a_mode == "create") {
            if ((bool) $a_final_confirmation) {
                $form->addCommandButton("saveCertificate", $this->lng->txt("save"));
            } else {
                $form->addCommandButton("confirmSaveCertificate", $this->lng->txt("save"));
            }
        } else {
            $form->addCommandButton("updateCertificate", $this->lng->txt("save"));
        }
        $form->addCommandButton("listCandidates", $this->lng->txt("cancel"));

        return $form;
    }

    /**
     * Confirm certificate saving
     */
    protected function confirmSaveCertificate()
    {

        $form = $this->initCertificateForm("create", true);

        include_once("./Services/ADN/ED/classes/class.adnSubjectArea.php");

        // check input
        if ($form->checkInput()) {
            //warning if issued on year is different from actual year
            $issued_on = (int) substr($form->getItemByPostVar("issued_on")->getDate()->get(IL_CAL_DATE), 0, 4);
            $year = (int) date("Y");

            if ($issued_on != $year) {
                ilUtil::sendFailure($this->lng->txt("adn_certificate_issued_on_warning"));
            }
            // output confirmation form
            ilUtil::sendInfo($this->lng->txt("adn_please_check_certificate"));
            $form->setValuesByPost();
            
            // insert correct number
            include_once("./Services/ADN/AD/classes/class.adnUser.php");
            $ne = $form->getItemByPostVar("cert_nr");
            $issued_date = $form->getItemByPostVar("issued_on");
            $ne->setValue(
                adnCertificate::_getFullCertificateNumber(
                    $_POST["issued_by_wmo"],
                    adnCertificate::_determineNextNumber(
                        $_POST["issued_by_wmo"],
                        $issued_date->getDate()
                    ),
                    $issued_date->getDate()
                )
            );

            $this->tpl->setContent($form->getHTML());
        } else {
            // input not valid: show form again

            // fix command buttons
            $form->clearCommandButtons();
            $form->addCommandButton("confirmSaveCertificate", $this->lng->txt("save"));
            $form->addCommandButton("listCandidates", $this->lng->txt("cancel"));

            $form->setValuesByPost();

            // insert correct number
            include_once("./Services/ADN/AD/classes/class.adnUser.php");
            $ne = $form->getItemByPostVar("cert_nr");
            $issued_date = $form->getItemByPostVar("issued_on");
            $ne->setValue(
                adnCertificate::_getFullCertificateNumber(
                    $_POST["issued_by_wmo"],
                    adnCertificate::_determineNextNumber(
                        $_POST["issued_by_wmo"],
                        $issued_date->getDate()
                    ),
                    $issued_date->getDate()
                )
            );


            $this->createCertificate($form);
        }
    }

    /**
     * Save certificate
     */
    protected function saveCertificate()
    {

        $form = $this->initCertificateForm();

        include_once("./Services/ADN/ED/classes/class.adnSubjectArea.php");

        // cr-008 start
        if ($_GET["ass_id"] > 0) {
            // get assignment
            $assignment_id = (int) $_GET["ass_id"];
            include_once("./Services/ADN/EP/classes/class.adnAssignment.php");
            $assignment = new adnAssignment($assignment_id);

            // get candidate
            $candidate_id = $assignment->getUser();
        } else {
            $candidate_id = $_GET["cd_id"];
        }
        // cr-008 end

        //include_once("./Services/ADN/ES/classes/class.adnCertifiedProfessional.php");
        //$candidate = new adnCertifiedProfessional($candidate_id);

        // check input
        if ($form->checkInput()) {
            include_once("./Services/ADN/ES/classes/class.adnCertificate.php");

            // create new certificate
            $cert = new adnCertificate();

            // certified professional
            $cert->setCertifiedProfessionalId($candidate_id);

            // examination cr-008 added if
            if ($_GET["ass_id"] > 0) {
                $cert->setExaminationId($assignment->getEvent());
            }

            // certificate types
            foreach (adnCertificate::getCertificateTypes() as $id => $caption) {
                if (in_array($id, $_POST["cert_type"])) {
                    $cert->setType($id, true);
                } else {
                    $cert->setType($id, false);
                }
            }

            // issued by wmo
            $cert->setIssuedByWmo($form->getInput("issued_by_wmo"));

            // signed by
            $cert->setSignedBy($form->getInput("signed_by"));

            // issued on
            $issued_date = $form->getItemByPostVar("issued_on");
            $cert->setIssuedOn($issued_date->getDate());

            // valid until
            $issued_date = $form->getItemByPostVar("valid_until");
            $cert->setValidUntil($issued_date->getDate());

            // save certificate
            $cert->save();

            // show success message and return to list
            ilUtil::sendSuccess($this->lng->txt("adn_certificate_saved"), true);
            // cr-008 start
            if ($_GET["ass_id"] > 0) {
                $this->ctrl->redirect($this, "listCandidates");
            } else {
                $this->ctrl->redirectByClass(array("adnBaseGUI", "adncertifiedprofessionalgui", "adnCertifiedProfessionalDataGUI"), "listProfessionals");
            }
            // cr-008 end
        }

        // input not valid: show form again
        $form->setValuesByPost();

        // insert correct number
        include_once("./Services/ADN/AD/classes/class.adnUser.php");
        $ne = $form->getItemByPostVar("cert_nr");
        $issued_date = $form->getItemByPostVar("issued_on");
        $ne->setValue(
            adnCertificate::_getFullCertificateNumber(
                $_POST["issued_by_wmo"],
                adnCertificate::_determineNextNumber(
                    $_POST["issued_by_wmo"],
                    $issued_date->getDate()
                ),
                $issued_date->getDate()
            )
        );

        $this->createCertificate($form);
    }

    /**
     * Update certificate
     */
    protected function updateCertificate()
    {

        $form = $this->initCertificateForm("edit");

        // get assignment
        $assignment_id = (int) $_GET["ass_id"];
        include_once("./Services/ADN/EP/classes/class.adnAssignment.php");
        $assignment = new adnAssignment($assignment_id);

        // get candidate
        $candidate_id = $assignment->getUser();
        include_once("./Services/ADN/ES/classes/class.adnCertifiedProfessional.php");
        $candidate = new adnCertifiedProfessional($candidate_id);

        // check input
        if ($form->checkInput()) {
            $certificate_id = (int) $_GET["ct_id"];
            include_once("./Services/ADN/ES/classes/class.adnCertificate.php");
            $cert = new adnCertificate($certificate_id);

            // certificate types
            foreach (adnCertificate::getCertificateTypes() as $id => $caption) {
                if (in_array($id, $_POST["cert_type"])) {
                    $cert->setType($id, true);
                } else {
                    $cert->setType($id, false);
                }
            }

            // issued by wmo
            //$cert->setIssuedByWmo($form->getInput("issued_by_wmo"));

            // signed by
            $cert->setSignedBy($form->getInput("signed_by"));

            // issued on
            //$issued_date = $form->getItemByPostVar("issued_on");
            //$cert->setIssuedOn($issued_date->getDate());

            // valid until
            $issued_date = $form->getItemByPostVar("valid_until");
            $cert->setValidUntil($issued_date->getDate());

            // save certificate
            $cert->update();

            // show success message and return to list
            ilUtil::sendSuccess($this->lng->txt("adn_certificate_updated"), true);
            $this->ctrl->redirect($this, "listCandidates");
        }

        // input not valid: show form again
        $form->setValuesByPost();
        $this->editCertificate($form);
    }

    
    //
    // Download
    //

    /**
     * Download certificates
     */
    public function downloadCertificates()
    {

        if (!is_array($_POST["cid"]) || count($_POST["cid"]) == 0) {
            ilUtil::sendFailure($this->lng->txt("no_checkbox"), true);
            $this->ctrl->redirect($this, "listCandidates");
        }

        $cids = array();
        foreach ($_POST["cid"] as $c) {
            $cids[] = (int) $c;
        }
        
        $this->ctrl->saveParameter($this, 'ct_id');
        $this->ctrl->saveParameter($this, 'ev_id');
        $this->ctrl->saveParameter($this, 'ass_id');

        include_once './Services/ADN/Report/exceptions/class.adnReportException.php';
        try {
            include_once './Services/ADN/EP/classes/class.adnExaminationEvent.php';
            include_once("./Services/ADN/Report/classes/class.adnReportCertificate.php");
            $report = new adnReportCertificate($cids);
            $report->create();
            
            ilUtil::deliverFile(
                $report->getOutfile(),
                'Bescheinigungen.pdf',
                'application/pdf'
            );
        } catch (adnReportException $e) {
            ilUtil::sendFailure($e->getMessage(), true);
            $this->ctrl->redirect($this, 'listCandidates');
        }
    }
}
