<?php
/**
 * Created by PhpStorm.
 * User: gauthierb
 * Date: 22/07/15
 * Time: 16:35
 */

namespace UnicaenApp\Message\Specification;

/**
 * Interface décrivant ce que doit être une spécification utilisable pour
 * décrire la pertinence d'un message d'après un contexte.
 *
 * @package UnicaenApp\Message\Specification
 */
interface MessageSpecificationInterface
{
    /**
     * Retourne <code>true</code> si la spécification est satisfaite par le contexte.
     * Sinon <code>false</code>.
     *
     * @param mixed $context Contexte extérieur
     * @param array $sentBackData Tableau passé par référence permettant de retourner des données à l'appelant
     * @return bool
     */
    public function isSatisfiedBy($context = null, array &$sentBackData = []);
}