<?php

namespace Oscar\Strategy\Search;

use Elasticsearch\Client;
use Elasticsearch\ClientBuilder;
use Elasticsearch\Common\Exceptions\Missing404Exception;
use Monolog\Logger;
use Oscar\Exception\OscarException;
use Oscar\Service\LoggerService;

abstract class ElasticSearchEngine
{

    const VARIANT_RAPID_SEARCH_TEXT = 'VARIANT_RAPID_SEARCH_TEXT';
    const VARIANT_ELASTIC_QUERY = 'VARIANT_ELASTIC_QUERY';

    private ?Client $elasticSearchClient = null;

    private array $hosts;

    private Logger $loggerService;

    private string $searchVariant;

    /**
     * @param array $hosts
     * @param Logger $logger
     * @param string $search_variant
     */
    public function __construct(array $hosts, Logger $logger, string $search_variant = self::VARIANT_RAPID_SEARCH_TEXT)
    {
        $this->hosts = $hosts;
        $this->loggerService = $logger;
        $this->searchVariant = $search_variant;
    }

    //
    abstract public function getIndexableDatas(mixed $object): array;

    abstract public function getFieldsSearchedWeighted(string $search): array;

    abstract public function getIndex(): string;

    abstract public function getType(): string;

    /**
     * @return Client
     * @throws OscarException
     */
    protected function getClient(): Client
    {
        try {
            if ($this->elasticSearchClient === null) {
                $this->elasticSearchClient = ClientBuilder::create()
                    ->setHosts($this->getHosts())
                    ->build();
            }
            return $this->elasticSearchClient;
        } catch (\Exception $exception) {
            $msg = "Création du client impossible";
            $this->loggerService->critical("$msg : " . $exception->getMessage());
            throw new OscarException($msg);
        }
    }

    /**
     * @param mixed $object
     * @return callable|array
     * @throws OscarException
     */
    public function addItem(mixed $object): callable|array
    {
        $client = $this->getClient();

        try {
            $params = ['body' => []];

            $params['body'][] = [
                'index' => [
                    '_index' => $this->getIndex(),
                    '_type'  => $this->getType(),
                    '_id'    => $object->getId(),
                ]
            ];

            $params['body'][] = $this->getIndexableDatas($object);

            return $client->bulk($params);
        } catch (\Exception $exception) {
            $msg = "Impossible d'indexer l'information";
            $this->loggerService->critical("$msg : " . $exception->getMessage());
            throw new OscarException($msg);
        }
    }

    /**
     * @param array $items
     * @return void
     * @throws OscarException
     */
    public function rebuildIndex(array $items): void
    {
        $this->loggerService->debug('[elasticsearch] Rebuilding index...');
        try {
            $this->resetIndex();
        } catch (\Exception $exception) {
        }
        $client = $this->getClient();

        try {
            $i = 0;
            foreach ($items as $item) {
                $i++;
                $params['body'][] = [
                    'index' => [
                        '_index' => $this->getIndex(),
                        '_type'  => $this->getType(),
                        '_id'    => $item->getId()
                    ]
                ];

                $params['body'][] = $this->getIndexableDatas($item);

                // On envoie par paquet de 1000
                if ($i % 1000 == 0) {
                    $responses = $this->getClient()->bulk($params);

                    // clean datas
                    $params = ['body' => []];
                    unset($responses);
                }
            }

            if (!empty($params['body'])) {
                $client->bulk($params);
            }
        } catch (\Exception $exception) {
            $msg = "Réindexation impossible";
            $this->loggerService->critical("$msg : " . $exception->getMessage());
            throw new OscarException($msg);
        }
    }

    /**
     * Retourne les paramètres utilisés pour la recherche.
     *
     * @param string $textSearch
     * @param int $limit
     * @return array
     */
    public function getParamsQuery(string $textSearch, int $limit = 10000): array
    {
        $search = trim($textSearch);


        $query = [
            'index' => $this->getIndex(),
            'type'  => $this->getType(),
            'body'  => [
                'size'  => $limit,
                "query" => $this->getFieldsSearchedWeighted($search)
            ]
        ];

        $this->loggerService->debug("----query elastic : \n" . json_encode($query['body']['query']) ."\n---- ");

        return $query;
    }

    /**
     * @throws OscarException
     */
    public function searchRaw(string $search, int $limit = 10000): array
    {
        $this->loggerService->info("Search '$search' in " . $this->getIndex());
        $client = $this->getClient();
        try {
            $params = $this->getParamsQuery($search, $limit);
            $ids = $client->search($params);
            return $ids;
        } catch (\Throwable $exception) {
            $ex = ElasticSearchEngineException::getInstance($exception);
            $this->loggerService->error($ex->getMessage());
            throw new OscarException($ex->getMessagePublic());
        }
    }

    /**
     * @param string $search
     * @param bool $withIdsProjects
     * @return array Liste des IDs
     * @throws OscarException
     */
    public function search(string $search, bool $withIdsProjects = false): array
    {
        $response = $this->searchRaw($search);

        $ids = [];
        $idsProjects = [];
        if ($response && $response['hits'] && $response['hits']['total'] > 0) {
            foreach ($response['hits']['hits'] as $hit) {
                $ids[] = intval($hit["_id"]);
                $idProject = intval($hit['_source']["project_id"]);
                if( $withIdsProjects && !in_array($idProject, $idsProjects) ){
                    $idsProjects[] = $idProject;
                }
            }
        }

        if( $withIdsProjects ){
            return [
                'activity_ids' => $ids,
                'project_ids' => $idsProjects
            ];
        }

        return $ids;
    }

    /**
     * Suppression d'un élément du moteur de recherche.
     *
     * @param $id
     * @return callable|array
     * @throws OscarException
     */
    public function searchDelete(int $id): callable|array
    {
        $params = [
            'index' => $this->getIndex(),
            'type'  => $this->getType(),
            'id'    => "$id"
        ];
        return $this->getClient()->delete($params);
    }

    /**
     * @param mixed $item
     * @return callable|array
     * @throws OscarException
     */
    public function searchUpdate(mixed $item): callable|array
    {
        $params = [
            'index' => $this->getIndex(),
            'type'  => $this->getType(),
            'id'    => $item->getId(),
            'body'  => [
                'doc' => $this->getIndexableDatas($item)
            ]
        ];
        try {
            return $this->getClient()->update($params);
        } catch (Missing404Exception $e) {
            $this->loggerService->warning("Item à mettre à jour absent " . $e->getMessage());
            return $this->addItem($item);
        } catch (\Exception $e) {
            throw new OscarException(
                sprintf(
                    "Impossible de mettre à jour le moteur de recherche %s : %s",
                    $this->getIndex(),
                    $e->getMessage()
                )
            );
        }
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function resetIndex(): array
    {
        $this->loggerService->debug('[elastic] reset index...');
        $params = [
            'index' => $this->getIndex()
        ];

        $client = $this->getClient();

        try {
            return $client->indices()->delete($params);
        } catch (\Exception $e) {
            throw ElasticSearchEngineException::getInstance($e);
        }
    }

    /**
     * @return array
     */
    public function getHosts(): array
    {
        return $this->hosts;
    }
}