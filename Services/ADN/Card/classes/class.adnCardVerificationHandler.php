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

    /**
     * @var \Psr\Http\Message\RequestInterface|\Psr\Http\Message\ServerRequestInterface
     */
    private $http_request;
    private ilLogger $logger;
    private ilLanguage $lng;

    private string $hid_tac = '';
    private string $hid_tag_id = '';
    private string $certificate_id = '';

    public function __construct()
    {
    }

    public function initRequest() : void
    {
        $this->initIlias();
        $this->initEnvironment();
    }

    /**
     * Handle Request
     * @return
     */
    public function handleRequest() : void
    {
        $error_code = $this->verifyHidToken();
        if ($error_code !== self::SUCCESS) {
            $this->handleError($error_code);
            return;
        }
        $this->handleSuccess();
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

    protected function handleSuccess() : void
    {
        $tpl = new ilTemplate(
            'tpl.checkcard.html',
            true,
            true,
            'Services/ADN/Card'
        );
        $tpl->setCurrentBlock('success');
        $tpl->setVariable('SUCCESS_MESSAGE_HEADING', $this->lng->txt('adn_card_verify_success'));
        $tpl->setVariable('TXT_ADN_CHECKCARD_SUCCESS', $this->lng->txt('adn_card_verify_success_info'));
        $tpl->parseCurrentBlock();

        $this->fillCertificate($tpl);

        echo $tpl->get();

    }

    protected function fillCertificate(ilTemplate $tpl)
    {
        $candidate = new adnCertifiedProfessional(1);


        $tpl->setCurrentBlock('has_card');
        $tpl->setVariable('TXT_BESCHEINIGUNGSNUMMER', '1-005-2020');
        $tpl->setVariable('TXT_NAME', $candidate->getLastName());
        $tpl->setVariable('TXT_VORNAME', $candidate->getFirstName());
        $tpl->setVariable('TXT_GEBURTSDATUM', $candidate->getBirthdate()->get(IL_CAL_FKT_DATE, 'Y-m-D'));
        $tpl->setVariable('TXT_STAATSANGEHOERIGKEIT', $candidate->getPostalCountry());
        $tpl->setVariable('TXT_BEHOERDE', 'GDWS Ost-Südsüd-West');
        $tpl->setVariable('TXT_GUELTIGKEIT', '10-10-2023');
        $tpl->setVariable('TXT_LISTEBESCHEINIGUNGEN_LINE', '8.2.1.3 (Trockengüterschiffe)');
        $tpl->setVariable('PERSONAL_ICON', $candidate->getImageHandler()->getAbsolutePath());
        $tpl->parseCurrentBlock();
    }

    protected function handleError(string $error_code) : void
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

        echo $tpl->get();
    }

    protected function initIlias()
    {
        $_COOKIE['ilClientId'] = $_GET['client_id'] = 'adn';

        include_once "Services/Context/classes/class.ilContext.php";
        ilContext::init(ilContext::CONTEXT_REST);

        require_once("Services/Init/classes/class.ilInitialisation.php");
        ilInitialisation::initILIAS();
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
