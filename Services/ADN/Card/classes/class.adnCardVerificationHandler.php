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


include_once './Services/Calendar/classes/class.ilCalendarAuthenticationToken.php';

/**
 * @classDescription Handles nfc verification requests
 * @author Stefan Meyer <meyer@leifos.de>
 * @ingroup ServicesCalendar
 *
 */
class adnCardVerificationHandler
{
    protected const SUCCESS = '0000';
    protected const ERROR_CONNECT = '0200';
    protected const ERROR_INVALID_CERTIFICATE = '0300';

    private ilLogger $logger;
    private ilLanguage $lng;

    private string $hid_tac = '';
    private string $hid_tag_id = '';
    private string $certificate_id = '';

    public function __construct(string $hid_tac, string $hid_tag_id, string $certificate_id)
    {
        global $DIC;

        $this->logger = $DIC->logger()->adn();
        $this->lng = $DIC->language();
        $this->lng->loadLanguageModule('adn');

        $this->hid_tac = $hid_tac;
        $this->hid_tag_id = $hid_tag_id;
        $this->certificate_id = $certificate_id;
    }

    /**
     * Handle Request
     * @return
     */
    public function handleRequest() : string
    {
        $error_code = $this->verifyHidToken();
        if ($error_code !== self::SUCCESS) {
            return $this->handleError($error_code);
        }
        return $this->handleSuccess();
    }

    protected function verifyHidToken() : string
    {
        $verification = new adnHidVerification($this->hid_tac, $this->hid_tag_id);
        try {
            $response = $verification->verify();
        } catch (Exception $e) {
            $this->logger->error($e->getMessage());
            return self::ERROR_CONNECT;
        }
        return $response->getCode();
    }

    protected function handleSuccess() : string
    {
        $tpl = new ilTemplate(
            'tpl.checkcard.html',
            true,
            true,
            'Services/ADN/Card'
        );

        $status = $this->fillCertificate($tpl);

        if ($status === self::SUCCESS) {
            $tpl->setCurrentBlock('success');
            $tpl->setVariable('SUCCESS_MESSAGE_HEADING', $this->lng->txt('adn_card_verify_success'));
            $tpl->setVariable('TXT_ADN_CHECKCARD_SUCCESS', $this->lng->txt('adn_card_verify_success_info'));
            $tpl->parseCurrentBlock();
        } else {
            $tpl->setCurrentBlock('warning');
            $tpl->setVariable('FAIL_MESSAGE_HEADING', sprintf($this->lng->txt('adn_card_verify_error_code'), $status));
            $tpl->setVariable('TXT_ADN_CHECKCARD_FAILED', $this->lng->txt('adn_card_verify_generic'));
            $tpl->parseCurrentBlock();

        }
        return $tpl->get();

    }

    protected function fillCertificate(ilTemplate $tpl) : string
    {
        $certificate_id = adnCertificate::lookupIdByUuid($this->certificate_id);
        if ($certificate_id === 0) {
            return self::ERROR_INVALID_CERTIFICATE;
        }

        $certificate = new adnCertificate((int) $certificate_id);
        $professional = new adnCertifiedProfessional($certificate->getCertifiedProfessionalId());

        $tpl->setVariable('TXT_BESCHEINIGUNGSNUMMER', $certificate->getFullCertificateNumber());
        $tpl->setVariable('TXT_NAME', $professional->getLastName());
        $tpl->setVariable('TXT_VORNAME', $professional->getFirstName());
        if ($professional->getBirthdate() instanceof ilDate) {
            $tpl->setVariable('TXT_GEBURTSDATUM', $professional->getBirthdate()->get(IL_CAL_FKT_DATE, 'Y-m-d'));
        }
        $country = new adnCountry($professional->getCitizenship());
        $tpl->setVariable('TXT_STAATSANGEHOERIGKEIT', $country->getName());
        $wmo = new adnWMO($certificate->getIssuedByWmo());
        $tpl->setVariable('TXT_BEHOERDE', $wmo->getName());
        if ($certificate->getValidUntil() instanceof ilDate) {
            $tpl->setVariable('TXT_GUELTIGKEIT', $certificate->getValidUntil()->get(IL_CAL_FKT_DATE, 'Y-m-d'));
        }
        if ($professional->getImageHandler() instanceof adnCertifiedProfessionalImageHandler) {
            $image = 'data:image/png;base64,' . base64_encode(file_get_contents($professional->getImageHandler()->getAbsolutePath()));
            $tpl->setVariable('PERSONAL_ICON', $image);
        }

        $types = [];
        foreach (adnCertificate::getCertificateTypes() as $type => $caption) {
            if ($certificate->getType($type)) {
                $tpl->setCurrentBlock('cert_line');
                $tpl->setVariable('TXT_LISTEBESCHEINIGUNGEN_LINE', $this->lng->txt('adn_subject_area_cert_' . $type));
                $tpl->parseCurrentBlock();
            }
        }
        return self::SUCCESS;
    }

    protected function handleError(string $error_code) : string
    {
        $tpl = new ilTemplate(
            'tpl.checkcard.html',
            true,
            true,
            'Services/ADN/Card'
        );
        $tpl->setCurrentBlock('warning');
        $tpl->setVariable('FAIL_MESSAGE_HEADING', sprintf($this->lng->txt('adn_card_verify_error_code'), $error_code));
        $tpl->setVariable('TXT_ADN_CHECKCARD_FAILED', $this->lng->txt('adn_card_verify_generic'));
        $tpl->parseCurrentBlock();

        return $tpl->get();
    }


    protected function initEnvironment() : void
    {
        global $DIC;

        $this->logger = $DIC->logger()->adn();
        $this->http_request = $DIC->http()->request();
        $this->hid_tac = $this->http_request->getQueryParams()['tac'] ?? '';
        $this->hid_tag_id = $this->http_request->getQueryParams()['tagID'] ?? '';
        $this->certificate_id = $this->http_request->getQueryParams()['certificateID'] ?? '';
        $this->lng = $DIC->language();
        $this->lng->loadLanguageModule('adn');
    }
}
