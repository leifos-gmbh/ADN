<?php

use Plasticard\PLZFT\Api\PLZFTApi as PLZFTApi;

class adnPlcVerification
{
    public const CURL_CONNECTTIMEOUT = 3;

    protected ilLogger $logger;
    protected adnCardSettings $settings;

    public function __construct()
    {
        global $DIC;

        $this->logger = $DIC->logger()->adn();
        $this->settings = adnCardSettings::getInstance();
    }

    public function verify()
    {
        $this->logger->info('Start plc verification');
        try {
            $api = $this->initApi();
            $order = $this->initOrder();
            $response = $api->addOrderWithHttpInfo($order);
            $this->logger->dump($response);
            return $response;
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

    protected function initOrder() : \Plasticard\PLZFT\Model\Order
    {
        $order = new \Plasticard\PLZFT\Model\Order();
        return $order;

    }
}