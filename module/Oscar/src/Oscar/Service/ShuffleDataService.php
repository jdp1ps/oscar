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
            if( !(strpos($person->getEmail(), '@jacksay.com')>0 ) ){
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
            }
            $i++;
        }
        $this->getEntityManager()->flush();
    }

    protected function randomName(){
        static $personnages;
        if( $personnages === null ){
            $personnages = [
                'Pelleas Anthor',
                'Bel Arvardan',
                'Harlan Branno',
                'Ammel Brodig',
                'Bail Channis',
                'Jord Commasson',
                'Arcadia Darell',
                'Eto Demerzel',
                'Gaal Dornick',
                'Salvor Hardin',
                'Hober Mallow',
                'Lev Meirus',
                'Elbing Mis',
                'Homir Munn',
                'Daneel Olivaw',
                'Janov Pélorat',
                'Preem Palver',
                'Han Pritcher',
                'Bel Riose',
                'Hari Seldon',
                'Golan Trevize',
                'Jole Turbor',
            ];
        }
        $rand = rand(0, count($personnages)-1);
        return $personnages[$rand];
    }

    protected function randomCity(){
        static $villes;
        if( $villes === null ){
            $villes = [
                'Winterfell' => 'Nord',
                'Dreadfort' => 'Nord',
                'Blancport' => 'Nord',
                'Greywater' => 'Nord',
                'Vivesaigues' => 'Conflans',
                'Viergétang' => 'Conflans',
                'Harrenhal' => 'Conflans',
                'Les Jumeaux' => 'Conflans',
                'Les Eyrié' => 'Le Val d\'Arryn',
                'Pyk' => 'îles de Fer',
                'Wyk' => 'îles de Fer',
                'Harloi' => 'îles de Fer',
                'Salfalaise' => 'îles de Fer',
                'Noirmarées' => 'îles de Fer',
                'Orkmont' => 'îles de Fer',
                'Peyredragon' => 'Terres de la Couronne',
                'Port-Réal' => 'Terres de la Couronne',
                'Castral Roc' => 'Terres de l\'Ouest',
                'Accalmie' => 'Terres de l\'Orage',
                'Hautjardin' => 'Le Bief',
                'Villevieille' => 'Le Bief',
                'Lancehélion' => 'Dorne',
                'Braavos' => 'Essos',
                'Pentos' => 'Essos',
                'Volantis' => 'Essos',
                'Lorath' => 'Essos',
                'Lys' => 'Essos',
                'Myr' => 'Essos',
                'Norvos' => 'Essos',
                'Qohor' => 'Essos',
                'Tyrosh' => 'Essos',
                'Astapor' => 'Baie des serfs',
                'Yunkaï' => 'Baie des serfs',
                'Meereen' => 'Baie des serfs',
            ];
        }
        $rand = array_rand($villes);
        return [
            'city' => $rand,
            'country' => $villes[$rand]
        ];
    }

    public function shuffleProjects(){
        $projects = $this->getEntityManager()->getRepository(Project::class)->findAll();
        $datas = [];
        /** @var Project $project */
        foreach ($projects as $project ){
            foreach( $this->locationReplace() as $reg=>$replace ){
                if( preg_match($reg, $project->getLabel()) ){
                    $project->setLabel(preg_replace($reg, $replace, $project->getLabel()));
                }
            }

            if( preg_match('/thèse de (.*)/ui', $project->getLabel()) ){
                $project->setLabel(preg_replace('/(.*)thèse de (.*)/ui', 'Thèse ' . $this->randomName(), $project->getLabel()));
            }

            $datas[] = [
                'acronym' => $project->getAcronym(),
                'label' => $project->getLabel(),
                'description' => $project->getDescription(),
            ];
        }
        /****
        shuffle($datas);
        $i = 0;
        foreach ($projects as $project ){
            $data = $datas[$i++];
            $project->setAcronym($data['acronym'])
                ->setLabel($data['label'])
                ->setDescription($data['description']);
        }
        /****/
        $this->getEntityManager()->flush();

    }

    public function shuffleOrganizations() {
        $organizations = $this->getEntityManager()->getRepository(Organization::class)->findAll();
        $steIndex = 0;
        $ste = ["ACME",
            "Alchemax",
            "Aperture",
            "BNL",
            "COGIP",
            "Dinoco",
            "Fondation",
            "Groupe W",
            "Groupe Winch",
            "LexCorp",
            "Stark Industries",
            "Tomato",
            "Tyrell Corporation",
            "Umbrella Corporation",
            "US Robots",
            "Wayne Enterprises",
            "World Compagny",
            "Queen Industries",
            "Oceanic Airline",
            "Black Mesa",
            "Shaara",
            "Wolfram & Har",
            "Cartel",
            "Shield",
            "Oscorp",
            "Pizza Planet",
            "Soylent",
            "Central Perk",
            "Cyberdyne"];

        $datas = [];
        /****
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
        /****/
        /** @var Organization $organization */
        foreach ($organizations as $organization ){
            if( $organization->getType() == 3 ){
            echo " - " . $organization->getType() . "\n";
                if ($steIndex >= count($ste) ){
                    $steIndex = 0;
                }
                $organization->setFullName($ste[$steIndex])
                    ->setSiret('MEH');
                $steIndex++;
            }

            $randomPlace = $this->randomCity();
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
                ->setZipCode('')

                ->setCity($randomPlace['city'])
                ->setCountry($randomPlace['country'])

                /*->setCode($data['code'])
                ->setFullName($data['fullname'])
                ->setShortName($data['shortname'])*/;
//            $count[$type]++;
        }
        $this->getEntityManager()->flush();
    }


    protected function locationReplace(){
        return [
            '/(.*)(caen)(.*)/uim' => '$1Trantor$3',
            '/(.*)(herouville saint clair)(.*)/uim' => '$1Gondor$3',
            '/(.*)(france)(.*)/uim' => '$1Helionia$3',
            '/(.*)(cotentin|manche)(.*)/uim' => '$1Dahl$3',
            '/(.*)(saint[- ]l.?)(.*)/uim' => '$1Anareon$3',
            '/(.*)(basse-normandie)(.*)/uim' => '$1Terminus$3',
            '/(.*)(normandie)(.*)/uim' => '$1Dorne$3',
            '/(.*)(calvados)(.*)/uim' => '$1$3',
            '/(.*)(england|grande bretagne|united kingdom|royaume uni|angleterre)(.*)/uim' => 'Terminus',
            '/(.*)(belgique)(.*)/uim' => '$1Kalgan$3',
            '/(.*)(br?sil)(.*)/uim' => '$1Kalgan$3',
            '/(.*)(iran)(.*)/uim' => '$1Solaria$3',
            '/(.*)(spain|espange)(.*)/uim' => '$1Melpomania$3',
            '/(.*)(chine)(.*)/uim' => '$1Helionia$3',
            '/(.*)(germany|allemagne)(.*)/uim' => '$1Cygni$3',
            '/(.*)(suisse)(.*)/uim' => '$1Santanni$3',

            '/(.*)(tunisie)(.*)/uim' => '$1Sirius$3',
            '/(.*)(new jersey|usa)(.*)/uim' => '$1Pallas$3',
            '/(.*)(campbodge)(.*)/uim' => '$1Vega$3',

            '/(.*)(finlande)(.*)/uim' => '$1Synnax$3',
            '/(.*)(mali)(.*)/uim' => '$1Terel$3',
            '/(.*)(afrique du sud)(.*)/uim' => '$1Nishaya$3',
            '/(.*)(taiwan)(.*)/uim' => '$1Zoranel$3',
        ];
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

            foreach( $this->locationReplace() as $reg=>$replace ){
                if( preg_match($reg, $activity->getLabel()) ){
                    $activity->setLabel(preg_replace($reg, $replace, $activity->getLabel()));
                }
            }

            $datas[$type][] = [
                'label' => $activity->getLabel(),
                'description' => $activity->getDescription(),
            ];
        }
        $this->getEntityManager()->flush();
        /****
        $count = [];
        foreach( $datas as $type=>$acts ){
            shuffle($datas[$type]);
            $count[$type] = 0;
        }

        foreach ( $activities as $activity ){
            $type = $activity->getTypeSlug() ? $activity->getTypeSlug() : 'none';
            $data = $datas[$type][$count[$type]];
            $activity->setLabel($data['label'])
                ->setDescription($data['description']);
            $count[$type]++;
        }
        /****/
    }
}

