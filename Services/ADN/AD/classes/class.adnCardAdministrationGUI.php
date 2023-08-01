<?php
/* Copyright (c) 2010 Leifos, GPL, see docs/LICENSE */

use ILIAS\UI\Factory as UiFactory;

/**
 * Card administration settings gui
 *
 * @author Stefan Meyer <meyer@leifos.de>
 * @ilCtrl_Calls adnCardAdministrationGUI
 *
 * @ingroup ServicesADN
 */
class adnCardAdministrationGUI
{
    private ilCtrl $ctrl;
    private $main_template;
    private ilLanguage $lng;
    private ilLogger $logger;
    private ilToolbarGUI $toolbar;

    private const ACCESS_READ = 'read';
    private const ACCESS_WRITE = 'write';
    private const ACCESS_NONE = 'none';

    private string $access = self::ACCESS_NONE;

    private adnCardSettings $settings;
    private UiFactory $ui_factory;

    public function __construct()
    {
        global $DIC;

        $this->ctrl = $DIC->ctrl();
        $this->main_template = $DIC->ui()->mainTemplate();
        $this->lng = $DIC->language();
        $this->logger = $DIC->logger()->adn();
        $this->toolbar = $DIC->toolbar();
        $this->ui_factory = $DIC->ui()->factory();

        if (adnPerm::check(adnPerm::AD, adnPerm::READ)) {
            $this->access = self::ACCESS_READ;
        }
        if (adnPerm::check(adnPerm::AD, adnPerm::WRITE)) {
            $this->access = self::ACCESS_WRITE;
        }
        $this->settings = adnCardSettings::getInstance();
    }
    public function executeCommand()
    {
        $next_class = $this->ctrl->getNextClass();

        $this->logger->info('Command:' . $this->ctrl->getCmd('settings'));

        switch ($next_class) {
            default:
                $cmd = $this->ctrl->getCmd('settings');
                switch ($cmd) {
                    // commands that need read permission
                    case 'settings':
                        if (adnPerm::check(adnPerm::AD, adnPerm::READ)) {
                            $this->$cmd();
                        }
                        break;

                    // commands that need write permission
                    default:
                        if (adnPerm::check(adnPerm::AD, adnPerm::WRITE)) {
                            $this->$cmd();
                        }
                        break;
                }
                break;
        }
    }

    protected function settings(ilPropertyFormGUI $form = null) : void
    {
        $this->showToolbar();
        if (!$form instanceof ilPropertyFormGUI) {
            $form = $this->initSettingsForm();
        }
        $this->main_template->setContent($form->getHTML());
    }

    protected function update() : void
    {
        $form = $this->initSettingsForm();
        if (!$form->checkInput()) {
            $form->setValuesByPost();
            ilUtil::sendFailure($this->lng->txt('err_check_input'));
            $this->settings($form);
            return;
        }
        $this->settings->setNfcUser($form->getInput('user'));
        $this->settings->setNfcPass($form->getInput('pass'));
        $this->settings->setNfcServiceUrl($form->getInput('service'));
        $this->settings->setPlcUser($form->getInput('plc_user'));
        $this->settings->setPlcPass($form->getInput('plc_pass'));
        $this->settings->setPlcServiceUrl($form->getInput('plc_service'));
        $this->settings->update();
        ilUtil::sendSuccess($this->lng->txt('settings_saved'), true);
        $this->ctrl->redirect($this, 'settings');
    }

    protected function initSettingsForm() : ilPropertyFormGUI
    {
        $form = new ilPropertyFormGUI();
        $form->setFormAction($this->ctrl->getFormAction($this));
        $form->setTitle($this->lng->txt('adn_card_settings_form'));

        // plasticard service
        $plc = new ilFormSectionHeaderGUI();
        $plc->setTitle($this->lng->txt('adn_card_plc_settings'));
        $form->addItem($plc);

        $plc_user = new ilTextInputGUI($this->lng->txt('adn_card_plc_user'),'plc_user');
        $plc_user->setValue($this->settings->getPlcUser());
        $plc_user->setRequired(true);
        $form->addItem($plc_user);

        $plc_pass = new ilPasswordInputGUI($this->lng->txt('adn_card_plc_pass'), 'plc_pass');
        $plc_pass->setValue($this->settings->getPlcPass());
        $plc_pass->setRequired(true);
        $plc_pass->setRetype(false);
        $plc_pass->setDisableHtmlAutoComplete(true);
        $plc_pass->setSkipSyntaxCheck(true);
        $form->addItem($plc_pass);

        $plc_service = new ilTextInputGUI($this->lng->txt('adn_card_plc_service'), 'plc_service');
        $plc_service->setValue($this->settings->getPlcServiceUrl());
        $plc_service->setRequired(true);
        $form->addItem($plc_service);

        // hid service
        $nfc = new ilFormSectionHeaderGUI();
        $nfc->setTitle($this->lng->txt('adn_card_nfc_settings'));
        $form->addItem($nfc);

        $user = new ilTextInputGUI($this->lng->txt('adn_card_nfc_user'), 'user');
        $user->setValue($this->settings->getNfcUser());
        $user->setRequired(true);
        $form->addItem($user);

        $pass = new ilPasswordInputGUI($this->lng->txt('adn_card_nfc_pass'), 'pass');
        $pass->setValue($this->settings->getNfcPass());
        $pass->setRequired(true);
        $pass->setRetype(false);
        $pass->setDisableHtmlAutoComplete(true);
        $pass->setSkipSyntaxCheck(true);
        $form->addItem($pass);

        $service = new ilTextInputGUI($this->lng->txt('adn_card_nfc_service'), 'service');
        $service->setValue($this->settings->getNfcServiceUrl());
        $service->setRequired(true);
        $form->addItem($service);

        if ($this->access === self::ACCESS_WRITE) {
            $form->addCommandButton('update', $this->lng->txt('save'));
        }

        return $form;
    }

    protected function validateNfcSettings() : void
    {
        $verification = new adnHidVerification('dummy', 'dummy');
        $response = null;
        try {
            $response = $verification->verify();
        } catch (\Hid\Verification\ApiException $e) {
            ilUtil::sendFailure($e->getMessage());
            $this->settings();
            return;
        }
        $error = false;
        if ($response->getCode() === adnHidVerification::CODE_PASSWORD_INVALID) {
            ilUtil::sendFailure($this->lng->txt('adn_card_hid_error_pwd'));
            $error = true;
        }
        if ($response->getCode() === adnHidVerification::CODE_USER_NOT_FOUND) {
            ilUtil::sendFailure($this->lng->txt('adn_card_hid_error_username'));
            $error = true;
        }
        if (!$error) {
            ilUtil::sendSuccess('adn_card_hid_success');
        }
        $this->settings();
    }

    protected function validatePlcSettings() : void
    {
        $verification = new adnPlcVerification();
        try {
            if (!$verification->verify()) {
                ilUtil::sendFailure($this->lng->txt('adn_card_plc_con_failed'));
                $this->settings();
                return;
            }
        } catch (Exception $e) {
            ilUtil::sendFailure($e->getMessage());
            $this->settings();
            return;
        }
        ilUtil::sendSuccess($this->lng->txt('adn_card_plc_success'));
        $this->settings();
    }

    protected function showToolbar()
    {
        $this->toolbar->setFormAction($this->ctrl->getFormAction($this));
        if ($this->settings->hasNfCSettings()) {
            $validation_button = $this->ui_factory->button()->standard(
                $this->lng->txt('adn_card_plc_button_validation'),
                $this->ctrl->getLinkTarget($this, 'validatePlcSettings')
            );
            $this->toolbar->addComponent($validation_button);
        }
        if ($this->settings->hasNfCSettings()) {
            $validation_button = $this->ui_factory->button()->standard(
                $this->lng->txt('adn_card_nfc_button_validation'),
                $this->ctrl->getLinkTarget($this, 'validateNfcSettings')
            );
            $this->toolbar->addComponent($validation_button);
        }
    }

}