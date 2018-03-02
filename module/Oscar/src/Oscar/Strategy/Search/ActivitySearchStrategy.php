<?php
/**
 * Created by PhpStorm.
 * User: jacksay
 * Date: 28/02/18
 * Time: 16:46
 */

namespace Oscar\Strategy\Search;


use Oscar\Entity\Activity;

interface ActivitySearchStrategy
{
    /**
     * Lance la recherche à partir de la chaîne de recherche, retourne une liste d'ID.
     *
     * @param $search
     * @return mixed
     */
    public function search( $search );

    /**
     * Ajoute une activité au moteur de recherche.
     *
     * @param Activity $activity
     * @return mixed
     */
    public function addActivity(Activity $activity);

    /**
     * Recherche dans les projets.
     *
     * @param $what
     * @return mixed
     */
    public function searchProject($what);

    /**
     * Supprime l'activité du moteur de recherche.
     *
     * @param $id
     * @return mixed
     */
    public function searchDelete( $id );

    /**
     * Mise à jour de l'activité dans le moteur de recherche.
     *
     * @param Activity $activity
     * @return mixed
     */
    public function searchUpdate( Activity $activity );

    /**
     * Supprime l'index du moteur de recherche.
     *
     * @return mixed
     */
    public function resetIndex();

    /**
     * @return mixed
     */
    public function rebuildIndex( $activities );
}