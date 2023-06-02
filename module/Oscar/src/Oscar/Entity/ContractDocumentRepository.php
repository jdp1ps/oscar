<?php
/**
 * @author Stéphane Bouvry<stephane.bouvry@unicaen.fr>
 * @date: 18/06/15 12:46
 * @copyright Certic (c) 2015
 */

namespace Oscar\Entity;


use Doctrine\ORM\NoResultException;
use Oscar\Exception\OscarException;

class ContractDocumentRepository extends AbstractTreeDataRepository
{
    /**
     * @return ContractDocument[]
     */
    public function getAllDocuments() :array
    {
        return $this->baseQuery()->addOrderBy('d.dateUpdoad', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @param int $id
     * @param bool $throw
     * @return ContractDocument|null
     * @throws OscarException
     */
    public function getDocument(int $id, bool $throw = false): ?ContractDocument
    {
        $query = $this->baseQuery()
            ->where('d.id = :id')
            ->setParameter('id', $id);

        try {
            return $query->getQuery()->getSingleResult();
        } catch (\Exception $e) {
            if ($throw) {
                throw new OscarException(sprintf("Le document '%s' n'existe pas", $id));
            }
        }
        return null;
    }

    /**
     * @return \Doctrine\ORM\QueryBuilder
     */
    protected function baseQuery()
    {
        return $this->getEntityManager()->createQueryBuilder()
            ->select('d, p, g')
            ->from(ContractDocument::class, 'd')
            ->leftJoin('d.person', 'p')
            ->leftJoin('d.grant', 'g');
    }
}