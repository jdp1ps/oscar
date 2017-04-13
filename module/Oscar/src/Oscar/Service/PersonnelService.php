<?php

namespace Oscar\Service;

use Doctrine\ORM\EntityManager;
use Monolog\Logger;
use Oscar\Entity\Person;
use Oscar\Entity\PublicPeople;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorAwareTrait;

/**
 * Class PersonnelService.
 *
 * Cette classe assure la partie métier qui communique avec LDap pour récupérer
 * les informations personnelles des utilisateurs. Ce service est principalement
 * utilisé dans l'API.
 */
class PersonnelService implements ServiceLocatorAwareInterface
{
    use ServiceLocatorAwareTrait;

    const LDAP_FILTER_EMAIL = '(&(|(proxyAddresses=*:%s)(mail=%s)))';


    public function tryMerge( array $persons ){
        /** @var Person $keep */
        $keep = $persons[0];
        for( $i=1; $i<count($persons); $i++ ){
            /** @var Person $other */
            $other = $persons[$i];


        }

    }

    /**
     * @return \UnicaenApp\Mapper\Ldap\People
     */
    protected function getServiceLdap()
    {
        return $this->getServiceLocator()->get('ldap_people_service')->getMapper();
    }

    /**
     * @return Logger
     */
    protected function getLogger()
    {
        return $this->getServiceLocator()->get('Logger');
    }

    /**
     * Recherche les personnes dans la base de données d'Oscar.
     *
     * @param $search
     */
    public function searchStaffOscar($search)
    {
        /** @var $em EntityManager */
        $em = $this->getServiceLocator()->get('doctrine.entitymanager.orm_default');
        $persons = $em->getRepository('Oscar\Entity\Person')->search($search);

        return $persons;
    }

    public function getLdapByEmail($email)
    {
        $this->getLogger()->addDebug(sprintf("Request LDAP with mail '%s'", $email));
        $persons = $this->getServiceLdap()->searchSimplifiedEntries(sprintf(self::LDAP_FILTER_EMAIL, $email, $email));
        var_dump($persons);
    }

    /**
     * Lance une recherche sur le Ldap à partir de la chaîne de caractère transmise.
     *
     * @param $search string
     * @param bool $groupByStatus
     *
     * @return array
     */
    public function searchStaff($search, $groupByStatus = true)
    {
        $out = array();
        $group = array();

        $this->getLogger()->addDebug(sprintf("Request LDAP with '%s'", $search));

        foreach ($this->getServiceLdap()->getLdap()->searchSimplifiedEntries($search) as $people) {
            //var_dump($people);
            $currentStatus = $people->getUcbnStatus();
            if ($groupByStatus) {
                if (!isset($group[$currentStatus])) {
                    $group[$currentStatus] = count($group);

                    $out[] = array(
                        'text' => $currentStatus,
                        'children' => array(),
                    );
                }
                $pushIn = &$out[$group[$currentStatus]]['children'];
            } else {
                $pushIn = &$out;
            }
            $p = new PublicPeople($people);
            $pushIn[] = $p->toJson();
        }

        return $out;
    }
}
