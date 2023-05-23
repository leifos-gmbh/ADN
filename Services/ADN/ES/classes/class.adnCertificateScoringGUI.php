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
    /**
     * Constructor
     */
    public function __construct()
    {
        global $ilCtrl;
        
        // save certificate ID through requests cr-008 added cd_id
        $ilCtrl->saveParameter($this, array("ct_id", "ev_id", "ass_id", "cd_id"));
        
        // $this->readCertificate();
    }
    
    /**
     * Execute command
     */
    public function executeCommand()
    {
        global $ilCtrl, $tpl, $lng;

        $tpl->setTitle($lng->txt("adn_es") . " - " .
            $lng->txt("adn_es_cts"));
        adnIcon::setTitleIcon("es_cts");

        $next_class = $ilCtrl->getNextClass();
        
        // forward command to next gui class in control flow
        switch ($next_class) {
            // no next class:
            // this class is responsible to process the command
            default:
                $cmd = $ilCtrl->getCmd("listEvents");
                
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
     * Read certificate
     */
    protected function readCertificate()
    {
        if ((int) $_GET["ct_id"] > 0) {
            include_once("./Services/ADN/CP/classes/class.adnCertificate.php");
            $this->scoring = new adnCertificate((int) $_GET["ct_id"]);
        }
    }
    
    /**
     * List all examination events
     */
    protected function listEvents()
    {
        global $tpl;

        // table of examination events
        include_once("./Services/ADN/ES/classes/class.adnExaminationEventTableGUI.php");
        $table = new adnExaminationEventTableGUI(
            $this,
            "listEvents",
            adnExaminationEventTableGUI::MODE_CERTIFICATE,
            true
        );
        
        // output table
        $tpl->setContent($table->getHTML());
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
        global $tpl, $ilCtrl, $ilTabs, $lng;

        $ilTabs->setBackTarget($lng->txt("back"), $ilCtrl->getLinkTarget($this, "listEvents"));

        $event_id = (int) $_GET["ev_id"];

        // cr-008 start
        if ($event_id == 0) {
            $ilCtrl->redirectByClass(array("adnBaseGUI", "adncertifiedprofessionalgui", "adnPersonalDataMaintenanceGUI"), "listPersonalData");
        }
        // cr-008 end

        $ilCtrl->setParameter($this, "ev_id", $event_id);

        // table of candidates
        include_once("./Services/ADN/ES/classes/class.adnCertificateCandidateTableGUI.php");
        $table = new adnCertificateCandidateTableGUI($this, "listCandidates", $event_id);

        // output table
        $tpl->setContent($table->getHTML());
    }

    /**
     * Create new certificate
     *
     * @param ilPropertyFormGUI $a_form
     */
    protected function createCertificate(ilPropertyFormGUI $a_form = null)
    {
        global $tpl, $lng, $ilTabs, $ilCtrl;

        $event_id = (int) $_GET["ev_id"];
        $candidate_id = (int) $_GET["cd_id"];

        $ilCtrl->setParameter($this, "ev_id", $event_id);

        $ilTabs->setBackTarget($lng->txt("back"), $ilCtrl->getLinkTarget($this, "listCandidates"));

        $ilCtrl->setParameter($this, "cd_id", $candidate_id);

        if (!$a_form) {
            $a_form = $this->initCertificateForm();
        }
        $tpl->setContent($a_form->getHTML());
    }

    /**
     * Edit certificate for candidate and event
     *
     * @param ilPropertyFormGUI $a_form
     */
    protected function editCertificate(ilPropertyFormGUI $a_form = null)
    {
        global $tpl, $lng, $ilTabs, $ilCtrl;

        $event_id = (int) $_GET["ev_id"];
        $candidate_id = (int) $_GET["cd_id"];

        $ilCtrl->setParameter($this, "ev_id", $event_id);

        $ilTabs->setBackTarget($lng->txt("back"), $ilCtrl->getLinkTarget($this, "listCandidates"));

        $ilCtrl->setParameter($this, "cd_id", $candidate_id);

        if (!$a_form) {
            $a_form = $this->initCertificateForm("edit");
        }
        $tpl->setContent($a_form->getHTML());
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
        global  $lng, $ilCtrl, $ilUser;

        // assignment
        $assignment_id = (int) $_GET["ass_id"];
        // cr-008 start
        if ($assignment_id > 0) {
            include_once("./Services/ADN/EP/classes/class.adnAssignment.php");
            $assignment = new adnAssignment($assignment_id);
            $ilCtrl->setParameter($this, "ass_id", $assignment_id);

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
        $form->setFormAction($ilCtrl->getFormAction($this, "listCandidates"));

        // title
        // cr-008 start
        if ($assignment_id > 0) {
            include_once("./Services/ADN/EP/classes/class.adnExaminationEvent.php");
            $title = "<div class=\"small\" style=\"font-weight:normal; margin-bottom:5px;\">" .
                adnExaminationEvent::lookupName($assignment->getEvent()) . "</div>";
        }
        // cr-008 end

        if ($a_mode == "create") {
            $title .= $lng->txt("adn_create_certificate");
        } else {
            $title .= $lng->txt("adn_edit_certificate");
        }
        $title .= ": " . $candidate->getLastname() . ", " . $candidate->getFirstname();
        $form->setTitle($title);

        // certificate number
        if ($a_mode == "edit") {
            $ne = new ilNonEditableValueGUI($lng->txt("adn_number"), "");
            $ne->setValue($certificate->getFullCertificateNumber());
            $form->addItem($ne);
        } else {
            include_once("./Services/ADN/AD/classes/class.adnUser.php");
            $ne = new ilNonEditableValueGUI($lng->txt("adn_number"), "cert_nr");
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

        if ($candidate->getImageHandler()->getAbsolutePath() !== '') {
            $pic = new ilImageFileInputGUI($lng->txt('adn_card_form_photo'), 'card_photo');
            $pic->setRequired(true);
            $pic->setDisabled(true);
            $pic->setALlowDeletion(false);
            $pic->setUseCache(false);
            $pic->setImage($candidate->getImageHandler()->getAbsolutePath() ?? '');
            $form->addItem($pic);
        } else {
            $pic = new ilTextInputGUI($lng->txt('adn_card_form_photo'), 'card_photo');
            $pic->setRequired(true);
            $pic->setDisabled(true);
            $pic->setAlert($lng->txt('adn_card_missing_photo_info'));
            $form->addItem($pic);
        }

        // certificate type
        $type = new ilCheckboxGroupInputGUI(
            $lng->txt("adn_type_of_cert"),
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
        $wmo = new ilSelectInputGUI($lng->txt("adn_issued_by"), "issued_by_wmo");
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
            $ne = new ilNonEditableValueGUI($lng->txt("adn_issued_on"), "");
            $c = $certificate->getIssuedOn();
            $ne->setValue(ilDatePresentation::formatDate(
                $certificate->getIssuedOn(),
                IL_CAL_DATE
            ));
            $form->addItem($ne);
        } else {
            $issued_on = new ilDateTimeInputGUI($lng->txt("adn_issued_on"), "issued_on");
            $issued_on->setRequired(true);
            $form->addItem($issued_on);
        }

        // valid until
        $valid_until = new ilDateTimeInputGUI($lng->txt("adn_valid_until"), "valid_until");
        $valid_until->setRequired(true);
        if ($a_mode == "create") {
            $vu_date = new ilDateTime(time(), IL_CAL_UNIX);
            $vu_date->increment(IL_CAL_YEAR, 5);
            $vu_date->increment(IL_CAL_DAY, -1);
            $valid_until->setDate($vu_date);
        }
        $form->addItem($valid_until);

        // signed by
        $signed_by = new ilTextInputGUI($lng->txt("adn_signed_by"), "signed_by");
        $signed_by->setRequired(true);
        $form->addItem($signed_by);

        if ($a_mode == "edit") {
            //$wmo->setValue($certificate->getIssuedByWmo());
            $valid_until->setDate($certificate->getValidUntil());
            $signed_by->setValue($certificate->getSignedBy());
        } else {
            $signed_by->setValue($ilUser->getLastname() . ", " . $ilUser->getFirstname());
        }

        // status
        if ($a_mode == "edit") {
            // status
            $status = new ilNonEditableValueGUI($lng->txt("adn_status"), "");
            $form->addItem($status);

            // if certificate is not valid anymore, overwrite status
            $today = new ilDate(time(), IL_CAL_UNIX);
            $today = $today->get(IL_CAL_DATE);
            if ($certificate->getValidUntil()->get(IL_CAL_DATE) < $today) {
                $status->setValue($lng->txt("adn_invalid"));
            } elseif ($certificate->getStatus() == adnCertificate::STATUS_INVALID) {
                $status->setValue($lng->txt("adn_invalid"));
            } else {
                $status->setValue($lng->txt("adn_valid"));
            }
        }

        // show postal address read only
        $postal_address = new ilFormSectionHeaderGUI();
        $postal_address->setTitle($lng->txt('adn_certificate_pa'));
        $form->addItem($postal_address);

        $name = new ilTextInputGUI($lng->txt('adn_certificate_pa_name'), 'unused_name');
        $name->setDisabled(true);
        $name->setRequired(true);
        $form->addItem($name);

        $street = new ilTextInputGUI($lng->txt('adn_certificate_pa_street'), 'unused_street');
        $street->setDisabled(true);
        $street->setRequired(true);
        $form->addItem($street);

        $city = new ilTextInputGUI($lng->txt('adn_certificate_pa_city'), 'unused_city');
        $city->setDisabled(true);
        $city->setRequired(true);
        $form->addItem($city);

        $country = new ilTextInputGUI($lng->txt('adn_certificate_pa_country'), 'unused_country');
        $country->setDisabled(true);
        $country->setRequired(true);
        $form->addItem($country);

        if ($candidate->isShippingActive()) {
            $name->setValue($candidate->getShippingFirstName() . ' ' . $candidate->getShippingLastName());
            $street->setValue($candidate->getShippingStreet() . ' ' . $candidate->getShippingStreetNumber());
            $city->setValue($candidate->getShippingCode() . ' ' . $candidate->getShippingCity());
            $country_handler = new adnCountry($candidate->getShippingCountry());
            $country->setValue($country_handler->getName());
        } else {
            $name->setValue($candidate->getFirstName() . ' ' . $candidate->getLastName());
            $street->setValue($candidate->getPostalStreet() . ' ' . $candidate->getPostalStreetNumber());
            $city->setValue($candidate->getPostalCode() . ' ' . $candidate->getPostalCity());
            $country_handler = new adnCountry($candidate->getPostalCountry());
            $country->setValue($country_handler->getName());
        }

        // form buttons
        if ($a_mode == "create") {
            if ((bool) $a_final_confirmation) {
                $form->addCommandButton("saveCertificate", $lng->txt("save"));
            } else {
                $form->addCommandButton("confirmSaveCertificate", $lng->txt("save"));
            }
        } else {
            $form->addCommandButton("updateCertificate", $lng->txt("save"));
        }
        $form->addCommandButton("listCandidates", $lng->txt("cancel"));

        return $form;
    }

    /**
     * Confirm certificate saving
     */
    protected function confirmSaveCertificate()
    {
        global $tpl, $lng, $ilCtrl;

        $form = $this->initCertificateForm("create", true);


        // get assignment
        //$assignment_id = (int)$_GET["ass_id"];
        //include_once("./Services/ADN/EP/classes/class.adnAssignment.php");
        //$assignment = new adnAssignment($assignment_id);

        // get candidate
        //$candidate_id = $assignment->getUser();
        //include_once("./Services/ADN/ES/classes/class.adnCertifiedProfessional.php");
        //$candidate = new adnCertifiedProfessional($candidate_id);

        // check input
        if ($form->checkInput()) {
            //warning if issued on year is different from actual year
            $issued_on = (int) substr($form->getItemByPostVar("issued_on")->getDate()->get(IL_CAL_DATE), 0, 4);
            $year = (int) date("Y");

            if ($issued_on != $year) {
                ilUtil::sendFailure($lng->txt("adn_certificate_issued_on_warning"));
            }
            // output confirmation form
            ilUtil::sendInfo($lng->txt("adn_please_check_certificate"));
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

            $tpl->setContent($form->getHTML());
        } else {
            // input not valid: show form again

            // fix command buttons
            $form->clearCommandButtons();
            $form->addCommandButton("confirmSaveCertificate", $lng->txt("save"));
            $form->addCommandButton("listCandidates", $lng->txt("cancel"));

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
        global $tpl, $lng, $ilCtrl;

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

        $candidate = new adnCertifiedProfessional($candidate_id);

        // check input
        if ($form->checkInput()) {

            // create new certificate
            $cert = new adnCertificate();
            $cert->initUuid();
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

            $cert->setIssuedByWmo($form->getInput("issued_by_wmo"));
            $cert->setSignedBy($form->getInput("signed_by"));
            $issued_date = $form->getItemByPostVar("issued_on");
            $cert->setIssuedOn($issued_date->getDate());
            $issued_date = $form->getItemByPostVar("valid_until");
            $cert->setValidUntil($issued_date->getDate());

            $order = new adnCardCertificateOrderHandler();
            try {
                $response = $order->send($order->initOrder($candidate, $cert));
            } catch (Exception $exception) {
                // resyet uuid
                $cert->setUuid('');
                $form->setValuesByPost();
                ilUtil::sendFailure($exception->getMessage());
                $this->createCertificate($form);
                return;
            }

            $cert->save();

            // show success message and return to list
            ilUtil::sendSuccess($lng->txt("adn_certificate_saved"), true);
            // cr-008 start
            if ($_GET["ass_id"] > 0) {
                $ilCtrl->redirect($this, "listCandidates");
            } else {
                $ilCtrl->redirectByClass(array("adnBaseGUI", "adncertifiedprofessionalgui", "adnCertifiedProfessionalDataGUI"), "listProfessionals");
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
        global $tpl, $lng, $ilCtrl;

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

            // signed by
            $cert->setSignedBy($form->getInput("signed_by"));

            // valid until
            $issued_date = $form->getItemByPostVar("valid_until");
            $cert->setValidUntil($issued_date->getDate());

            // save certificate
            $cert->update();

            // show success message and return to list
            ilUtil::sendSuccess($lng->txt("adn_certificate_updated") . $cert->getUUid(), true);
            $ilCtrl->redirect($this, "listCandidates");
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
        global $ilCtrl, $lng;

        if (!is_array($_POST["cid"]) || count($_POST["cid"]) == 0) {
            ilUtil::sendFailure($lng->txt("no_checkbox"), true);
            $ilCtrl->redirect($this, "listCandidates");
        }

        $cids = array();
        foreach ($_POST["cid"] as $c) {
            $cids[] = (int) $c;
        }
        
        $ilCtrl->saveParameter($this, 'ct_id');
        $ilCtrl->saveParameter($this, 'ev_id');
        $ilCtrl->saveParameter($this, 'ass_id');

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
            $ilCtrl->redirect($this, 'listCandidates');
        }
    }
}
