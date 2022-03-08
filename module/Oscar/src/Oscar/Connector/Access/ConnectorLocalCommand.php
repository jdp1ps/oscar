<?php


namespace Oscar\Connector\Access;


use Oscar\Connector\IConnector;
use Oscar\Exception\OscarException;
use Oscar\Utils\PhpPolyfill;

class ConnectorLocalCommand implements IConnectorAccess
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
        $output = null;
        $outputCode = null;
        $success = exec($url, $output, $outputCode);

        if( $success && $outputCode == 0 ){
            if( count($output) == 1 ){
                $jsonString = $output[0];
                try {
                    return PhpPolyfill::jsonDecode($jsonString);
                } catch (\Exception $e) {
                    throw new OscarException("Impossible de convertir la chaîne en donnèes JSON");
                }
            } else {
                throw new OscarException("problème avec le retour JSON depuis '$url', le résultat attendu doit être d'une seule ligne");
            }
        } else {
            throw new OscarException("La commande '$url' a retournée un code d'erreur $outputCode");
        }
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