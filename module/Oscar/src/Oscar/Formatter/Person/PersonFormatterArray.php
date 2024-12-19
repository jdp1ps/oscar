<?php

namespace Oscar\Formatter\Person;

use Laminas\Mvc\Controller\Plugin\Url;
use Oscar\Entity\Person;

class PersonFormatterArray
{
    private ?Url $url = null;

    /**
     * @param Url|null $url
     */
    public function __construct(?Url $url = null)
    {
        $this->url = $url;
    }


    public function format(Person $person, ?Url $urlMaker = null): array
    {
        $url_show = "";
        /** @var Url $url */
        $url = $urlMaker ?: $this->url;
        if ($url) {
            $url_show = $url->fromRoute('person/show', ['id' => $person->getId()]);
        }

        return [
            'id'          => $person->getId(),
            'firstname'   => $person->getFirstname(),
            'lastname'    => $person->getLastname(),
            'fullname'    => $person->getFullname(),
            'email'       => $person->getEmail(),
            'location'    => $person->getLdapSiteLocation(),
            'affectation' => $person->getLdapAffectation(),
            'gravatar'    => $person->getMd5Email(),
            'url_show'    => $url_show,
        ];
    }
}