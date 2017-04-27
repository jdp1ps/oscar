<?php
namespace UnicaenApp\Service\Ldap;

use UnicaenApp\Mapper\Ldap\AbstractMapper;
use Zend\Ldap\Ldap;

/**
 * Classe mère des services d'accès à l'annuaire LDAP.
 *
 * @author Bertrand GAUTHIER <bertrand.gauthier at unicaen.fr>
 */
abstract class AbstractService
{
    /**
     * @var AbstractMapper
     */
    protected $mapper;
    
    /**
     * @var Ldap
     */
    protected $ldap;

    /**
     * Constructeur.
     *
     * @param Ldap $ldap Objet d'accès à l'annuaire LDAP
     */
    public function __construct(Ldap $ldap = null)
    {
        if (null !== $ldap) {
            $this->setLdap($ldap);
        }
    }

    /**
     * Retourne l'objet d'accès à l'annuaire LDAP.
     *
     * @return Ldap
     */
    public function getLdap()
    {
        return $this->getMapper()->getLdap();
    }
    
    /**
     * Spécifie l'objet d'accès à l'annuaire LDAP.
     *
     * @param Ldap $ldap
     * @return AbstractService
     */
    public function setLdap(Ldap $ldap)
    {
        $this->getMapper()->setLdap($ldap);
        return $this;
    }

    /**
     * Spécifie le mapper LDAP à utiliser.
     * 
     * @param AbstractMapper $mapper
     * @return AbstractService
     */
    public function setMapper(AbstractMapper $mapper)
    {
        $this->mapper = $mapper;
        return $this;
    }

    /**
     * Retourne le mapper LDAP à utiliser.
     * 
     * @return AbstractMapper
     */
    abstract public function getMapper();
    
}
