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
    private string $plc_user = '';
    private string $plc_pass = '';
    private string $plc_service_url = '';

    private string $plc_proxy = '';

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
        $this->setPlcUser($this->storage->get('plc_user', $this->getPlcUser()));
        $this->setPlcPass($this->storage->get('plc_pass', $this->getPlcPass()));
        $this->setPlcServiceUrl($this->storage->get('plc_service_url', $this->getPlcServiceUrl()));
        $this->setPlcProxy($this->storage->get('plc_proxy', $this->getPlcProxy()));
    }

    public function update()
    {
        $this->storage->set('nfc_user', $this->getNfcUser());
        $this->storage->set('nfc_pass', $this->getNfcPass());
        $this->storage->set('nfc_service_url', $this->getNfcServiceUrl());
        $this->storage->set('plc_user', $this->getPlcUser());
        $this->storage->set('plc_pass', $this->getPlcPass());
        $this->storage->set('plc_service_url', $this->getPlcServiceUrl());
        $this->storage->set('plc_proxy', $this->getPlcProxy());
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

    public function hasPlcSettings() : bool
    {
        if ($this->getPlcServiceUrl() !== '') {
            return true;
        }
        return false;
    }

    public function getPlcUser() : string
    {
        return $this->plc_user;
    }

    public function setPlcUser(string $plc_user) : void
    {
        $this->plc_user = $plc_user;
    }

    public function getPlcPass() : string
    {
        return $this->plc_pass;
    }

    public function setPlcPass(string $plc_pass) : void
    {
        $this->plc_pass = $plc_pass;
    }

    public function getPlcServiceUrl() : string
    {
        return $this->plc_service_url;
    }

    public function setPlcServiceUrl(string $plc_service_url) : void
    {
        $this->plc_service_url = $plc_service_url;
    }

    public function getPlcProxy() : string
    {
        return $this->plc_proxy;
    }

    public function setPlcProxy(string $plc_proxy) : void
    {
        $this->plc_proxy = $plc_proxy;
    }

}