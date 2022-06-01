<?php
/* Copyright (c) 2010 Leifos, GPL, see docs/LICENSE */

/**
 * ADN certificate GUI class
 *
 * Handles certificates, duplicates and extensions
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.de>
 * @version $Id: class.adnCertificateGUI.php 34375 2012-04-26 08:47:22Z jluetzen $
 *
 * @ilCtrl_Calls adnCertificateGUI:
 *
 * @ingroup ServicesADN
 */
class adnCertificateGUI
{
    // current certificate object
    protected ?adnCertificate $certificate = null;
    
    // current form object
    protected ?ilPropertyFormGUI $form = null;

    // professional id (may not be certified, see #13)
    protected int $pid;
    protected ilCtrl $ctrl;
    protected ilGlobalTemplateInterface $tpl;
    protected ilLanguage $lng;
    protected ilToolbarGUI $toolbar;
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
        $this->toolbar = $DIC->toolbar();
        $this->tabs = $DIC->tabs();
        $this->user = $DIC->user();
        
        // save certificate ID through requests
        $this->ctrl->saveParameter($this, array("ct_id"));
        $this->ctrl->saveParameter($this, array("pid"));

        $this->pid = (int) $_GET["pid"];		// see #13
        
        $this->readCertificate();
    }
    
    /**
     * Execute command
     */
    public function executeCommand()
    {

        $this->tpl->setTitle($this->lng->txt("adn_cp") . " - " . $this->lng->txt("adn_cp_cts"));
        
        $next_class = $this->ctrl->getNextClass();
        
        // forward command to next gui class in control flow
        switch ($next_class) {
            // no next class:
            // this class is responsible to process the command
            default:
                $cmd = $this->ctrl->getCmd("listCertificates");
                
                switch ($cmd) {
                    // commands that need read permission
                    case "listCertificates":
                    case "showCertificate":
                    case "showInvalidCertificate":
                    case "showCertificatesOfProfessional":
                    case "downloadInvoice":
                    case 'downloadExtension':
                    case 'downloadDuplicate':
                    case 'downloadCertificate':
                    case 'applyFilter':
                    case 'resetFilter':
                    case 'confirmSaveExtension':
                    case 'afterExtension':
                        if (adnPerm::check(adnPerm::CP, adnPerm::READ)) {
                            $this->$cmd();
                        }
                        break;
                    
                    // commands that need write permission
                    case "extendCertificate":
                    case "saveExtension":
                    case "duplicateCertificate":
                    case "saveDuplicate":
                    case "generateInvoice":
                    case "saveInvoice":
                    case "edit":
                    case "update":
                        if (adnPerm::check(adnPerm::CP, adnPerm::WRITE)) {
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
            include_once("./Services/ADN/ES/classes/class.adnCertificate.php");
            $this->certificate = new adnCertificate((int) $_GET["ct_id"]);
        }
    }
    
    /**
     * List all certificates
     */
    protected function listCertificates()
    {

        // toggle invalid switch
        if (isset($_POST["cmd"]["listCertificates"])) {
            $_SESSION["ct_ct_invalid"] = (bool) $_POST["ct_invalid"];
        }

        // invalid switch
        include_once "Services/Form/classes/class.ilPropertyFormGUI.php";
        $checkbox = new ilCheckboxInputGUI($this->lng->txt("adn_show_archived_certificates"), "ct_invalid");
        if ($_SESSION["ct_ct_invalid"]) {
            $checkbox->setChecked(true);
        }
        $this->toolbar->addInputItem($checkbox, true);
        $this->toolbar->setFormAction($this->ctrl->getFormAction($this));
        $this->toolbar->addFormButton($this->lng->txt("adn_update_view"), "listCertificates");

        // table of certificates
        include_once("./Services/ADN/CP/classes/class.adnCertificateTableGUI.php");
        $table = new adnCertificateTableGUI($this, "listCertificates");
        
        // output table
        $this->tpl->setContent($table->getHTML());
    }

    /**
     * Apply filter settings (from table GUI)
     */
    protected function applyFilter()
    {
        include_once("./Services/ADN/CP/classes/class.adnCertificateTableGUI.php");
        $table = new adnCertificateTableGUI($this, "listCertificates");
        $table->resetOffset();
        $table->writeFilterToSession();

        $this->listCertificates();
    }

    /**
     * Reset filter settings (from table GUI)
     */
    protected function resetFilter()
    {
        include_once("./Services/ADN/CP/classes/class.adnCertificateTableGUI.php");
        $table = new adnCertificateTableGUI($this, "listCertificates");
        $table->resetOffset();
        $table->resetFilter();

        $this->listCertificates();
    }
    
    /**
     * Show certificate
     */
    public function showCertificate()
    {

        // add back tab
        $this->tabs->setBackTarget(
            $this->lng->txt("back"),
            $this->ctrl->getLinkTarget($this, "listCertificates")
        );

        $form = $this->initCertificateForm("show");
        $form = $form->convertToReadonly();
        $this->tpl->setContent($form->getHTML());
    }

    /**
     * Show certificate (from archived view)
     */
    public function showInvalidCertificate()
    {

        $this->ctrl->setParameter($this, "cp_id", (int) $_GET["cp_id"]);

        // add back tab
        $this->tabs->setBackTarget(
            $this->lng->txt("back"),
            $this->ctrl->getLinkTarget($this, "showCertificatesOfProfessional")
        );

        $form = $this->initCertificateForm("show");
        $form = $form->convertToReadonly();
        $this->tpl->setContent($form->getHTML());
    }

    /**
     * Init training provider form.
     *
     * @param string $a_mode form mode ("show" | "edit" | "extend" | "duplicate")
     * @return ilPropertyFormGUI
     */
    protected function initCertificateForm($a_mode = "edit", $a_final_confirmation = false)
    {

        // certified professional
        if (!is_null($this->certificate)) {
            $cp_id = $this->certificate->getCertifiedProfessionalId();
        } elseif ($a_mode == "extend" && $this->pid > 0) {		// #13, get professional id per GET (extension for foreign certificate)
            $cp_id = $this->pid;
        }
        include_once("./Services/ADN/ES/classes/class.adnCertifiedProfessional.php");
        $cp = new adnCertifiedProfessional($cp_id);

        // check if everything is ok
        if (($this->certificate == null) && ($this->pid == 0 || !$cp->hasForeignCertificateHandedIn())) {
            throw new Exception("Extension not allowed (no certificate or foreign certificate given).");
        }

        // get form object and add input fields
        include_once("Services/Form/classes/class.ilPropertyFormGUI.php");
        $form = new ilPropertyFormGUI();

        // title
        $form->setTitle($this->lng->txt("adn_certificate"));

        // nr
        if ($a_mode != "extend") {
            $nr = new ilNonEditableValueGUI($this->lng->txt("adn_number"), "adn_nr");
            $form->addItem($nr);
            $nr->setValue($this->certificate->getFullCertificateNumber());
        } else {
            // certificate extension: nr cannot be edited
            include_once("./Services/ADN/AD/classes/class.adnUser.php");
            $ne = new ilNonEditableValueGUI($this->lng->txt("adn_number"), "adn_nr");
            include_once("./Services/ADN/ES/classes/class.adnCertificate.php");

            $issued_on = ($this->certificate == null)
                ? new ilDateTime(time(), IL_CAL_UNIX)
                : $this->certificate->getIssuedOn();

            $ne->setValue(
                adnCertificate::_getFullCertificateNumber(
                    adnUser::lookupWmoId(),
                    adnCertificate::_determineNextNumber(
                        adnUser::lookupWmoId(),
                        $issued_on
                    ),
                    $issued_on
                )
            );
            $form->addItem($ne);
        }

        // last name
        $last_name = new ilNonEditableValueGUI($this->lng->txt("adn_last_name"), "last_name");
        $form->addItem($last_name);
        $last_name->setValue($cp->getLastname());

        // first name
        $first_name = new ilNonEditableValueGUI($this->lng->txt("adn_first_name"), "first_name");
        $form->addItem($first_name);
        $first_name->setValue($cp->getFirstname());

        // type of certificate
        $type = new ilCheckboxGroupInputGUI(
            $this->lng->txt("adn_type_of_cert"),
            "cert_type"
        );
        $values = array();
        foreach (adnCertificate::getCertificateTypes() as $id => $caption) {
            $cb = new ilCheckboxOption($caption, $id);
            $type->addOption($cb);
            if ($this->certificate != null && $this->certificate->getType($id)) {
                $values[] = $id;
            }
        }
        $type->setValue($values);
        $type->setRequired(true);
        if ($a_mode != "duplicate") {
            $form->addItem($type);
        } else {
            $form->addItem($form->getReadOnlyItem($type));
        }

        // issued by
        include_once("./Services/ADN/MD/classes/class.adnWMO.php");
        $wmos = adnWMO::getAllWMOs();
        $options = array();
        foreach ($wmos as $wmo) {
            $options[$wmo["id"]] = $wmo["name"];
            if (strlen($wmo['subtitle'])) {
                $options[$wmo['id']] .= (' (' . $wmo['subtitle'] . ')');
            }
        }
        $wmo = new ilSelectInputGUI($this->lng->txt("adn_issued_by"), "issued_by_wmo");
        $wmo->setOptions($options);
        $wmo->setRequired(true);
        if ($this->certificate != null) {
            $wmo->setValue($this->certificate->getIssuedByWmo());
        }
        // extension: wmo may be changed
        if ($a_mode == "extend") {
            $form->addItem($wmo);
            include_once("./Services/ADN/AD/classes/class.adnUser.php");
            $wmo->setValue(adnUser::lookupWmoId());
        } else {
            $form->addItem($form->getReadOnlyItem($wmo));
        }

        // issued on
        $issued_on = new ilDateTimeInputGUI($this->lng->txt("adn_issued_on"), "issued_on");
        $issued_on->setRequired(true);
        // extension: issued on date may be changed
        if ($a_mode == "extend") {
            $issued_on->setDate(new ilDate(time(), IL_CAL_UNIX));
            $form->addItem($issued_on);
        } else {
            $issued_on->setDate($this->certificate->getIssuedOn());
            $form->addItem($form->getReadOnlyItem($issued_on));
        }

        if ($this->certificate != null) {
            $duplicates = $this->certificate->getDuplicateDates();
            if ($duplicates) {
                $caption = array();
                foreach ($duplicates as $date) {
                    $caption[] = ilDatePresentation::formatDate($date);
                }
                $duplicate_issued_on = new ilNonEditableValueGUI(
                    $this->lng->txt("adn_duplicate_issued_on"),
                    "dissued_on"
                );
                $duplicate_issued_on->setValue(implode("<br />", $caption));
                $form->addItem($duplicate_issued_on);
            }
        }

        // valid until
        $valid_until = new ilDateTimeInputGUI($this->lng->txt("adn_valid_until"), "valid_until");
        $valid_until->setRequired(true);
        // extension: valid until today as default
        if ($a_mode == "extend") {
            $vu_date = new ilDateTime(time(), IL_CAL_UNIX);
            $valid_until->setDate($vu_date);
        } else {
            $valid_until->setDate($this->certificate->getValidUntil());
        }
        if ($a_mode != "duplicate") {
            $form->addItem($valid_until);
        }
        // duplicate: valid until cannot be changed
        else {
            $form->addItem($form->getReadOnlyItem($valid_until));
        }

        // duplicate: 2nd issued on
        if ($a_mode == "duplicate") {
            $duplicate_issued_on = new ilDateTimeInputGUI(
                $this->lng->txt("adn_duplicate_issued_on"),
                "duplicate_issued_on"
            );
            $duplicate_issued_on->setRequired(true);
            $duplicate_issued_on->setDate(new ilDate(time(), IL_CAL_UNIX));
            $form->addItem($duplicate_issued_on);
        }

        // signed by
        $signed_by = new ilTextInputGUI($this->lng->txt("adn_signed_by"), "signed_by");
        $signed_by->setRequired(true);
        $form->addItem($signed_by);
        // extension/duplicate: signed by defaults to current user
        if ($a_mode == "extend" || $a_mode == "duplicate") {
            $signed_by->setValue($this->user->getLastname() . ", " . $this->user->getFirstname());
        } else {
            $signed_by->setValue($this->certificate->getSignedBy());
        }

        // proof
        if (($a_mode != "duplicate") &&
            ($a_mode == "extend" || $this->certificate->getIsExtension())) {
            $proof = new ilCheckboxGroupInputGUI(
                $this->lng->txt("adn_proof"),
                "proof"
            );
            $values = array();
            foreach (adnCertificate::getProofTypes() as $id => $caption) {
                $cb = new ilCheckboxOption($caption, $id);
                $proof->addOption($cb);
                if ($this->certificate != null && $this->certificate->getProof($id)) {
                    $values[] = $id;
                }
            }
            $proof->setValue($values);
            $proof->setRequired(true);
            $form->addItem($proof);
        }

        // status
        if ($a_mode == "show" || $a_mode == "edit") {
            // status
            $status = new ilNonEditableValueGUI($this->lng->txt("adn_status"), "");
            $form->addItem($status);

            // if certificate is not valid anymore, overwrite status
            $today = new ilDate(time(), IL_CAL_UNIX);
            $today = $today->get(IL_CAL_DATE);
            if ($this->certificate->getValidUntil()->get(IL_CAL_DATE) < $today) {
                $this->certificate->setStatus(adnCertificate::STATUS_INVALID);
            }

            if ($this->certificate->getStatus() == adnCertificate::STATUS_INVALID) {
                $status->setValue($this->lng->txt("adn_invalid"));
            } else {
                $status->setValue($this->lng->txt("adn_valid"));
            }
        }

        // command buttons
        if ($a_mode == "extend") {
            if ($a_final_confirmation) {
                $form->addCommandButton("saveExtension", $this->lng->txt("adn_create_extension"));
            } else {
                $form->addCommandButton("confirmSaveExtension", $this->lng->txt("adn_create_extension"));
            }
        }
        if ($a_mode == "duplicate") {
            $form->addCommandButton("saveDuplicate", $this->lng->txt("adn_create_duplicate"));
        }
        if ($a_mode == "edit") {
            $form->addCommandButton("update", $this->lng->txt("save"));
        }
        $form->addCommandButton("afterExtension", $this->lng->txt("cancel"));
        
        $form->setFormAction($this->ctrl->getFormAction($this));

        return $form;
    }

    /**
     * Show certificates of professional
     */
    protected function showCertificatesOfProfessional()
    {

        // add back tab
        $this->tabs->setBackTarget(
            $this->lng->txt("back"),
            $this->ctrl->getLinkTarget($this, "listCertificates")
        );

        $this->ctrl->setParameter($this, "cp_id", (int) $_GET["cp_id"]);

        // table of certificates
        include_once("./Services/ADN/CP/classes/class.adnCertificateTableGUI.php");
        $table = new adnCertificateTableGUI(
            $this,
            "showCertificatesOfProfessional",
            (int) $_GET["cp_id"]
        );

        // output table
        $this->tpl->setContent($table->getHTML());
    }


    ////
    //// Edit
    ////

    /**
     * Edit certificate form
     *
     * @param ilPropertyFormGUI $a_form
     */
    public function edit(ilPropertyFormGUI $a_form = null)
    {

        // add back tab
        $this->tabs->setBackTarget(
            $this->lng->txt("back"),
            $this->ctrl->getLinkTarget($this, "listCertificates")
        );

        if (is_object($a_form)) {
            $form = $a_form;
        } else {
            $form = $this->initCertificateForm("edit");
        }
        $this->tpl->setContent($form->getHTML());
    }

    /**
     * Update duplicate
     */
    protected function update()
    {

        include_once("./Services/ADN/ES/classes/class.adnCertificate.php");

        $form = $this->initCertificateForm("edit");

        // check input
        if ($form->checkInput()) {
            // certificate types
            foreach (adnCertificate::getCertificateTypes() as $id => $caption) {
                if (in_array($id, $_POST["cert_type"])) {
                    $this->certificate->setType($id, true);
                } else {
                    $this->certificate->setType($id, false);
                }
            }

            // signed by
            $this->certificate->setSignedBy($form->getInput("signed_by"));

            // valid until
            $valid_date = $form->getItemByPostVar("valid_until");
            $this->certificate->setValidUntil($valid_date->getDate());

            // proof
            if ($this->certificate->getIsExtension()) {
                foreach (adnCertificate::getProofTypes() as $id => $caption) {
                    if (in_array($id, $_POST["proof"])) {
                        $this->certificate->setProof($id, true);
                    } else {
                        $this->certificate->setProof($id, false);
                    }
                }
            }

            // update certificate
            $this->certificate->update();

            // show success message and return to list
            ilUtil::sendSuccess($this->lng->txt("adn_certificate_updated"), true);
            $this->ctrl->redirect($this, "listCertificates");
        }

        // input not valid: show form again
        $form->setValuesByPost();
        $this->edit($form);
    }

    
    ////
    //// Certificate Extension
    ////

    /**
     * Extend certificate form
     *
     * @param ilPropertyFormGUI $a_form
     */
    public function extendCertificate(ilPropertyFormGUI $a_form = null)
    {

        // add back tab
        $this->tabs->setBackTarget(
            $this->lng->txt("back"),
            $this->ctrl->getLinkTarget($this, "afterExtension")
        );

        if (is_object($a_form)) {
            $form = $a_form;
        } else {
            $form = $this->initCertificateForm("extend");
        }
        $this->tpl->setContent($form->getHTML());
    }

    /**
     * Confirm save extension
     */
    protected function confirmSaveExtension()
    {

        $form = $this->initCertificateForm("extend", true);

        // check input
        if ($form->checkInput()) {
            // check if certificate is not valid anymore
            if ($this->certificate != null && !$this->certificate->isValid()) {
                ilUtil::sendQuestion($this->lng->txt("adn_cert_not_valid_save_anyway"));
            }
            ilUtil::sendInfo($this->lng->txt("adn_please_check_certificate"));
            $form->setValuesByPost();

            // insert correct number
            include_once("./Services/ADN/AD/classes/class.adnUser.php");
            $ne = $form->getItemByPostVar("adn_nr");
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
            $form->addCommandButton("confirmSaveExtension", $this->lng->txt("adn_create_extension"));
            $form->addCommandButton("afterExtension", $this->lng->txt("cancel"));

            $form->setValuesByPost();

            // insert correct number
            include_once("./Services/ADN/AD/classes/class.adnUser.php");
            $ne = $form->getItemByPostVar("adn_nr");
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

            $this->extendCertificate($form);
        }
    }

    /**
     * Save extension
     */
    protected function saveExtension()
    {

        $form = $this->initCertificateForm("extend");

        // check input
        if ($form->checkInput()) {
            include_once("./Services/ADN/ES/classes/class.adnCertificate.php");

            if ($this->certificate == null && $this->pid > 0) {
                $this->certificate = new adnCertificate();
                $this->certificate->setCertifiedProfessionalId($this->pid);
            }

            // certificate types
            foreach (adnCertificate::getCertificateTypes() as $id => $caption) {
                if (in_array($id, $_POST["cert_type"])) {
                    $this->certificate->setType($id, true);
                } else {
                    $this->certificate->setType($id, false);
                }
            }

            // issued by wmo
            $this->certificate->setIssuedByWmo($form->getInput("issued_by_wmo"));

            // signed by
            $this->certificate->setSignedBy($form->getInput("signed_by"));

            // issued on
            $issued_date = $form->getItemByPostVar("issued_on");
            $this->certificate->setIssuedOn($issued_date->getDate());

            // valid until
            $issued_date = $form->getItemByPostVar("valid_until");
            $this->certificate->setValidUntil($issued_date->getDate());

            // proof
            foreach (adnCertificate::getProofTypes() as $id => $caption) {
                if (in_array($id, $_POST["proof"])) {
                    $this->certificate->setProof($id, true);
                } else {
                    $this->certificate->setProof($id, false);
                }
            }

            // save certificate
            $this->certificate->createExtension();
            
            include_once './Services/ADN/Report/exceptions/class.adnReportException.php';
            try {
                include_once("./Services/ADN/Report/classes/class.adnReportCertificate.php");
                $report = new adnReportCertificate();
                $report->createExtension($this->certificate->getId());
                
                ilUtil::sendSuccess($this->lng->txt('adn_extension_created'), true);
                $this->afterExtension();
            } catch (adnReportException $e) {
                ilUtil::sendFailure($e->getMessage(), true);
                $this->afterExtension();
            }
        }

        // input not valid: show form again
        $form->setValuesByPost();

        // insert correct number
        include_once("./Services/ADN/AD/classes/class.adnUser.php");
        $ne = $form->getItemByPostVar("adn_nr");
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

        $this->extendCertificate($form);
    }

    /**
     * After extension creation or cancel
     *
     * @param
     * @return
     */
    protected function afterExtension()
    {

        if ($this->certificate == null && $this->pid > 0) {
            // #13
            $this->ctrl->redirectByClass(array("adnCertifiedProfessionalGUI", "adnPersonalDataMaintenanceGUI"), 'listPersonalData');
        } else {
            $this->ctrl->redirect($this, 'listCertificates');
        }
    }


    ////
    //// Certificate Duplicates
    ////

    /**
     * Duplicate certificate form
     *
     * @param ilPropertyFormGUI $a_form
     */
    public function duplicateCertificate(ilPropertyFormGUI $a_form = null)
    {

        // add back tab
        $this->tabs->setBackTarget(
            $this->lng->txt("back"),
            $this->ctrl->getLinkTarget($this, "listCertificates")
        );

        if (is_object($a_form)) {
            $form = $a_form;
        } else {
            $form = $this->initCertificateForm("duplicate");
        }
        $this->tpl->setContent($form->getHTML());
    }

    /**
     * Save duplicate
     */
    protected function saveDuplicate()
    {

        include_once("./Services/ADN/ES/classes/class.adnCertificate.php");

        $form = $this->initCertificateForm("duplicate");

        // check input
        if ($form->checkInput()) {
            // signed by
            $this->certificate->setSignedBy($form->getInput("signed_by"));

            // issued on
            $duplicate_issued_date = $form->getItemByPostVar("duplicate_issued_on");

            // save certificate
            $this->certificate->createDuplicate($duplicate_issued_date->getDate());

            // create report
            include_once './Services/ADN/Report/exceptions/class.adnReportException.php';
            try {
                include_once("./Services/ADN/Report/classes/class.adnReportCertificate.php");
                $report = new adnReportCertificate();
                $report->createDuplicate($this->certificate->getId());
                
                ilUtil::sendSuccess($this->lng->txt('adn_duplicate_created'), true);
                $this->ctrl->redirect($this, 'listCertificates');
            } catch (adnReportException $e) {
                ilUtil::sendFailure($e->getMessage(), true);
                $this->ctrl->redirect($this, 'listCertificates');
            }
        }

        // input not valid: show form again
        $form->getItemByPostVar("signed_by")->setValue($form->getInput("signed_by"));
        $this->duplicateCertificate($form);
    }

    
    ////
    //// Invoice
    ////

    /**
     * Form to generate invoice
     *
     * @param ilPropertyFormGUI $a_form
     */
    protected function generateInvoice(ilPropertyFormGUI $a_form = null)
    {

        // add back tab
        $this->tabs->setBackTarget(
            $this->lng->txt("back"),
            $this->ctrl->getLinkTarget($this, "listCertificates")
        );

        if ($a_form) {
            $form = $a_form;
        } else {
            $form = $this->initInvoiceForm();
        }
        $this->tpl->setContent($form->getHTML());
    }

    /**
     * Init invoice form.
     *
     * @return ilPropertyFormGUI
     */
    protected function initInvoiceForm()
    {

        // get form object and add input fields
        include_once("Services/Form/classes/class.ilPropertyFormGUI.php");
        $form = new ilPropertyFormGUI();

        $form->setTitle($this->lng->txt("adn_invoice"));

        $date = new ilDateTimeInputGUI($this->lng->txt("adn_due_date"), "date");
        $date->setRequired(true);
        $form->addItem($date);

        $zuev = new ilTextInputGUI($this->lng->txt("adn_zuev_number"), "zuev");
        $zuev->setRequired(true);
        $zuev->setMaxLength(50);
        $form->addItem($zuev);

        include_once("./Services/ADN/AD/classes/class.adnUser.php");
        $current_wmo = adnUser::lookupWmoId();
        if ($current_wmo) {
            include_once("./Services/ADN/MD/classes/class.adnWMO.php");
            $wmo = new adnWMO($current_wmo);
            $types = array();
            
            $cost = $wmo->getCostCertificate();
            $types[adnWMO::COST_CERTIFICATE] = $this->lng->txt("adn_wmo_cost_certificate") .
                " (" . $cost["no"] . " - " . $cost["value"] . " EUR)";
            $cost = $wmo->getCostDuplicate();
            $types[adnWMO::COST_DUPLICATE] = $this->lng->txt("adn_wmo_cost_duplicate") .
                " (" . $cost["no"] . " - " . $cost["value"] . " EUR)";
            $cost = $wmo->getCostExtension();
            $types[adnWMO::COST_EXTENSION] = $this->lng->txt("adn_wmo_cost_extension") .
                " (" . $cost["no"] . " - " . $cost["value"] . " EUR)";
            $cost = $wmo->getCostExam();
            $types[adnWMO::COST_EXAM] = $this->lng->txt("adn_wmo_cost_exam") .
                " (" . $cost["no"] . " - " . $cost["value"] . " EUR)";
            $cost = $wmo->getCostExamGasChem();
            $types[adnWMO::COST_EXAM_GAS_CHEM] = $this->lng->txt("adn_wmo_cost_exam_gas_chem") .
                " (" . $cost["no"] . " - " . $cost["value"] . " EUR)";
        }

        $type = new ilSelectInputGUI($this->lng->txt("adn_type_of_cost"), "type");
        $type->setOptions($types);
        $type->setRequired(true);

        $form->addItem($type);
        
        $form->addCommandButton("saveInvoice", $this->lng->txt("adn_generate_invoice"));
        $form->addCommandButton("listCertificates", $this->lng->txt("cancel"));
        
        $form->setFormAction($this->ctrl->getFormAction($this));

        return $form;
    }

    /**
     * Save invoice data
     */
    protected function saveInvoice()
    {
        
        $form = $this->initInvoiceForm();

        if ($form->checkInput()) {
            include_once './Services/ADN/Report/exceptions/class.adnReportException.php';
            try {
                include_once("./Services/ADN/Report/classes/class.adnReportInvoice.php");
                $report = new adnReportInvoice($this->certificate);
                $report->setInvoiceType($form->getInput('type'));
                $report->setDue($form->getItemByPostVar('date')->getDate());
                $report->setCode($form->getInput('zuev'));
                $report->create();
                
                ilUtil::sendSuccess($this->lng->txt('adn_report_invoice_created'), true);
                $this->ctrl->redirect($this, 'listCertificates');
            } catch (adnReportException $e) {
                ilUtil::sendFailure($e->getMessage(), true);
                $this->ctrl->redirect($this, 'listCertificates');
            }
        }

        $form->setValuesByPost();
        $this->generateInvoice($form);
    }
    
    /**
     * Download invoice
     */
    protected function downloadInvoice()
    {

        if (!(int) $_REQUEST['ct_id']) {
            ilUtil::sendFailure($this->lng->txt('select_one'));
            $this->ctrl->redirect($this, 'listCertificates');
        }
        include_once("./Services/ADN/Report/classes/class.adnReportInvoice.php");
        ilUtil::deliverFile(
            adnReportInvoice::getInvoice((int) $_REQUEST['ct_id']),
            "Kostenbescheid.pdf",
            'application/pdf'
        );
    }

    /**
     * Download extension
     */
    protected function downloadExtension()
    {

        if (!(int) $_REQUEST['ct_id']) {
            ilUtil::sendFailure($this->lng->txt('select_one'));
            $this->ctrl->redirect($this, 'listCertificates');
        }
        include_once("./Services/ADN/Report/classes/class.adnReportCertificate.php");
        ilUtil::deliverFile(
            adnReportCertificate::lookupCertificate((int) $_REQUEST['ct_id']),
            "Verlaengerung.pdf",
            'application/pdf'
        );
    }

    /**
     * Download extension
     */
    protected function downloadDuplicate()
    {

        if (!(int) $_REQUEST['ct_id']) {
            ilUtil::sendFailure($this->lng->txt('select_one'));
            $this->ctrl->redirect($this, 'listCertificates');
        }
        include_once("./Services/ADN/Report/classes/class.adnReportCertificate.php");
        ilUtil::deliverFile(
            adnReportCertificate::lookupCertificate((int) $_REQUEST['ct_id']),
            "Ersatzausfertigung.pdf",
            'application/pdf'
        );
    }

    // cr-008 start
    /**
     * Download certificate
     */
    protected function downloadCertificate()
    {

        if (!(int) $_REQUEST['ct_id']) {
            ilUtil::sendFailure($this->lng->txt('select_one'));
            $this->ctrl->redirect($this, 'listCertificates');
        }

        $ct_id = (int) $_REQUEST['ct_id'];

        include_once './Services/ADN/Report/exceptions/class.adnReportException.php';
        try {
            include_once './Services/ADN/EP/classes/class.adnExaminationEvent.php';
            include_once("./Services/ADN/Report/classes/class.adnReportCertificate.php");
            if (adnReportCertificate::lookupCertificate($ct_id) != "") {	// create if not existent
                $report = new adnReportCertificate(array($ct_id));
                $report->create();
            }

            include_once("./Services/ADN/Report/classes/class.adnReportCertificate.php");
            ilUtil::deliverFile(
                adnReportCertificate::lookupCertificate($ct_id),
                "Bescheinigung.pdf",
                'application/pdf'
            );
        } catch (adnReportException $e) {
            ilUtil::sendFailure($e->getMessage(), true);
            $this->ctrl->redirect($this, 'listCertificates');
        }
    }
    // cr-008 end
}
