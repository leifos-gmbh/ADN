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

namespace ADN\Card\Api;

class PostalAddress extends CertificateElement
{
    private string $addressName = '';
    private string $addressStreet = '';
    private string $addressPostalCode = '';
    private string $addressCity = '';
    private string $addressCountry = '';

    public function __construct()
    {
    }

    public function getAddressName() : string
    {
        return $this->addressName;
    }

    public function setAddressName(string $addressName) : void
    {
        $this->addressName = $addressName;
    }

    public function getAddressStreet() : string
    {
        return $this->addressStreet;
    }

    public function setAddressStreet(string $addressStreet) : void
    {
        $this->addressStreet = $addressStreet;
    }

    public function getAddressPostalCode() : string
    {
        return $this->addressPostalCode;
    }

    public function setAddressPostalCode(string $addressPostalCode) : void
    {
        $this->addressPostalCode = $addressPostalCode;
    }

    public function getAddressCity() : string
    {
        return $this->addressCity;
    }

    public function setAddressCity(string $addressCity) : void
    {
        $this->addressCity = $addressCity;
    }

    public function getAddressCountry() : string
    {
        return $this->addressCountry;
    }

    public function setAddressCountry(string $addressCountry) : void
    {
        $this->addressCountry = $addressCountry;
    }

    public function toXml(\XMLWriter $writer) : void
    {
        $writer->startElementNs(self::ELEMENT_NS, 'PostalAddress', null);
        $writer->writeElementNs(self::ELEMENT_NS, 'AddressName', null, $this->getAddressName());
        $writer->writeElementNs(self::ELEMENT_NS, 'AddressStreet', null, $this->getAddressStreet());
        $writer->writeElementNs(self::ELEMENT_NS, 'AddressPostalCode', null, $this->getAddressPostalCode());
        $writer->writeElementNs(self::ELEMENT_NS, 'AddressCity', null, $this->getAddressCity());
        $writer->writeElementNs(self::ELEMENT_NS, 'AddressCountry', null, $this->getAddressCountry());
        $writer->endElement();
    }


}