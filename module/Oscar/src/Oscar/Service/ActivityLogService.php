<?php
/**
 * @author Stéphane Bouvry<stephane.bouvry@unicaen.fr>
 * @date: 07/10/15 11:00
 * @copyright Certic (c) 2015
 */

namespace Oscar\Service;

use Doctrine\ORM\EntityManager;
use Monolog\Logger;
use Oscar\Entity\ActivityLogRepository;
use Oscar\Entity\LogActivity;
use Oscar\Entity\Authentification;
use Oscar\Entity\Person;
use Oscar\Traits\UseServiceContainer;
use Oscar\Traits\UseServiceContainerTrait;

class ActivityLogService implements UseServiceContainer {

    // Pour ce service, j'utilise directement le container
    // afin d'éviter les références cyclique que Zend ne
    // gère pas nativement.
    // J'ai fait des recherches sur des composants tiers
    // (PHP-DI) mais le manque de documentation sur le sujet
    // méritera d'y consacrer du temps (quand on en aura à perdre)
    use UseServiceContainerTrait;

    /**
     * @return OscarUserContext
     */
    public function getOscarUserContextService(){
        return $this->getServiceContainer()->get(OscarUserContext::class);
    }

    /**
     * @return EntityManager
     */
    public function getEntityManager(){
        return $this->getServiceContainer()->get(EntityManager::class);
    }

    /**
     * @return Logger
     */
    public function getLogger(){
        return $this->getServiceContainer()->get('Logger');
    }

    public function getLoggerService(){
        return $this->getLogger();
    }

    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    /**
     * @return \UnicaenAuth\Entity\Ldap\People
     */
    protected function getLdapUser() {
        return $this->getOscarUserContextService()->getUserContext()->getLdapUser();
    }

    /**
     * @return \ZfcUser\Entity\UserInterface
     */
    protected function getDbUser(){
        return $this->getOscarUserContextService()->getUserContext()->getDbUser();
    }

    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
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
        $requestUri = "";
        if( php_sapi_name() === 'cli' ){
            $requestUri = "console";
        } else {
            $requestUri = $_SERVER['REQUEST_URI'];
        }
        $data = [
          'REQUEST_URI' => $requestUri
        ];

        $this->addActivity($message, LogActivity::TYPE_INFO, $level, $userid, $context, $contextId, $data);
    }

    /**
     * @return Person
     */
    public function getCurrentPerson()
    {
        return $this->getOscarUserContextService()->getCurrentPerson();
    }

    public function getAuthentificationActivities( $authentificationId, $limit=20 ){
        /** @var ActivityLogRepository $repo */
        $repo = $this->getEntityManager()->getRepository(LogActivity::class);

        return $repo->getUserActivity($authentificationId, $limit);
    }

    public function addUserInfo($message, $context = 'Application', $contextId = -1, $level = LogActivity::LEVEL_ADMIN)
    {
        $this->getLoggerService()->info($message);
        $person = $this->getCurrentPerson();
        $personText = "Anonymous";
        if( is_string($person) ){
            $personText = $person;
        } elseif( $person ) {
            $personText = $person->log();
        } elseif ( $this->getDbUser() ) {
            $personText = $this->getDbUser()->getId().':'.$this->getDbUser()->getDisplayName();
        }
        $message = sprintf($personText.' %s', $message);
        return $this->addInfo($message, $this->getDbUser(), $level, $context, $contextId);
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
