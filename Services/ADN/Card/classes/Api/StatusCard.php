<?php

namespace ADN\Card\Api;

use XMLWriter;

class StatusCard
{
    private const ROOT_NAMESPACE = 'plzft';
    protected const ELEMENT_NS = 'plzft';

    private string $certificate_id = '';

    public function __construct()
    {
    }

    public function getCertificateId() : string
    {
        return $this->certificate_id;
    }

    public function setCertificateId(string $certificate_id) : void
    {
        $this->certificate_id = $certificate_id;
    }

    public function toXml(): string
    {
        $writer = new XMLWriter();
        $writer->openMemory();
        $writer->startDocument('1.0', 'UTF-8');
        $writer->startElementNs(self::ELEMENT_NS, 'root', self::ROOT_NAMESPACE);
        $writer->writeElementNs(self::ELEMENT_NS, 'CertificateId', null, $this->getCertificateId());
        $writer->endElement();
        return $writer->outputMemory();
    }
}
