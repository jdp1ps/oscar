<?php

namespace Oscar\Hydrator;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\ORMException;
use Oscar\Entity\TabDocument;
use Oscar\Entity\TabsDocumentsRoles;
use UnicaenApp\ServiceManager\ServiceLocatorAwareInterface;
use UnicaenApp\ServiceManager\ServiceLocatorAwareTrait;
use Laminas\Hydrator\HydratorInterface;

class MigrateDocumentFormHydrator implements HydratorInterface, ServiceLocatorAwareInterface
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
    public function extract(object $object): array
    {
        $data = [
            'typeDocument' => $object['typeDocument'],
            'tabDocument' => $object['tabDocument']
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
        var_dump($data);
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
