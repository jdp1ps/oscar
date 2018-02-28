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


    public function search($search)
    {
        // TODO: Implement search() method.
    }

    protected function getClient()
    {
        if( !$this->elasticSearchClient )
            $this->elasticSearchClient = ClientBuilder::create()->build();
        return $this->elasticSearchClient;
    }

    public function addActivity(Activity $activity)
    {
        $params = [
            'index' => 'activity',
            'type' => 'activity',
            'id' => $activity->getId(),
            'body' => [
                'label' => $activity->getLabel(),
                'acronym' => $activity->getAcronym(),
            ]
        ];

        $response = $this->getClient()->index($params);
    }

    public function searchProject($what)
    {
        // TODO: Implement searchProject() method.
    }

    public function searchDelete($id)
    {
        // TODO: Implement searchDelete() method.
    }

    public function searchUpdate(Activity $activity)
    {
        // TODO: Implement searchUpdate() method.
    }

    public function resetIndex()
    {
        // TODO: Implement resetIndex() method.
    }
}