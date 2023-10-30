<?php
/**
 * Created by PhpStorm.
 * User: bouvry
 * Date: 23/09/19
 * Time: 11:06
 */

namespace Oscar\Service;


use Oscar\Exception\OscarException;
use Psr\Container\ContainerInterface;
use Laminas\ServiceManager\Exception\ServiceNotFoundException;

class OscarServiceFactory
{
    public function getService( string $serviceName , ContainerInterface $c, $throw=true ){
        if( !$serviceName ){
            throw new OscarException("Le nom de service est vide...");
        }

        $s = null;

        try {
            $s = $c->get($serviceName);

        } catch (ServiceNotFoundException $e){
            if( $throw === true ){
                throw new OscarException("Impossible de charger le service '$serviceName'");
            }
        }

        return $s;
    }
}