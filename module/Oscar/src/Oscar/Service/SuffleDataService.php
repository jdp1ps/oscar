<?php
/**
 * Created by PhpStorm.
 * User: jacksay
 * Date: 23/06/17
 * Time: 10:39
 */

namespace Oscar\Service;


use Oscar\Entity\Organization;
use Oscar\Entity\Person;
use UnicaenApp\Service\EntityManagerAwareInterface;
use UnicaenApp\Service\EntityManagerAwareTrait;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorAwareTrait;

class SuffleDataService implements ServiceLocatorAwareInterface, EntityManagerAwareInterface
{
    use ServiceLocatorAwareTrait, EntityManagerAwareTrait;

    public function shufflePersons() {
        $persons = $this->getEntityManager()->getRepository(Person::class)->findAll();
        $noms = [];
        $prenoms = [];
        /** @var Person $person */
        foreach ( $persons as $person ){
            $noms[] = $person->getLastname();
            $prenoms[] = $person->getFirstname();
            echo $person->getEmail() . " - ";
        }
        shuffle($noms);
        shuffle($prenoms);
        $i = 0;

        foreach ( $persons as $person ){
            $person->setFirstname($prenoms[$i])
                ->setEmail(md5($person->getEmail()).'@oscar-demo.fr')
                ->setLadapLogin(null)
                ->setLdapAffectation(null)
                ->setLdapSiteLocation(null)
                ->setLastname($noms[$i]);
            $i++;
        }

        $this->getEntityManager()->flush();
    }

    public function shuffleOrganizations() {
        $organizations = $this->getEntityManager()->getRepository(Organization::class)->findAll();
        $datas = [];
        /** @var Organization $organization */
        foreach ( $organizations as $organization ){
            echo $organization->getType()."\n";
        }
    }
}

