<?php


namespace Oscar\Connector\Access;

use Oscar\Connector\IConnector;
use Oscar\Exception\ConnectorException;
use Oscar\Utils\PhpPolyfill;

/**
 * FICHIER d'EXEMPLE
 * Class ConnectorAccessFile
 * @package Oscar\Connector\Access
 */
class ConnectorAccessFile implements IConnectorAccess
{
    /** @var IConnector */
    private $connector;

    /** @var string */
    private $filepath;

    // Paramètres attendus dans le fichier YAML
    private const FILE_PATH_PARAMETER_NAME = 'file_data';

    /**
     * ConnectorAccessCurlHttp constructor.
     * @param IConnector $connector Connector qui va consommer l'accès aux données.
     */
    public function __construct(IConnector $connector, $options)
    {
        $this->connector = $connector;
    }

    public function getDatas($id=null)
    {
        if( !$this->connector->hasParameter(self::FILE_PATH_PARAMETER_NAME) ){
            throw new ConnectorException("Le paramètre '%s' est requis.", self::FILE_PATH_PARAMETER_NAME);
        }

        $file = $this->connector->getParameter(self::FILE_PATH_PARAMETER_NAME);
        if( !file_exists($file) ){
            throw new ConnectorException(sprintf("Le fichier '%s' n'existe pas.", $file));
        }

        $datas = file_get_contents($file);

        return PhpPolyfill::jsonDecode($datas);
    }

    public function getConnector(): IConnector
    {
        return $this->getConnector();
    }

    public function getDataSingle($remoteId, $params = null)
    {
        $all = $this->getDatas();
        foreach ($all as $key=>$datas) {
            if( $datas['id'] == $remoteId ){
                return $datas;
            }
        }
        throw new \Exception("No data for $remoteId");
    }

    public function getDataAll($params = null)
    {
        return $this->getDatas();
    }
}