<?php
/**
 * Created by PhpStorm.
 * User: jacksay
 * Date: 23/06/17
 * Time: 10:39
 */

namespace Oscar\Service;


use Oscar\Entity\Activity;
use Oscar\Entity\Organization;
use Oscar\Entity\Person;
use Oscar\Entity\Project;
use UnicaenApp\Service\EntityManagerAwareInterface;
use UnicaenApp\Service\EntityManagerAwareTrait;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorAwareTrait;

class ShuffleDataService implements ServiceLocatorAwareInterface, EntityManagerAwareInterface
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
        /** @var Person $person */
        foreach ( $persons as $person ){
            $person->setFirstname($prenoms[$i])
                ->setCodeHarpege(null)
                ->setConnector([])
               // ->setCo
                ->setEmail(md5($person->getEmail()).'@oscar-demo.fr')
                ->setLadapLogin(null)
                ->setCodeLdap(null)
                ->setPhone(null)
                ->setLadapLogin(null)
                ->setLdapAffectation(null)
                ->setLdapSiteLocation(null)

                ->setLastname($noms[$i]);
            $i++;
        }

        $this->getEntityManager()->flush();
    }

    public function shuffleProjects(){
        $projects = $this->getEntityManager()->getRepository(Project::class)->findAll();
        $datas = [];
        /** @var Project $project */
        foreach ($projects as $project ){
            $datas[] = [
                'acronym' => $project->getAcronym(),
                'label' => $project->getLabel(),
                'description' => $project->getDescription(),
            ];
        }
        shuffle($datas);
        $i = 0;
        foreach ($projects as $project ){
            $data = $datas[$i++];
            $project->setAcronym($data['acronym'])
                ->setLabel($data['label'])
                ->setDescription($data['description']);
        }
        $this->getEntityManager()->flush();

    }

    public function shuffleOrganizations() {
        $organizations = $this->getEntityManager()->getRepository(Organization::class)->findAll();
        $datas = [];
        /** @var Organization $organization */
        foreach ( $organizations as $organization ){
            $type = $organization->getType() ? $organization->getType() : 'none';
            if( !array_key_exists($type, $datas ) ){
                $datas[$type] = [];
            }
            $datas[$type][] = [
                'shortname' => $organization->getShortName(),
                'fullname' => $organization->getFullName(),
                'code' => $organization->getCode() ? strtoupper(substr(md5($organization), 0, 5)) : null,
            ];
        }
        $count = [];
        foreach( $datas as $type=>$orgas ){
            shuffle($datas[$type]);
            $count[$type] = 0;
        }
        /** @var Organization $organization */
        foreach ($organizations as $organization ){
            $type = $organization->getType() ? $organization->getType() : 'none';
            $data = $datas[$type][$count[$type]];
            $organization->setPhone(null)
                ->setEmail(null)
                ->setPhone(null)
                ->setBp(null)
                ->setLdapSupannCodeEntite(null)
                ->setStreet1(null)
                ->setStreet2(null)
                ->setStreet3(null)
                ->setConnector([])
                ->setBp(null)
                ->setCode($data['code'])
                ->setFullName($data['fullname'])
                ->setShortName($data['shortname']);
            $count[$type]++;
        }
        $this->getEntityManager()->flush();
    }

    public function shuffleActivity() {
        $activities = $this->getEntityManager()->getRepository(Activity::class)->findAll();
        $datas = [];
        /** @var Activity $activity */
        foreach ( $activities as $activity ){
            $type = $activity->getTypeSlug() ? $activity->getTypeSlug() : 'none';
            if( !array_key_exists($type, $datas ) ){
                $datas[$type] = [];
            }
            $datas[$type][] = [
                'label' => $activity->getLabel(),
                'description' => $activity->getDescription(),
            ];
        }
        $count = [];
        foreach( $datas as $type=>$acts ){
            shuffle($datas[$type]);
            $count[$type] = 0;
        }
        /** @var Organization $organization */
        foreach ( $activities as $activity ){
            $type = $activity->getTypeSlug() ? $activity->getTypeSlug() : 'none';
            $data = $datas[$type][$count[$type]];
            $activity->setLabel($data['label'])
                ->setDescription($data['description']);
            $count[$type]++;
        }
        $this->getEntityManager()->flush();
    }
}

