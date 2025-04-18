<?php

namespace App\Model;

use App\Entity\LegalRepresentative;
use App\Entity\Member;

class LegalRepresentativeData
{
    public string $firstName = '';

    public string $lastName = '';

    public string $email = '';

    public string $phoneNumber = '';

    public static function denormalize(array $data): static
    {
        $object = new static();
        $object->firstName = $data['firstName'] ?? '';
        $object->lastName = $data['lastName'] ?? '';
        $object->email = $data['email'] ?? '';
        $object->phoneNumber = $data['phoneNumber'] ?? '';

        return $object;
    }

    public function normalize(): array
    {
        return [
            'firstName' => $this->firstName,
            'lastName' => $this->lastName,
            'email' => $this->email,
            'phoneNumber' => $this->phoneNumber,
        ];
    }

    public function toLegalRepresentative(?LegalRepresentative $legalRepresentative, Member $member): LegalRepresentative
    {
        $legalRepresentative = $legalRepresentative ?? new LegalRepresentative($member);
        $legalRepresentative->setFirstName($this->firstName);
        $legalRepresentative->setLastName($this->lastName);
        $legalRepresentative->setEmail($this->email);
        $legalRepresentative->setPhoneNumber($this->phoneNumber);

        return $legalRepresentative;
    }

    public function fromLegalRepresentative(LegalRepresentative $legalRepresentative): static
    {
        $this->firstName = $legalRepresentative->getFirstName();
        $this->lastName = $legalRepresentative->getLastName();
        $this->email = $legalRepresentative->getEmail();
        $this->phoneNumber = $legalRepresentative->getPhoneNumber();

        return $this;
    }
}
