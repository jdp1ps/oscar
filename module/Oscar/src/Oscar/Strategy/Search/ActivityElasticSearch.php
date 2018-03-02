<?php
/**
 * Created by PhpStorm.
 * User: jacksay
 * Date: 28/02/18
 * Time: 18:14
 */

namespace Oscar\Strategy\Search;


use Oscar\Entity\Activity;
use Elasticsearch\ClientBuilder;

class ActivityElasticSearch implements ActivitySearchStrategy
{
    private $elasticSearchClient;
    private $hosts;
    private $index = 'oscar';
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
    public function getIndex(): string
    {
        return $this->index;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    public function getHosts(){
        return $this->hosts;
    }

    public function search($search)
    {
        $params = [
            'index' => $this->getIndex(),
            'type' => $this->getType(),
            'body' => [
                'query' => [
                    'query_string' => [
                        'query' => $search
                    ]
                ]
            ]
        ];

        $response = $this->getClient()->search($params);
        $ids = [];
        if( $response && $response['hits'] && $response['hits']['total'] > 0 ){
            foreach ($response['hits']['hits'] as $hit) {
                $ids[] = $hit["_id"];
            }
        }
        return $ids;
    }

    protected function getClient()
    {
        if( !$this->elasticSearchClient )
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

    protected function getIndexableDatas( Activity $activity ){
        $project_body = "";
        $project_id = null;

        if( $activity->getProject() ){
            $project_body = $activity->getProject()->getCorpus();
            $project_id = $activity->getProject()->getId();
        }

        $members = [];
        $partners = [];

        foreach ( $activity->getPersonsDeep() as $personRoled ){
            $members[] = (string) $personRoled->getPerson();
        }

        foreach ( $activity->getOrganizationsDeep() as $organizationRolead ){
            $partners[] = (string) $organizationRolead->getOrganization();
        }

        return [
            'label' => $activity->getLabel(),
            'description' => $activity->getDescription(),
            'saic' => $activity->getCentaureId(),
            'oscar' => $activity->getOscarId(),
            'eotp' => $activity->getCodeEOTP(),
            'acronym' => $activity->getAcronym(),
            'activity_id' => $activity->getId(),
            'members' => $members,
            'partners' => $partners,
            'project_id' => $project_id,
            'project' => $project_body,
        ];
    }

    public function searchProject($what)
    {
        // TODO: Implement searchProject() method.
    }

    public function searchDelete($id)
    {
        return $this->getClient()->delete([
            'index' => $this->getIndex(),
            'type' => $this->getType(),
            'id' => $id
        ]);
    }

    public function searchUpdate(Activity $activity)
    {
        $params = [
            'index' => $this->getIndex(),
            'type' => $this->getType(),
            'id' => $activity->getId(),
            'body' => $this->getIndexableDatas()
        ];
        return $this->getClient()->update($params);
    }

    public function resetIndex()
    {
        $params = [
            'index' => 'oscar'
        ];

        $this->getClient()->indices()->delete($params);
    }
}