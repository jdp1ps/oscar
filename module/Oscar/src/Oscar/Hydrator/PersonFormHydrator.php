<?php
/**
 * @author Stéphane Bouvry<stephane.bouvry@unicaen.fr>
 * @date: 12/10/15 14:21
 * @copyright Certic (c) 2015
 */

namespace Oscar\Hydrator;


use Oscar\Entity\Person;
use Laminas\Hydrator\HydratorInterface;

class PersonFormHydrator implements HydratorInterface
{
    private $connectorsName;

    public function __construct( $connectorsName )
    {
        $this->connectorsName = $connectorsName;
    }


    /**
     * @param array $data
     * @param Person $object
     */
    public function hydrate(array $data, $object)
    {
        $dateFin = $data['ldapfininscription'];
        if($dateFin == ""){
            $dateFin = null;
        }

        $object->setFirstname($data['firstname'])
            ->setLastname($data['lastname'])
            ->setCodeHarpege($data['codeHarpege'])
            ->setLadapLogin($data['ladapLogin'])
            ->setLdapAffectation($data['ldapAffectation'])
            ->setLdapSiteLocation($data['ldapSiteLocation'])
            ->setLdapFinInscription($dateFin)
            ->setEmail($data['email'])
            ->setPhone($data['phone']);

        foreach( $this->connectorsName as $connector ){
            $object->setConnectorID($connector, $data['connector_'.$connector]);
        }

        return $object;
    }

    /**
     * @param Person $object
     * @return array
     */
    public function extract( $object )
    {
        $d = [
            'id'        => $object->getId(),
            'firstname' => $object->getFirstname(),
            'lastname' => $object->getLastname(),
            'ladapLogin' => $object->getLadapLogin(),
            'phone' => $object->getPhone(),
            'ldapAffectation' => $object->getLdapAffectation(),
            'ldapSiteLocation' => $object->getLdapSiteLocation(),
            'ldapfininscription' => $object->getLdapFinInscription(),
            'email' => $object->getEmail(),
        ];

        foreach( $this->connectorsName as $connector ){
            $d['connector_'.$connector] = $object->getConnectorID($connector);
        }
        return $d;

    }
}