<?php
/**
 * @author Stéphane Bouvry<stephane.bouvry@unicaen.fr>
 * @date: 26/06/15 12:02
 * @copyright Certic (c) 2015
 */

namespace Oscar\Utils;


class EntityHydrator {
    private $map;
    function __construct( array $map = null ){
        $this->map = $map;
    }

    public function hydrateAuto( array $datas, $entity ){
        foreach( $datas as $fieldName=>$value ){

            // Setter attendu
            $method = 'set'.ucfirst($fieldName);
            if( !method_exists($entity, $method) ){
                throw new \Exception(sprintf("La méthode d'accès '%s($value)' est attendue dans la classe '%s'",
                    $method,
                    get_class($entity)));
            }

            // Récupération et nettoyage de la données
            $value = $datas[$fieldName];
            if( isset($conf['cleaner']) ){
                if( !$conf['cleaner'] instanceof \Closure ){
                    throw new \Exception("La propriété 'cleaner' doit être une Closure");
                }
                $value = $conf['cleaner']($value);
            }

            $entity->$method($value);

        }
    }

    public function hydrate( $datas, $entity ){
        foreach( $datas as $fieldName=>$value ){
            if( !isset($this->map[$fieldName]) ){
                continue;
            }
            $conf = $this->map[$fieldName];

            // Propriété
            $property = $conf['property'];

            // Setter attendu
            $method = 'set'.ucfirst($property);
            if( !method_exists($entity, $method) ){
                throw new \Exception(sprintf("La méthode d'accès '%s($value)' est attendue dans la classe '%s'",
                    $method,
                    get_class($entity)));
            }

            // Récupération et nettoyage de la données
            $value = $datas[$fieldName];
            if( isset($conf['cleaner']) ){
                if( !$conf['cleaner'] instanceof \Closure ){
                    throw new \Exception("La propriété 'cleaner' doit être une Closure");
                }
                $value = $conf['cleaner']($value);
            }
            if( $value ) {
                $entity->$method($value);
            }
        }
    }

    public function listFields(){
        return array_keys($this->map);
    }
}