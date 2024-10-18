<?php

namespace Oscar\Connector\Access;

use Oscar\Connector\IConnector;
use Oscar\Exception\OscarException;

class ConnectorAccessOracleDB implements IConnectorAccess
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

    /**
     * Retourne les données "brutes" PHP
     * @return mixed
     */
    public function getDatas( $url )
    {
        return null;
    }

    /**
     * Retourne le connector
     * @return IConnector
     */
    public function getConnector(): IConnector
    {
        return $this->connector;
    }

    /**
     * Retourne les informations pour l'objet $remoteId.
     *
     * @param $remoteId
     * @param null $params
     * @return mixed
     */
    public function getDataSingle($remoteId, $params = null)
    {
        $rows = [];

        try {
            $c = $this->getConnection($params);

            if ($c) {
                $stid = oci_parse($c, $params['db_query_single']);
                if (!$stid) {
                    throw new OscarException("ORACLE - PARSE ERROR : " . oci_error());
                }
                oci_bind_by_name($stid, ':p1', $remoteId);
                if (!oci_execute($stid)) {
                    throw new OscarException("ORACLE - QUERY ERROR : " . oci_error());
                }

                while ($row = oci_fetch_array($stid, OCI_ASSOC + OCI_RETURN_NULLS)) {
                    $rows[] = $row;
                }
            }
            else {
                throw new OscarException("Erreur de connection ORACLE");
            }
        } catch (\Exception $e) {
            $this->getConnector()->logError($e->getMessage());
            throw $e;
        }

        if (\count($rows) < 1) {
            throw new OscarException("PERSON CONNECTOR SYNC ONE : Aucune personne trouvée pour l'ID " . $remoteId);
        }
        if (\count($rows) > 1) {
            throw new OscarException("PERSON CONNECTOR SYNC ONE : Plusieurs personnes trouvées pour l'ID " . $remoteId);
        }

        return $rows[0];
    }

    /**
     * Retourne toutes les informations.
     *
     * @param null $params
     * @return mixed
     */
    public function getDataAll($params = null)
    {
        $rows = [];

        try {
            $c = $this->getConnection($params);

            if ($c) {
                $stid = oci_parse($c, $params['db_query_all']);
                if (!$stid) {
                    throw new OscarException("ORACLE - PARSE ERROR : " . oci_error());
                }
                if (!oci_execute($stid)) {
                    throw new OscarException("ORACLE - QUERY ERROR : " . oci_error());
                }

                while ($row = oci_fetch_array($stid, OCI_ASSOC + OCI_RETURN_NULLS)) {
                    $rows[] = $row;
                }
            }
            else {
                throw new OscarException("Erreur de connection ORACLE");
            }
        } catch (\Exception $e) {
            $this->getConnector()->logError($e->getMessage());
            throw $e;
        }

        return $rows;
    }

    /**
     * @return mixed
     * @throws OscarException
     */
    protected function getConnection($params)
    {
        if (!function_exists('oci_connect')) {
            throw new \Exception("Le module OCI pour les connections PHP > ORACLE est nécessaire.");
        }

        $c = @oci_connect(
            $params['db_user'],
            $params['db_password'],
            sprintf(
                "%s:%s/%s",
                $params['db_host'],
                $params['db_port'],
                $params['db_name']
            )
        );

        if (!$c) {
            $err = oci_error();
            $message = "Unknow error";
            if( array_key_exists('message', $err) ){
                $message = $err['message'];
            }
            throw new OscarException($message);
        }

        return $c;
    }
}
