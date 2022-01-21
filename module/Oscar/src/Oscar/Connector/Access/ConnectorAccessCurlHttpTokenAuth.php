<?php


namespace Oscar\Connector\Access;

use Oscar\Connector\ConnectorRepport;
use Oscar\Connector\IConnector;
use Oscar\Exception\ConnectorException;
use Oscar\Utils\PhpPolyfill;

/**
 * Utilisation de CURL pour accéder aux données en HTTP avec token auth
 *
 * paramètres de connecteur :
 *
 *    access_strategy:  Oscar\Connector\Access\ConnectorAccessCurlHttpTokenAuth
 *    token: 1234567890abcdef
 *    method: GET ou POST (optionnel : défault = POST)
 *    force_unsecure_http: true ou false (optionnel : défault = false)
 *
 * Class ConnectorAccessCurlHttpTokenAuth
 * @package Oscar\Connector\Access
 */
class ConnectorAccessCurlHttpTokenAuth implements IConnectorAccess
{
    const OPTION_TOKEN = 'token';
    const OPTION_METHOD = 'method';

    const OPTION_METHOD_DEFAULT = 'POST';

    const OPTION_FORCE_UNSECURE_HTTP = 'force_unsecure_http';
    const OPTION_FORCE_UNSECURE_HTTP_DEFAULT = false;

    /** @var IConnector */
    private $connector;

    /**
     * ConnectorAccessCurlHttpTokenAuth constructor.
     * @param IConnector $connector Connector qui va consommer l'accès aux données.
     * @param string $url Nom du paramètre contenant d'URL.
     */
    public function __construct(IConnector $connector)
    {
        $this->connector = $connector;
    }

    /**
     * configuration la méthode HTTP dans la session curl selon la configuration du Connecteur
     * par défault : POST
     */
    private function setCurlMethod($ch)
    {
        $method = $this->getConnector()->getParameter(self::OPTION_METHOD, self::OPTION_METHOD_DEFAULT);

        if (empty($method) || !in_array($method, ['GET', 'POST']) || $method == "POST") {
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, '');
        } else {
            curl_setopt($ch, CURLOPT_HTTPGET, 1);
        }
    }

    /**
     * Vérifie que l'url est sécurisée. Si elle ne l'est pas, l'utilisateur peut toujours forcer l'utilisation d'une url non sécurisée
     * avec l'option OPTION_FORCE_UNSECURE_HTTP.
     * @throws Exception si l'url n'est pas sécurisée et que l'option n'est pas = true
     */
    private function checkScheme($url)
    {
        if ((substr($url, 0, 8) != "https://") && !$this->getConnector()->getParameter(
                self::OPTION_FORCE_UNSECURE_HTTP,
                false
            )) {
            throw new \Exception(
                "l'URL d'accès n'est pas sécurisée. Utilisez le protocole HTTPs ou forcez l'utilisation d'HTTP avec l'option " . self::OPTION_FORCE_UNSECURE_HTTP
            );
        }
    }

    public function getDatas($url)
    {
        $this->checkScheme($url);

        $token = $this->getConnector()->getParameter(self::OPTION_TOKEN);
        $authHeaders = 'Authorization: Bearer ' . $token;
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json', $authHeaders));
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        $this->setCurlMethod($ch);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $return = curl_exec($ch);
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if ($return === false || $httpcode != 200) {
            $error = "";
            if ($return === false) {
                $error .= "Retour vide. ";
            }

            $error .= "CODE $httpcode. ";

            if ($httpcode == 401) {
                $error = "Accès à l'API non-autorisé. ";
            }
            throw new \Exception($error);
        }
        curl_close($ch);

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