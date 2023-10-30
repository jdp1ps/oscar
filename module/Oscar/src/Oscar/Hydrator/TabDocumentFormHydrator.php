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
use Oscar\Entity\ContractDocument;
use Oscar\Entity\ContractDocumentRepository;
use Oscar\Entity\Role;
use Oscar\Entity\TabDocument;
use Oscar\Entity\TabsDocumentsRoles;
use UnicaenApp\ServiceManager\ServiceLocatorAwareInterface;
use UnicaenApp\ServiceManager\ServiceLocatorAwareTrait;
use Laminas\Hydrator\HydratorInterface;

class TabDocumentFormHydrator implements HydratorInterface, ServiceLocatorAwareInterface
{
    use ServiceLocatorAwareTrait;

    private EntityManager $em;

    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    /**
     * Extract object pour afficher au formulaire
     *
     * @param TabDocument $object
     * @return array
     */
    public function extract($object): array
    {
        $data = [
            'id' => $object->getId(),
            'label' => $object->getLabel(),
            'default' => $object->isDefault() ? 'on' : '',
            'description' => $object->getDescription(),
        ];

        foreach ($object->getTabsDocumentsRoles() as $tabDocumentRole) {
            $data ['roleId_' . $tabDocumentRole->getRole()->getId()] = $tabDocumentRole->getAccess();
        }

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

        $isDefault = $data['default'] == 'on';

        // On bascule les onglets "non par défaut"
        if( $isDefault ){
            /** @var ContractDocumentRepository $documentRepo */
            $documentRepo = $this->em->getRepository(ContractDocument::class);
            foreach ( $documentRepo->getTabDocuments() as $tabDocument ){
                $tabDocument->setDefault(false);
            }
        }

        // Supprime les relations entre le tabDocument et les TabsDocumentsRoles
        $tabsDocumentsRoles = $object->getTabsDocumentsRoles();
        foreach ($tabsDocumentsRoles as $tabDocumentRole) {
            $this->em->remove($tabDocumentRole);
            $this->em->flush();
        }
        // Reset Collection de relations
        $object->resetTabDocumentRole();

        // Ajoute-les objects en relation
        foreach ($data as $key => $value) {
            if (strstr($key, 'roleId_')) {
                $id = str_replace('roleId_', '', $key);
                $role = $this->em->getRepository(Role::class)->findOneBy(["id" => $id]);
                $entityTabDocumentRole = new TabsDocumentsRoles();
                $entityTabDocumentRole->setRole($role);
                $entityTabDocumentRole->setAccess($value);
                $object->addTabDocumentRole($entityTabDocumentRole);
            }
        }

        $object->setDescription($data['description'])
            ->setDefault($isDefault)
            ->setLabel($data['label']);

        return $object;
    }

    /**
     * Récupère les rôles associés aux onglets pour le form
     *
     * @param ?Collection $tabsDocumentsRoles
     * @return ArrayCollection
     */
    private function getRoles(?Collection $tabsDocumentsRoles): ArrayCollection
    {
        $roles = new ArrayCollection();
        /** @var  TabsDocumentsRoles $tabDocumentRole */
        foreach ($tabsDocumentsRoles as $tabDocumentRole) {
            $roles->add($tabDocumentRole->getRole());
        }
        return $roles;
    }

}
