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
    private ?Client $elasticSearchClient = null;
    private array $hosts;

    private Logger $loggerService;

    /**
     * @param array $hosts
     * @param Logger $logger
     */
    public function __construct(array $hosts, Logger $logger)
    {
        $this->hosts = $hosts;
        $this->loggerService = $logger;
    }

    //
    abstract public function getIndexableDatas(mixed $object): array;

    abstract public function getFieldsSearchedWeighted(): array;

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
        $this->resetIndex();
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
        return [
            'index' => $this->getIndex(),
            'type'  => $this->getType(),
            'body'  => [
                'size'  => $limit,
                'query' => [
                    'query_string' => [
                        'query'     => $textSearch,
                        'fields'    => $this->getFieldsSearchedWeighted(),
                        "fuzziness" => "auto"
                    ]
                ]
            ]
        ];
    }

    /**
     * @throws OscarException
     */
    public function searchRaw(string $search, int $limit = 10000): array
    {
        $this->loggerService->info("Search '$search' in " . $this->getIndex());
        $client = $this->getClient();
        try {
            $search = trim($search);
            // TRAITEMENT de la recherche
            if (strpos($search, 'AND') || strpos($search, 'OR') || strpos($search, '"')) {
            }
            else {
                $split = explode(" ", $search);
                if (count($split) == 1) {
                    $search = sprintf('%s OR %s', $split[0], $split[0] . '*');
                }
                else {
                    $assembly = [];
                    foreach ($split as $term) {
                        $assembly[] = trim($term);
                    }
                    $search = implode(' AND ', $assembly);
                }
            }

            $params = $this->getParamsQuery($search, $limit);

            return $client->search($params);

        } catch (\Exception $exception) {
            $msg = "Erreur de recherche";
            $this->loggerService->critical("$msg : " . $exception->getMessage());
            throw new OscarException($msg);
        }
    }

    /**
     * @param string $search
     * @return array Liste des IDs
     * @throws OscarException
     */
    public function search(string $search): array
    {
        $response = $this->searchRaw($search);

        $ids = [];
        if ($response && $response['hits'] && $response['hits']['total'] > 0) {
            foreach ($response['hits']['hits'] as $hit) {
                $ids[] = intval($hit["_id"]);
            }
        }

        return $ids;
    }

    /**
     * Suppression d'un élément du moteur de recherche.
     *
     * @param $id
     * @return callable|array
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
        $params = [
            'index' => $this->getIndex()
        ];

        $client = $this->getClient();

        try {
            return $client->indices()->delete($params);
        } catch (\Exception $e) {
            $msg = "Impossible de réinitialiser le moteur de recherche";
            $this->loggerService->critical("$msg : " . $e->getMessage());
            throw new OscarException($msg);
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