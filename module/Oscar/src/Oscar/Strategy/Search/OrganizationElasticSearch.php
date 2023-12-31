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

class OrganizationElasticSearch implements OrganizationSearchStrategy
{
    private $elasticSearchClient;
    private $hosts;
    private $index = 'oscar-organization';
    private $type = 'organization';

    /**
     * ActivityElasticSearch constructor.
     * @param $hosts
     */
    public function __construct($hosts)
    {
        $this->hosts = $hosts;
    }

    /**
     * @return string
     */
    public function getIndex()
    {
        return $this->index;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    public function getHosts()
    {
        return $this->hosts;
    }

    /**
     * @return \Elasticsearch\Client
     */
    protected function getClient()
    {
        if (!$this->elasticSearchClient) {
            $this->elasticSearchClient = ClientBuilder::create()
                ->setHosts($this->getHosts())
                ->build();
        }


        return $this->elasticSearchClient;
    }


    public function add(Organization $organization)
    {
        $params = ['body' => []];

        $params['body'][] = [
            'index' => [
                '_index' => $this->getIndex(),
                '_type' => $this->getType(),
                '_id' => $organization->getId(),
            ]
        ];

        $params['body'][] = $this->getIndexableDatas($organization);

        return $this->getClient()->bulk($params);
    }

    /**
     * La reconstruction d'index utilise BULK pour des raisons de performance.
     *
     * @param $activities
     */
    public function rebuildIndex($organizations)
    {
        $repport = new ConnectorRepport();
        $this->resetIndex();
        $repport->addnotice("Index réinitialisé");


        /****/


        $i = 0;
        /** @var Activity $organization */
        foreach ($organizations as $organization) {
            $i++;
            $repport->addadded((string)$organization);
            $params['body'][] = [
                'index' => [
                    '_index' => $this->getIndex(),
                    '_type' => $this->getType(),
                    '_id' => $organization->getId()
                ]
            ];

            $params['body'][] = $this->getIndexableDatas($organization);

            // On envoi par paquet de 1000
            if ($i % 1000 == 0) {
                $responses = $this->getClient()->bulk($params);

                // clean datas
                $params = ['body' => []];
                unset($responses);
            }
        }

        if (!empty($params['body'])) {
            $this->getClient()->bulk($params);
        }

        return $repport;
    }

    protected function getIndexableDatas(Organization $organization)
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

    public function search($search)
    {
        $params = [
            'index' => $this->getIndex(),
            'type' => $this->getType(),
            'body' => [
                'size' => 10000,
                "query" => [
                    'multi_match' => [
                        'fields' => [
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
                        ],
                        "fuzziness"=> "auto",
                        'query' => $search,

                    ]
                ]
            ]
        ];

        $response = $this->getClient()->search($params);
        $ids = [];

        if ($response && $response['hits'] && $response['hits']['total'] > 0) {
            foreach ($response['hits']['hits'] as $hit) {
                $ids[] = $hit["_id"];
            }
        }

        return $ids;
    }

    public function remove($id)
    {
        $parms = [
            'index' => $this->getIndex(),
            'type' => $this->getType(),
            'id' => "$id"
        ];
        return $this->getClient()->delete($parms);
    }

    public function update(Organization $organization)
    {
        $params = [
            'index' => $this->getIndex(),
            'type' => $this->getType(),
            'id' => $organization->getId(),
            'body' => [
                'doc' => $this->getIndexableDatas($organization)
            ]
        ];
        try {
            return $this->getClient()->update($params);
        } catch (Missing404Exception $e) {
            return $this->add($organization);
        }
    }

    public function resetIndex()
    {
        $params = [
            'index' => $this->getIndex()
        ];

        try {
            $this->getClient()->indices()->delete($params);
        } catch (Missing404Exception $e) {
        } catch (\Exception $e) {
            throw $e;
        }
//
//        try {
//            $this->getClient()->indices()->create(
//                [
//                    'index' => $this->getIndex(),
//                    'body' => [
//                        'settings' => [
//                            'analysis' => [
//                                'analyzer' => [
//                                    'noaccent' => [
//                                        "type" => "custom",
//                                        "tokenizer" => "standard",
//                                        "filter" => ['asciifolding', "lowercase"]
//                                    ]
//                                ],
//                            ]
//                        ],
//                        'mappings' => [
//                            "organization" => [
//                                'properties' => [
//                                    'code' => [
//                                        'type' => 'keyword',
//                                    ],
//                                    'shortname' => [
//                                        'type' => 'text',
////                                        'analyzer' => 'noaccent'
//                                    ],
//                                    'fullname' => [
////                                        'analyzer' => 'noaccent',
//                                        'type' => 'text',
//                                    ],
//                                    'description' => [
////                                        'analyzer' => 'noaccent',
//                                        'type' => 'text',
//                                    ],
//                                    'email' => [
////                                        'analyzer' => 'noaccent',
//                                        'type' => 'text',
//                                    ],
//                                    'city' => [
////                                        'analyzer' => 'noaccent',
//                                        'type' => 'text',
//                                    ],
//                                    'siret' => [
//                                        'type' => 'keyword',
//                                    ],
//                                    'country' => [
//                                        'type' => 'keyword',
//                                    ],
//                                    'connectors' => [
//                                        'type' => 'keyword',
//                                    ],
//                                    'zipcode' => [
//                                        'type' => 'keyword',
//                                    ],
//                                    'persons' => [
////                                        'analyzer' => 'noaccent',
//                                        'type' => 'text',
//                                    ],
//                                    'activities' => [
////                                        'analyzer' => 'noaccent',
//                                        'type' => 'text',
//                                    ],
//                                ]
//                            ]
//                        ]
//                    ]
//                ]
//            );
//        } catch (\Exception $e) {
//            throw new OscarException("Impossible de créer l'index de recherche : \n---\n" . $e->getMessage());
//        }
    }
}