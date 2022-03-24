<?php
/**
 * @author Stéphane Bouvry<stephane.bouvry@unicaen.fr>
 * @date: 16-09-23 13:54
 * @copyright Certic (c) 2016
 */

namespace Oscar\Entity;


use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\NoResultException;


class PcruSourceFinancementRepository extends EntityRepository
{
    public function getFlatArrayLabel(): array
    {
        $query = $this->createQueryBuilder('psf');
        $query->select('psf.label');
        $entities = $query->getQuery()->getResult();
        return array_map('current', $entities);
    }

    /**
     * @param string $label
     * @return PcruSourceFinancement|null
     */
    public function getByLabel( string $label ): ?PcruSourceFinancement
    {
        return $this->findOneByLabel($label);
    }
}