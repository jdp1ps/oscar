<?php
/**
 * @author Stéphane Bouvry<stephane.bouvry@unicaen.fr>
 * @date: 20/11/15 15:09
 * @copyright Certic (c) 2015
 */

namespace Oscar\Hydrator;


use Doctrine\ORM\EntityManager;
use Oscar\Entity\ActivityType;
use Oscar\Entity\DateType;
use Oscar\Entity\OscarFacet;
use Oscar\Entity\Role;
use UnicaenApp\ServiceManager\ServiceLocatorAwareInterface;
use UnicaenApp\ServiceManager\ServiceLocatorAwareTrait;
use Zend\Hydrator\HydratorInterface;

class DateTypeFormHydrator implements HydratorInterface, ServiceLocatorAwareInterface
{
    use ServiceLocatorAwareTrait;

    private EntityManager $em;

    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }


    /**
     * @param DateType $object
     * @return array
     */
    public function extract($object): array
    {
        $data = [
            'id' => $object->getId(),
            'description' => $object->getDescription(),
            'facet' => array_search($object->getFacet(), OscarFacet::getFacets()),
            'label' => $object->getLabel(),
            'recursivity' => $object->getRecursivity(),
        ];
        if( $object->isFinishable() ){
            $data['finishable'] = 1;
        }

        return $data;
    }

    /**
     * @param array $data
     * @param DateType $object
     * @return DateType
     */
    public function hydrate(array $data, $object): DateType
    {
        $object->resetRoles();
        foreach ($data['roles'] as $idRole){
            $entityRole = $this->em->getRepository(Role::class)->findOneBy(["id"=>$idRole]);
            $object->addRole($entityRole);
        }

        $object->setDescription($data['description'])
            ->setFacet(OscarFacet::getFacets()[$data['facet']])
            ->setRecursivity($data['recursivity'])
            ->setFinishable(array_key_exists('finishable', $data) ? true : false)
            ->setLabel($data['label']);
        return $object;
    }
}
