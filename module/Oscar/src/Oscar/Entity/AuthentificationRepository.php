<?php
namespace Oscar\Entity;

use Doctrine\ORM\EntityRepository;

class AuthentificationRepository extends EntityRepository
{
    /**
     * @param Person $person
     * @return Authentification
     */
    public function getAuthentificationPerson(Person $person){
        return $this->getAuthentificationByUsername($person->getLadapLogin());
    }

    /**
     * @return Authentification
     */
    public function getAuthentificationByUsername( $username ){
        return $this->createQueryBuilder('a')
            ->where('a.username = :username')
            ->setParameter('username', $username)
            ->getQuery()
            ->getSingleResult();
    }
}