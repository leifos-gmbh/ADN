<?php

use Plasticard\PLZFT\Api\DefaultApi as DefaultApi;

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

    protected function initApi() : DefaultApi
    {
        $config = new \Plasticard\PLZFT\Configuration();
        $config->setHost($this->settings->getPlcServiceUrl());
        $config->setUsername($this->settings->getPlcUser());
        $config->setPassword($this->settings->getPlcPass());
        $config->setDebug(false);

        if (strlen($this->settings->getPlcProxy())) {
            $client = new \GuzzleHttp\Client(
                [
                    'proxy' => $this->settings->getPlcProxy()
                ]
            );
        } else {
            $client = new \GuzzleHttp\Client();
        }
        $api = new DefaultApi($client, $config);
        return $api;
    }
}