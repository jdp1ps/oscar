<?php

namespace Oscar\Entity;

use Doctrine\ORM\EntityRepository;

class TabsDocumentsRepository extends EntityRepository
{
    /**
     * @return TabDocument[]
     */
    public function getTabsDocuments(): array
    {
        return $this->createQueryBuilder('t')
            ->getQuery()
            ->getArrayResult();
    }

    /**
     * @param int $tabDocumentId
     * @return ContractDocument[]
     */
    public function getDocumentsForTabId(int $tabDocumentId): array
    {
        $query = $this->getEntityManager()->createQueryBuilder()
            ->select('d')
            ->from(ContractDocument::class, 'd')
            ->where('d.tabDocument = :tabDocumentId')
            ->setParameter('tabDocumentId', $tabDocumentId);

        return $query->getQuery()->getResult();
    }
}
