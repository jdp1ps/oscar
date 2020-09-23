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
class ConnectorAccessCurlHttpBasicAuth implements IConnectorAccess
{
    const OPTION_USERNAME = 'username';
    const OPTION_PASSWORD = 'password';

    /** @var IConnector */
    private $connector;

    /**
     * ConnectorAccessCurlHttp constructor.
     * @param IConnector $connector Connector qui va consommer l'accès aux données.
     * @param string $url Nom du paramètre contenant d'URL.
     */
    public function __construct(IConnector $connector)
    {
        $this->connector = $connector;
    }

    public function getDatas( $url )
    {
        $additionalHeaders = '';
        $payloadName = '';
        $username = $this->getConnector()->getParameter(self::OPTION_USERNAME);
        $password = $this->getConnector()->getParameter(self::OPTION_PASSWORD);
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json', $additionalHeaders));
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_USERPWD, $username . ":" . $password);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payloadName);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        $return = curl_exec($ch);
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if( $return === FALSE || $httpcode != 200 ){
            $error = "";
            if( $return === FALSE ){
                $error .= "Retour vide. ";
            }

            $error .= "CODE $httpcode. ";

            if( $httpcode == 401 ){
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