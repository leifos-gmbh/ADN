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

use Plasticard\PLZFT\Api\DefaultApi as PLZFTApi;
use ADN\Card\Api\Certificates as Certificates;
use ADN\Card\Api\Certificate as Certificate;
use ADN\Card\Api\PostalAddress;
use ADN\Card\Api\ReturnAddress;
use Plasticard\PLZFT\Api\DefaultApi as DefaultApi;

/**
 * @classDescription Sends an certificate order request
 * @author Stefan Meyer <meyer@leifos.de>
 */
class adnCardCertificateOrderHandler
{
    public const CURL_CONNECTTIMEOUT = 3;

    protected ilLogger $logger;
    protected adnCardSettings $settings;
    protected ilLanguage $lng;

    public function __construct()
    {
        global $DIC;

        $this->lng = $DIC->language();
        $this->logger = $DIC->logger()->adn();
        $this->settings = adnCardSettings::getInstance();
    }



    public function send(Certificates $order): array
    {
        $this->logger->info('Start plc verification');
        try {
            $api = $this->initApi();
            $this->logger->dump($order->toXml());
            $response = $api->orderWithHttpInfo($order->toXml());
            $this->logger->dump($response);
            return $response;
        } catch (\Plasticard\PLZFT\ApiException $e) {
            $this->logger->dump('Sending order failed with message:');
            $this->logger->dump($e->getResponseBody(), ilLogLevel::ERROR);
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
        $config->setDebug(true);
        $config->setDebugFile('/srv/www/log/slim.log');


        $api = new DefaultApi(null, $config);
        return $api;
    }

    public function initOrder(adnCertifiedProfessional $professional, adnCertificate $cert) : Certificates
    {
        $certificates = new Certificates();

        $certificate = new Certificate();


        // postal address
        $postal = new PostalAddress();
        if ($professional->isShippingActive()) {
            $postal->setAddressName($professional->getShippingFirstName() . ' ' . $professional->getShippingLastName());
            $postal->setAddressStreet($professional->getShippingStreet() . ' ' . $professional->getShippingStreetNumber());
            $postal->setAddressPostalCode($professional->getPostalCode());
            $postal->setAddressCity($professional->getShippingCity());
            $country = new adnCountry($professional->getShippingCountry());
            $postal->setAddressCountry($country->getName());
        } else {
            $postal->setAddressName($professional->getFirstName() . ' ' . $professional->getLastName());
            $postal->setAddressStreet($professional->getPostalStreet() . ' ' . $professional->getPostalStreetNumber());
            $postal->setAddressPostalCode($professional->getPostalCode());
            $postal->setAddressCity($professional->getPostalCity());
            $country = new adnCountry($professional->getPostalCountry());
            $postal->setAddressCountry($country->getName());
        }
        // return address
        $wmo = new adnWMO($cert->getIssuedByWmo());
        $return = new ReturnAddress();
        $return->setAddressName($wmo->getName());
        $return->setAddressStreet($wmo->getPostalStreet() . $wmo->getPostalStreetNumber());
        $return->setAddressPostalCode($wmo->getPostalZip());
        $return->setAddressCity($wmo->getPostalCity());
        $return->setAddressCountry('Deutschland');

        $certificate->setCertificateId($cert->getUuid());
        $certificate->setCertificateNumber($cert->getFullCertificateNumber());
        $certificate->setLastname($professional->getLastName());
        $certificate->setFirstname($professional->getFirstName());
        $country = new adnCountry($professional->getCitizenship());
        $certificate->setNationality($country->getName());
        $certificate->setBirthday(new DateTime($professional->getBirthdate()->get(IL_CAL_FKT_DATE, 'Y-m-d')));
        $certificate->setIssuedBy($wmo->getName());
        $certificate->setValidUntil(new DateTime($cert->getValidUntil()->get(IL_CAL_FKT_DATE, 'Y-m-d')));

        $types = [];
        foreach (adnCertificate::getCertificateTypes() as $type => $caption) {
            if ($cert->getType($type)) {
                $types[] = $this->lng->txt('adn_subject_area_cert_' . $type);
            }
        }
        $certificate->setCertificateTypes($types);
        $certificate->setPhoto(base64_encode(file_get_contents($professional->getImageHandler()->getAbsolutePath())));
        $certificate->setPostalAddress($postal);
        $certificate->setReturnAddress($return);

        $certificates->setCertificates([$certificate]);
        return $certificates;
    }
}
