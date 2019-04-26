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

class PersonElasticSearch implements PersonSearchStrategy
{
    private $elasticSearchClient;
    private $hosts;
    private $index = 'oscar-person';
    private $type = 'person';

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

    public function search($search)
    {
        $params = [
            'index' => $this->getIndex(),
            'type' => $this->getType(),
            'body' => [
                'size' => 10000,
                'query' => [
                    'query_string' => [
                        'query' => $search
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

    public function add(Person $person)
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

    /**
     * La reconstruction d'index utilise BULK pour des raisons de performance.
     *
     * @param $activities
     */
    public function rebuildIndex($persons)
    {
        $repport = new ConnectorRepport();
        $this->resetIndex();
        $repport->addnotice("Index réinitialisé");

        $i = 0;
        /** @var Activity $person */
        foreach ($persons as $person) {
            $i++;
            $repport->addadded((string)$person);
            $params['body'][] = [
                'index' => [
                    '_index' => $this->getIndex(),
                    '_type' => $this->getType(),
                    '_id' => $person->getId()
                ]
            ];

            $params['body'][] = $this->getIndexableDatas($person);

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

    protected function getIndexableDatas(Person $person)
    {
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
            if( $activityPerson->getActivity()->getProject() ){
                $project = (string) $activityPerson->getActivity()->getProject()->getAcronym() ." " . (string) $activityPerson->getActivity()->getProject()->getLabel();
                if( !in_array($project, $projects) ){
                    $projects[] = $project;
                }
            }
            $activity = (string)$activityPerson->getActivity()->getLabel();
            if( !in_array($activity, $activities) ){
                $activities[] = $activity;
            }
        }
        if( $person->getConnectors() ){
            foreach ($person->getConnectors() as $name=>$value) {
                $connectors[] = $value;
            }
        }

        return [
            'id' => $person->getId(),
            'firstname' => $person->getFirstname(),
            'lastname' => $person->getLastname(),
            'fullname' => $person->getDisplayName(),
            'email' => $person->getEmail(),
            'affectation' => $person->getLdapAffectation(),
            'location' => $person->getLdapSiteLocation(),
            'organizations' => $organizations,
            'activities' => $activities,
            'connectors' => $connectors
        ];
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

    public function update(Person $person)
    {
        $params = [
            'index' => $this->getIndex(),
            'type' => $this->getType(),
            'id' => $person->getId(),
            'body' => [
                'doc' => $this->getIndexableDatas($person)
            ]
        ];
        try {
            return $this->getClient()->update($params);
        } catch (Missing404Exception $e){
            return $this->addActivity($person);
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