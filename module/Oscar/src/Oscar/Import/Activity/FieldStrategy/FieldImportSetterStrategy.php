<?php
/**
 * @author Stéphane Bouvry<stephane.bouvry@unicaen.fr>
 * @date: 17-11-22 15:31
 * @copyright Certic (c) 2017
 */

namespace Oscar\Import\Activity\FieldStrategy;


use Doctrine\ORM\EntityManager;
use Oscar\Entity\ActivityOrganization;
use Oscar\Entity\Organization;
use Oscar\Entity\OrganizationRepository;
use Oscar\Entity\OrganizationRole;
use Oscar\Entity\OrganizationRoleRepository;

class FieldImportSetterStrategy implements IFieldImportStrategy
{

    private $key;

    /**
     * FieldImportOrganizationStrategy constructor.
     * @param $entityManager
     * @param $role
     */
    public function __construct($key)
    {
        $this->key = $key;
    }

    public function run(&$activity, $datas, $index)
    {
        $setter = 'set'.ucfirst($this->key);
        echo "$setter\n";
        $activity->$setter($datas[$index]);
        return $activity;
    }
}