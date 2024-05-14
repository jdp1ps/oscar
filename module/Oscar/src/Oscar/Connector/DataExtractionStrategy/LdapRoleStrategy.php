<?php

namespace Oscar\Connector\DataExtractionStrategy;

use Doctrine\ORM\EntityManager;
use Oscar\Entity\Role;
use Zend\ServiceManager\ServiceManager;

class LdapRoleStrategy
{
    private ServiceManager $serviceManager;
    private array $arrayBdd = array();

    public function __construct(ServiceManager $sm)
    {
        $this->serviceManager = $sm;
    }
    public function compareRolesPerson($configFile, $rolesPerson, $organizationRepository, $personOscar){
        $dataOrgPer =
            $organizationRepository->getOrganizationPerson($personOscar);

        if($dataOrgPer != null){
            /* On vérifie si la personne a des rôles déjà en BDD et on les stocke dans un tableau $this->arrayBdd */
            if(is_array($dataOrgPer)){
                foreach($dataOrgPer as $orgPer){
                    $this->compareRole($configFile, $orgPer);
                }
            } else {
                $this->compareRole($configFile, $dataOrgPer);
            }

            if(is_array($rolesPerson)){
                $roleToAdd = array_values(array_diff($rolesPerson, $this->arrayBdd));
                $roleToDelete = array_values(array_diff($this->arrayBdd, $rolesPerson));
                $this->removeRole($configFile, $roleToDelete, $organizationRepository, $dataOrgPer, $personOscar);
                
                return $roleToAdd;
            } else {
                $this->removeRole($configFile, $this->arrayBdd, $organizationRepository, $dataOrgPer, $personOscar);

                foreach($this->arrayBdd as $role){
                    if($rolesPerson != $role){
                        return $rolesPerson;
                    }
                }

                $rolesPerson = null;
            }
        }

        return $rolesPerson;
    }
    
    public function removeRole($configFile, $rolesToDelete, $organizationRepository, $dataOrgPer, $personOscar): void
    {
        $roleRepository = $this->serviceManager->get(EntityManager::class)->getRepository(
            Role::class
        );

        foreach($rolesToDelete as $deleteRole) {
            $substringRole = substr($deleteRole, 1, strlen($deleteRole) - 2);
            $explodeRole = explode("][", $substringRole);
            $exactRole = substr($explodeRole[0], 5, strlen($explodeRole[0]));
            $countRole = count($configFile["mapping_role_person"]);

            for ($i = 0; $i < $countRole; $i++) {
                if (array_key_exists($exactRole, $configFile["mapping_role_person"][$i])) {
                    $idRole = $roleRepository->getRoleByRoleId(
                        $configFile["mapping_role_person"][$i][$exactRole]
                    )->getId();
                    $organizationRepository->removeOrganizationPerson($dataOrgPer, $personOscar, $idRole);
                }
            }
        }
    }
    
    public function compareRole($configFile, $orgPer): void
    {
        foreach($configFile["mapping_role_person"] as $key => $value){
            if($value == $orgPer->getRole()){
                $this->arrayBdd[] = $key;
            }
        }
    }
}
