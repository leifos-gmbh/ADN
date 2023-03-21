<?php
/* Copyright (c) 2010 Leifos, GPL, see docs/LICENSE */

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

    private const ACCESS_READ = 'read';
    private const ACCESS_WRITE = 'write';
    private const ACCESS_NONE = 'none';

    private string $access = self::ACCESS_NONE;

    private adnCardSettings $settings;

    public function __construct()
    {
        global $DIC;

        $this->ctrl = $DIC->ctrl();
        $this->main_template = $DIC->ui()->mainTemplate();
        $this->lng = $DIC->language();
        $this->logger = $DIC->logger()->adn();

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
        $this->settings->update();
        ilUtil::sendSuccess($this->lng->txt('settings_saved'), true);
        $this->ctrl->redirect($this, 'settings');
    }

    protected function initSettingsForm() : ilPropertyFormGUI
    {
        $form = new ilPropertyFormGUI();
        $form->setFormAction($this->ctrl->getFormAction($this));
        $form->setTitle($this->lng->txt('adn_card_settings_form'));

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

}