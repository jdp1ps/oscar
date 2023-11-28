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
use Oscar\Entity\ActivityOrganization;
use Oscar\Entity\Organization;
use Oscar\Entity\OrganizationPerson;
use Oscar\Exception\OscarException;

class OrganizationElasticSearch extends ElasticSearchEngine implements IOrganizationSearchStrategy
{
    public function getIndex(): string
    {
        return 'oscar-organization';
    }

    public function getType(): string
    {
        return 'organization';
    }


    public function add(Organization $organization) :callable|array
    {
        $this->addItem($organization);
    }

    public function getIndexableDatas(mixed $organization) :array
    {
        $projects = [];
        $activities = [];
        $persons = [];
        $connectors = [];

        /** @var OrganizationPerson $member */
        foreach ($organization->getPersons() as $member) {
            $persons[] = (string)$member->getPerson();
        }

        /** @var ActivityOrganization $activityOrganization */
        foreach ($organization->getActivities() as $activityOrganization) {
            if ($activityOrganization->getActivity()->getProject()) {
                $project = (string)$activityOrganization->getActivity()->getProject()->getAcronym(
                    ) . " " . (string)$activityOrganization->getActivity()->getProject()->getLabel();
                if (!in_array($project, $projects)) {
                    $projects[] = $project;
                }
            }
            $activity = (string)$activityOrganization->getActivity()->getLabel();
            if (!in_array($activity, $activities)) {
                $activities[] = $activity;
            }
        }
        if ($organization->getConnectors()) {
            foreach ($organization->getConnectors() as $name => $value) {
                $connectors[] = $value;
            }
        }

        return [
            'id' => $organization->getId(),
            'code' => $organization->getCode() ? $organization->getCode() : "",
            'shortname' => $organization->getShortName(),
            'fullname' => $organization->getFullName(),
            'email' => $organization->getEmail(),
            'city' => $organization->getCity(),
            'country' => $organization->getCountry(),
            'zipcode' => $organization->getZipCode(),
            'description' => $organization->getDescription(),
            'siret' => $organization->getSiret(),
            'persons' => $persons,
            'activities' => $activities,
            'connectors' => $connectors
        ];
    }

//    public function search($search)
//    {
//        $params = [
//            'index' => $this->getIndex(),
//            'type' => $this->getType(),
//            'body' => [
//                'size' => 10000,
//                "query" => [
//                    'multi_match' => [
//                        'fields' => [
//                            'code^7',
//                            'shortname^9',
//                            'fullname^5',
//                            'description',
//                            'email',
//                            'city',
//                            'siret',
//                            'country',
//                            'connectors',
//                            'zipcode',
//                            'persons',
//                            'activities'
//                        ],
//                        "fuzziness"=> "auto",
//                        'query' => $search,
//
//                    ]
//                ]
//            ]
//        ];
//
//        $response = $this->getClient()->search($params);
//        $ids = [];
//
//        if ($response && $response['hits'] && $response['hits']['total'] > 0) {
//            foreach ($response['hits']['hits'] as $hit) {
//                $ids[] = $hit["_id"];
//            }
//        }
//
//        return $ids;
//    }


    public function getFieldsSearchedWeighted(): array
    {
        return [
            'code^7',
            'shortname^9',
            'fullname^5',
            'description',
            'email',
            'city',
            'siret',
            'country',
            'connectors',
            'zipcode',
            'persons',
            'activities'
        ];
    }

    public function update(Organization $entity): callable|array
    {
        return $this->searchUpdate($entity);
    }
}