<?php
namespace UnicaenLdap\Entity;

use UnicaenLdap\Filter\Filter;

/**
 * Classe mère des structures de l'annuaire LDAP.
 *
 * @author Laurent Lécluse <laurent.lecluse at unicaen.fr>
 */
class Structure extends Entity
{

    protected $type = 'Structure';
    
    /**
     * Liste des classes d'objet nécessaires à la création d'une structure
     * 
     * @var string[] 
     */
    protected $objectClass = array(
	'top',
	'organizationalUnit',
	'supannEntite',
	'ucbnEntite'
    );

    /**
     * Liste des attributs contenant des dates
     *
     * @var string[]
     */
    protected $dateTimeAttributes = array(
    );

    /**
     * Retourne la structure parente, si elle existe
     *
     * @return Structure[]
     */
    public function getParents()
    {
        if (null !== $parentIds = $this->get('supannCodeEntiteParent')){
            return $this->service->getAll($parentIds);
        }
        return null;
    }

    /**
     * Retourne la liste des structures filles
     *
     * @param string|Filter $filter  Filtre éventuel
     * @param string $orderBy        Champ de tri
     * @return Structure[]
     */
    public function getChildren( $filter=null, $orderBy=null )
    {
        $childrenFilter = Filter::equals('supannCodeEntiteParent', $this->getId());

        if (empty($filter)){
            $filter = $childrenFilter;
        }else{
            if (is_string($filter)) $filter = Filter::string ($filter);
            $filter = Filter::andFilter($childrenFilter, $filter);
        }
        return $this->service->search($filter, $orderBy, -1, 0);
    }

    /**
     * Retourne le code Harpège
     *
     * @return string
     */
    public function getCodeHarpege()
    {
        $code = $this->get('supannCodeEntite');
        if (0 === strpos($code, 'HS_')){
            return substr( $code, 3 );
        }else{
            return null; // Ne retourne rien si le code ne correspond pas à la nomenclature Harpège
        }
    }
}