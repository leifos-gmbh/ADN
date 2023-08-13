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

class adnCardCertificateCardStatusHandler
{
    private const CARD_STATUS_NS = 'plzft';

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
        $xml = $status_card_request->toXml();
        try {
            $response = $api->cardstatus($xml);
            $this->writeStatus($response);
        } catch (\Plasticard\PLZFT\ApiException $e) {
            $this->logger->dump('Sending card status failed with message:');
            $this->logger->dump($e->getResponseBody(), ilLogLevel::ERROR);
            $this->logger->error($e->getMessage());
        }
        return adnCertificate::CARD_STATUS_UNDEFINED;
    }

    protected function writeStatus(string $response) : void
    {
        $root = new SimpleXMLElement($response, 0, false, self::CARD_STATUS_NS, false);
        $certificate_id = '';
        $production_state = '';
        foreach ($root->children(self::CARD_STATUS_NS, true) as $element) {
            switch ($element->getName()) {
                case 'CertificateId':
                    $certificate_id = (string) $element;
                    break;
                case 'ProductionState':
                    $production_state = (string) $element;
                    break;

                default:
                    $this->logger->dump('Element name: ' . (string) $element);
            }
        }
        $this->logger->dump('Found' . $certificate_id . ' ' . $production_state);
        if (
            $certificate_id !== '' && $production_state !== ''
        ) {
            $certificates = new adnCertificates();
            $certificates->updateProductionState($certificate_id, (int) $production_state);
        }
    }

    protected function initApi() : DefaultApi
    {
        $config = new \Plasticard\PLZFT\Configuration();
        $config->setHost($this->settings->getPlcServiceUrl());
        $config->setUsername($this->settings->getPlcUser());
        $config->setPassword($this->settings->getPlcPass());
        #$config->setDebug(true);
        #$config->setDebugFile('/srv/www/log/slim.log');
        $api = new DefaultApi(null, $config);
        return $api;
    }




}