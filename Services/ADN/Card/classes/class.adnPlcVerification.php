<?php

use Plasticard\PLZFT\Api\DefaultApi as PLZFTApi;

class adnPlcVerification
{
    private const ALIVE = 'ALIVE';

    public const CURL_CONNECTTIMEOUT = 3;

    protected ilLogger $logger;
    protected adnCardSettings $settings;

    public function __construct()
    {
        global $DIC;

        $this->logger = $DIC->logger()->adn();
        $this->settings = adnCardSettings::getInstance();
    }

    public function verify() : bool
    {
        $this->logger->info('Start plc verification');
        $response = null;
        try {
            $api = $this->initApi();
            $response = $api->services65TestV1HeartbeatGet();
            return $response === self::ALIVE;
        } catch (\Plasticard\PLZFT\ApiException $e) {
            $this->logger->error($e->getMessage());
            throw $e;
        }
    }

    protected function initApi() : PLZFTApi
    {
        $config = new \Plasticard\PLZFT\Configuration();
        $config->setHost($this->settings->getPlcServiceUrl());
        $config->setUsername($this->settings->getPlcUser());
        $config->setPassword($this->settings->getPlcPass());
        $config->setDebug(false);
        //$config->setDebugFile('/srv/www/hal/log/slim.log');

        $api = new PLZFTApi(null, $config);
        return $api;
    }
}