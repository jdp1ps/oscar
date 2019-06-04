<?php
/**
 * @author Stéphane Bouvry<stephane.bouvry@unicaen.fr>
 * @date: 17-09-12 09:52
 * @copyright Certic (c) 2017
 */

namespace Oscar\Service;

use Doctrine\ORM\NoResultException;
use Doctrine\ORM\Query\ResultSetMapping;
use Moment\Moment;
use Oscar\Entity\Activity;
use Oscar\Entity\ActivityDate;
use Oscar\Entity\ActivityNotification;
use Oscar\Entity\ActivityOrganization;
use Oscar\Entity\ActivityPayment;
use Oscar\Entity\ActivityPerson;
use Oscar\Entity\Authentification;
use Oscar\Entity\Notification;
use Oscar\Entity\NotificationPerson;
use Oscar\Entity\OrganizationPerson;
use Oscar\Entity\Person;
use Oscar\Entity\Project;
use Oscar\Entity\SpentTypeGroup;
use Oscar\Entity\SpentTypeGroupRepository;
use Oscar\Entity\ValidationPeriod;
use Oscar\Exception\OscarException;
use Oscar\Provider\Privileges;
use UnicaenApp\Service\EntityManagerAwareInterface;
use UnicaenApp\Service\EntityManagerAwareTrait;
use Zend\Log\Logger;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorAwareTrait;

class SpentService implements ServiceLocatorAwareInterface, EntityManagerAwareInterface
{
    use ServiceLocatorAwareTrait, EntityManagerAwareTrait;

    public function getAllArray(){
        $array = [];
        /** @var SpentTypeGroup $spendTyêGroup */
        foreach ($this->getSpentTypeRepository()->getAll() as $spendTyêGroup) {
            $array[] = $spendTyêGroup->toJson();
        }
        return $array;
    }

    public function createSpentTypeGroup( $datas ){
        $this->checkDatas($datas);

        $label = $datas['label'];
        $code = $datas['code'];
        $description = $datas['description'];

        $type = new SpentTypeGroup();
        $type->setLabel($label)
            ->setCode($code)
            ->setDescription($description);

        $inside = $datas['inside'];
        if( $inside == 'root' ){
            // todo : Récupération du dernier noeud

            $last = $this->getSpentTypeRepository()->getLastSpentTypeGroup();

            if( $last ){
                $lgt = $last->getRgt()+1;
                $rgt = $lgt+1;
            } else {
                $lgt = 1;
                $rgt = $lgt+1;
            }

            $type->setLft($lgt)->setRgt($rgt);
            $this->getEntityManager()->persist($type);
            $this->getEntityManager()->flush($type);
            return $type;
        }
        else {
            $insiderId = intval($inside);
            if ($insiderId < 1 ) throw new OscarException(_("DATA ERROR : Type de destination incohérent"));

            // Récupération du noeud racine
            /** @var SpentTypeGroup $insider */
            $insider = $this->getSpentTypeRepository()->find($insiderId);

            if (!$insider ) throw new OscarException(_("Impossible de localiser l'emplacement pour le nouveau type"));

            $lgt = $insider->getRgt();
            $rgt = $lgt+1;

            // Mise à jour des bornes
            $this->getEntityManager()->createNativeQuery(
                'UPDATE spenttypegroup SET lft = lft+2 WHERE lft > :lft', new ResultSetMapping()
            )->execute(['lft' => $lgt]);

            $this->getEntityManager()->createNativeQuery(
                'UPDATE spenttypegroup SET rgt = rgt+2 WHERE rgt >= :rgt', new ResultSetMapping()
            )->execute(['rgt' => $lgt]);

            $type->setLft($lgt)->setRgt($rgt);
            $this->getEntityManager()->persist($type);
            $this->getEntityManager()->flush($type);
            return $type;
        }
    }


    public function updateSpentTypeGroup( $datas ){
        $this->checkDatas($datas);

        $id = intval($datas['id']);

        if( $id < 1 ){
            throw new OscarException(_("Impossible de trouver le type de dépense à mettre à jour"));
        }

        /** @var SpentTypeGroup $spentTypeGroup */
        $spentTypeGroup = $this->getSpentTypeRepository()->find($id);

        if( !$spentTypeGroup ){
            throw new OscarException(_("Le type de dépense n'a pas été trouvé"));
        }

        $label = $datas['label'];
        $code = $datas['code'];
        $description = $datas['description'];

        if( array_key_exists('inside', $datas) ){
            $inside = $datas['inside'];
        }

        $spentTypeGroup->setLabel($label)
            ->setDescription($description)
            ->setCode($code);

        $this->getEntityManager()->flush($spentTypeGroup);
    }

    /**
     * @return SpentTypeGroupRepository
     */
    protected function getSpentTypeRepository(){
        return $this->getEntityManager()->getRepository(SpentTypeGroup::class);
    }

    protected function checkDatas( $datas ){
        if( !$datas['label'] ){
            throw new OscarException(_("Vous devez renseigner un intitulé."));
        }
        if( !$datas['code'] ){
            throw new OscarException(_("Le champ code doit être renseigné"));
        }
    }
}