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

class ActivityElasticSearch implements ActivitySearchStrategy
{
    private $elasticSearchClient;
    private $hosts;
    private $index = 'oscar-activity';
    private $type = 'activity';

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
        if (!$this->elasticSearchClient)
            $this->elasticSearchClient = ClientBuilder::create()
                ->setHosts($this->getHosts())
                ->build();
        return $this->elasticSearchClient;
    }

    public function addActivity(Activity $activity)
    {
        $params = ['body' => []];

        $params['body'][] = [
            'index' => [
                '_index' => $this->getIndex(),
                '_type' => $this->getType(),
                '_id' => $activity->getId(),
            ]
        ];

        $params['body'][] = $this->getIndexableDatas($activity);

        return $this->getClient()->bulk($params);
    }

    /**
     * La reconstruction d'index utilise BULK pour des raisons de performance.
     *
     * @param $activities
     */
    public function rebuildIndex($activities)
    {
        $repport = new ConnectorRepport();
        $this->resetIndex();
        $repport->addnotice("Index réinitialisé");

        $i = 0;
        /** @var Activity $activity */
        foreach ($activities as $activity) {
            $i++;
            $repport->addadded((string)$activity);
            $params['body'][] = [
                'index' => [
                    '_index' => $this->getIndex(),
                    '_type' => $this->getType(),
                    '_id' => $activity->getId()
                ]
            ];

            $params['body'][] = $this->getIndexableDatas($activity);

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

    protected function getIndexableDatas(Activity $activity)
    {
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

        $out = [
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

        return $out;
    }

    public function search($search)
    {
        $search = trim($search);
        // TRAITEMENT de la recherche
        if( strpos($search, 'AND') || strpos($search, 'OR') || strpos($search, '"') ){

        } else {
            $split = explode(" ", $search);
            if( count($split) == 1 ){
                $search = sprintf('%s OR %s', $split[0], $split[0].'*');
            } else {
                $assembly = [];
                foreach ($split as $term) {
                    $assembly[] = trim($term);
                }
                $search = implode(' AND ', $assembly);
            }
        }

        $params = [
            'index' => $this->getIndex(),
            'type' => $this->getType(),
            'body' => [
                'size' => 10000,
                'query' => [
                    'query_string' => [
                        'query' => $search,
                        'fields' => [
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
                            'members^5'],
                        "fuzziness"=> "auto"
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

    public function searchProject($what)
    {
        $params = [
            'index' => $this->getIndex(),
            'type' => $this->getType(),
            'body' => [
                'size' => 10000,
                'query' => [
                    'query_string' => [
                        'query' => sprintf('%s OR  %s*', $what, $what)
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

    public function searchDelete($id)
    {
        $parms = [
            'index' => $this->getIndex(),
            'type' => $this->getType(),
            'id' => "$id"
        ];
        return $this->getClient()->delete($parms);
    }

    public function searchUpdate(Activity $activity)
    {
        $params = [
            'index' => $this->getIndex(),
            'type' => $this->getType(),
            'id' => $activity->getId(),
            'body' => [
                'doc' => $this->getIndexableDatas($activity)
            ]
        ];
        try {
            return $this->getClient()->update($params);
        } catch (Missing404Exception $e){
            return $this->addActivity($activity);
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
    }
}
