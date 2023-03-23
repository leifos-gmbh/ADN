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

    public const VERIFICATION_CONNECT = 1;
    public const VERIFICATION_USER = 2;
    public const VERIFICATION_PASS = 3;
    public const VERIFICATION_PARAMETER = 4;
    public const VERIFCATION_TAG_ID = 5;
    public const VERIFICATION_TAC_LENGTH = 6;

    public const CODE_USER_NOT_FOUND = '0003';

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