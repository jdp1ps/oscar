<?php
namespace Oscar\Strategy\Search;


use Oscar\Entity\Organization;

interface IOrganizationSearchStrategy extends ISearchStrategyCore
{

    /**
     * Ajoute au moteur de recherche.
     */
    public function add(Organization $entity) :callable|array;


    /**
     * Mise à jour dans le moteur de recherche.
     *
     * @param Person $entity
     * @return mixed
     */
    public function update( Organization $entity ): callable|array;

}