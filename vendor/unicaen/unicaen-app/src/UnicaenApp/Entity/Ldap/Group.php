<?php
namespace UnicaenApp\Entity\Ldap;

use InvalidArgumentException;
use UnicaenApp\Exception\MandatoryValueException;

/**
 * Classe mère des groupes de l'annuaire LDAP.
 *
 * @author Bertrand GAUTHIER <bertrand.gauthier@unicaen.fr>
 */
class Group extends AbstractEntity
{
    protected $dn;
    protected $cn;
    protected $description;
    protected $member;
    protected $supanngroupedatefin;

    /**
     * Crée des instances de cette classes à partir des données LDAP spécifiés.
     * 
     * @param array $entries Données de plusieurs entrées de l'annuaire LDAP.
     * @return array Instances de cette classe
     */
    static public function getInstances($entries)
    {
        if (!is_array($entries)) {
            throw new InvalidArgumentException("Les données fournies sont invalides.");
        }
        if (!$entries) {
            throw new InvalidArgumentException("Les données fournies sont vides.");
        }
        $instances = array();
        foreach ($entries as $key => $entry) {
            if (!is_int($key)) {
                throw new InvalidArgumentException("Les données fournies doivent avoir des clés numériques.");
            }
            if (!isset($entry['dn'])) {
                throw new InvalidArgumentException("Chacune des données fournies doit posséder une clé 'dn' valide.");
            }
            $instances[$entry['dn']] = new self($entry);
        }
        return $instances;
    }

    /**
     * Spécifie les valeurs des attributs de ce groupe LDAP.
     *
     * @param array $data Données brutes
     * @return self
     */
    public function setData(array $data = array())
    {
        $this->data = $data;
        
        try {
            $this->dn                  = $this->processDataValue('dn', true);
            $this->cn                  = $this->processDataValue('cn');
            $this->description         = $this->processDataValue('description');
            $this->member              = $this->processDataValue('member');
            $this->supanngroupedatefin = $this->processDataValue('supanngroupedatefin');
        }
        catch (MandatoryValueException $mve) {
            throw new InvalidArgumentException("Les données fournies sont invalides.", null, $mve);
        }
        
        return $this;
    }

    public function getDn()
    {
        return $this->dn;
    }
    
    public function getDescription()
    {
        return $this->description;
    }

    public function getCn()
    {
        return $this->cn;
    }

    public function getMember()
    {
        return (array)$this->member;
    }

    /**
     * 
     * @return \DateTime
     */
    public function getSupannGroupeDateFin()
    {
        if (is_string($this->supanngroupedatefin)) {
            $date = \Zend\Ldap\Converter\Converter::fromLdapDateTime($this->supanngroupedatefin);
            $this->supanngroupedatefin = $date;
        }
        return $this->supanngroupedatefin;
    }
        
    /**
     * Représentation littérale de cet objet.
     * 
     * @return string 
     */
    public function __toString()
    {
        return sprintf("%s [%s]", $this->getDescription(), $this->getCn());
    }
}