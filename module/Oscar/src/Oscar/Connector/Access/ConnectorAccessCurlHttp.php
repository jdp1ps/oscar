<?php


namespace Oscar\Connector\Access;

use Oscar\Connector\ConnectorRepport;
use Oscar\Connector\IConnector;
use Oscar\Exception\ConnectorException;
use Oscar\Utils\PhpPolyfill;

/**
 * Utilisation de CURL pour accéder aux données en HTTP.
 *
 * Class ConnectorAccessCurlHttp
 * @package Oscar\Connector\Access
 */
class ConnectorAccessCurlHttp implements IConnectorAccess
{
    /** @var IConnector */
    private $connector;

    /**
     * ConnectorAccessCurlHttp constructor.
     * @param IConnector $connector Connector qui va consommer l'accès aux données.
     */
    public function __construct(IConnector $connector)
    {
        $this->connector = $connector;
    }

    public function getDatas($url)
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_COOKIESESSION, true);

        // Affichage des erreurs CURL
        curl_setopt($curl, CURLOPT_FAILONERROR, true);

        // Fix CURL error curl: (18) transfer closed with 141 bytes remaining to read
        curl_setopt($curl, CURLOPT_IGNORE_CONTENT_LENGTH, true);

        $return = curl_exec($curl);

        if (false === $return) {
            throw new ConnectorException(
                sprintf(
                    "ConnectorAccessCurlHttp(%s) n'a pas fournis les données attendues : %s",
                    $url,
                    curl_error($curl)
                )
            );
        }

        return PhpPolyfill::jsonDecode($return);
    }

    public function getConnector(): IConnector
    {
        return $this->connector;
    }

    public function getDataSingle($remoteId, $params = null)
    {
        return $this->getDatas($this->getConnector()->getPathSingle($remoteId));
    }

    public function getDataAll($params = null)
    {
        return $this->getDatas($this->getConnector()->getPathAll());
    }
}
