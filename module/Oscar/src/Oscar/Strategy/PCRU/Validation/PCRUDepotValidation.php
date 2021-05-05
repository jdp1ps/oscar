<?php
namespace Oscar\Strategy\PCRU\Validation;

use Cassandra\Exception\ValidationException;
use Oscar\Entity\Activity;
use Oscar\Entity\ProjectGrantRepository;

class PCRUDepotValidation
{
    /** @var ProjectGrantRepository */
    private $activityRepository;

    /**
     * @param Activity $activity
     */
    public function validate(Activity $activity): bool
    {
        $errors = [];

        // TODO Validation de l'activité
        $errors[] = "Non implanté";

        // Retour
        if (count($errors)) {
            $reasons = explode($errors, ', ');
            $message = sprintf(__("L'activité %s n'est pas éligible à un dépôt PCRU : %s"), (string)$activity, $reasons);
            throw new ValidationException($message);
        }

        return true;
    }
}