<?php
/**
 * @author Stéphane Bouvry<stephane.bouvry@unicaen.fr>
 * @date: 16-03-15 11:52
 * @copyright Certic (c) 2016
 */

namespace Oscar\Provider;


abstract class AbstractOracleProvider
{
    /**
     * AbstractOracleProvider constructor.
     * @param array $config ['user'=>'', 'password'=>'','host'=>'']
     */
    abstract public function config();

    /**
     * @return resource
     * @throws \Exception
     */
    protected function getConnection()
    {
        static $connection;
        if (null === $connection) {
            $config = $this->config();

            $user = $config['user'];
            $pass = $config['password'];
            $connectionString = sprintf('%s:%s/%s', $config['host'], $config['port'], $config['dbname']);
            $encoding = $config['charset'];

            $connection = \oci_connect($user, $pass, $connectionString, $encoding);
            if (!$connection) {
                $e = oci_error();
                throw new \Exception(sprintf('Impossible de se connecter à la BDD oracle : %s', $e));
            }
        }
        return $connection;
    }

    /**
     * @param $query
     * @throws \Exception
     */
    protected function query( $query )
    {
        $stid = oci_parse($this->getConnection(), $query);
        oci_execute($stid);
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
}