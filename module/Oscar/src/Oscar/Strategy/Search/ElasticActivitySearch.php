<?php
/**
 * Created by PhpStorm.
 * User: jacksay
 * Date: 28/02/18
 * Time: 18:14
 */

namespace Oscar\Strategy\Search;


use Elasticsearch\Common\Exceptions\Missing404Exception;
use Oscar\Connector\ConnectorRepport;
use Oscar\Entity\Activity;
use Elasticsearch\ClientBuilder;
use Oscar\Exception\OscarException;

class ElasticActivitySearch extends ElasticSearchEngine implements IActivitySearchStrategy
{
    /**
     * @param Activity $activity
     * @return array|callable
     */
    public function addActivity(Activity $activity) :array|callable
    {
        return $this->addItem($activity);
    }

    /**
     * @param mixed $object
     * @return array
     * @throws OscarException
     */
    public function getIndexableDatas(mixed $object) :array
    {
        if (!$object instanceof Activity) {
            throw new OscarException("L'objet indexé doit être une activité");
        } else {
            $activity = $object;
        }

        $project_body = "";
        $project_id = null;

        if ($activity->getProject()) {
            $project_body = $activity->getProject()->getCorpus();
            $project_id = $activity->getProject()->getId();
        }

        $members = [];
        $partners = [];

        foreach ($activity->getPersonsDeep() as $personRoled) {
            $members[] = (string)$personRoled->getPerson();
        }

        foreach ($activity->getOrganizationsDeep() as $organizationRolead) {
            $partners[] = (string)$organizationRolead->getOrganization();
        }

        return [
            'label' => $activity->getLabel(),
            'description' => $activity->getDescription(),
            'saic' => $activity->getCentaureId(),
            'numerotation' => $activity->getNumbers(),
            'oscar' => $activity->getOscarNum(),
            'activitytype' => $activity->getActivityType() ? (string)$activity->getActivityType() : '',
            'numbers' => implode(" ", $activity->getNumbersValues()),
            'eotp' => $activity->getCodeEOTP(),
            'acronym' => $activity->getAcronym(),
            'activity_id' => $activity->getId(),
            'disciplines' => $activity->getDisciplinesArray(),
            'members' => $members,
            'partners' => $partners,
            'project_id' => $project_id,
            'project' => $project_body,
        ];
    }

    public function searchProject($what): array
    {
        $params = [
            'index' => $this->getIndex(),
            'type' => $this->getType(),
            'body' => [
                'size' => 10000,
                'query' => [
                    'query_string' => [
                        'query' => sprintf('%s OR %s*', $what, $what)
                    ]
                ]
            ]
        ];

        $response = $this->getClient()->search($params);
        $ids = [];

        if ($response && $response['hits'] && $response['hits']['total'] > 0) {
            foreach ($response['hits']['hits'] as $hit) {
                $ids[] = $hit["_source"]["project_id"];
            }
        }

        return $ids;
    }

    public function getFieldsSearchedWeighted(): array
    {
        return [
            'acronym^10',
            'numerotation^9',
            'eotp^9',
            'numbers^9',
            'oscar^9',
            'label^7',
            'description^2',
            'project^5',
            'disciplines^5',
            'activitytype^2',
            'partners^5',
            'members^5'
        ];
    }

    public function getIndex(): string
    {
       return 'oscar-activity';
    }

    public function getType(): string
    {
        return 'activity';
    }

    public function deleteActivity(int $id): callable|array
    {
        return $this->searchDelete($id);
    }

    public function updateActivity(Activity $activity): callable|array
    {
        return $this->searchUpdate($activity);
    }
}