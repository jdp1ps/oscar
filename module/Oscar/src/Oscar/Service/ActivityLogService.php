<?php
/**
 * @author Stéphane Bouvry<stephane.bouvry@unicaen.fr>
 * @date: 07/10/15 11:00
 * @copyright Certic (c) 2015
 */

namespace Oscar\Service;

use Doctrine\ORM\Query\ResultSetMapping;
use Oscar\Entity\LogActivity;
use Oscar\Entity\Authentification;
use Oscar\Entity\Person;
use UnicaenApp\Service\EntityManagerAwareInterface;
use UnicaenApp\Service\EntityManagerAwareTrait;
use UnicaenAuth\Service\UserContext;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorAwareTrait;
use ZfcUser\Mapper\UserInterface;

class ActivityLogService  implements ServiceLocatorAwareInterface, EntityManagerAwareInterface
{
    use ServiceLocatorAwareTrait, EntityManagerAwareTrait;

    /**
     * @return UserContext
     */
    protected function getUserContext()
    {
        return $this->getServiceLocator()->get('authUserContext');
    }


    protected function addUserLog()
    {
    }

    protected function addActivity($message, $type, $level, $userId, $context, $contextId, $data)
    {
        $activity = new LogActivity();
        $activity->setMessage($message)
            ->setType($type)
            ->setLevel($level)
            ->setUserId($userId)
            ->setContext($context)
            ->setContextId($contextId)
            ->setDatas($data)
        ;
        $this->getEntityManager()->persist($activity);
        $this->getEntityManager()->flush($activity);
    }

    public function addInfo($message, Authentification $user=null, $level=LogActivity::LEVEL_ADMIN, $context='Application', $contextId=-1)
    {
        $userid = $user ? $user->getId() : -1;
        $data = [
          'REQUEST_URI' => $_SERVER['REQUEST_URI']
        ];

        $this->addActivity($message, LogActivity::TYPE_INFO, $level, $userid, $context, $contextId, $data);
    }


    private $_currentPerson;

    /**
     * @return Person
     */
    public function getCurrentPerson()
    {
        if ($this->_currentPerson === null) {
            if ($this->getUserContext()->getLdapUser()) {
                $this->_currentPerson = "ldapuser";
                $login = $this->getUserContext()->getLdapUser()->getSupannAliasLogin();
            }
            elseif ( $this->getUserContext()->getDbUser() ){
                $login = $this->getUserContext()->getDbUser()->getUsername();
            }
            if ($login) {
                $this->_currentPerson = $this->getEntityManager()->getRepository(Person::class)->findOneBy([
                    'ladapLogin' => $login
                ]);
            }
        }
        return $this->_currentPerson;
    }

    public function addUserInfo($message, $context = 'Application', $contextId = -1, $level = LogActivity::LEVEL_ADMIN)
    {
        $person = $this->getCurrentPerson();
        $personText = "Anonymous";
        if( is_string($person) ){
            $personText = $person;
        } elseif( $person ) {
            $personText = $person->log();
        } elseif ( $this->getUserContext()->getDbUser() ) {
            $personText = $this->getUserContext()->getDbUser()->getId().':'.$this->getUserContext()->getDbUser()->getDisplayName();
        }
        $message = sprintf($personText.' %s', $message);
        return $this->addInfo($message, $this->getUserContext()->getDbUser(), $level, $context, $contextId);
    }


    ////////////////////////////////////////////////////////////////////////////
    public function listAdmin($level=200, $includeDebug=0)
    {
        $qb = $this->getEntityManager()->createQueryBuilder()
            ->select('a')
            ->from(LogActivity::class, 'a')
            ->where('a.level >= :level');
        if ($includeDebug != 1) {
            $qb->andWhere('a.type NOT IN(\'debug\')');
        }
        $qb    ->setParameter('level', $level)
            ->orderBy('a.dateCreated', 'DESC');
        return $qb;
    }

    public function projectActivities($projectId)
    {
        $result = $this->getEntityManager()->createQuery("SELECT a.id FROM Oscar\Entity\Activity a WHERE a.project = $projectId")->getScalarResult();
        $ids = array_map('current', $result);


        $qb = $this->getEntityManager()->createQueryBuilder()
            ->select('a')
            ->from(LogActivity::class, 'a')
            ->where('a.type NOT IN(\'debug\')')
            ->andWhere("(a.contextId = :id AND a.context LIKE 'Project%') OR (a.contextId IN(:ids) AND a.context LIKE 'Activity%')")
            ->orderBy('a.dateCreated', 'DESC')
            ->setParameters([
                'id' => $projectId,
                'ids' => $ids
            ]);
        return $qb;
    }

    public function activityActivities($idActivity)
    {

        $qb = $this->getEntityManager()->createQueryBuilder()
            ->select('a')
            ->from(LogActivity::class, 'a')
            ->where('a.type NOT IN(\'debug\')')
            ->andWhere("(a.contextId = :id AND a.context LIKE 'Activity%')")
            ->orderBy('a.dateCreated', 'DESC')
            ->setParameters([
                'id' => $idActivity
            ]);
        return $qb;
    }
}
