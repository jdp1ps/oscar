<?php
namespace Oscar\Strategy\Search;


use Oscar\Entity\Person;

interface IPersonISearchStrategy extends ISearchStrategyCore
{
    /**
     * Ajoute au moteur de recherche.
     *
     * @param Person $person
     * @return mixed
     */
    public function add(Person $person): callable|array;


    /**
     * Mise à jour dans le moteur de recherche.
     *
     * @param Person $person
     * @return mixed
     */
    public function update( Person $person ): callable|array;
}