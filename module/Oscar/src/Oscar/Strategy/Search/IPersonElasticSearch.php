<?php
/**
 * Created by PhpStorm.
 * User: bouvry
 * Date: 16/04/19
 * Time: 16:58
 */

namespace Oscar\Strategy\Search;


use Elasticsearch\ClientBuilder;
use Elasticsearch\Common\Exceptions\Missing404Exception;
use Oscar\Connector\ConnectorRepport;
use Oscar\Entity\ActivityPerson;
use Oscar\Entity\OrganizationPerson;
use Oscar\Entity\Person;
use Oscar\Exception\OscarException;

class IPersonElasticSearch extends ElasticSearchEngine implements IPersonISearchStrategy
{

    public function getIndex(): string
    {
        return 'oscar-person';
    }

    public function getType(): string
    {
        return 'person';
    }

    public function add(Person $person):callable|array
    {
        $params = ['body' => []];

        $params['body'][] = [
            'index' => [
                '_index' => $this->getIndex(),
                '_type' => $this->getType(),
                '_id' => $person->getId(),
            ]
        ];

        $params['body'][] = $this->getIndexableDatas($person);

        return $this->getClient()->bulk($params);
    }

    public function getIndexableDatas(mixed $item) :array
    {
        if( !$item instanceof Person ){
            throw new OscarException("Un objet PERSON est attendu");
        }

        $person = $item;

        $projects = [];
        $activities = [];
        $organizations = [];
        $connectors = [];

        /** @var OrganizationPerson $personOrganization */
        foreach ($person->getOrganizations() as $personOrganization) {
            $organizations[] = (string)$personOrganization->getOrganization();
        }

        /** @var ActivityPerson $activityPerson */
        foreach ($person->getActivities() as $activityPerson) {
            if ($activityPerson->getActivity()->getProject()) {
                $project = (string)$activityPerson->getActivity()->getProject()->getAcronym(
                    ) . " " . (string)$activityPerson->getActivity()->getProject()->getLabel();
                if (!in_array($project, $projects)) {
                    $projects[] = $project;
                }
            }
            $activity = (string)$activityPerson->getActivity()->getLabel();
            if (!in_array($activity, $activities)) {
                $activities[] = $activity;
            }
        }
        if ($person->getConnectors()) {
            foreach ($person->getConnectors() as $name => $value) {
                $connectors[] = $value;
            }
        }

        return [
            'id' => $person->getId(),
            'firstname' => $person->getFirstname(),
            'lastname' => $person->getLastname(),
            'fullname' => $person->getLastname() . ' ' . $person->getFirstname(),
            'email' => $person->getEmail(),
            'affectation' => $person->getLdapAffectation(),
            'location' => $person->getLdapSiteLocation(),
            'organizations' => $organizations,
            'activities' => $activities,
            'connectors' => $connectors
        ];
    }

    public function update(Person $person):callable|array
    {
       return $this->searchUpdate($person);
    }

    public function getFieldsSearchedWeighted(): array
    {
        return [
            'fullname3',
            'lastname^7',
            'firstname',
            'email',
            'location',
            'affectation',
            'connectors'
        ];
    }

    public function remove(int $id): callable|array
    {
        return $this->searchDelete($id);
    }
}