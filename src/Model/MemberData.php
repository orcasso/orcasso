<?php

namespace App\Model;

use App\Entity\Member;

class MemberData
{
    public string $gender = Member::GENDER_MALE;

    public string $firstName = '';

    public string $lastName = '';

    public \DateTimeImmutable $birthDate;

    public string $email = '';

    public string $phoneNumber = '';

    public string $street1 = '';

    public string $street2 = '';

    public string $street3 = '';

    public string $postalCode = '';

    public string $city = '';

    public static function denormalize(array $memberData): static
    {
        $object = new static();
        $object->gender = $memberData['gender'] ?? '';
        $object->firstName = $memberData['firstName'] ?? '';
        $object->lastName = $memberData['lastName'] ?? '';
        $object->birthDate = date_create_immutable(($memberData['birthDate'] ?? date('Y-m-d')).' 00:00:00');
        $object->email = $memberData['email'] ?? '';
        $object->phoneNumber = $memberData['phoneNumber'] ?? '';
        $object->street1 = $memberData['street1'] ?? '';
        $object->street2 = $memberData['street2'] ?? '';
        $object->street3 = $memberData['street3'] ?? '';
        $object->postalCode = $memberData['postalCode'] ?? '';
        $object->city = $memberData['city'] ?? '';

        return $object;
    }

    public function normalize(): array
    {
        return [
            'gender' => $this->gender,
            'firstName' => $this->firstName,
            'lastName' => $this->lastName,
            'birthDate' => $this->birthDate->format('Y-m-d'),
            'email' => $this->email,
            'phoneNumber' => $this->phoneNumber,
            'street1' => $this->street1,
            'street2' => $this->street2,
            'street3' => $this->street3,
            'postalCode' => $this->postalCode,
            'city' => $this->city,
        ];
    }

    public function toMember(?Member $member): Member
    {
        $member = $member ?? new Member();
        $member->setGender($this->gender);
        $member->setFirstName($this->firstName);
        $member->setLastName($this->lastName);
        $member->setBirthDate($this->birthDate);
        $member->setEmail($this->email);
        $member->setPhoneNumber($this->phoneNumber);
        $member->setStreet1($this->street1);
        $member->setStreet2($this->street2);
        $member->setStreet3($this->street3);
        $member->setPostalCode($this->postalCode);
        $member->setCity($this->city);

        return $member;
    }

    public function fromMember(Member $member): static
    {
        $this->gender = $member->getGender();
        $this->firstName = $member->getFirstName();
        $this->lastName = $member->getLastName();
        $this->birthDate = $member->getBirthDate();
        $this->email = $member->getEmail();
        $this->phoneNumber = $member->getPhoneNumber();
        $this->street1 = $member->getStreet1();
        $this->street2 = $member->getStreet2();
        $this->street3 = $member->getStreet3();
        $this->postalCode = $member->getPostalCode();
        $this->city = $member->getCity();

        return $this;
    }
}
