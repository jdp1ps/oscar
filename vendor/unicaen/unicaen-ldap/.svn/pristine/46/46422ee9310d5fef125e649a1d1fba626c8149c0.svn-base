<?php
namespace UnicaenLdap\Entity;

use UnicaenLdap\Node;
use UnicaenLdap\Exception;
use UnicaenLdap\Service\Service;

/**
 * Classe mère des entrées de l'annuaire LDAP.
 *
 * @author Laurent Lécluse <laurent.lecluse at unicaen.fr>
 */
abstract class Entity
{

    /**
     * Type d'entité
     * 
     * @var string
     */
    protected $type;

    /**
     * Service qui gère l'entité
     * 
     * @var Service
     */
    protected $service;

    /**
     * @var Node
     */
    protected $node;
    
    /**
     * Liste des classes d'objet nécessaires à la création de l'entité
     * 
     * @var string[] 
     */
    protected $objectClass = array(

    );

    /**
     * Liste des attributs contenant des dates
     *
     * @var string[]
     */
    protected $dateTimeAttributes = array(

    );



    public static function getNodeParams($type)
    {
        $params = array(
            'Generic' => array( 'uid', 'UnicaenLdap\\Entity\\Generic' ),
            'Group' => array( 'cn', 'UnicaenLdap\\Entity\\Group' ),
            'People' => array( 'uid', 'UnicaenLdap\\Entity\\People' ),
            'Structure' => array( 'supannCodeEntite', 'UnicaenLdap\\Entity\\Structure' ),
            'System' => array( 'uid', 'UnicaenLdap\\Entity\\System' ),
        );
        if (! isset($params[$type])) throw new Exception('Paramètres de "'.$type.'" non trouvés');
        return $params[$type];
    }

    /**
     * Construit une entrée.
     *
     * @param Service $service Service qui gèrera la future entité
     * @param Node|Dn|array|string $data Noeud si Node, sinon DN
     */
    public function __construct( Service $service, $data )
    {
        $this->service = $service;

        if ($data instanceof Node){
            $this->setNode( $data );
        }else{
            $this->setNode( Node::create($data, $this->objectClass) );
        }
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
     * Retourne le service qui gère l'entité
     * 
     * @return Service
     */
    public function getService()
    {
        return $this->service;
    }

    /**
     * Retourne le nœud Ldap de base
     *
     * @return Node
     */
    public function getNode()
    {
        return $this->node;
    }

    /**
     * Affecte un nœud de base
     * 
     * @param Node $node
     */
    public function setNode( Node $node )
    {
        $this->node = $node;
    }

    /**
     * Retourne le Dn de l'entité sous forme de chaîne de caractères
     *
     * @return string
     */
    public function getDn()
    {
        return $this->getNode()->getDnString();
    }

    /**
     * Retourne la clé primaire correspondant à l'entité
     *
     * @return string
     */
    public function getId()
    {
        list($key) = self::getNodeParams($this->type);
        return $this->get($key);
    }

    /**
     * Retourne la clé primaire correspondant à l'entité
     *
     * @return string
     */
    public function getKey()
    {
        return reset(self::getNodeParams($this->type));
    }

    /**
     * Retourne la liste des attributs de l'entité
     *
     * @return string[]
     */
    public function getAttributesList()
    {
        return array_keys($this->getNode()->getAttributes());
    }

    /**
     * Exporte sous forme de tableau le contenu de l'entité
     *
     * @return array
     */
    public function toArray()
    {
        $result = array();
        $attrsList = $this->getAttributesList();
        foreach( $attrsList as $attrName ){
            $result[$attrName] = $this->get($attrName);
        }
        return $result;
    }

    /**
     * Mise à jour de l'entité
     * 
     * @return self
     */
    public function update()
    {
        $this->getNode()->update();
        return $this;
    }

    /**
     * Suppression de l'entité
     *
     * @return self
     */
    public function delete()
    {
        $this->getNode()->delete();
        return $this;
    }

    /**
     * Insertion de l'entité
     * 
     * @return self
     */
    public function insert()
    {
        $this->getNode()->attachLdap($this->service->getLdap());
        return $this;
    }

    /**
     * Retourne un attribut
     *
     * @param string $attrName
     * @return mixed
     */
    public function get($attrName)
    {
        if (in_array($attrName, $this->dateTimeAttributes)){
            $value = $this->getNode()->getDateTimeAttribute($attrName);
        }else{
            $value = $this->getNode()->getAttribute($attrName);
        }
        if (empty($value)){
            $value = null;
        }elseif (1 == count($value)){
            $value = $value[0];
        }
        return $value;
    }

    /**
     * Affecte une nouvelle valeur à un attribut
     *
     * @param string $attrName
     * @param mixed $value
     * @return self
     */
    public function set($attrName, $value)
    {
        if (in_array($attrName, $this->dateTimeAttributes)){
            $this->getNode()->setDateTimeAttribute($attrName, $value, true);
        }else{
            $this->getNode()->setAttribute($attrName, $value);
        }
        return $this;
    }

    /**
     * Retourne <code>true</code> si l'attribut $param possède la valeur $value, <code>false</code> sinon.
     *
     * @param string $attrName
     * @param mixed $value
     * @return boolean
     */
    public function has($attrName, $value)
    {
        return $this->getNode()->attributeHasValue($attrName, $value);
    }

    /**
     * Ajoute une valeur à un attribut
     *
     * @param string $attrName
     * @param mixed $value
     * @return self
     */
    public function add($attrName, $value)
    {
        if (in_array($attrName, $this->dateTimeAttributes)){
            $this->getNode()->appendToDateTimeAttribute($attrName, $value);
        }else{
            $this->getNode()->appendToAttribute($attrName, $value);
        }
        return $this;
    }

    /**
     * Retire une valeur à un attribut
     *
     * @param type $attrName
     * @param type $value
     * @return self
     */
    public function remove($attrName, $value)
    {
        $this->getNode()->removeFromAttribute($attrName, $value);
        return $this;
    }

    /**
     * Méthode magique...
     *
     * @param string $attrName
     * @return mixed
     */
    public function __get($attrName)
    {
        return $this->get($attrName);
    }

    /**
     * Méthode magique...
     *
     * @param string $attrName
     * @param mixed $value
     * @return self
     */
    public function __set($attrName, $value)
    {
        return $this->set($attrName, $value);
    }

    /**
     * Methode magique ...
     *
     * @param string $method Nom de la méthode à appeler
     * @param array $arguments Tableau de paramètres
     * @return mixed
     */
    public function __call($method, array $arguments)
    {
        $methods = array( 'get', 'set', 'has', 'add', 'remove' );
        foreach( $methods as $methodName ){
            if (0 === strpos($method, $methodName)){
                $attrName = lcfirst(substr( $method, strlen($methodName) ));
                $arguments = array_merge( (array)$attrName, $arguments);
                return call_user_func_array(array($this,$methodName), $arguments );
            }
        }
    }
}