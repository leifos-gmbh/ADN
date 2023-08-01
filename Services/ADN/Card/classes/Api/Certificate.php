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

class Certificate extends CertificateElement
{
    protected string $certificateId = '';
    protected string $certificateNumber = '';
    protected string $lastname = '';
    protected string $firstname = '';
    protected string $nationality = '';
    protected ?\DateTime $birthday = null;
    protected string $issuedBy = '';
    protected ?\DateTime $validUntil = null;

    protected array $certificateTypes = [];
    protected string $photo = '';
    protected ?PostalAddress $postalAddress = null;
    protected ?ReturnAddress $returnAddress = null;



    public function __construct()
    {
    }


    public function getCertificateId() : string
    {
        return $this->certificateId;
    }

    public function setCertificateId(string $certificateId) : void
    {
        $this->certificateId = $certificateId;
    }

    public function getCertificateNumber() : string
    {
        return $this->certificateNumber;
    }

    public function setCertificateNumber(string $certificateNumber) : void
    {
        $this->certificateNumber = $certificateNumber;
    }

    public function getLastname() : string
    {
        return $this->lastname;
    }

    public function setLastname(string $lastname) : void
    {
        $this->lastname = $lastname;
    }

    public function getFirstname() : string
    {
        return $this->firstname;
    }

    public function setFirstname(string $firstname) : void
    {
        $this->firstname = $firstname;
    }

    public function getNationality() : string
    {
        return $this->nationality;
    }

    public function setNationality(string $nationality) : void
    {
        $this->nationality = $nationality;
    }

    public function getBirthday() : ?\DateTime
    {
        return $this->birthday;
    }

    public function setBirthday(?\DateTime $birthday) : void
    {
        $this->birthday = $birthday;
    }

    public function getIssuedBy() : string
    {
        return $this->issuedBy;
    }

    public function setIssuedBy(string $issuedBy) : void
    {
        $this->issuedBy = $issuedBy;
    }

    public function getValidUntil() : ?\DateTime
    {
        return $this->validUntil;
    }

    public function setValidUntil(?\DateTime $validUntil) : void
    {
        $this->validUntil = $validUntil;
    }

    public function getCertificateTypes() : array
    {
        return $this->certificateTypes;
    }

    public function setCertificateTypes(array $certificateTypes) : void
    {
        $this->certificateTypes = $certificateTypes;
    }

    public function getPhoto() : string
    {
        return $this->photo;
    }

    public function setPhoto(string $photo) : void
    {
        $this->photo = $photo;
    }

    public function getPostalAddress() : ?PostalAddress
    {
        return $this->postalAddress;
    }

    public function setPostalAddress(?PostalAddress $postalAddress) : void
    {
        $this->postalAddress = $postalAddress;
    }

    public function getReturnAddress() : ?ReturnAddress
    {
        return $this->returnAddress;
    }

    public function setReturnAddress(?ReturnAddress $returnAddress) : void
    {
        $this->returnAddress = $returnAddress;
    }

    public function toXml(\XMLWriter $writer) : void
    {
        $writer->startElementNs(self::ELEMENT_NS, 'Certificate', null);
        $writer->writeElementNs(self::ELEMENT_NS, 'CertificateId', null, $this->getCertificateId());
        $writer->writeElementNs(self::ELEMENT_NS, 'CertificateNumber', null, $this->getCertificateNumber());
        $writer->writeElementNs(self::ELEMENT_NS, 'Lastname', null, $this->getLastname());
        $writer->writeElementNs(self::ELEMENT_NS, 'Firstname', null, $this->getFirstname());
        $writer->writeElementNs(self::ELEMENT_NS, 'Nationality', null, $this->getNationality());
        $writer->writeElementNs(self::ELEMENT_NS, 'IssuedBy', null, $this->getIssuedBy());
        $writer->writeElementNs(self::ELEMENT_NS, 'Birthday', null, $this->getBirthday()->format('Y-m-d'));
        $writer->writeElementNs(self::ELEMENT_NS, 'ValidUntil', null, $this->getValidUntil()->format('Y-m-d'));
        $writer->startElementNs(self::ELEMENT_NS, 'CertificateTypes', null);
        foreach ($this->getCertificateTypes() as $type_string) {
            $writer->writeElementNs(self::ELEMENT_NS, 'CertificateType', null, $type_string);
        }
        $writer->endElement();
        $writer->writeElementNs(self::ELEMENT_NS, 'Photo', null, $this->getPhoto());
        $this->getPostalAddress()->toXml($writer);
        $this->getReturnAddress()->toXml($writer);
        $writer->endElement();
    }


}