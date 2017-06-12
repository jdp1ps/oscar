<?php

namespace Oscar\Service;

use Doctrine\ORM\Query;
use Oscar\Connector\ConnectorPersonOrganization;
use Oscar\Entity\Organization;
use Oscar\Entity\OrganizationPerson;
use Oscar\Entity\Person;
use Oscar\Entity\PersonRepository;
use Oscar\Entity\ProjectMember;
use Oscar\Entity\TimeSheet;
use Oscar\Entity\WorkPackage;
use Oscar\Exception\OscarException;
use Oscar\Utils\UnicaenDoctrinePaginator;
use UnicaenApp\Mapper\Ldap\People;
use UnicaenApp\Service\EntityManagerAwareInterface;
use UnicaenApp\Service\EntityManagerAwareTrait;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorAwareTrait;

/**
 * Gestion des Personnes :
 *  - Collaborateurs
 *  - Membres de projet/organisation.
 */
class TimesheetService implements ServiceLocatorAwareInterface, EntityManagerAwareInterface
{
    use ServiceLocatorAwareTrait, EntityManagerAwareTrait;




    public function send( $data, $by ){
        throw new OscarException('Send not implemented');
        var_dump('SEND : ' . $data . ' - ' . (string)$by);
        die("Test");
    }

    public function create( $datas, $by ){
        foreach ($datas as $data) {
            if ($data['id'] && $data['id'] != 'null') {
                $timeSheet = $this->getEntityManager()->getRepository(TimeSheet::class)->find($data['id']);
            } else {
                $timeSheet = new TimeSheet();
                $this->getEntityManager()->persist($timeSheet);
            }

            $status = TimeSheet::STATUS_INFO;

            if( isset($data['idworkpackage']) && $data['idworkpackage'] != 'null' ){
                $workPackage = $this->getEntityManager()->getRepository(WorkPackage::class)->find($data['idworkpackage']);
                $timeSheet->setWorkpackage($workPackage);
                $status = TimeSheet::STATUS_DRAFT;
            } elseif ( isset($data['idactivity']) && $data['idactivity'] != 'null' ){
                $activity = $this->getEntityManager()->getRepository(Activity::class)->find($data['idactivity']);
                $timeSheet->setActivity($activity);
                $status = TimeSheet::STATUS_DRAFT;
            }

            $timeSheet->setComment($data['description'])
                ->setLabel($data['label'])
                ->setCreatedBy($by)
                ->setPerson($by)
                ->setStatus($status)
                ->setDateFrom(new \DateTime($data['start']))
                ->setDateTo(new \DateTime($data['end']));

            $json = $timeSheet->toJson();
            $json['credentials'] = $this->resolveTimeSheetCredentials($timeSheet);
            $timesheets[] = $json;

        }
        $this->getEntityManager()->flush($timeSheet);
        throw new OscarException('create not implemented');
        var_dump('SEND : ' . $data . ' - ' . (string)$by);
        die("Test");
    }

    public function delete( $data, $by ){
        throw new OscarException('Delete not implemented');
        var_dump('SEND : ' . $data . ' - ' . (string)$by);
        die("Test");
    }

    public function validateSci( $data, $by ){
        throw new OscarException('Validate Scie not implemented');
        var_dump('VS : ' . $data . ' - ' . (string)$by);
        die("Test");
    }

    public function validateAdmin( $data, $by ){
        throw new OscarException('Validate Admin not implemented');
        var_dump('VA : ' . $data . ' - ' . (string)$by);
        die("Test");
    }

}
