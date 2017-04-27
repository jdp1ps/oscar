<?php
namespace UnicaenApp\Entity\Ldap;

use UnicaenApp\Exception\LogicException;
use UnicaenApp\Exception\MandatoryValueException;

/**
 * Classe mère des entrées de l'annuaire LDAP.
 *
 * @author Bertrand GAUTHIER <bertrand.gauthier@unicaen.fr>
 */
abstract class AbstractEntity
{
    /**
     * @var array Valeurs des attributs LDAP brutes.
     */
    protected $data = array();
    
    /**
     * Construit une entrée.
     *
     * @param array $data Valeurs des attributs brutes
     */
    public function __construct($data = null)
    {
        if ($data) {
            $this->setData((array) $data);
        }
    }
    
    /**
     * Retourne les valeurs des attributs LDAP brutes.
     * 
     * @param string $key Nom éventuel du seul attribut voulu
     * @return array
     */
    public function getData($key = null)
    {
        if ($key) {
            if (!array_key_exists($key, $this->data)) {
                throw new LogicException("Attribut introuvable: '$key'.");
            }
            return $this->data[$key];
        }
        return $this->data;
    }

    /**
     * Spécifie les valeurs des attributs de cet individu LDP.
     *
     * @param array $data Valeurs des attributs brutes
     * @return self
     */
    abstract public function setData(array $data = array());
    
    /**
     * Simplifie le format d'une valeur d'un attribut.
     * 
     * @param string $key Nom de l'attribut
     * @param boolean $mandatory Indique si l'attribut doit exister et être non vide
     * @return mixed
     */
    protected function processDataValue($key, $mandatory = false)
    {
        $value = isset($this->data[$key]) ? $this->data[$key] : null;
        
        if ($mandatory && !$value) {
            throw new MandatoryValueException("La clé '$key' est introuvable.");
        }
        if (!$value) {
            return null;
        }
        if (is_array($value)) {
            $value = (count($value) > 1) ? $value : $value[0];
        }
        
        return $value;
    }
}