<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

declare(strict_types=1);

use Plasticard\PLZFT\Api\DefaultApi as DefaultApi;
use ADN\Card\Api\StatusCard as StatusCard;
use Plasticard\PLZFT\Model\CardStateResponse as CardStateResponse;

class adnCardCertificateOrderStatusHandler
{
    private ilLogger $logger;
    private ilLanguage $lng;

    private adnCardSettings $settings;


    public function __construct()
    {
        global $DIC;

        $this->logger = $DIC->logger()->adn();
        $this->lng = $DIC->language();
        $this->lng->loadLanguageModule('adn');
        $this->settings = adnCardSettings::getInstance();
    }

    public function updateStatus(): void
    {
        $api = $this->initApi();
        $certificates = new adnCertificates();
        foreach ($certificates->getCertificatesByCardOrderStatus(adnCertificate::CARD_STATUS_UNDEFINED) as $certificate) {
            $status = $this->readProductionStatus($api, $certificate->getUuid());
            if ($status !== adnCertificate::CARD_STATUS_UNKNOWN) {

            }
        }
    }

    protected function readProductionStatus(DefaultApi $api, string $certficate_id): int
    {
        $status_card_request = new StatusCard();
        $status_card_request->setCertificateId($certficate_id);
        try {
            $request = new \Plasticard\PLZFT\Model\CardStateRequest();
            $request->setPlzftCertificateId($certficate_id);
            $response = $api->cardstatus($request);
            $this->writeStatus($response);
        } catch (\Plasticard\PLZFT\ApiException $e) {
            $this->logger->dump('Sending order failed with message:');
            $this->logger->dump($e->getResponseBody(), ilLogLevel::ERROR);
            $this->logger->error($e->getMessage());
        }
        return adnCertificate::CARD_STATUS_UNDEFINED;
    }

    protected function writeStatus(CardStateResponse $response)
    {
        $state = $response->getPlzftCard()->getPlzftProductionState();
        $this->logger->warning('Current status: ' . $state);
    }

    protected function initApi() : DefaultApi
    {
        $config = new \Plasticard\PLZFT\Configuration();
        $config->setHost($this->settings->getPlcServiceUrl());
        $config->setUsername($this->settings->getPlcUser());
        $config->setPassword($this->settings->getPlcPass());
        $config->setDebug(true);
        $config->setDebugFile('/srv/www/log/slim.log');
        $api = new DefaultApi(null, $config);
        return $api;
    }




}