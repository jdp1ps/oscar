<?php


namespace Oscar\Strategy\PCRU;


use Cassandra\Exception\ValidationException;
use Oscar\Entity\Activity;
use Oscar\Strategy\PCRU\Validation\PCRUDepotValidation;

class PCRUDepotFtpStrategy implements PCRUDepotStrategy
{

    public function sendActivity(Activity $activity): bool
    {
        $validator = new PCRUDepotValidation();

        try {
            $validator->validate($activity);

            // TRANSFERT FTP
        } catch (ValidationException $e) {

        }
    }
}