<?php
/**
 * @author Stéphane Bouvry<stephane.bouvry@unicaen.fr>
 * @date: 16-09-23 13:54
 * @copyright Certic (c) 2016
 */

namespace Oscar\Entity;


use Doctrine\ORM\EntityRepository;
use Oscar\Connector\IConnectedRepository;
use Oscar\Exception\OscarException;
use Oscar\Provider\Privileges;

class PrivilegeRepository extends EntityRepository
{
    public function getPrivilegeByCode( $code ){

        $privilegeQuery = $this->getEntityManager()->createQueryBuilder()
            ->select('p')
            ->from(Privilege::class, 'p')
            ->innerJoin('p.categorie', 'c')
            ->where("concat(c.code, '-', p.code) = :code")
            ->getQuery();

        try {
            return $privilegeQuery->setParameter('code', $code)->getSingleResult();
        }catch (NoResultException $e ){
            throw new OscarException("Privilege introuvable");
        }
        catch (\Exception $e ){
            throw new OscarException("Erreur inattendue : " . $e->getMessage());
        }
    }
}