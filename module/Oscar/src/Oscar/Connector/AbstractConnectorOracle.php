<?php
/**
 * @author Stéphane Bouvry<stephane.bouvry@unicaen.fr>
 * @date: 16-09-15 13:19
 * @copyright Certic (c) 2016
 */

namespace Oscar\Connector;


abstract class AbstractConnectorOracle extends AbstractConnectorBdd
{

    /**
     * AbstractConnectorOracle constructor.
     */
    public function __construct($params)
    {
        parent::__construct($params);
    }

    /**
     * @param $query
     * @throws \Exception
     */
    public function query( $query )
    {
        // Préparation
        $stid = oci_parse($this->getConnexion(), $query);
        if( $stid === FALSE ){
            throw new Exception("Erreur avec le connecteur Oracle, impossible de préparer la requête.");
        }

        // Exécution
        if( oci_execute($stid) === FALSE ){
            throw new \Exception("Erreur avec le connecteur Oracle, l'éxécution de la requète a échoué.");
        }

        return $stid;
    }

    /**
     * Retourne la chaîne débarassée des espaces à la c..
     *
     * @param $str
     * @return string
     */
    public static function cleanBullshitStr( $str )
    {
        return trim($str);
    }

    /**
     * Retourne la date qui va bien.
     *
     * @param $str
     * @return \DateTime|null
     */
    public static function extractDateFromStr( $str )
    {
        $date = \DateTime::createFromFormat('Ymd', $str);
        return $date ? $date : null;
    }

    /**
     * Se connecte et retourne la ressource de connexion.
     *
     * @return resource
     * @throws \Exception
     */
    public function getConnexion()
    {
        static $connection;
        if (null === $connection) {
            $user = $this->getParam('user');
            $pass = $this->getParam('password');//$config['password'];
            $connectionString = sprintf('%s:%s/%s', $this->getParam('host'), $this->getParam('port'), $this->getParam('dbname'));
            $encoding = $this->getParam('charset');

            $connection = @\oci_connect($user, $pass, $connectionString, $encoding);
            if (!$connection) {
                $e = oci_error();
                throw new \Exception(sprintf('Impossible de se connecter à la BDD oracle : %s', $e['message']));
            }
        }
        return $connection;
    }
}