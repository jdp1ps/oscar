<?php

namespace Oscar\Strategy\Search;

use Elasticsearch\Common\Exceptions\NoNodesAvailableException;
use Oscar\Exception\OscarException;

class ElasticSearchEngineException extends OscarException
{
    // Index non trouvé

    const CODE_NO_NODES_AVAILABLE = 400;
    const CODE_UNEXPECTED_VALUE = 401;
    const CODE_INDEX_NOT_FOUND = 404;
    // Requêtes foireuse
    const CODE_BAD_QUERY = 405;

    const CODE_UNKNOW = 0;

    private string $messagePublic;
    private string $jsonSource;

    public function __construct(string $message = "", int $code = self::CODE_UNKNOW)
    {
        parent::__construct($message, $code);
        $this->messagePublic = "Elastic error";
    }

    public function getMessagePublic(): string
    {
        return $this->messagePublic;
    }

    public function setMessagePublic(string $messagePublic): self
    {
        $this->messagePublic = $messagePublic;
        return $this;
    }

    public function getJsonSource(): string
    {
        return $this->jsonSource;
    }

    public function setJsonSource(string $jsonSource): self
    {
        $this->jsonSource = $jsonSource;
        return $this;
    }

    public static function getInstance(\Throwable $throw) :self
    {
        $exception = new ElasticSearchEngineException();

        if( $throw instanceof NoNodesAvailableException ){

            $exception->code = self::CODE_NO_NODES_AVAILABLE;
            $exception->messagePublic = "Le serveur Elasticsearch est indisponible";
            $exception->message = "$throw->message";
        } elseif ( $throw instanceof \UnexpectedValueException ){
            $exception->code = self::CODE_UNEXPECTED_VALUE;
            $exception->messagePublic = "La requête envoyée est incomplète (Contactez l'administrateur)";
            $exception->message = "$throw->message";
        }

        else {

            $exception->setJsonSource($throw->getMessage());
            $json = json_decode($throw->getMessage(), true);

            if( is_array($json) && array_key_exists('error', $json) && array_key_exists('root_cause', $json['error']) ) {
                $exception->code = self::CODE_UNKNOW;
                $exception->messagePublic = 'Erreur Elastic inconnue';
                $exception->message = $throw->getMessage();
                $errors = $json['error']['root_cause'];
                if( count($errors) > 0 ){
                    $error = $errors[0];
                    switch ($error['type']) {
                        case 'query_shard_exception':
                            $exception->code = self::CODE_BAD_QUERY;
                            $exception->messagePublic = 'Requête incorrecte';
                            $exception->message = $error['type'] . " : " . $error['reason'];
                            break;
                        case 'index_not_found_exception':
                            $exception->code = self::CODE_INDEX_NOT_FOUND;
                            $exception->messagePublic = 'Index de recherche introuvable';
                            $exception->message = $error['type'] . " : " . $error['reason'];
                            break;
                        default:
                            $exception->code = self::CODE_UNKNOW;
                            $exception->messagePublic = 'Erreur Elastic inconnue';
                            break;
                    }
                }
            }
        }

        return $exception;
    }
}