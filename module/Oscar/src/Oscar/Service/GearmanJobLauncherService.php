<?php


namespace Oscar\Service;

use Oscar\Entity\Activity;
use Oscar\Entity\ActivityOrganization;
use Oscar\Entity\Organization;
use Oscar\Entity\Person;
use Oscar\Entity\Project;
use Oscar\Entity\ProjectPartner;
use Oscar\Traits\UseLoggerService;
use Oscar\Traits\UseLoggerServiceTrait;
use Oscar\Traits\UseOscarConfigurationService;
use Oscar\Traits\UseOscarConfigurationServiceTrait;

/**
 * Ce service a pour fonction de centraliser les communications avec Gearman.
 *
 * Class GearmanJobLauncherService
 * @package Oscar\Service
 */
class GearmanJobLauncherService implements UseOscarConfigurationService, UseLoggerService
{
    use UseOscarConfigurationServiceTrait;
    use UseLoggerServiceTrait;

    private $_clientGearman;

    /**
     * Retourne le client d'accès à Gearman.
     *
     * @return \GearmanClient
     */
    private function getGearmanClient(): \GearmanClient
    {
        if ($this->_clientGearman === null) {
            $this->_clientGearman = new \GearmanClient();
            $this->_clientGearman->addServer($this->getOscarConfigurationService()->getGearmanHost());
        }
        return $this->_clientGearman;
    }

    /**
     * Envoi à Gearman une tâche avec les paramètres donnés, avec la clef donnée.
     *
     * @param string $task Fonction référencées côté oscar-worker.php
     * @param array $params Paramètres attendus par la fonction [clef => valeur]
     * @param string $key Identifiant unique de la tâche
     */
    protected function sendBackgroundTask(string $task, array $params, string $key): void
    {
        try {
            $this->getLoggerService()->debug(" > gearman : $task($key) " . json_encode($params));
            $this->getGearmanClient()->doBackground($task, json_encode($params), $key);
        } catch (\Exception $e){
            throw new \Exception(sprintf("Impossible de programmer une task gearman (%s)", $e->getMessage()));
        }
    }

    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    /// TRIGGERS

    /**
     * Mise à jour des notifications d'une activité de recherche
     *
     * @param Activity $activity
     */
    public function triggerUpdateNotificationActivity(Activity $activity): void
    {
        $task = self::UPDATE_NOTIFICATION_ACTIVITY;
        $params = ['activityid' => $activity->getId()];
        $key = sprintf('%s-%s', self::UPDATE_NOTIFICATION_ACTIVITY, $activity->getId());
        $this->sendBackgroundTask($task, $params, $key);
    }

    public function triggerUpdateNotificationOrganization(Organization $organization): void
    {
        /** @var ActivityOrganization $activityOrganization */
        foreach ($organization->getActivities() as $activityOrganization) {
            if( $activityOrganization->isPrincipal() ){
                $this->triggerUpdateNotificationActivity($activityOrganization->getActivity());
            }
        }

        /** @var ProjectPartner $projectPartner */
        foreach ($organization->getProjects() as $projectPartner) {
            if( $projectPartner->isPrincipal() ){
                $this->triggerUpdateNotificationProject($projectPartner->getProject());
            }
        }
    }

    /**
     * Mise à jour des notifications des activités d'un projet de recherche.
     *
     * @param Project $project
     */
    public function triggerUpdateNotificationProject(Project $project): void
    {
        foreach ($project->getActivities() as $activity) {
            $this->triggerUpdateNotificationActivity($activity);
        }
    }

    /**
     * @param Project $project
     */
    public function triggerUpdateSearchIndexProject(Project $project): void
    {
        foreach ($project->getActivities() as $activity) {
            $this->triggerUpdateSearchIndexActivity($activity);
        }
    }

    /**
     * Mise à jour de l'index de recherche de l'organisation
     *
     * @param Organization $organization
     */
    public function triggerUpdateSearchIndexOrganization(Organization $organization): void
    {
        $task = self::UPDATE_INDEX_ORGANIZATION;
        $params = ['organizationid' => $organization->getId()];
        $key = sprintf('%s-%s', $task, $organization->getId());
        $this->sendBackgroundTask($task, $params, $key);
    }

    /**
     * Mise à jour de l'index de recherche de la personne
     *
     * @param Person $person
     */
    public function triggerUpdateSearchIndexPerson(Person $person): void
    {
        $task = self::UPDATE_INDEX_PERSON;
        $params = ['personid' => $person->getId()];
        $key = sprintf('%s-%s', $task, $person->getId());
        $this->sendBackgroundTask($task, $params, $key);
    }

    /**
     * Mise à jour de l'index de recherche de l'activity
     *
     * @param Activity $activity
     */
    public function triggerUpdateSearchIndexActivity(Activity $activity): void
    {
        $task = self::UPDATE_INDEX_ACTIVITY;
        $params = ['activityid' => $activity->getId()];
        $key = sprintf('%s-%s', $task, $activity->getId());
        $this->sendBackgroundTask($task, $params, $key);
    }

    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    /// NOM des FONCTIONS (voir ./bin/oscar-worker.php)
    const UPDATE_NOTIFICATION_ACTIVITY = 'updateNotificationsActivity';
    const UPDATE_INDEX_ORGANIZATION = 'updateIndexOrganization';
    const UPDATE_INDEX_PERSON = 'updateIndexPerson';
    const UPDATE_INDEX_ACTIVITY = 'updateIndexActivity';


}