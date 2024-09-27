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
        $words = explode(" ", $search);

        $wordsNbr = count($words);
        $andQuery = implode(" AND ", $words);


        $query = [
            "bool" => [
                "should"               => [

                ],
                "minimum_should_match" => 1
            ]
        ];

        // TODO si plusieurs mots, ajouter une règle spécifique
        if ($wordsNbr > 1) {
            $wordsUpdated = [];
            foreach ($words as $word) {
                $lng = (int)(strlen($word) / 4);
                $wordsUpdated[] = $word . ($lng > 0 ? "~$lng" : "");
            }
            $wordsUpdatedAndQuery = implode(" AND ", $wordsUpdated);
            $query["bool"]["should"][] = [
                "query_string" => [
                    "query" => $wordsUpdatedAndQuery,
                    "fields" => [
                        'acronym^10',
                        'numerotation^9',
                        'eotp^9',
                        'numbers^9',
                        'oscar^9',
                        'label~1^7',
                        'description^2',
                        'project^7',
                        'disciplines^5',
                        'activitytype^7',
                        'partners^5',
                        'members^5'
                    ]
                ]
                ];
        }
        else {
            $query["bool"]["should"] = [
                [
                    "query_string" => [
                        "query"  => $andQuery,
                        "fields" => ["label^7", "description2"]
                    ]
                ],
                [
                    "match" => [
                        "discipline" => [
                            "query"     => $search,
                            "boost"     => 5
                        ]
                    ]
                ],
                [
                    "match" => [
                        "acronym" => [
                            "query"     => $search,
                            "boost"     => 10
                        ]
                    ]
                ],
                [
                    "match" => [
                        "description" => [
                            "query"     => $search,
                            'fuzziness' => "AUTO", // "Tolérance" aux fautes,
                        ]
                    ]
                ],
                [
                    "match" => [
                        "activitytype" => [
                            "query"     => $search,
                            'fuzziness' => "AUTO", // "Tolérance" aux fautes,
                            "boost"     => 2
                        ]
                    ]
                ],
                [
                    "match" => [
                        "project" => [
                            "query"     => $search,
                            'fuzziness' => "AUTO", // "Tolérance" aux fautes,
                            "boost"     => 2
                        ]
                    ]
                ],

                [
                    "match" => [
                        "partners" => [
                            "query" => $search
                        ]
                    ]
                ],

                [
                    "match" => [
                        "members" => [
                            "query" => $search
                        ]
                    ]
                ],

//                [
//                    "prefix" => [
//                        "acronym" => $search,  // Documents qui commencent par l'expression
//                    ]
//                ],
//                [
//                    "prefix" => [
//                        "numerotation" => $search,  // Documents qui commencent par l'expression
//                    ]
//                ],
//                [
//                    "prefix" => [
//                        "eotp" => $search,  // Documents qui commencent par l'expression
//                    ]
//                ]
//                ,
//                [
//                    "prefix" => [
//                        "oscar" => $search,  // Documents qui commencent par l'expression
//                    ]
//                ]
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
