<?php
/**
 * @author Stéphane Bouvry<stephane.bouvry@unicaen.fr>
 * @date: 18/06/15 12:46
 * @copyright Certic (c) 2015
 */

namespace Oscar\Entity;

use Doctrine\ORM\NoResultException;
use Doctrine\ORM\Query\ResultSetMapping;
use Doctrine\ORM\QueryBuilder;
use GuzzleHttp\Exception\TooManyRedirectsException;
use Oscar\Exception\OscarException;

class ContractDocumentRepository extends AbstractTreeDataRepository
{
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    /// ONGLETS
    /**
     * @return TabDocument[]
     */
    public function getTabDocuments(): array
    {
        return $this->getEntityManager()->getRepository(TabDocument::class)->findBy([], ['label' => 'ASC']);
    }

    /**
     * @param int $tabDocumentId
     * @return TabDocument
     */
    public function getTabDocumentById(int $tabDocumentId): TabDocument
    {
        return $this->getEntityManager()->getRepository(TabDocument::class)->findOneBy(['id' => $tabDocumentId]);
    }

    /**
     * @return TabDocument|null
     */
    public function getDefaultTabDocument(): ?TabDocument
    {
        return $this->getEntityManager()->getRepository(TabDocument::class)->findOneBy(['default' => true]);
    }


    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    /// DOCUMENTS

    /**
     * Retourne toutes les versions d'un même document.
     *
     * @param ContractDocument $contractDocument
     * @return ContractDocument[]
     */
    public function getDocumentsForFilenameAndActivity(ContractDocument $contractDocument): array
    {
        $query = $this->createQueryBuilder('c')
            ->where('c.fileName = :fileName AND c.grant = :activityId')
            ->setParameters(
                [
                    'fileName' => $contractDocument->getFileName(),
                    'activityId' => $contractDocument->getActivity()
                ]
            );
        return $query->getQuery()->getResult();
    }

    /**
     * @return ContractDocument[]
     */
    public function getAllDocuments(): array
    {
        return $this->baseQuery()->addOrderBy('d.dateUpdoad', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Liste des types de documents disponibles.
     *
     * @return TypeDocument[]
     */
    public function getTypes(): array
    {
        return $this->getEntityManager()->getRepository(TypeDocument::class)
            ->findBy([], ['label' => 'ASC']);
    }

    public function countUntypedDocuments(): int
    {
        $infos = $this->getInfosTypes();
        if( array_key_exists("", $infos) ){
            return $infos[""];
        }
        return 0;
    }

    /**
     * Retourne un tableau associatif sous la forme ID => COUNT
     *
     * @return array
     */
    public function getInfosTypes() :array
    {
        $out = [];
        $rsm = new ResultSetMapping();
        $rsm->addScalarResult('total', 'total');
        $rsm->addScalarResult('type', 'type');
        $sql = 'SELECT COUNT(d.id) as total, d.typedocument_id AS type 
                    FROM contractdocument d 
                    GROUP BY d.typedocument_id 
                    ';
        $query = $this->getEntityManager()->createNativeQuery($sql, $rsm);
        $datas = $query->getScalarResult();
        foreach ($datas as $counted) {
            $type = $counted['type'];
            $total = $counted['total'];
            $out[$type] = $total;
        }

        return $out;
    }

    /**
     * Retourne le type de document par défaut (ou NULL si aucun n'est disponible).
     *
     * @return TypeDocument|null
     */
    public function getDefaultTypeDocument(): ?TypeDocument
    {
        return $this->getEntityManager()->getRepository(TypeDocument::class)->findOneBy(['default' => true]);
    }

    public function createOrUpdateTypeDocument(?int $id = null, string $label = "", string $description = "", bool $default = false ) :void {
        if( $id == null ){
            $type = new TypeDocument();
            $this->getEntityManager()->persist($type);
        } else {
            $type = $this->getType($id);
        }

        if( $default === true ){
            foreach ($this->getTypes() as $t) {
                $t->setDefault(false);
            }
        }

        $type->setLabel($label)
            ->setDefault($default)
            ->setDescription($description);

        $this->getEntityManager()->flush();
    }

    /**
     * Retourne la liste des types de documents sous la forme ID => Label
     *
     * @return array
     */
    public function getTypesSelectable(bool $countDocUntab = false): array
    {
        $out = [];
        foreach ($this->getTypes() as $type) {
            $out[$type->getId()] = $type->getLabel();
        }
        if ($countDocUntab) {
            $rsm = new ResultSetMapping();
            $rsm->addScalarResult('total', 'total');
            $rsm->addScalarResult('type', 'type');
            $sql = 'SELECT COUNT(d.id) as total, d.typedocument_id AS type 
                    FROM contractdocument d 
                    WHERE d.tabdocument_id IS NULL  
                    GROUP BY d.typedocument_id 
                    ';
            $query = $this->getEntityManager()->createNativeQuery($sql, $rsm);
            $datas = $query->getScalarResult();
            foreach ($datas as $counted) {
                $type = $counted['type'];
                $total = $counted['total'];
                if ($total > 0) {
                    $out[$type] .= " ($total document(s) non rangés)";
                }
            }
        }
        return $out;
    }

    /**
     * Retourne la liste des onglets de documents sous la forme ID => Label
     *
     * @return array
     */
    public function getTabDocumentSelectable(): array
    {
        $out = [];
        foreach ($this->getTabDocuments() as $tab) {
            $out[$tab->getId()] = $tab->getLabel();
        }
        return $out;
    }

    /**
     * Mise à jour automatique des documents sans onglet en fonction de leur TypeDocument.
     *
     * @param int $fromTypeDocumentId
     * @param int $toTabDocumentId
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function migrateUntabledDocument(int $fromTypeDocumentId, int $toTabDocumentId): void
    {
        // TODO Trouver un moyen de faire ça en une seule requête
        $documents = $this->getEntityManager()->createQueryBuilder()
            ->select('d')
            ->from(ContractDocument::class, 'd')
            ->leftJoin('d.tabDocument', 't')
            //->where('d IS NULL')
            ->andWhere('d.typeDocument = :typeDocument')
            ->setParameter('typeDocument', $fromTypeDocumentId)
            ->getQuery()
            ->getResult();

        $tab = $this->getTabDocumentById($toTabDocumentId);

        /** @var ContractDocument $document */
        foreach ($documents as $document) {
            if (!$document->getTabDocument()) {
                $document->setTabDocument($tab);
            }
        }

        $this->getEntityManager()->flush();
    }

    /**
     * @param int $typeDocumentId
     * @return TypeDocument|null
     */
    public function getType(int $typeDocumentId): ?TypeDocument
    {
        return $this->getEntityManager()->getRepository(TypeDocument::class)
            ->findOneBy(['id' => $typeDocumentId]);
    }

    public function migrateUntypedDocuments( TypeDocument $typeDocument ):void
    {
        $qb = $this->createQueryBuilder('d')
            ->where('d.typeDocument IS NULL');
        /** @var ContractDocument $document */
        foreach ($qb->getQuery()->getResult() as $document) {
            $document->setTypeDocument($typeDocument);
        }
        $this->getEntityManager()->flush();
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
        } catch (NoResultException | TooManyRedirectsException $e) {
            if ($throw) {
                throw new OscarException(sprintf("Le document '%s' n'existe pas", $id));
            }
        }
        return null;
    }

    /**
     * @return \Doctrine\ORM\QueryBuilder
     */
    protected function baseQuery(): QueryBuilder
    {
        return $this->getEntityManager()->createQueryBuilder()
            ->select('d, p, g')
            ->from(ContractDocument::class, 'd')
            ->leftJoin('d.person', 'p')
            ->leftJoin('d.grant', 'g');
    }
}