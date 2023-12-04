<?php

/* Copyright (c) 2010 Leifos, GPL, see docs/LICENSE */

use Hid\Verification\Model\VerificationRequest as VerificationRequest;

/**
 * NFC tag verification using "Trusted Tag Services" by HID
 *
 * @author       Stefan Meyer <meyer@leifos.de>
 * @ingroup      ServicesADN
 */
class adnHidVerification
{
    public const CURL_CONNECTTIMEOUT = 3;

    public const CODE_PASSED = '0000';
    public const CODE_TAC_FAIL = '0001';
    public const CODE_PASSWORD_INVALID = '0002';
    public const CODE_USER_NOT_FOUND = '0003';
    public const CODE_TAG_INVALID = '0005';
    public const CODE_TAC_INVALID = '0006';
    public const CODE_OTHER = '0100';

    protected ilLogger $logger;
    protected adnCardSettings $settings;

    protected string $hid_tac = '';
    protected string $hid_tag_id = '';


    public function __construct(string $tac, string $tag_id)
    {
        global $DIC;

        $this->logger = $DIC->logger()->adn();
        $this->settings = adnCardSettings::getInstance();
        $this->setHidTac($tac);
        $this->setHidTagId($tag_id);
    }

    public function verify() : \Hid\Verification\Model\VerificationResponse
    {
        $this->logger->info('Start hid verification');
        try {
            $api = $this->initApi();
            $request = $this->initRequest();
            $response = $api->testHidVerificationPost($request);
            $this->logger->dump($request);
            $this->logger->dump($response);
            return $response;
        } catch (\Hid\Verification\ApiException $e) {
            $this->logger->error($e->getMessage());
            throw $e;
        }
    }

    protected function initApi() : Hid\Verification\Api\DefaultApi
    {
        $config = new \Hid\Verification\Configuration();
        $config->setHost($this->settings->getNfcServiceUrl());
        $config->setDebug(false);
        //$config->setDebugFile('/srv/www/hal/log/slim.log');

        $api = new Hid\Verification\Api\DefaultApi(null, $config);
        return $api;
    }

    protected function initRequest() : VerificationRequest
    {
        $request = new VerificationRequest();
        $request->setSystemUserName($this->settings->getNfcUser());
        $request->setSystemPassword($this->settings->getNfcPass());
        $request->setTac($this->getHidTac());
        $request->setTagId($this->getHidTagId());
        return $request;
    }


    public function getHidTagId() : string
    {
        return $this->hid_tag_id;
    }

    public function setHidTagId(string $hid_tag_id) : void
    {
        $this->hid_tag_id = $hid_tag_id;
    }

    public function getHidTac() : string
    {
        return $this->hid_tac;
    }

    public function setHidTac(string $hid_tac) : void
    {
        $this->hid_tac = $hid_tac;
    }

}