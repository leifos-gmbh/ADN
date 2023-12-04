<?php
/* Copyright (c) 2010 Leifos, GPL, see docs/LICENSE */

use ILIAS\FileUpload\FileUpload;
use ILIAS\FileUpload\Exception\IllegalStateException;

/**
 * Candidate GUI class (preparation context)
 *
 * Candidate list, forms and persistence
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.de>
 * @version $Id: class.adnPreparationCandidateGUI.php 45509 2013-10-16 10:16:00Z smeyer $
 *
 * @ilCtrl_Calls adnPreparationCandidateGUI:
 *
 * @ingroup ServicesADN
 */
class adnPreparationCandidateGUI
{
    // cr-008 start
    const MODE_CANDIDATE = "";
    const MODE_GENERAL = "general";
    // cr-008 end

    // current candidate object
    protected $candidate = null;

    // cr-008 start
    /**
     * @var string
     */
    protected $mode = "";
    // cr-008 end

    protected ilLogger $logger;
    
    /**
     * Constructor
     */
    public function __construct()
    {
        global $DIC;
        global $ilCtrl;

        $this->logger = $DIC->logger()->adn();

        // save candidate ID through requests, cr-008 added mode
        $ilCtrl->saveParameter($this, array("cd_id", "mode"));

        // cr-008 start
        $this->mode = $_GET["mode"];
        // cr-008 end
        
        $this->readCandidate();
    }
    
    /**
     * Execute command
     */
    public function executeCommand()
    {
        global $ilCtrl, $lng, $tpl;

        // cr-008 start
        if ($this->mode == self::MODE_GENERAL) {
            $tpl->setTitle($lng->txt("adn_ad_add_person"));
        } else {
            $tpl->setTitle($lng->txt("adn_ep") . " - " . $lng->txt("adn_ep_ecs"));
            adnIcon::setTitleIcon("ep_ecs");
        }
        // cr-008 end
        
        $next_class = $ilCtrl->getNextClass();

        // forward command to next gui class in control flow
        switch ($next_class) {
            // no next class:
            // this class is responsible to process the command
            default:
                $cmd = $ilCtrl->getCmd("listCandidates");
                
                switch ($cmd) {
                    // commands that need read permission
                    case "listCandidates":
                    case "listCandidatesToggle":
                    case "applyFilter":
                    case "resetFilter":
                    // cr-008 start
                    case "listPersonData":
                    // cr-008 end
                        if (adnPerm::check(adnPerm::EP, adnPerm::READ)) {
                            $this->$cmd();
                        }
                        break;
                    
                    // commands that need write permission
                    case "editCandidate":
                    case "updateCandidate":
                    case "updateCandidateAndEditTraining":
                    case "createCandidate":
                    case "saveCandidate":
                    case "saveCandidateAndEditTraining":
                    case "confirmCandidatesDeletion":
                    case "deleteCandidates":
                    case "showEventList":
                    case "applyTrainingEventFilter":
                    case "resetTrainingEventFilter":
                    case "saveLastTraining":
                    case "saveCandidateAndAddExtension":
                    case "saveCandidateAndListPersonData":
                        if (adnPerm::check(adnPerm::EP, adnPerm::WRITE)) {
                            $this->$cmd();
                        }
                        break;
                    
                }
                break;
        }
    }
    
    /**
     * Read candidate
     *
     * @param int $a_id
     */
    protected function readCandidate($a_id = null)
    {
        if (!$a_id) {
            $a_id = (int) $_GET["cd_id"];
        }
        if ($a_id > 0) {
            include_once("./Services/ADN/ES/classes/class.adnCertifiedProfessional.php");
            $this->candidate = new adnCertifiedProfessional($a_id);
        }
    }

    /**
     * Toggle candidate view (professionals and prospects)
     */
    protected function listCandidatesToggle()
    {
        $_SESSION["adn_cand_cpr"] = (bool) $_POST["ct_cpr"];
        $_SESSION["adn_cand_pro"] = (bool) $_POST["ct_pro"];

        $this->listCandidates();
    }
    
    /**
     * List all candidates
     */
    protected function listCandidates()
    {
        global $tpl, $ilToolbar, $lng, $ilCtrl;

        // cr-008 start
        if ($this->mode == self::MODE_GENERAL) {
            return $this->listPersonData();
        }
        // cr-008 end

        // professional / prospects toggles
        include_once "Services/Form/classes/class.ilPropertyFormGUI.php";
        $checkbox = new ilCheckboxInputGUI($lng->txt("adn_show_certified_professionals"), "ct_cpr");
        if ($_SESSION["adn_cand_cpr"]) {
            $checkbox->setChecked(true);
        }
        $ilToolbar->addInputItem($checkbox, true);
        $checkbox = new ilCheckboxInputGUI($lng->txt("adn_show_prospects"), "ct_pro");
        if ($_SESSION["adn_cand_pro"]) {
            $checkbox->setChecked(true);
        }
        $ilToolbar->addInputItem($checkbox, true);
        $ilToolbar->addFormButton($lng->txt("adn_update_view"), "listCandidatesToggle");
        $ilToolbar->setFormAction($ilCtrl->getFormAction($this), "listCandidatesToggle");
        $ilToolbar->addSeparator();

        if (adnPerm::check(adnPerm::EP, adnPerm::WRITE)) {
            $ilToolbar->addButton(
                $lng->txt("adn_add_candidate"),
                $ilCtrl->getLinkTarget($this, "createCandidate")
            );
        }

        // table of examination candidates
        include_once("./Services/ADN/EP/classes/class.adnPreparationCandidateTableGUI.php");
        $table = new adnPreparationCandidateTableGUI(
            $this,
            "listCandidates",
            $_SESSION["adn_cand_cpr"],
            $_SESSION["adn_cand_pro"]
        );
        
        // output table
        $tpl->setContent($table->getHTML());
    }

    /**
     * Apply filter settings (from table gui)
     */
    protected function applyFilter()
    {
        include_once("./Services/ADN/EP/classes/class.adnPreparationCandidateTableGUI.php");
        $table = new adnPreparationCandidateTableGUI($this, "listCandidates");
        $table->resetOffset();
        $table->writeFilterToSession();

        $this->listCandidates();
    }

    /**
     * Reset filter settings (from table gui)
     */
    protected function resetFilter()
    {
        include_once("./Services/ADN/EP/classes/class.adnPreparationCandidateTableGUI.php");
        $table = new adnPreparationCandidateTableGUI($this, "listCandidates");
        $table->resetOffset();
        $table->resetFilter();

        $this->listCandidates();
    }

    /**
     * Create candidate form
     *
     * @param ilPropertyFormGUI $a_form
     */
    protected function createCandidate(ilPropertyFormGUI $a_form = null)
    {
        global $tpl, $lng, $ilTabs, $ilCtrl;

        $ilTabs->setBackTarget($lng->txt("back"), $ilCtrl->getLinkTarget($this, "listCandidates"));

        if (!$a_form) {
            $a_form = $this->initCandidateForm("create");
        }
        $tpl->setContent($a_form->getHTML());
    }
    
    /**
     * Edit candidate form
     *
     * @param ilPropertyFormGUI $a_form
     */
    protected function editCandidate(ilPropertyFormGUI $a_form = null)
    {
        global $tpl, $lng, $ilTabs, $ilCtrl;

        $ilTabs->setBackTarget($lng->txt("back"), $ilCtrl->getLinkTarget($this, "listCandidates"));

        if (!$a_form) {
            $a_form = $this->initCandidateForm("edit");
        }
        $tpl->setContent($a_form->getHTML());
    }

    /**
     * Build candidate form
     *
     * @param string $a_mode create | edit
     * @return ilPropertyFormGUI
     */
    protected function initCandidateForm($a_mode = "create")
    {
        global  $lng, $ilCtrl;

        include_once "Services/Form/classes/class.ilPropertyFormGUI.php";
        $form = new ilPropertyFormGUI();
        $form->setFormAction($ilCtrl->getFormAction($this, "listCandidates"));

        $salutation = new ilSelectInputGUI($lng->txt("adn_salutation"), "salutation");
        $salutation->setOptions(array("m" => $lng->txt("salutation_m"),
                "f" => $lng->txt("salutation_f")));
        $salutation->setRequired(true);
        $form->addItem($salutation);

        $name = new ilTextInputGUI($lng->txt("adn_last_name"), "last_name");
        $name->setMaxLength(50);
        $name->setRequired(true);
        $form->addItem($name);

        $first_name = new ilTextInputGUI($lng->txt("adn_first_name"), "first_name");
        $first_name->setMaxLength(50);
        $first_name->setRequired(true);
        $form->addItem($first_name);

        $pic = new ilImageFileInputGUI($lng->txt('adn_card_form_photo'), 'card_photo');
        $pic->setALlowDeletion(true);
        $pic->setUseCache(false);
        if ($this->candidate instanceof adnCertifiedProfessional) {
            $pic->setImage($this->candidate->getImageHandler()->getAbsolutePath() ?? '');
        }
        $form->addItem($pic);

        $birthdate = new ilDateTimeInputGUI($lng->txt("adn_birthdate"), "birthdate");
        $birthdate->setRequired(true);
        $birthdate->setStartYear(date("Y") - 100);
        $form->addItem($birthdate);

        // foreign key
        include_once "Services/ADN/MD/classes/class.adnCountry.php";
        $countries = array();
        if ($a_mode != "create") {
            $countries[] = $this->candidate->getCitizenship();
            if ($this->candidate->getPostalCountry()) {
                $countries[] = $this->candidate->getPostalCountry();
            }
            if ($this->candidate->getShippingCountry()) {
                $countries[] = $this->candidate->getShippingCountry();
            }
        }
        $countries = adnCountry::getCountriesSelect($countries);
        
        $citizenship = new ilSelectInputGUI($lng->txt("adn_citizenship"), "citizenship");
        $citizenship->setOptions($countries);
        $citizenship->setRequired(true);
        $form->addItem($citizenship);

        $type = new ilSelectInputGUI($lng->txt("adn_type_of_examination"), "type");
        include_once "Services/ADN/ED/classes/class.adnSubjectArea.php";
        $options = array("" => $lng->txt("adn_filter_none")) + adnSubjectArea::getAllAreas();
        $type->setOptions($options);
        $form->addItem($type);

        $registered = new ilCheckboxInputGUI($lng->txt("adn_applied_for_examination"), "applied");
        $form->addItem($registered);

        if ($this->mode != self::MODE_GENERAL) {
            $foreign = new ilCheckboxInputGUI($lng->txt("adn_foreign_certificate"), "foreign");
            $form->addItem($foreign);
        }

        $foreign_cert_handed_id = new ilCheckboxInputGUI($lng->txt("adn_foreign_cert_handed_in"), "foreign_cert_handed_in");
        $form->addItem($foreign_cert_handed_id);

        $phone = new ilTextInputGUI($lng->txt("adn_phone"), "phone");
        $phone->setMaxLength(30);
        $phone->setSize(30);
        $form->addItem($phone);

        $email = new ilEmailInputGUI($lng->txt("adn_email"), "email");
        $form->addItem($email);

        // foreign key
        include_once "Services/ADN/MD/classes/class.adnWMO.php";
        $wmos = array();
        if ($a_mode != "create") {
            $wmos[] = $this->candidate->getRegisteredBy();
            if ($this->candidate->getBlockedBy()) {
                $wmos[] = $this->candidate->getBlockedBy();
            }
        }
        $wmos = adnWMO::getWMOsSelect($wmos);

        $registered_by = new ilSelectInputGUI($lng->txt("adn_registered_by"), "registered_by");
        $registered_by->setOptions($wmos);
        $registered_by->setRequired(true);
        $form->addItem($registered_by);

        $comment = new ilTextAreaInputGUI($lng->txt("adn_comment"), "comment");
        $comment->setCols(80);
        $comment->setRows(5);
        $form->addItem($comment);

        $holdback = new ilCheckboxInputGUI($lng->txt("adn_holdback"), "holdback");
        $form->addItem($holdback);

        $holdback_by = new ilSelectInputGUI($lng->txt("adn_holdback_by"), "holdback_by");
        $holdback_by->setOptions($wmos);
        $holdback->addSubItem($holdback_by);

        include_once("./Services/ADN/AD/classes/class.adnUser.php");
        $current_wmo = adnUser::lookupWmoId();
        $holdback_by->setValue($current_wmo);

        $holdback_until = new ilDateTimeInputGUI($lng->txt("adn_holdback_until"), "holdback_until");
        $holdback->addSubItem($holdback_until);



        $header = new ilFormSectionHeaderGUI();
        $header->setTitle($lng->txt("adn_permanent_address"));
        $form->addItem($header);

        $country = new ilSelectInputGUI($lng->txt("adn_country"), "country");
        $country->setOptions($countries);
        $country->setRequired(true);
        $form->addItem($country);

        $zip = new ilTextInputGUI($lng->txt("adn_zip"), "zip");
        $zip->setMaxLength(10);
        $zip->setSize(10);
        $zip->setRequired(true);
        $form->addItem($zip);

        $city = new ilTextInputGUI($lng->txt("adn_city"), "city");
        $city->setMaxLength(50);
        $city->setRequired(true);
        $form->addItem($city);

        $street = new ilTextInputGUI($lng->txt("adn_street"), "street");
        $street->setMaxLength(50);
        $street->setRequired(true);
        $form->addItem($street);

        $hno = new ilTextInputGUI($lng->txt("adn_house_number"), "hno");
        $hno->setMaxLength(10);
        $hno->setSize(10);
        $hno->setRequired(true);
        $form->addItem($hno);



        $header = new ilFormSectionHeaderGUI();
        $header->setTitle($lng->txt("adn_shipping_address"));
        $form->addItem($header);

        $cb = new ilCheckboxInputGUI(
            $lng->txt("adn_shipping_address_activated"),
            "shipping_address_activated"
        );
        $form->addItem($cb);

        $ssalutation = new ilSelectInputGUI($lng->txt("adn_salutation"), "ssalutation");
        $ssalutation->setRequired(true);
        $ssalutation->setOptions(array("m" => $lng->txt("salutation_m"),
            "f" => $lng->txt("salutation_f")));
        $cb->addSubItem($ssalutation);

        $sname = new ilTextInputGUI($lng->txt("adn_last_name"), "slast_name");
        $sname->setRequired(true);
        $sname->setMaxLength(50);
        $cb->addSubItem($sname);

        $sfirst_name = new ilTextInputGUI($lng->txt("adn_first_name"), "sfirst_name");
        $sfirst_name->setRequired(true);
        $sfirst_name->setMaxLength(50);
        $cb->addSubItem($sfirst_name);

        $scountry = new ilSelectInputGUI($lng->txt("adn_country"), "scountry");
        $scountry->setRequired(true);
        $scountry->setOptions($countries);
        $cb->addSubItem($scountry);

        $szip = new ilTextInputGUI($lng->txt("adn_zip"), "szip");
        $szip->setRequired(true);
        $szip->setMaxLength(10);
        $szip->setSize(10);
        $cb->addSubItem($szip);

        $scity = new ilTextInputGUI($lng->txt("adn_city"), "scity");
        $scity->setRequired(true);
        $scity->setMaxLength(50);
        $cb->addSubItem($scity);

        $sstreet = new ilTextInputGUI($lng->txt("adn_street"), "sstreet");
        $sstreet->setRequired(true);
        $sstreet->setMaxLength(50);
        $cb->addSubItem($sstreet);

        $shno = new ilTextInputGUI($lng->txt("adn_house_number"), "shno");
        $shno->setRequired(true);
        $shno->setMaxLength(10);
        $shno->setSize(10);
        $cb->addSubItem($shno);

        if ($a_mode == "create") {
            // preset: wmo of current user
            $wmo_id = adnUser::lookupWMOId();
            $registered_by->setValue($wmo_id);
            $holdback_by->setValue($wmo_id);


            // cr-008 start
            if ($this->mode == self::MODE_GENERAL) {
                $form->addCommandButton(
                    "saveCandidateAndListPersonData",
                    $lng->txt("save")
                );
                $form->addCommandButton(
                    "saveCandidateAndAddExtension",
                    $lng->txt("adn_save_and_add_extension")
                );
                $form->addCommandButton("listPersonData", $lng->txt("cancel"));
                $form->setTitle($lng->txt("adn_ad_add_person"));
            } else {
                $form->addCommandButton("saveCandidate", $lng->txt("save"));

                $form->addCommandButton(
                    "saveCandidateAndEditTraining",
                    $lng->txt("adn_save_and_edit_training")
                );
                $form->addCommandButton("listCandidates", $lng->txt("cancel"));
                $form->setTitle($lng->txt("adn_add_candidate"));
            }
            // cr-008 end
        } else {
            $wmo_id = adnUser::lookupWMOId();
            
            // preset: wmo of current user if no value is set
            if (!$this->candidate->getRegisteredBy()) {
                $registered_by->setValue($wmo_id);
            }
            
            $salutation->setValue($this->candidate->getSalutation());
            $name->setValue($this->candidate->getLastName());
            $first_name->setValue($this->candidate->getFirstName());
            $birthdate->setDate($this->candidate->getBirthdate());
            $citizenship->setValue($this->candidate->getCitizenship());
            $type->setValue($this->candidate->getSubjectArea());
            $registered->setChecked($this->candidate->isRegisteredForExam());
            if ($this->mode != self::MODE_GENERAL) {
                $foreign->setChecked($this->candidate->hasForeignCertificate());
            }
            $foreign_cert_handed_id->setChecked($this->candidate->hasForeignCertificateHandedIn());
            $phone->setValue($this->candidate->getPhone());
            $email->setValue($this->candidate->getEmail());
            $registered_by->setValue($this->candidate->getRegisteredBy());
            $comment->setValue($this->candidate->getComment());
            $country->setValue($this->candidate->getPostalCountry());
            $zip->setValue($this->candidate->getPostalCode());
            $city->setValue($this->candidate->getPostalCity());
            $street->setValue($this->candidate->getPostalStreet());
            $hno->setValue($this->candidate->getPostalStreetNumber());
            $ssalutation->setValue($this->candidate->getShippingSalutation());
            $sname->setValue($this->candidate->getShippingLastName());
            $sfirst_name->setValue($this->candidate->getShippingFirstName());
            $scountry->setValue($this->candidate->getShippingCountry());
            $szip->setValue($this->candidate->getShippingCode());
            $scity->setValue($this->candidate->getShippingCity());
            $sstreet->setValue($this->candidate->getShippingStreet());
            $shno->setValue($this->candidate->getShippingStreetNumber());

            if ($this->candidate->isShippingActive()) {
                $cb->setChecked(true);
            }

            $blocked = $this->candidate->getBlockedBy();
            if ($blocked) {
                $holdback->setChecked(true);
                $holdback_by->setValue($this->candidate->getBlockedBy());
                $holdback_until->setDate($this->candidate->getBlockedUntil());
            }

            $last_event = $this->candidate->getLastEvent();
            if ($last_event) {
                include_once "Services/ADN/TA/classes/class.adnTrainingEvent.php";
                include_once "Services/ADN/TA/classes/class.adnTrainingProvider.php";
                include_once "Services/ADN/TA/classes/class.adnTypesOfTraining.php";
                include_once "Services/ADN/TA/classes/class.adnTrainingFacility.php";
                $levent = new adnTrainingEvent($last_event);
                
                $header = new ilFormSectionHeaderGUI();
                $header->setTitle($lng->txt("adn_current_training"));
                $form->addItem($header);

                $provider = new ilNonEditableValueGUI($lng->txt("adn_training_provider"));
                $provider->setValue(adnTrainingProvider::lookupName($levent->getProvider()));
                $form->addItem($provider);

                $training = new ilNonEditableValueGUI($lng->txt("adn_type_of_training"));
                $training->setValue(adnTypesOfTraining::getTextRepresentation($levent->getType()));
                $form->addItem($training);

                $event = new ilNonEditableValueGUI($lng->txt("adn_training"));
                $event->setValue(ilDatePresentation::formatDate($levent->getDateFrom()) . ", " .
                    adnTrainingFacility::lookupName($levent->getFacility()));
                $form->addItem($event);
            }

            $form->addCommandButton("updateCandidate", $lng->txt("save"));
            $form->addCommandButton(
                "updateCandidateAndEditTraining",
                $lng->txt("adn_save_and_edit_training")
            );
            $form->addCommandButton("listCandidates", $lng->txt("cancel"));
            $form->setTitle($lng->txt("adn_edit_candidate"));
        }

        return $form;
    }

    /**
     * Create new candidate
     *
     * @param bool $a_edit_training
     */
    protected function saveCandidate($a_edit_training = false)
    {
        global $DIC, $tpl, $lng, $ilCtrl;

        $form = $this->initCandidateForm("create");

        // check input
        if ($form->checkInput()) {
            // #13
            if ($ilCtrl->getCmd() == "saveCandidateAndAddExtension" && !$form->getInput("foreign_cert_handed_in")) {
                ilUtil::sendFailure($lng->txt("adn_create_ext_only_if_for_cert"));
                $form->setValuesByPost();
                $this->createCandidate($form);
                return;
            }

            if ($this->showDialog($form, null, $a_edit_training)) {
                return;
            }

            // input ok: create new candidate
            include_once("./Services/ADN/ES/classes/class.adnCertifiedProfessional.php");
            $candidate = new adnCertifiedProfessional();
            $candidate->setSalutation($form->getInput("salutation"));
            $candidate->setLastName($form->getInput("last_name"));
            $candidate->setFirstName($form->getInput("first_name"));
            $date = $form->getInput("birthdate");
            $candidate->setBirthdate(new ilDate($date, IL_CAL_DATE));
            $candidate->setCitizenship($form->getInput("citizenship"));
            $candidate->setSubjectArea($form->getInput("type"));
            $candidate->setRegisteredForExam($form->getInput("applied"));
            if ($this->mode != self::MODE_GENERAL) {
                $candidate->setForeignCertificate($form->getInput("foreign"));
            }
            $candidate->setForeignCertificateHandedIn($form->getInput("foreign_cert_handed_in"));
            $candidate->setRegisteredBy($form->getInput("registered_by"));
            $candidate->setPhone($form->getInput("phone"));
            $candidate->setEmail($form->getInput("email"));
            $candidate->setComment($form->getInput("comment"));
            $candidate->setPostalCountry($form->getInput("country"));
            $candidate->setPostalCode($form->getInput("zip"));
            $candidate->setPostalCity($form->getInput("city"));
            $candidate->setPostalStreet($form->getInput("street"));
            $candidate->setPostalStreetNumber($form->getInput("hno"));
            $candidate->setShippingSalutation($form->getInput("ssalutation"));
            $candidate->setShippingLastName($form->getInput("slast_name"));
            $candidate->setShippingFirstName($form->getInput("sfirst_name"));
            $candidate->setShippingCountry($form->getInput("scountry"));
            $candidate->setShippingCode($form->getInput("szip"));
            $candidate->setShippingCity($form->getInput("scity"));
            $candidate->setShippingStreet($form->getInput("sstreet"));
            $candidate->setShippingStreetNumber($form->getInput("shno"));
            $candidate->setShippingActive($form->getInput("shipping_address_activated"));

            if ($form->getInput("holdback")) {
                $date = $form->getInput("holdback_until");
                $candidate->setBlockedBy($form->getInput("holdback_by"));
                $candidate->setBlockedUntil(new ilDate($date, IL_CAL_DATE));
            }

            if ($candidate->save()) {
                $upload = $form->getItemByPostVar('card_photo');
                if ($upload->getDeletionFlag()) {
                    $this->candidate->getImageHandler()->delete();
                }
                $candidate->getImageHandler()->handleUpload(
                    $DIC->upload(),
                    $_FILES['card_photo']['tmp_name']
                );

                if (!$a_edit_training) {
                    // show success message and return to list
                    ilUtil::sendSuccess($lng->txt("adn_candidate_created"), true);

                    // cr-008 start
                    if ($this->mode == self::MODE_GENERAL) {
                        $this->listPersonData();
                    } else {
                        $ilCtrl->redirect($this, "listCandidates");
                    }
                    // cr-008 end
                } else {
                    $this->readCandidate($candidate->getId());
                    $ilCtrl->setParameter($this, "cd_id", $this->candidate->getId());
                    // cr-008 start
                    $this->cd_id = $this->candidate->getId();
                    // cr-008 end
                    return true;
                }
            }
        }

        // input not valid: show form again
        $form->setValuesByPost();
        $this->createCandidate($form);
    }

    /**
     * Update candidate
     *
     * @param bool $a_edit_training
     */
    protected function updateCandidate($a_edit_training = false)
    {
        global $DIC, $tpl, $lng, $ilCtrl;

        $form = $this->initCandidateForm("edit");

        // check input
        if ($form->checkInput()) {
            if ($this->showDialog($form, $this->candidate->getId(), $a_edit_training)) {
                return;
            }

            $this->candidate->setSalutation($form->getInput("salutation"));
            $this->candidate->setLastName($form->getInput("last_name"));
            $this->candidate->setFirstName($form->getInput("first_name"));
            $date = $form->getInput("birthdate");
            $this->candidate->setBirthdate(new ilDate($date, IL_CAL_DATE));
            $this->candidate->setCitizenship($form->getInput("citizenship"));
            $this->candidate->setSubjectArea($form->getInput("type"));
            $this->candidate->setRegisteredForExam($form->getInput("applied"));
            if ($this->mode != self::MODE_GENERAL) {
                $this->candidate->setForeignCertificate($form->getInput("foreign"));
            }
            $this->candidate->setForeignCertificateHandedIn($form->getInput("foreign_cert_handed_in"));
            $this->candidate->setRegisteredBy($form->getInput("registered_by"));
            $this->candidate->setPhone($form->getInput("phone"));
            $this->candidate->setEmail($form->getInput("email"));
            $this->candidate->setComment($form->getInput("comment"));
            $this->candidate->setPostalCountry($form->getInput("country"));
            $this->candidate->setPostalCode($form->getInput("zip"));
            $this->candidate->setPostalCity($form->getInput("city"));
            $this->candidate->setPostalStreet($form->getInput("street"));
            $this->candidate->setPostalStreetNumber($form->getInput("hno"));
            $this->candidate->setShippingSalutation($form->getInput("ssalutation"));
            $this->candidate->setShippingLastName($form->getInput("slast_name"));
            $this->candidate->setShippingFirstName($form->getInput("sfirst_name"));
            $this->candidate->setShippingCountry($form->getInput("scountry"));
            $this->candidate->setShippingCode($form->getInput("szip"));
            $this->candidate->setShippingCity($form->getInput("scity"));
            $this->candidate->setShippingStreet($form->getInput("sstreet"));
            $this->candidate->setShippingStreetNumber($form->getInput("shno"));
            $this->candidate->setShippingActive($form->getInput("shipping_address_activated"));

            if ($form->getInput("holdback")) {
                $date = $form->getInput("holdback_until");
                $this->candidate->setBlockedBy($form->getInput("holdback_by"));
                $this->candidate->setBlockedUntil(new ilDate($date, IL_CAL_DATE));
            } else {
                $this->candidate->setBlockedBy(null);
                $this->candidate->setBlockedUntil(null);
            }

            if ($this->last_event_dialog === false) {
                $this->candidate->setLastEvent(null);
            }

            if ($this->candidate->update()) {

                $upload = $form->getItemByPostVar('card_photo');
                if ($upload->getDeletionFlag()) {
                    $this->candidate->getImageHandler()->delete();
                }
                $this->candidate->getImageHandler()->handleUpload(
                    $DIC->upload(),
                    $_FILES['card_photo']['tmp_name']
                );

                if ($this->last_event_dialog === true) {
                    return $this->showProviderList();
                } elseif (!$a_edit_training) {
                    // show success message and return to list
                    ilUtil::sendSuccess($lng->txt("adn_candidate_updated"), true);
                    $ilCtrl->redirect($this, "listCandidates");
                } else {
                    return true;
                }
            }
        }

        // input not valid: show form again
        $form->setValuesByPost();
        $this->editCandidate($form);
    }

    /**
     * Validate candidate data, show confirmation(s) if needed
     *
     * @param ilPropertyFormGUI $a_form
     * @param int $a_id
     * @param bool $a_edit_training
     * @return bool
     */
    protected function showDialog(ilPropertyFormGUI $a_form, $a_id = null, $a_edit_training = false)
    {
        global $lng, $ilCtrl, $tpl;

        if ($a_id) {
            $cmd = "updateCandidate";
        } else {
            $cmd = "saveCandidate";
        }

        if ($a_edit_training) {
            $cmd .= "AndEditTraining";
        }

        $done = array();
        
        // process incoming dialog answer
        if ($_POST["dialog"]) {
            $done = $_POST["dialog_done"];
            $type = array_shift($_POST["dialog"]);
            $done[] = $type;
            switch ($type) {
                case "holdback":
                    $confirmed = ($_POST["cmd"][$cmd] == $lng->txt("adn_allow_application"));
                    if (!$confirmed) {
                        $_POST["applied"] = 0;
                    }
                    break;

                case "unique":
                case "foreign":
                    $confirmed = ($_POST["cmd"][$cmd] == $lng->txt("yes"));
                    if (!$confirmed) {
                        $ilCtrl->redirect($this, "listCandidates");
                    }
                    break;

                case "lastevent":
                    $this->last_event_dialog =
                        ($_POST["cmd"][$cmd] == $lng->txt("adn_candidate_edit_last_event"));
                    break;
            }
        }

        $types = array();

        // applied vs. holdback dialog
        if (!in_array("holdback", $done) && $a_form->getInput("applied") &&
            $a_form->getInput("holdback")) {
            $date = $a_form->getInput("holdback_until");
            if ($date >= date("Y-m-d")) {
                $message = $lng->txt("adn_exam_application_holdback");
                $button_ok = $lng->txt("adn_allow_application");
                $button_cancel = $lng->txt("adn_dismiss_application");
                $types[] = "holdback";
            }
        }

        // unique user dialog
        if (!in_array("unique", $done)) {
            $date = $a_form->getInput("birthdate");
            include_once("./Services/ADN/ES/classes/class.adnCertifiedProfessional.php");
            if (!adnCertifiedProfessional::isUserUnique(
                $a_form->getInput("last_name"),
                $a_form->getInput("first_name"),
                new ilDate($date, IL_CAL_DATE),
                $a_id
            )) {
                if (!$message) {
                    $message = $lng->txt("adn_candidate_not_unique");
                    $button_ok = $lng->txt("yes");
                    $button_cancel = $lng->txt("no");
                }
                $types[] = "unique";
            }
        }

        // gas/chem vs. foreign certificate
        if (!in_array("foreign", $done) && in_array($a_form->getInput("type"), array("gas", "chem"))) {
            // existing
            if ($a_id) {
                $valid = $this->candidate->hasValidBaseCertificate($a_form->getInput("foreign"));
            }
            // new candidate: cannot have domestic
            else {
                $valid = $a_form->getInput("foreign");
            }
            if (!$valid) {
                if (!$message) {
                    $message = $lng->txt("adn_foreign_certificate_missing");
                    $button_ok = $lng->txt("yes");
                    $button_cancel = $lng->txt("no");
                }
                $types[] = "foreign";
            }
        }

        // valid last event
        if (!in_array("lastevent", $done) && $a_form->getInput("type") && $this->candidate &&
            $this->candidate->getLastEvent() && !$a_edit_training) {
            include_once "Services/ADN/TA/classes/class.adnTrainingEvent.php";
            $event = new adnTrainingEvent($this->candidate->getLastEvent());
            
            if ($a_form->getInput("type") != $event->getType()) {
                if (!$message) {
                    $message = $lng->txt("adn_candidate_last_event_mismatch");
                    $button_ok = $lng->txt("adn_candidate_edit_last_event");
                    $button_cancel = $lng->txt("adn_candidate_reset_last_event");
                }
                $types[] = "lastevent";
            }
        }
        
        if ($message) {
            // display confirmation dialog
            include_once("./Services/Utilities/classes/class.ilConfirmationGUI.php");
            $cgui = new ilConfirmationGUI();
            $cgui->setFormAction($ilCtrl->getFormAction($this));
            $cgui->setHeaderText($message);
            $cgui->setCancel($button_cancel, $cmd);
            $cgui->setConfirm($button_ok, $cmd);
            $cgui->addItem("dummy", 1, $a_form->getInput("last_name") . ", " .
                $a_form->getInput("first_name"));

            foreach ($types as $type) {
                $cgui->addHiddenItem("dialog[]", $type);
            }
            foreach ($done as $dtype) {
                $cgui->addHiddenItem("dialog_done[]", $dtype);
            }
            $cgui->addHiddenItem("salutation", $a_form->getInput("salutation"));
            $cgui->addHiddenItem("last_name", $a_form->getInput("last_name"));
            $cgui->addHiddenItem("first_name", $a_form->getInput("first_name"));
            $date = $a_form->getInput("birthdate");
            $cgui->addHiddenItem("birthdate", $date);
            $cgui->addHiddenItem("citizenship", $a_form->getInput("citizenship"));
            $cgui->addHiddenItem("type", $a_form->getInput("type"));
            $cgui->addHiddenItem("applied", $a_form->getInput("applied"));
            if ($this->mode != self::MODE_GENERAL) {
                $cgui->addHiddenItem("foreign", $a_form->getInput("foreign"));
            }
            $cgui->addHiddenItem("foreign_cert_handed_in", $a_form->getInput("foreign_cert_handed_in"));
            $cgui->addHiddenItem("registered_by", $a_form->getInput("registered_by"));
            $cgui->addHiddenItem("phone", $a_form->getInput("phone"));
            $cgui->addHiddenItem("email", $a_form->getInput("email"));
            $cgui->addHiddenItem("comment", $a_form->getInput("comment"));
            $cgui->addHiddenItem("country", $a_form->getInput("country"));
            $cgui->addHiddenItem("zip", $a_form->getInput("zip"));
            $cgui->addHiddenItem("city", $a_form->getInput("city"));
            $cgui->addHiddenItem("street", $a_form->getInput("street"));
            $cgui->addHiddenItem("hno", $a_form->getInput("hno"));
            $cgui->addHiddenItem("ssalutation", $a_form->getInput("ssalutation"));
            $cgui->addHiddenItem("slast_name", $a_form->getInput("slast_name"));
            $cgui->addHiddenItem("sfirst_name", $a_form->getInput("sfirst_name"));
            $cgui->addHiddenItem("scountry", $a_form->getInput("scountry"));
            $cgui->addHiddenItem("szip", $a_form->getInput("szip"));
            $cgui->addHiddenItem("scity", $a_form->getInput("scity"));
            $cgui->addHiddenItem("sstreet", $a_form->getInput("sstreet"));
            $cgui->addHiddenItem("shno", $a_form->getInput("shno"));
            $cgui->addHiddenItem(
                "shipping_address_activated",
                $a_form->getInput("shipping_address_activated")
            );
            $cgui->addHiddenItem("holdback", $a_form->getInput("holdback"));
            $cgui->addHiddenItem("holdback_by", $a_form->getInput("holdback_by"));
            $date = $a_form->getInput("holdback_until");
            $cgui->addHiddenItem("holdback_until", $date);

            $tpl->setContent($cgui->getHTML());
            return true;
        }
        return false;
    }

    /**
     * Save candidate and edit last training
     */
    protected function saveCandidateAndEditTraining()
    {
        if ($this->saveCandidate(true)) {
            $this->showProviderList();
        }
    }

    /**
     * Update candidate and edit last training
     */
    protected function updateCandidateAndEditTraining()
    {
        if ($this->updateCandidate(true)) {
            $this->showProviderList();
        }
    }

    /**
     * 1st step edit last training (select provider)
     */
    protected function showProviderList()
    {
        global $tpl, $lng, $ilCtrl, $ilTabs;

        if (!$this->candidate) {
            return;
        }

        $ilTabs->setBackTarget(
            $lng->txt("back"),
            $ilCtrl->getLinkTarget($this, "editCandidate")
        );

        include_once("Services/Form/classes/class.ilPropertyFormGUI.php");
        $form = new ilPropertyFormGUI();
        $form->setTitle($lng->txt("adn_edit_last_training"));
        $form->setFormAction($ilCtrl->getFormAction($this));

        include_once "Services/ADN/TA/classes/class.adnTrainingProvider.php";
        $provider = new ilSelectInputGUI($lng->txt("adn_training_provider"), "provider");
        $provider->setRequired(true);
        $provider->setOptions(adnTrainingProvider::getTrainingProvidersSelect());
        $form->addItem($provider);

        $form->addCommandButton("showEventList", $lng->txt("btn_next"));
        $form->addCommandButton("editCandidate", $lng->txt("cancel"));

        $tpl->setContent($form->getHTML());
    }

    /**
     * Set last training for candidate
     */
    protected function saveLastTraining()
    {
        global $lng, $ilCtrl;

        if (!$this->candidate) {
            return;
        }

        $event_id = (int) $_REQUEST["te_id"];
        if ($event_id) {
            $this->candidate->setLastEvent($event_id);
            if ($this->candidate->update()) {
                ilUtil::sendSuccess($lng->txt("adn_training_attendance_saved"), true);
            }
        }

        $ilCtrl->redirect($this, "editCandidate");
    }

    /**
     * 2nd step edit last training (select event)
     */
    protected function showEventList()
    {
        global $tpl, $ilTabs, $ilCtrl, $lng;

        if (!$this->candidate) {
            return;
        }

        $ilTabs->setBackTarget($lng->txt("back"), $ilCtrl->getLinkTarget($this, "editCandidate"));

        $provider_id = (int) $_REQUEST["provider"];

        $ilCtrl->setParameter($this, "provider", $provider_id);

        // table of training events
        include_once("./Services/ADN/TA/classes/class.adnTrainingEventTableGUI.php");
        $table = new adnTrainingEventTableGUI(
            $this,
            "showEventList",
            $provider_id,
            null,
            true,
            false,
            $this->candidate->getSubjectArea()
        );

        // output table
        $tpl->setContent($table->getHTML());
    }

    /**
     * Apply filter settings (from table gui)
     */
    protected function applyTrainingEventFilter()
    {
        if (!$this->candidate) {
            return;
        }

        $provider_id = (int) $_REQUEST["provider"];

        include_once("./Services/ADN/TA/classes/class.adnTrainingEventTableGUI.php");
        $table = new adnTrainingEventTableGUI(
            $this,
            "showEventList",
            $provider_id,
            null,
            true,
            false,
            $this->candidate->getSubjectArea()
        );
        $table->resetOffset();
        $table->writeFilterToSession();

        $this->showEventList();
    }

    /**
     * Reset filter settings (from table gui)
     */
    protected function resetTrainingEventFilter()
    {
        if (!$this->candidate) {
            return;
        }

        $provider_id = (int) $_REQUEST["provider"];

        include_once("./Services/ADN/TA/classes/class.adnTrainingEventTableGUI.php");
        $table = new adnTrainingEventTableGUI(
            $this,
            "showEventList",
            $provider_id,
            null,
            true,
            false,
            $this->candidate->getSubjectArea()
        );
        $table->resetOffset();
        $table->resetFilter();

        $this->showEventList();
    }

    /**
     * Confirm candidates deletion
     */
    protected function confirmCandidatesDeletion()
    {
        global $ilCtrl, $tpl, $lng, $ilTabs;

        // check whether at least one item has been seleced
        if (!is_array($_POST["cand_id"]) || count($_POST["cand_id"]) == 0) {
            ilUtil::sendFailure($lng->txt("no_checkbox"), true);
            $ilCtrl->redirect($this, "listCandidates");
        } else {
            $ilTabs->setBackTarget($lng->txt("back"), $ilCtrl->getLinkTarget($this, "listCandidates"));
            
            // display confirmation message
            include_once("./Services/Utilities/classes/class.ilConfirmationGUI.php");
            $cgui = new ilConfirmationGUI();
            $cgui->setFormAction($ilCtrl->getFormAction($this));
            $cgui->setHeaderText($lng->txt("adn_sure_delete_candidates"));
            $cgui->setCancel($lng->txt("cancel"), "listCandidates");
            $cgui->setConfirm($lng->txt("delete"), "deleteCandidates");

            // list objects that should be deleted
            include_once("./Services/ADN/ES/classes/class.adnCertifiedProfessional.php");
            foreach ($_POST["cand_id"] as $i) {
                $cgui->addItem("cand_id[]", $i, adnCertifiedProfessional::lookupName($i));
            }

            $tpl->setContent($cgui->getHTML());
        }
    }

    /**
     * Delete candidates
     */
    protected function deleteCandidates()
    {
        global $ilCtrl, $lng;

        include_once("./Services/ADN/ES/classes/class.adnCertifiedProfessional.php");

        if (is_array($_POST["cand_id"])) {
            foreach ($_POST["cand_id"] as $i) {
                $candidate = new adnCertifiedProfessional($i);
                $candidate->delete();
            }
        }
        ilUtil::sendSuccess($lng->txt("adn_candidate_deleted"), true);
        $ilCtrl->redirect($this, "listCandidates");
    }

    // cr-008 start
    /**
     * List personal data
     */
    public function listPersonData()
    {
        global $ilCtrl;

        $ilCtrl->redirectByClass(array("adnBaseGUI", "adncertifiedprofessionalgui", "adnCertifiedProfessionalDataGUI"), "listProfessionals");
        //$ilCtrl->redirectByClass(array("adnBaseGUI", "adncertifiedprofessionalgui", "adnPersonalDataMaintenanceGUI"), "listPersonalData");
    }

    /**
     * Save candidate and add extension
     */
    protected function saveCandidateAndAddExtension()
    {
        global $ilCtrl;

        if ($this->saveCandidate(true)) {
            $ilCtrl->setParameterByClass("adnCertificateGUI", "pid", $this->cd_id);
            $ilCtrl->redirectByClass(array("adnBaseGUI", "adnCertifiedProfessionalGUI", "adnCertificateGUI"), "extendCertificate");
        }
    }

    /**
     * Save candidate and list personal data
     */
    protected function saveCandidateAndListPersonData()
    {
        global $ilCtrl;

        if ($this->saveCandidate(true)) {
            $this->listPersonData();
        }
    }

    // cr-008 end
}
