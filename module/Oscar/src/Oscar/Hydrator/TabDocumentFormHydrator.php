<?php
/**
 * @author Hervé Marie<herve.marie@unicaen.fr>
 * @date: 20/10/22
 * @copyright Certic (c) 2022
 */

namespace Oscar\Hydrator;


use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\ORMException;
use Oscar\Entity\Role;
use Oscar\Entity\TabDocument;
use Oscar\Entity\TabsDocumentsRoles;
use UnicaenApp\ServiceManager\ServiceLocatorAwareInterface;
use UnicaenApp\ServiceManager\ServiceLocatorAwareTrait;
use Zend\Hydrator\HydratorInterface;

class TabDocumentFormHydrator implements HydratorInterface, ServiceLocatorAwareInterface
{
    use ServiceLocatorAwareTrait;

    private EntityManager $em;

    public function __construct(EntityManager $em){
        $this->em = $em;
    }

    /**
     * Extract object pour afficher au formulaire
     *
     * @param TabDocument $object
     * @return array
     */
    public function extract($object):array
    {
        $data = [
            'id' => $object->getId(),
            'label' => $object->getLabel(),
            'description' => $object->getDescription(),
            'roles' =>  $this->getRoles($object->getTabsDocumentsRoles()),
        ];

        return $data;
    }

    /**
     * Hydrate object pour enregistrer en BD
     *
     * @param array $data
     * @param TabDocument $object
     * @return TabDocument
     * @throws ORMException
     */
    public function hydrate(array $data, $object): TabDocument
    {
        // Supprime les relations entre le tabDocument et les TabsDocumentsRoles
        $tabsDocumentsRoles = $object->getTabsDocumentsRoles();
        foreach ($tabsDocumentsRoles as $tabDocumentRole){
            $this->em->remove($tabDocumentRole);
            $this->em->flush();
        }
        // Reset Collection de relations
        $object->resetTabDocumentRole();
        // Ajoute-les objects en relation
        foreach ($data['roles'] as $idsRoles){
            $role = $this->em->getRepository(Role::class)->findOneBy(["id"=>$idsRoles]);
            $entityTabDocumentRole = new TabsDocumentsRoles();
            $entityTabDocumentRole->setRole($role);
            $entityTabDocumentRole->setAccess(1);
            $object->addTabDocumentRole($entityTabDocumentRole);
        }

        $object->setDescription($data['description'])
            ->setLabel($data['label']);
        return $object;
    }

    /**
     * Récupère les rôles associés aux onglets pour le form
     *
     * @param ?Collection $tabsDocumentsRoles
     * @return ArrayCollection
     */
    private function getRoles(?Collection $tabsDocumentsRoles):ArrayCollection{
        $roles = new ArrayCollection();
        /** @var  TabsDocumentsRoles $tabDocumentRole */
        foreach ($tabsDocumentsRoles as $tabDocumentRole){
            $roles->add($tabDocumentRole->getRole());
        }
        return $roles;
    }

}
