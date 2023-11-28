<?php

namespace Oscar\Strategy\Search;

interface ISearchStrategyCore
{
    /**
     * Lance la recherche à partir de la chaîne de recherche, retourne une liste d'ID.
     *
     * @param string $search
     * @return mixed
     */
    public function search(string $search): array;

    /**
     * Supprime l'index du moteur de recherche.
     *
     * @return mixed
     */
    public function resetIndex() :array;

    /**
     * Lance la reconstruction complète de l'index de recherche à partir
     * de la liste des items fournie.
     *
     */
    public function rebuildIndex(array $items): void;
}