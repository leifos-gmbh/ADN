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

use Plasticard\PLZFT\Api\PLZFTApi as PLZFTApi;
use Plasticard\PLZFT\Model\Order as Order;

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



    public function send(Order $order)
    {
        $this->logger->info('Start plc verification');
        try {
            $api = $this->initApi();
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

    public function initOrder(adnCertifiedProfessional $professional, adnCertificate $cert) : Order
    {
        $order = new Order();
        $certificate = new \Plasticard\PLZFT\Model\OrderPlzftCertificate();

        // postal address
        $postal = new \Plasticard\PLZFT\Model\OrderPlzftCertificatePlzftPostalAddress();
        if ($professional->isShippingActive()) {
            $postal->setPlzftAddressName($professional->getShippingFirstName() . ' ' . $professional->getShippingLastName());
            $postal->setPlzftAddressStreet($professional->getShippingStreet() . ' ' . $professional->getShippingStreetNumber());
            $postal->setPlzftAddressPostalCode($professional->getPostalCode());
            $postal->setPlzftAddressCity($professional->getShippingCity());
            $country = new adnCountry($professional->getShippingCountry());
            $postal->setPlzftAddressCountry($country->getName());
        } else {
            $postal->setPlzftAddressName($professional->getFirstName() . ' ' . $professional->getLastName());
            $postal->setPlzftAddressStreet($professional->getPostalStreet() . ' ' . $professional->getPostalStreetNumber());
            $postal->setPlzftAddressPostalCode($professional->getPostalCode());
            $postal->setPlzftAddressCity($professional->getPostalCity());
            $country = new adnCountry($professional->getPostalCountry());
            $postal->setPlzftAddressCountry($country->getName());
        }
        // return address
        $wmo = new adnWMO($cert->getIssuedByWmo());
        $return = new \Plasticard\PLZFT\Model\OrderPlzftCertificatePlzftReturnAddress();
        $return->setPlzftAddressName($wmo->getName());
        $return->setPlzftAddressStreet($wmo->getPostalStreet() . $wmo->getPostalStreetNumber());
        $return->setPlzftAddressPostalCode($wmo->getPostalZip());
        $return->setPlzftAddressCity($wmo->getPostalCity());
        $return->setPlzftAddressCountry('Deutschland');

        $certificate->setPlzftCertificateId($cert->getUuid());
        $certificate->setPlzftCertificateNumber($cert->getFullCertificateNumber());
        $certificate->setPlzftLastname($professional->getLastName());
        $certificate->setPlzftFirstname($professional->getFirstName());
        $country = new adnCountry($professional->getCitizenship());
        $certificate->setPlzftNationality($country->getName());
        $certificate->setPlzftBirthday(new DateTime($professional->getBirthdate()->get(IL_CAL_FKT_DATE, 'Y-m-d')));
        $certificate->setPlzftIssuedBy($wmo->getName());
        $certificate->setPlzftValidUntil(new DateTime($cert->getValidUntil()->get(IL_CAL_FKT_DATE, 'Y-m-d')));

        $types = new \Plasticard\PLZFT\Model\OrderPlzftCertificatePlzftCertificateTypes();
        foreach (adnCertificate::getCertificateTypes() as $type => $caption) {
            if ($cert->getType($type)) {
                $types->setPlzftCertificateType($this->lng->txt('adn_subject_area_cert_' . $type));
            }
        }
        $certificate->setPlzftCertificateTypes($types);
        $certificate->setPlzftPhoto(base64_encode(file_get_contents($professional->getImageHandler()->getAbsolutePath())));
        $certificate->setPlzftPostalAddress($postal);
        $certificate->setPlzftReturnAddress($return);
        $order->setPlzftCertificate($certificate);
        return $order;
    }
}
