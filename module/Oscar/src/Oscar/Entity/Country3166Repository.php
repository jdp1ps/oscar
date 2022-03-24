<?php
/**
 * @author Stéphane Bouvry<stephane.bouvry@unicaen.fr>
 * @date: 18/06/15 12:46
 * @copyright Certic (c) 2015
 */

namespace Oscar\Entity;


use Doctrine\ORM\EntityRepository;

class Country3166Repository extends EntityRepository
{
    public function allKeyByAlpha2()
    {
        $out = [];
        /** @var Country3166 $country */
        foreach ($this->getAll() as $country) {
            $out[$country->getAlpha2()] = $country;
        }

        return $out;
    }

    public function getAll()
    {
        $q = $this->createQueryBuilder('q')->orderBy('q.alpha2');
        return $q->getQuery()->getResult();
    }

    public function getAllForSelects()
    {
        $out = ["" => "Non-définit"];
        /** @var Country3166 $country */
        foreach ($this->getAll() as $country) {
            $out[$country->getFr()] = $country->getFr();
        }
        return $out;
    }
}