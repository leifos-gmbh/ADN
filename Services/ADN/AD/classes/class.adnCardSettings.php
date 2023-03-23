<?php

/* Copyright (c) 2010 Leifos, GPL, see docs/LICENSE */

/**
 * Card settings
 *
 * @author       Stefan Meyer <meyer@leifos.de>
 * @ilCtrl_Calls adnCardAdministrationGUI
 *
 * @ingroup      ServicesADN
 */
class adnCardSettings
{
    private const STORAGE_MODULE = 'adn_card';

    private static ?adnCardSettings $instance = null;

    private ilSetting $storage;

    private string $nfc_user = '';
    private string $nfc_pass = '';
    private string $nfc_service_url = '';

    protected function __construct()
    {
        global $DIC;

        $this->storage = new ilSetting(self::STORAGE_MODULE);
        $this->read();
    }

    public static function getInstance() : self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    protected function read()
    {
        $this->setNfcUser($this->storage->get('nfc_user', $this->getNfcUser()));
        $this->setNfcPass($this->storage->get('nfc_pass', $this->getNfcPass()));
        $this->setNfcServiceUrl($this->storage->get('nfc_service_url', $this->getNfcServiceUrl()));
    }

    public function update()
    {
        $this->storage->set('nfc_user', $this->getNfcUser());
        $this->storage->set('nfc_pass', $this->getNfcPass());
        $this->storage->set('nfc_service_url', $this->getNfcServiceUrl());
    }

    public function getNfcUser() : string
    {
        return $this->nfc_user;
    }

    public function setNfcUser(string $nfc_user) : void
    {
        $this->nfc_user = $nfc_user;
    }

    public function getNfcPass() : string
    {
        return $this->nfc_pass;
    }

    public function setNfcPass(string $nfc_pass) : void
    {
        $this->nfc_pass = $nfc_pass;
    }

    public function getNfcServiceUrl() : string
    {
        return $this->nfc_service_url;
    }

    public function setNfcServiceUrl(string $nfc_service_url) : void
    {
        $this->nfc_service_url = $nfc_service_url;
    }

    public function hasNfCSettings() : bool
    {
        if ($this->getNfcServiceUrl() !== '') {
            return true;
        }
        return false;
    }

}