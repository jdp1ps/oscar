<?php
namespace Oscar\Entity;

use Doctrine\ORM\EntityRepository;

class AuthentificationRepository extends EntityRepository
{
    /**
     * @param Person $person
     * @param bool $normalize
     * @return Authentification|null
     */
    public function getAuthentificationPersonNullable(Person $person, bool $normalize) :?Authentification
    {
        try {
            return $this->getAuthentificationPerson($person, $normalize);
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * @param Person $person
     * @param bool $normalize
     * @return Authentification
     * @throws \Doctrine\ORM\NoResultException
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getAuthentificationPerson(Person $person, bool $normalize = false) :Authentification
    {
        return $this->getAuthentificationByUsername($person->getLadapLogin(), $normalize);
    }

    /**
     * @param $username
     * @param false $normalize
     * @return Authentification
     * @throws \Doctrine\ORM\NoResultException
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getAuthentificationByUsername( $username, $normalize=false ) :Authentification
    {
        return $this->createQueryBuilder('a')
            ->where($normalize ? 'lower(a.username) = lower(:username)' : 'a.username = :username')
            ->setParameter('username', $username)
            ->getQuery()
            ->getSingleResult();
    }
}