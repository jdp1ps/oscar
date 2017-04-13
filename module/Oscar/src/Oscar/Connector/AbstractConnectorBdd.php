<?php
/**
 * @author Stéphane Bouvry<stephane.bouvry@unicaen.fr>
 * @date: 16-09-15 09:22
 * @copyright Certic (c) 2016
 */

namespace Oscar\Connector;

use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Oscar\Entity\Organization;

/**
 * Cette classe centralise le fonctionnement des connecteurs type "Base de données".
 *
 * Class AbstractConnectorBdd
 * @package Oscar\Connector
 */
abstract class AbstractConnectorBdd
{
    private $params;
    private $fieldConfiguration;
    private $dateUpdateField;
    private $queryOne;
    private $queryAll;
    private $hydratationPostProcess;

    protected function setHydratationPostProcess( \Closure $function ){
        $this->hydratationPostProcess = $function;
    }

    protected function setQueryOne( $query ){
        $this->queryOne = $query;
    }

    protected function setQueryAll( $query ){
        $this->queryAll = $query;
    }

    /**
     * $parameters est un tableau associatif qui doit contenir les informations
     * pour la connection à la BDD.
     *
     * AbstractConnectorBdd constructor.
     * @param $parameters
     */
    function __construct( $parameters )
    {
        $this->configure($parameters);
    }

    /**
     * Cette méthode permet de vérifier et compléter la configuration des champs.
     *
     * @param $config
     */
    final protected function buildFieldConfiguration( $config ){
        $this->fieldConfiguration = [];
        foreach( $config as $oscarName=>$params ){
            if( !isset($params['remoteName']) ){
                throw new Exception("La configuration du champ $oscarName doit contenir une clef 'remoteName'.");
            }

            $required = key_exists('required', $params) ? (bool)$params['required'] : true;
            $cleanupFunction = key_exists('cleanupFunction', $params) ? $params['cleanupFunction'] : function($str){return $str; };
            $setter = key_exists('setter', $params) ? $params['setter']  : 'set' . strtoupper(substr($oscarName, 0, 1)) . substr($oscarName, 1);

            $conf = [
                'remoteName' => $params['remoteName'],
                'required' => $required,
                'setter' => $setter,
                'cleanupFunction' => $cleanupFunction
            ];

            $this->fieldConfiguration[$oscarName] = $conf;
        }
    }

    /**
     * Retourne la configuration pour la champ $name.
     *
     * @param $name
     * @return mixed
     */
    final protected function getField($name){
        return $this->fieldConfiguration[$name];
    }

    protected function getFieldsConfiguration(){
        return $this->fieldConfiguration;
    }

    protected function hydrateObjectWithRemote( $object, $data ){
        // Date de mise à jour
        if( $this->getUpdateField() ) {
            $dataUpdated = \DateTime::createFromFormat($this->getUpdateFieldFormat(),
                $data[$this->getUpdateFieldName()]);
            $object->setDateUpdated($dataUpdated);
        }

        // ID côté connecteur
        $object->setConnectorID($this->getName(), $data[$this->getRemoteID()]);


        foreach( $this->getFieldsConfiguration() as $oscar=>$params ){
            $setter = $this->getSetterFieldname($oscar);
            $cleanup = $this->getCleanupFieldname($oscar);
            if( $cleanup )
                $value = $cleanup($data[$this->getRemoteFieldname($oscar)]);
            else
                $value = $data[$this->getRemoteFieldname($oscar)];

            if( !isset($data[$this->getRemoteFieldname($oscar)]) && $this->isRequiredFieldname($oscar) ){
                throw new \Exception(sprintf('Un champ %s est attendu dans les résultats du connecteur !',$this->getRemoteFieldname($oscar)));
            }

            $value = $data[$this->getRemoteFieldname($oscar)];
            $object->$setter($value);
        }
        if( $this->hydratationPostProcess ){
            $postProcess = $this->hydratationPostProcess;
            $postProcess($object, $data);
        }
        return $object;
    }

    protected function syncAll( IConnectedRepository $repository, $force=false ){
        $syncRepport = [];
        $stid = $this->query($this->queryAll);
        $dateFormat = $this->getUpdateFieldFormat();
        $dateRemoteName = $this->getUpdateFieldName();
        $type = "";
        $LENGTH = 20;

        while ($row = oci_fetch_array($stid, OCI_ASSOC + OCI_RETURN_NULLS)) {

            try {
                $remoteID = $row[$this->getRemoteID()];

                /** @var IConnectedObject $person */
                $object = $repository->getObjectByConnectorID($this->getName(), $remoteID);
                $dateUpdated = new \DateTime();

                // On test les date de mise à jour
                if( $this->getUpdateField() !== null && $object->getDateUpdated() ) {
                    $dateLastMaj = $object->getDateUpdated()->format($dateFormat);
                    $dateRemoteMaj = \DateTime::createFromFormat($dateFormat,
                        $row[$this->getUpdateFieldName()]);
                    if ($dateLastMaj >= $dateRemoteMaj->format($dateFormat)) {
                        continue;
                    }
                    $dateUpdated = $dateRemoteMaj;
                }
                $type = "update";

            } catch( NoResultException $ex ){
                $type = "add";
                $object = $repository->newPersistantObject();
            } catch( NonUniqueResultException $ex ){
                $type = "error";
                $message = "! Erreur, Plusieurs enregistrements sont présents dans Oscar avec l'identifiant $remoteID";
            } catch( \Exception $ex ){
                $type = "error";
                die("ERREUR ! " . $ex->getMessage());
            }
            if( $type == "update" || $type == "add" ){
                $this->hydrateObjectWithRemote($object, $row);
                $message = sprintf("%s de %s.", ($type == "update" ? 'Mise à jour' : 'Création'), (string)$object);
                if( $force === true )
                    $repository->flush($object);
            }


            $syncRepport[] = [
                'type' => $type,
                'message' => $message
            ];

            /*
            if( $LENGTH <= 0 ){
                return $syncRepport;
            } else {
                $LENGTH--;
            }
            /****/

        }
/*
        if( !$force )
            $syncRepport[] = "Aperçu des opération uniquement, utilisez l'option --force pour appliquer la mise à jour.";
*/
        return $syncRepport;
    }


    protected function syncOne($object, $id){
        $stid = $this->query(sprintf($this->queryOne, $id));
        $this->hydrateObjectWithRemote($object, oci_fetch_array($stid, OCI_ASSOC + OCI_RETURN_NULLS));
        return $object;
    }

    /**
     * Retourne le nom du champ côté connecteur.
     *
     * @param $oscarFieldname
     * @return \Closure
     */
    public function getRemoteFieldname( $oscarFieldname ){
        return $this->getField($oscarFieldname)['remoteName'];
    }

    /**
     * Retourne la fonction d'affectation (setter) utilisé par oscar pour
     * affecter la donnée.
     *
     * @param $oscarFieldname
     * @return string
     */
    protected function getSetterFieldname( $oscarFieldname ){
        return $this->getField($oscarFieldname)['setter'];
    }

    /**
     * Retourne la fonction de traitement des données avant de l'injecter dans
     * Oscar.
     *
     * @param $oscarFieldname
     * @return \Closure
     */
    protected function getCleanupFieldname( $oscarFieldname ){
        return $this->getField($oscarFieldname)['cleanupFunction'];
    }

    /**
     * Retourne un Booléen indiquant si le champ est requis.
     *
     * @param $oscarFieldname
     * @return boolean
     */
    protected function isRequiredFieldname( $oscarFieldname ){
        return $this->getField($oscarFieldname)['required'];
    }

    /**
     * Cette méthode retourne la configuration du champ utilisé pour gérer
     * la date de mise à jour. Elle est utilisé pour tester si l'écriture est
     * necessaire.
     */
    protected function getUpdateField(){
        return $this->dateUpdateField;
    }

    protected function getUpdateFieldName(){
        return $this->getUpdateField()['name'];
    }

    protected function getUpdateFieldFormat(){
        return $this->getUpdateField()['format'];
    }

    final protected function configureFieldUpdate( $conf ){
        $this->dateUpdateField = $conf;
    }

    /**
     * Méthode utilisée pour transmettre les informations de connection et les
     * gabarits de requète.
     *
     * @param $parameters
     * @return mixed
     */
    final protected function configure( $parameters ){
        $this->params = $parameters;
    }

    /**
     * Se connecte à la source et retourne la ressource (PDO ou autre).
     */
    abstract function getConnexion();

    abstract function getName();

    /**
     * Retourne le paramètre si il existe ou lève une exception.
     *
     * @param $params
     * @param $key
     * @return mixed
     * @throws \Exception
     */
    protected function getParam( $key ){
        if( !key_exists($key, $this->params) ){
            throw new \Exception(sprintf("Missing parameter %s in connector configuration", $key));
        }
        return $this->params[$key];
    }
}