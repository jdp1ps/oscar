<?php

/**
 * @author Stéphane Bouvry<stephane.bouvry@unicaen.fr>
 * @date: 25/09/15 10:20
 *
 * @copyright Certic (c) 2015
 */

namespace Oscar\Service;

use Doctrine\ORM\Query;
use Oscar\Entity\ActivityRequest;
use Oscar\Entity\Person;
use Oscar\Exception\OscarException;
use UnicaenApp\Service\EntityManagerAwareInterface;
use UnicaenApp\Service\EntityManagerAwareTrait;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorAwareTrait;

/**
 * Class ActivityRequestService
 * @package Oscar\Service
 */
class ActivityRequestService implements ServiceLocatorAwareInterface, EntityManagerAwareInterface
{
    use ServiceLocatorAwareTrait, EntityManagerAwareTrait;

    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    ///
    /// RÉCUPÉRATION DES DONNÉES
    ///
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    /**
     * @param $id
     * @param bool $throw
     * @return null|object
     * @throws OscarException
     */
    public function getActivityRequest($id, $throw = true)
    {
        $activityRequest = $this->getEntityManager()->getRepository(ActivityRequest::class)->find($id);
        if (!$activityRequest && $throw == true) {
            throw new OscarException("Impossible de charger la demande N° $id");
        }
        return $activityRequest;
    }

    /**
     * Retourne la liste des demandes d'une personne.
     *
     * @param Person $person
     * @return array
     */
    public function getActivityRequestPerson(Person $person, $format = 'object')
    {
        try {
            $query = $this->getEntityManager()->getRepository(ActivityRequest::class)->createQueryBuilder('r')
                ->where('r.createdBy = :person')
                ->setParameter('person', $person)
                ->getQuery();

            if ($format == 'json') {
                $array = [];
                /** @var ActivityRequest $activityRequest */
                foreach ($query->getResult() as $activityRequest) {
                    $json = $activityRequest->toJson();
                    $json['statutText'] = $this->getStatutText($json['statut']);
                    $array[] = $json;
                }

                return $array;
            }

            return $query->getResult();
        } catch (\Exception $e) {
            throw new OscarException("Impossible de charger les demande pour $person : " . $e->getMessage());
        }
    }

    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    ///
    /// ENREGISTREMENT DES DONNÉES
    ///
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    public function createActivityRequest($datas)
    {
        throw new OscarException("La création des demandes d'activité n'est pas encore implanté.");
    }

    public function updateActivityRequest($datas)
    {
        throw new OscarException("L'enregistrement des demandes d'activité n'est pas encore implanté.");
    }

    public function createOrUpdateActivityRequest($datas)
    {
        if ($datas['id']) {
            $this->updateActivityRequest($datas);
        } else {
            $this->createActivityRequest($datas);
        }
    }

    public function sendActivityRequest(ActivityRequest $activityRequest)
    {
        throw new OscarException("L'envoi des demandes d'activité n'est pas encore implanté.");
    }

    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    ///
    /// ENREGISTREMENT DES DONNÉES
    ///
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    public function getStatutText( $statut ){
        static $statutText;
        if( !$statutText ){
            $statutText = [
                1 => "Brouillon"
            ];
        }
        if( array_key_exists($statut, $statutText) ){
            return $statutText[$statut];
        } else {
            return "Status inconnu";
        }
    }


}
