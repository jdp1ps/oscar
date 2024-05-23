<?php

namespace Oscar\Connector\Parser;

class LdapAddressParser
{
    public function parse(string $address): \stdClass
    {
        $object = (object) [
            'street1' => null,
            'street2' => null,
            'street3' => null,
            'zipCode' => null,
            'city' => null,
            'country' => null,
        ];
        $addressFields = explode('$', $address);

        // Vérifier et extraire le pays si présent
        if (!empty($addressFields) && !preg_match('/\d/', end($addressFields))) {
            $object->country = array_pop($addressFields);
        }

        // Vérifier et extraire le code postal et la ville
        if (!empty($addressFields)) {
            $zipCity = explode(' ', array_pop($addressFields), 2);
            if (preg_match('/^\d{4,5}$/', $zipCity[0])) {
                $object->zipCode = $zipCity[0];
                $object->city = $zipCity[1];
            } else {
                throw new \Exception("Invalid zip code: {$zipCity[0]}");
            }
        } else {
            throw new \Exception("Invalid address: $address");
        }

        if (!empty($addressFields)) {
            $object->street1 = array_shift($addressFields);
        } else {
            throw new \Exception("Invalid address: $address");
        }

        if (!empty($addressFields)) {
            $object->street2 = array_shift($addressFields);
        }

        if (!empty($addressFields)) {
            $object->street3 = implode(' ', $addressFields);
        }

        return $object;
    }
}
