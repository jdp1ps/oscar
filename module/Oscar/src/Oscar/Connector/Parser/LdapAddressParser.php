<?php

namespace Oscar\Connector\Parser;

class LdapAddressParser
{
    public function parse(string $address): \stdClass
    {
        $object = (object)[
            'street1' => null,
            'street2' => null,
            'street3' => null,
            'zipCode' => null,
            'city' => null,
            'country' => null
        ];
        $addressFields = explode('$', $address);

        if (!empty($addressFields) && !preg_match('/^\d\w{4}/', end($addressFields))) {
            $object->country = array_pop($addressFields);
        }

        if (!empty($addressFields)) {
            $zipCityField = array_pop($addressFields);
            if (preg_match('/(\d{4,5})/', $zipCityField, $matches)) {
                $object->zipCode = $matches[0];
                $object->city = trim(str_replace($matches[0], '', $zipCityField));
            } else {
                throw new \Exception("Invalid address: $address");
            }
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


