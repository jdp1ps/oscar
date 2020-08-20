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

        die($datas);

        return PhpPolyfill::jsonDecode($datas);
    }
}