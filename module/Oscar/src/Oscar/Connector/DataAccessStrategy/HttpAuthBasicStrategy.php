<?php
/**
 * Created by PhpStorm.
 * User: bouvry
 * Date: 09/01/20
 * Time: 11:38
 */

namespace Oscar\Connector\DataAccessStrategy;


use Oscar\Connector\IConnectorOscar;

class HttpAuthBasicStrategy implements IDataAccessStrategy
{
    const OPTION_USERNAME = 'username';
    const OPTION_PASSWORD = 'password';
    const OPTION_URL = 'url';

    /** @var IConnectorOscar */
    private $connector;

    /**
     * HttpAuthBasicStrategy constructor.
     * @param IConnectorOscar $connector
     */
    public function __construct(IConnectorOscar $connector)
    {
        $this->connector = $connector;
    }

    public function getConnector(): IConnectorOscar
    {
        return $this->connector;
    }

    public function getData(string $url)
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
            throw new \Exception(curl_error($ch));
        }
        curl_close($ch);
        return $return;
    }
}