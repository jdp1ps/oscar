<?php

namespace UnicaenAuth\Service;

/**
 * Interface spécifiant une dépendance avec un utilisateur issu d'une base de données.
 * 
 * @author Bertrand GAUTHIER <bertrand.gauthier at unicaen.fr>
 */
interface DbUserAwareInterface
{
    /**
     * Injecte l'utilisateur.
     *
     * @param UserInterface $user
     */
    public function setDbUser(\ZfcUser\Entity\UserInterface $user);

    /**
     * Retourne l'utilisateur injecté.
     *
     * @return \ZfcUser\Entity\UserInterface
     */
    public function getDbUser();
}