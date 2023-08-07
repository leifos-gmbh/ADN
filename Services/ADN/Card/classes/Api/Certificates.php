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


namespace ADN\Card\Api;

class Certificates extends CertificateElement
{
    private const ROOT_NAMESPACE = 'plzft';
    private array $certificates = [];

    public function __construct()
    {

    }

    public function setCertificates(array $certificates)
    {
        $this->certificates = $certificates;
    }

    public function toXml() : string
    {
        $writer = new \XMLWriter();
        $writer->openMemory();
        $writer->startDocument('1.0', 'UTF-8');
        $writer->startElementNs(self::ELEMENT_NS, 'root', self::ROOT_NAMESPACE);
        foreach ($this->certificates as $certificate) {
            $certificate->toXml($writer);
        }
        $writer->endElement();
        return $writer->outputMemory();
    }
}