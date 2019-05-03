<?php
namespace Oscar\Strategy\Search;


use Oscar\Entity\Organization;

interface OrganizationSearchStrategy
{
    /**
     * Lance la recherche à partir de la chaîne de recherche, retourne une liste d'ID.
     *
     * @param $search
     * @return mixed
     */
    public function search( $search );

    /**
     * Ajoute au moteur de recherche.
     *
     * @param Person $entity
     * @return mixed
     */
    public function add(Organization $entity);


    /**
     * Supprime du moteur de recherche.
     *
     * @param $id
     * @return mixed
     */
    public function remove( $id );

    /**
     * Mise à jour dans le moteur de recherche.
     *
     * @param Person $entity
     * @return mixed
     */
    public function update( Organization $entity );

    /**
     * Supprime l'index du moteur de recherche.
     *
     * @return mixed
     */
    public function resetIndex();

    /**
     * Lance la reconstruction complète de l'index de recherche à partir
     * de la liste des entitées fournie.
     *
     * @return mixed
     */
    public function rebuildIndex( $entities );
}