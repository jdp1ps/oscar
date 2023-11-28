<?php
/**
 * Created by PhpStorm.
 * User: jacksay
 * Date: 28/02/18
 * Time: 16:46
 */

namespace Oscar\Strategy\Search;


use Oscar\Entity\Activity;

interface IActivitySearchStrategy extends ISearchStrategyCore

{
    /**
     * Ajoute une activité au moteur de recherche.
     *
     * @param Activity $activity
     * @return mixed
     */
    public function addActivity(Activity $activity): callable|array;

    /**
     * Recherche dans les projets.
     *
     * @param string $what
     * @return mixed
     */
    public function searchProject(string $what): array;

    /**
     * Supprime l'activité du moteur de recherche.
     *
     * @param int $id
     * @return callable|array
     */
    public function deleteActivity(int $id): callable|array;

    /**
     * Mise à jour de l'activité dans le moteur de recherche.
     *
     * @param Activity $activity
     * @return callable|array
     */
    public function updateActivity(Activity $activity): callable|array;
}