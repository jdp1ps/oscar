<?php


namespace Oscar\Formatter\person;


use Oscar\Formatter\IFormatter;

class PersonToJsonBasic implements IPersonFormatter
{
    public function format($person, ?array $options = null): array
    {
        return [
            'person' => $person->getDisplayName(),
            'mail' => $person->getEmail(),
            'mailMd5' => md5($person->getEmail()),
            'person_id' => $person->getId()
        ];
    }
}