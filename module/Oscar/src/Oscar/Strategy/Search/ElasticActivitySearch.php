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
     * @throws OscarException
     */
    public function addActivity(Activity $activity): array|callable
    {
        return $this->addItem($activity);
    }

    /**
     * @param mixed $object
     * @return array
     * @throws OscarException
     */
    public function getIndexableDatas(mixed $object): array
    {
        if (!$object instanceof Activity) {
            throw new OscarException("L'objet indexé doit être une activité");
        }
        else {
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
            'label'        => $activity->getLabel(),
            'description'  => $activity->getDescription(),
            'saic'         => $activity->getCentaureId(),
            'numerotation' => $activity->getNumbers(),
            'oscar'        => $activity->getOscarNum(),
            'activitytype' => $activity->getActivityType() ? (string)$activity->getActivityType() : '',
            'numbers'      => implode(" ", $activity->getNumbersValues()),
            'eotp'         => $activity->getCodeEOTP(),
            'acronym'      => $activity->getAcronym(),
            'activity_id'  => $activity->getId(),
            'disciplines'  => $activity->getDisciplinesArray(),
            'members'      => $members,
            'partners'     => $partners,
            'project_id'   => $project_id,
            'project'      => $project_body,
        ];
    }

    public function searchProject($what): array
    {
        $params = [
            'index' => $this->getIndex(),
            'type'  => $this->getType(),
            'body'  => [
                'size'  => 10000,
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

    public function getFieldsSearchedWeighted(string $search): array
    {
        $andQuery = null;
        $wordsNbr = 1;

        // Détection des recherches strictes
        if(preg_match_all('/^"(.*)"$/', $search, $matches, PREG_SET_ORDER)) {
            if( count($matches) == 1 && array_key_exists(1, $matches[0])) {
                $andQuery = $matches[0][0];
                $wordsNbr = 2;
            }
        }

//        if(preg_match_all('/^query:(.*)$/', $search, $matches, PREG_SET_ORDER)) {
//            if( count($matches) == 1 && array_key_exists(1, $matches[0])) {
//                $andQuery = $matches[0][1];
//                $wordsNbr = 2;
//            }
//        }

        if(preg_match_all('/^(.*)\*$/', $search, $matches, PREG_SET_ORDER)) {
            if( count($matches) == 1 && array_key_exists(1, $matches[0])) {
                $andQuery = $matches[0][0];
                $wordsNbr = 2;
            }
        }

        if( $andQuery === null ){
            $words = explode(" ", $search);
            $wordsNbr = count($words);
            $andQuery = implode(" AND ", $words);

            // TEST d'approximation
//            $wordsUpdated = [];
//            foreach ($words as $word) {
//                $lng = (int)(strlen($word) / 4);
//                $wordsUpdated[] = $word . ($lng > 0 ? "~$lng" : "");
//            }
        }


        $query = [
            "bool" => [
                "should"               => [

                ],
                "minimum_should_match" => 1
            ]
        ];

        // TODO si plusieurs mots, ajouter une règle spécifique
        if ($wordsNbr > 1) {

            $query["bool"]["should"][] = [
                "query_string" => [
                    "query"  => $andQuery,
                    "fields" => [
                        'oscar^20',
                        'eotp^20',
                        'acronym^15',
                        'numbers^10',
                        'disciplines^7',
                        'label^5',
                        'project^5',
                        'activitytype^3',
                        'description^2',
                        'partners',
                        'members'
                    ]
                ]
            ];
        }
        else {
            $query["bool"]["should"] = [
                ["match" => [ "oscar" => ["query" => $andQuery, "boost" => 20]]],
                ["match" => [ "eotp" => ["query" => $andQuery, "boost" => 20]]],
                ["match" => [ "acronym" => ["query" => $andQuery, "boost" => 15]]],
                ["match" => [ "numbers" => ["query" => $andQuery, "boost" => 10]]],
                ["match" => [ "disciplines" => ["query" => $andQuery, "boost" => 7]]],
                ["match" => [ "label" => ["query" => $andQuery, "boost" => 5]]],
                ["match" => [ "activitytype" => ["query" => $andQuery, "boost" => 3]]],
                ["match" => [ "project" => ["query" => $andQuery, "boost" => 5]]],
                ["match" => [ "description" => ["query" => $andQuery, "boost" => 1]]],
                ["match" => [ "partners" => ["query" => $andQuery, "boost" => 1]]],
                ["match" => [ "members" => ["query" => $andQuery, "boost" => 1]]],
            ];
        }

        return $query;
    }

    public function getIndex(): string
    {
        return 'oscar-activity';
    }

    public function getType(): string
    {
        return 'activity';
    }

    /**
     * @throws OscarException
     */
    public function deleteActivity(int $id): callable|array
    {
        return $this->searchDelete($id);
    }

    /**
     * @throws OscarException
     */
    public function updateActivity(Activity $activity): callable|array
    {
        return $this->searchUpdate($activity);
    }
}
