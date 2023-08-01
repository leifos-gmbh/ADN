<?php

namespace ADN\Card\Api;

class Certificates extends CertificateElement
{
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
        $writer->startElementNs(self::ELEMENT_NS, 'root', null);
        foreach ($this->certificates as $certificate) {
            $certificate->toXml($writer);
        }
        $writer->endElement();
        return $writer->outputMemory();
    }
}