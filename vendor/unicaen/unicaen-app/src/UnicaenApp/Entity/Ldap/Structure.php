<?php
namespace UnicaenApp\Entity\Ldap;

use InvalidArgumentException;
use UnicaenApp\Exception\MandatoryValueException;

/**
 * Classe mère des structures de l'annuaire LDAP.
 *
 * @author Bertrand GAUTHIER <bertrand.gauthier@unicaen.fr>
 */
class Structure extends AbstractEntity
{
    const SUPANN_CODE_ENTITE_PREFIX = 'HS_';
    const C_STRUCTURE_FILTER        = '(supannCodeEntite=HS_%s)';
    
    protected $description;
    protected $dn;
    protected $facsimiletelephonenumber;
    protected $ou;
    protected $postaladdress;
    protected $supanncodeentite;
    protected $supanncodeentiteparent;
    protected $supanntypeentite;
    protected $telephonenumber;
    
    protected $c_structure;
    protected $c_structure_pere;
    protected $libelle_annuaire;
    protected $lc_structure;
    protected $ll_structure;

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
     * Spécifie les valeurs des attributs de cette structure LDAP.
     *
     * @param array $data Données brutes
     * @return self
     */
    public function setData(array $data = array())
    {
        $this->data = $data;
        
        try {
            $this->description              = $this->processDataValue('description');
            $this->dn                       = $this->processDataValue('dn');
            $this->facsimiletelephonenumber = $this->processDataValue('facsimiletelephonenumber');
            $this->ou                       = $this->processDataValue('ou');
            $this->postaladdress            = $this->processDataValue('postaladdress');
            $this->supanncodeentite         = $this->processDataValue('supanncodeentite', true);
            $this->supanncodeentiteparent   = $this->processDataValue('supanncodeentiteparent');
            $this->supanntypeentite         = $this->processDataValue('supanntypeentite');
            $this->telephonenumber          = $this->processDataValue('telephonenumber');
        
            // ajouts d'attributs pour coller à la table d'Harpege
            $this->c_structure      = self::extractCodeStructureHarpege($this->supanncodeentite);
            $this->c_structure_pere = $this->supanncodeentiteparent ? self::extractCodeStructureHarpege($this->supanncodeentiteparent) : null;
            $this->lc_structure     = $this->ou;
            $this->ll_structure     = $this->ou;
            $this->libelle_annuaire = $this->description;
        }
        catch (MandatoryValueException $mve) {
            throw new InvalidArgumentException("Les données fournies sont invalides.", null, $mve);
        }
        
        return $this;
    }
    
    public function getDescription()
    {
        return $this->description;
    }

    public function getDn()
    {
        return $this->dn;
    }

    public function getFacSimileTelephoneNumber()
    {
        return $this->facsimiletelephonenumber;
    }

    public function getOu()
    {
        return $this->ou;
    }

    public function getPostaladdress()
    {
        return $this->postaladdress;
    }

    public function getSupannCodeEntite()
    {
        return $this->supanncodeentite;
    }

    public function getSupannCodeEntiteParent()
    {
        return $this->supanncodeentiteparent;
    }

    public function getSupannTypeEntite()
    {
        return $this->supanntypeentite;
    }

    public function getTelephoneNumber()
    {
        return $this->telephonenumber;
    }

    public function getCStructure()
    {
        return $this->c_structure;
    }

    public function getCStructurePere()
    {
        return $this->c_structure_pere;
    }

    public function getLibelleAnnuaire()
    {
        return $this->libelle_annuaire;
    }

    public function getLcStructure()
    {
        return $this->lc_structure;
    }

    public function getLlStructure()
    {
        return $this->ll_structure;
    }
        
    /**
     * Représentation littérale de cet objet.
     * 
     * @return string 
     */
    public function __toString()
    {
        return $this->description;
    }

    /**
     * Extrait le code structure Harpege du code au format préfixé Supann.
     *
     * @param string $codeSupann 'HS_C68' par exemple
     * @return string 'C68' par exemple
     * @see Structure::SUPANN_CODE_ENTITE_PREFIX
     */
    public static function extractCodeStructureHarpege($codeSupann)
    {
        if (!is_string($codeSupann)) {
            throw new \InvalidArgumentException("Le code structure fourni est invalide.");
        }
        if (!$codeSupann) {
            throw new \InvalidArgumentException("Le code structure fourni est vide.");
        }
        $codeStructure = substr($codeSupann, strlen(self::SUPANN_CODE_ENTITE_PREFIX));
        return $codeStructure ?: null;
    }
    
    /**
     * Crée un filtre LDAP de recherche de structure à partir du(des) code(s) Harpege
     * spécifié(s).
     *
     * @param array|string $codesStructures
     * @return string
     */
    public static function createFilterForStructure($codesStructures = null)
    {
        if (!$codesStructures) {
            return sprintf(self::C_STRUCTURE_FILTER, '*');
        }
        if (is_array($codesStructures) && count($codesStructures) === 1) {
            $codesStructures = current($codesStructures);
        }
        if (is_array($codesStructures)) {
            $filter = '';
            foreach ($codesStructures as $code) {
                $filter .= sprintf(self::C_STRUCTURE_FILTER, $code);
            }
            $filter = '(|' . $filter . ')';
        }
        else {
            $filter = sprintf(self::C_STRUCTURE_FILTER, $codesStructures);
        }
        return $filter;
    }
}
