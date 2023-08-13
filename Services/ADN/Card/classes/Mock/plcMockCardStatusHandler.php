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

use Slim\Http\Request as Request;
use Slim\Http\Response as Response;

class plcMockCardStatusHandler
{
    private const XML_NS = 'plzft';
    private const ID = '9';
    private const CERTIFICATE_ID = '43328b28ba63c28691b3d7198e8b80bf';
    private const UID = 'A0A1B2C3D4E5F';

    private ilLogger $logger;

    public function __construct()
    {
        global $DIC;
        $this->logger = $DIC->logger()->adn();
    }

    public function status(Request $request, Response $response) : Response
    {
        $certificate_id = $this->parseCertificateId((string) $request->getBody()->getContents());

        $status = rand(0, 3);
        $xml = '';
        switch ($status) {
            case 0:
            case 1:
            case 2:
                $xml = $this->writeResponseXmlWithNamespaces($certificate_id, $status);
                break;
            default:
                // return 480 status
                return $response
                    ->withHeader('ContentType', 'text/plain')
                    ->withStatus(480, 'ERROR WHILE PROCESSING ORDER STATUS REQUEST');

        }
        $response->getBody()->write($xml);
        return $response
            ->withHeader('ContentType', 'application/xml')
            ->withStatus(200);

    }

    protected function writeResponseXml(string $certificate_id, int $production_code): string
    {
        $writer = new XMLWriter();
        $writer->openMemory();
        $writer->startDocument('1.0', 'UTF-8');
        $writer->startElement('CardStateResponse');
        $writer->writeElement('Id', self::ID);
        $writer->writeElement('CertificateId', $certificate_id);
        $writer->writeElement('ProductionState', (string) $production_code);
        $writer->writeElement('UID', self::UID);
        $writer->endElement();
        $this->logger->dump('Outgoing: ' . $writer->outputMemory(false));
        return $writer->outputMemory();

    }

    protected function writeResponseXmlWithNamespaces(string $certificate_id, int $production_code): string
    {
        $writer = new XMLWriter();
        $writer->openMemory();
        $writer->startDocument('1.0', 'UTF-8');
        $writer->startElementNs(self::XML_NS, 'CardStateResponse', self::XML_NS);
        $writer->writeElementNs(self::XML_NS, 'Id', null, self::ID);
        $writer->writeElementNs(self::XML_NS, 'CertificateId', null, $certificate_id);
        $writer->writeElementNs(self::XML_NS, 'ProductionState', null, (string) $production_code);
        $writer->writeElementNs(self::XML_NS, 'UID', null, self::UID);
        $writer->endElement();
        $this->logger->dump('Outgoing: ' . $writer->outputMemory(false), ilLogLevel::DEBUG);
        return $writer->outputMemory();
    }

    protected function parseCertificateId(string $xml_content): string
    {
        $this->logger->dump('Got......................' . $xml_content, ilLogLevel::DEBUG);
        $root = new SimpleXMLElement($xml_content, 0, false, self::XML_NS, false);
        $certificate_id = '';
        foreach ($root->children(self::XML_NS, true) as $element) {
            switch ($element->getName()) {
                case 'CertificateId':
                    $certificate_id = (string) $element;
                    break;
                default:
                    $this->logger->warning('Element name: ' . (string) $element);
            }
        }
        $this->logger->dump('Found ' . (string) $certificate_id);
        return (string) $certificate_id;
    }


}