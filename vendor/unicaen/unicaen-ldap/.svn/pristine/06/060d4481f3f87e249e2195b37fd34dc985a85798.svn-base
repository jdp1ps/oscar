<?php
namespace UnicaenLdap\Service;

use UnicaenLdap\Ldap;
use UnicaenLdap\Entity\Entity;
use UnicaenLdap\Exception;
use UnicaenLdap\Collection;
use UnicaenLdap\Filter\Filter;
use Zend\ServiceManager\ServiceManager;
use Zend\ServiceManager\ServiceManagerAwareInterface;
use Zend\Ldap\Filter\AbstractFilter;
use Zend\Ldap\Dn;
use Zend\Stdlib\ErrorHandler;

/**
 * Classe mère des services d'accès à l'annuaire LDAP.
 *
 * @author Laurent Lécluse <laurent.lecluse at unicaen.fr>
 */
abstract class Service implements ServiceManagerAwareInterface 
{

    /**
     * Limite de recherche par défaut
     */
    const DEFAULT_LIMIT = 10;

    /**
     * Offset par défaut
     */
    const DEFAULT_OFFSET = 0;

    /**
     * @var ServiceManager
     */
    protected $serviceManager;

    /**
     * @var Ldap
     */
    protected $ldap;

    /**
     *
     * @var string
     */
    protected $type;

    /**
     * Organizational Units
     * 
     * @var string[]
     */
    protected $ou = array();

    /**
     *
     * @var integer
     */
    protected $count;

    /**
     * Get service manager
     *
     * @return ServiceManager
     */
    public function getServiceManager()
    {
        return $this->serviceManager;
    }

    /**
     * Set service manager
     *
     * @param ServiceManager $serviceManager
     * @return self
     */
    public function setServiceManager(ServiceManager $serviceManager)
    {
        $this->serviceManager = $serviceManager;
        return $this;
    }

    /**
     * Retourne le type du service
     * 
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Retourne l'objet d'accès à l'annuaire LDAP.
     *
     * @return Ldap
     */
    public function getLdap()
    {
        if (empty($this->ldap)){
            $this->ldap = $this->getServiceManager()->get('ldap');
        }
        return $this->ldap;
    }

    /**
     * Spécifie l'objet d'accès à l'annuaire LDAP.
     *
     * @param Ldap $ldap
     * @return self
     */
    public function setLdap(Ldap $ldap = null)
    {
        $this->ldap = $ldap;
        return $this;
    }

    /**
     * Retourne la liste des organizational units
     *
     * @return string[]
     */
    public function getOu()
    {
        return $this->ou;
    }

    /**
     * Redéfinie la liste des organizational units
     *
     * @param string[] $ou
     * @return self
     */
    public function setOu(array $ou)
    {
        $this->ou = $ou;
        return $this;
    }

    /**
     * Retourne la liste de toutes les entités correspondantes
     * 
     * @param string $orderBy Propriété de référence pour le tri
     * @return Collection
     */
    public function getList( $orderBy=null, $limit=self::DEFAULT_LIMIT, $offset=self::DEFAULT_OFFSET )
    {
        list($key) = Entity::getNodeParams($this->type);
        return $this->search( "($key=*)", $orderBy, $limit, $offset );
    }

    /**
     * Recherche une liste d'entités correspondantes
     *
     * @param string|AbstractFilter $filter     Valeur de recherche
     * @param string                $orderBy    Attribut de référence pour le tri
     * @param integer               $limit      Nombre maximum d'occurences renvoyées (-1 = infini)
     * @param integer               $offset     Renvoi les entités à partir de $offset uniquement
     * @return Collection
     */
    public function search( $filter, $orderBy=null, $limit=self::DEFAULT_LIMIT, $offset=self::DEFAULT_OFFSET )
    {
        list($key) = Entity::getNodeParams($this->type);
        if ($limit < 0) $limit = 3999999999; // Limite maximum à 4 milliard...
        if ($offset < 0) $offset = 0; // Moins de zéro = impossible

        list( $resource, $search ) = $this->__searchBegin($filter, $this->ou, array($key,$orderBy));

        ErrorHandler::start(E_WARNING);
        $this->count = ldap_count_entries($resource, $search);
        ErrorHandler::stop();

        if ($this->count > 0){

            if ($orderBy !== null && is_string($orderBy)) {
                ErrorHandler::start(E_WARNING);
                $isSorted = ldap_sort($resource, $search, $orderBy);
                ErrorHandler::stop();
                if ($isSorted === false) {
                    throw new Exception($this, 'sorting: ' . $orderBy);
                }
            }

            $result = array();
            $i = 0;
            ErrorHandler::start(E_WARNING);
            for ($entry=ldap_first_entry($resource,$search); $entry; $entry=ldap_next_entry($resource,$entry)) {
                list($value) = ldap_get_values_len($resource,$entry,$key);
                if (null !== $value){
                    $result[] = $value;
                    $i++;
                }
                if ($i > $limit + $offset - 1) break; // Pas besoin d'aller plus loin...
            }
            ErrorHandler::stop();
        }else{
            $result = array();
        }
        $this->__searchEnd( $search );
        $this->count = count($result);
        return new Collection( $this, array_slice( $result, $offset, $limit ) );
    }

    /**
     * Retourne le nombre d'entités correspondant au filtre transmis
     *
     * @param string|AbstractFilter $filter
     * @return integer
     * @throws Exception
     */
    public function searchCount( $filter )
    {
        list( $resource, $search ) = $this->__searchBegin($filter, $this->ou);

        if ($search === false) {
            throw new Exception('searching: ' . $filter);
        }
        $this->count = ldap_count_entries($resource, $search);
        $this->__searchEnd($search);
        return $this->count;
    }

    /**
     * Recherche une liste d'entités correspondantes
     *
     * @param string|AbstractFilter $filter Valeur de recherche
     * @param array $attributes Liste des attributs à retourner
     * @param string $orderBy Champ de référence pour le tri
     * @param integer $limit Nombre maximum d'occurences renvoyées (-1 = infini)
     * @param integer $offset Renvoi les entités à partir de $offset uniquement
     * @return Collection
     */
    public function searchAttributes( $filter, array $attributes, $orderBy=null, $limit=self::DEFAULT_LIMIT, $offset=self::DEFAULT_OFFSET )
    {
        list($key) = Entity::getNodeParams($this->type);
        if ($limit < 0) $limit = 3999999999; // Limite maximum à 4 milliard...
        if ($offset < 0) $offset = 0; // Moins de zéro = impossible

        $searchAttributes = $attributes;
        if (! in_array($key, $searchAttributes)) $searchAttributes[] = $key;
        if (null !== $orderBy && ! in_array($orderBy, $searchAttributes)) $searchAttributes[] = $orderBy;
        list( $resource, $search ) = $this->__searchBegin($filter, $this->ou, $searchAttributes);

        ErrorHandler::start(E_WARNING);
        $this->count = ldap_count_entries($resource, $search);
        ErrorHandler::stop();
        if ($this->count > 0){
            if ($orderBy !== null && is_string($orderBy)) {
                ErrorHandler::start(E_WARNING);
                $isSorted = ldap_sort($resource, $search, $orderBy);
                ErrorHandler::stop();
                if ($isSorted === false) {
                    throw new Exception($this, 'sorting: ' . $orderBy);
                }
            }

            $result = array();
            $i = 0;
            ErrorHandler::start(E_WARNING);
            for ($entry=ldap_first_entry($resource,$search); $entry; $entry=ldap_next_entry($resource,$entry)) {
                list($id) = ldap_get_values_len($resource,$entry,$key);
                $data = array();
                foreach( $attributes as $attribute ){
                    $attrValue = ldap_get_values_len($resource,$entry,$attribute);
                    if (1 == $attrValue['count']) $attrValue = $attrValue[0];
                    $data[$attribute] = $attrValue;
                }
                $result[$id] = $data;
                $i++;
                if ($i > $limit + $offset - 1) break; // Pas besoin d'aller plus loin...
            }
            ErrorHandler::stop();
        }else{
            $result = array();
        }
        $this->__searchEnd( $search );
        return array_slice( $result, $offset, $limit );
    }

    /**
     *
     * @param string|AbstractFilter $filter     Filtre Ldap à appliquer
     * @param string[]              $ou         Liste des organisations dans lesquelles rechercher
     * @param string[]              $attributes Liste des attributs à retourner
     */
    private function __searchBegin( $filter, array $ou=null, array $attributes=null )
    {
        /* Initialisation $basedn et $filter */
        if ($filter instanceof AbstractFilter) {
            $filter = $filter->toString();
        }

        if (is_string($ou)) $ou = array($ou);
        elseif (null === $ou) $ou = $this->ou;

        if (1 == count($ou)){
            $basedn = "ou=".$ou[0].",".$this->getLdap()->getBaseDn();
        }else{
            $basedn = $this->getLdap()->getBaseDn();
            $ouFilter = '(&(|';
            foreach( $ou as $ouItem ){
                $ouFilter .= "(ou:dn:=$ouItem)";
            }
            $filter = $ouFilter.")$filter)";
        }
        $resource = $this->getLdap()->getResource();
        if (null === $attributes) $search = ldap_search($resource, $basedn, $filter);
        else $search = ldap_search($resource, $basedn, $filter, $attributes);
        if ($search === false) {
            throw new Exception('searching: ' . $filter);
        }
        return array( $resource, $search );
    }

    /**
     * Libère la mémoire du résultat de recherche
     *
     * @param resource $search
     */
    private function __searchEnd( $search )
    {
        ldap_free_result($search);
    }

    /**
     * Retourne un tableau d'attributs
     *
     * @param resource $resource
     * @param resource $entry
     * @return null|array
     */
    private function __getEntryAttributes( $resource, $entry )
    {
        if (!is_resource($resource)) {
            return null;
        }

        $berIdentifier = null;

        $name = ldap_first_attribute(
            $resource, $entry, $berIdentifier
        );

        $attributes         = array();

        while ($name) {
            ErrorHandler::start(E_WARNING);
            $data = ldap_get_values_len($resource, $entry, $name);
            ErrorHandler::stop();

            if (!$data) {
                $data = array();
            }

            if (isset($data['count'])) {
                unset($data['count']);
            }

            $attrName = strtolower($name);
            $attributes[$attrName] = $data;

            $name = ldap_next_attribute(
                $resource, $entry,
                $berIdentifier
            );
        }
        ksort($attributes, SORT_LOCALE_STRING);
        return $attributes;
    }

    /**
     * Retourne une entité du type correspondant au service courant
     *
     * 
     * @param string $id
     * @return Entity
     */
    public function get( $id )
    {
        list($key) = Entity::getNodeParams($this->type);
        return $this->getBy( $id, $key );
    }

    /**
     * Retourne une entité en fonction de la valeur d'un champ donné.
     * Attention : une seule entrée doit correspondre dans l'annuaire, faute de quoi une exception sera levée.
     *
     * @param mixed $value
     * @param string $by
     * @return Entity
     * @throws Exception
     */
    public function getBy( $value, $by )
    {
        if ('dn' == $by){
            $value = Dn::factory($value)->get(0);
            $by = key($value);
            $value = current($value);
        }

        list($tmp, $classname) = Entity::getNodeParams($this->type);
        list( $resource, $search ) = $this->__searchBegin( Filter::equals($by, $value), $this->ou, array('*','+') );
        $count = ldap_count_entries($resource, $search);
        switch( $count ){
            case 0:
                $this->__searchEnd($search);
                throw new Exception( 'Entité de type "'.$this->type.'" ayant "'.$by.'"="'.$value.'" non trouvée');
            case 1:
                $entry = ldap_first_entry($resource,$search);
                $attributes = $this->__getEntryAttributes($resource, $entry);
                $dn = ldap_get_dn( $resource, $entry );
                $attributes['dn'] = $dn;
                $this->__searchEnd($search);
                return new $classname( $this, \UnicaenLdap\Node::fromEntry($dn, $this->getLdap(), $attributes) );
            default:
                $this->__searchEnd($search);
                throw new Exception( 'Plusieurs entités de type "'.$this->type.'" ayant "'.$by.'"="'.$value.'" ont été trouvées');
        }
    }

    /**
     * Retourne un tableau d'entités de format array[ID] = Entite
     * 
     * @param string[] $ids   Tableau d'identifiants
     * @param string $orderBy Nom d'attribut à trier
     * @return Entity[]
     */
    public function getAll( $ids, $orderBy=null )
    {
        list($key) = Entity::getNodeParams($this->type);
        return $this->getAllBy($ids, $key, $orderBy);
    }

    /**
     * Retourne un tableau d'entités de format array[ID] = Entite
     *
     * @param array     $values     Tableau des valeurs à rechercher
     * @param string    $by         Nom de champ à rechercher
     * @param string    $orderBy    Nom d'attribut à trier
     * @return Entity[]
     */
    public function getAllBy( $values, $by, $orderBy=null )
    {
        if (! is_array($values)) $values = array($values);
        
        $data = array();
        $sortedData = array();
        foreach( $values as $val ){
            $valRes = $this->getBy( $val, $by );
            if (! empty($orderBy)){
                $sortedData[$valRes->getId()] = $valRes->get($orderBy);
            }
            $data[$valRes->getId()] = $valRes;
        }
        if (! empty($orderBy)){
            asort($sortedData);
            foreach( $sortedData as $id => $val ){
                $sortedData[$id] = $data[$id];
            }
            return $sortedData;
        }else{
            return $data;
        }
    }

    /**
     * Détermine si une entité existe ou non
     *
     * @param string $id Identifiant de l'entité
     * @return boolean
     */
    public function exists( $id )
    {
        list($key) = Entity::getNodeParams($this->type);
        return $this->existsBy($id, $key);
    }

    /**
     * Détermine si une entité existe ou non en fonction d'un champ déterminé
     * 
     * @param mixed $value Valeur de champ à rechercher
     * @param string $by Nom du champ à tester
     * @return boolean
     */
    public function existsBy( $value, $by )
    {
        if ('dn' == $by){
            $value = Dn::factory($value)->get(0);
            $by = key($value);
            $value = current($value);
        }

        return 1 == $this->searchCount( Filter::equals($by, $value) );
    }

    /**
     * Crée une nouvelle entité à partir de son futur identifiant et (si nécessaire) de son organizational unit (OU)
     * Attention, l'entité créée ne sera pas ajoutée au Ldap.
     * Elle doit d'abord être peuplée.
     * Une fois cela fait, il conviendra d'appeler la méthode attach() de la nouvelle entité.
     *
     * @param string $id Identifiant
     * @param string $ou Organizational Unit
     * @return Entity
     * @throws Exception
     */
    public function create( $id, $ou=null )
    {
        list($key, $classname) = Entity::getNodeParams($this->type);
        if (empty($ou)){
            if (count($this->ou) > 1){
                throw new Exception('$ou non renseigné alors que le service couvre plusieurs Organizational Units (OU).');
            }
            $ou = $this->ou[0];
        }
        $dn = $key.'='.$id.',ou='.$ou.','.$this->getLdap()->getBaseDn();
        $classname = 'UnicaenLdap\\Entity\\'.$this->type;
        return new $classname( $this, $dn);
    }

    /**
     * Retourne le nombre d'occurences retournées par la dernière recherche
     *
     * @return integer
     */
    public function getLastCount()
    {
        return $this->count;
    }
}