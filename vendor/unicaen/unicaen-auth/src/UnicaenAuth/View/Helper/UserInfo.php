<?php
namespace UnicaenAuth\View\Helper;

use UnicaenApp\Entity\Ldap\People;
use UnicaenApp\Mapper\Ldap\Structure as MapperStructure;
use Zend\View\Helper\HtmlList;

/**
 * Aide de vue affichant des info sur l'utilisateur connecté :
 * - affectations administratives et recherche
 * - responsabilités administratives
 *
 * @author Bertrand GAUTHIER <bertrand.gauthier@unicaen.fr>
 */
class UserInfo extends UserAbstract
{
    /**
     * @var MapperStructure 
     */
    protected $mapperStructure;

    /**
     * @var bool
     */
    protected $affectationPrincipale = false;
    
    /**
     * Point d'entrée.
     *
     * @param boolean $affectationPrincipale Indique s'il ne faut prendre en compte que l'affectation principale
     * @return self 
     */
    public function __invoke($affectationPrincipale = false)
    {
        $this->setAffectationPrincipale($affectationPrincipale);
        return $this;
    }
    
    /**
     * Retourne le code HTML généré par cette aide de vue.
     * 
     * @return string 
     */
    public function __toString()
    {
        if (!($authIdentity = $this->getIdentity())) {
            return '';
        }
        
        $libAffAdmin = _("Affectations administratives");
        $libAffRech  = _("Affectations recherche");
        $libRespons  = _("Responsabilités");
        
        if ($this->getTranslator()) {
            $libAffAdmin = $this->getTranslator()->translate($libAffAdmin, $this->getTranslatorTextDomain());
            $libAffRech  = $this->getTranslator()->translate($libAffRech, $this->getTranslatorTextDomain());
            $libRespons  = $this->getTranslator()->translate($libRespons, $this->getTranslatorTextDomain());
        }
        
        $out = '';
        $templateAff         = "<strong>" . $libAffAdmin . " :</strong> %s";
        $templateAffRech     = "<strong>" . $libAffRech  . " :</strong> %s";
        $templateFonctStruct = "<strong>" . $libRespons  . " :</strong> %s";

        $helperHtmlList = new HtmlList();
        
        if ($authIdentity instanceof People) {
            
            // affectations admin
            $affectations = $authIdentity->getAffectationsAdmin($this->getMapperStructure(), $this->getAffectationPrincipale());
            if (!$affectations) {
                $aucuneAffTrouvee = _("Aucune affectation trouvée.");
                if ($this->getTranslator()) {
                    $aucuneAffTrouvee = $this->getTranslator()->translate($aucuneAffTrouvee, $this->getTranslatorTextDomain());
                }
                $affectations[] = $aucuneAffTrouvee;
            }
            ksort($affectations);
            $affectations = $helperHtmlList($affectations, $ordered = false, $attribs = false, $escape = false);
            $out .= sprintf($templateAff, $affectations);

            // affectations recherche
            $affectations = $authIdentity->getAffectationsRecherche($this->getMapperStructure());
            if ($affectations) {
                ksort($affectations);
                $affectations = $helperHtmlList($affectations, $ordered = false, $attribs = false, $escape = false);
                $out .= sprintf($templateAffRech, $affectations);
            }

            // fonctions structurelles
            $fonctions = $authIdentity->getFonctionsStructurelles($this->getMapperStructure());
            if ($fonctions) {
                ksort($fonctions);
                $fonctions = $helperHtmlList($fonctions, $ordered = false, $attribs = false, $escape = false);
                $out .= sprintf($templateFonctStruct, $fonctions);
            }
        }
        else {
            $aucuneAffDispo = _("Aucune information disponible.");
            if ($this->getTranslator()) {
                $aucuneAffDispo = $this->getTranslator()->translate($aucuneAffDispo, $this->getTranslatorTextDomain());
            }
            $out .= $aucuneAffDispo;
        }
        
        return $out;
    }

    /**
     * Indique si l'affichage de l'affectation princiaple seulement est activé ou non.
     * @return bool
     */
    public function getAffectationPrincipale()
    {
        return $this->affectationPrincipale;
    }

    /**
     * Active ou non l'affichage de l'affectation principale seulement.
     * 
     * @param bool $affectationPrincipale
     * @return self
     */
    public function setAffectationPrincipale($affectationPrincipale = true)
    {
        $this->affectationPrincipale = $affectationPrincipale;
        return $this;
    }

    /**
     * Spécifie le mapper d'accès aux structures de l'annuaire LDAP.
     * 
     * @param MapperStructure $mapperStructure
     * @return self
     */
    public function setMapperStructure(MapperStructure $mapperStructure)
    {
        $this->mapperStructure = $mapperStructure;
        return $this;
    }

    /**
     * Retourne le mapper d'accès aux structures de l'annuaire LDAP.
     * 
     * @return MapperStructure
     */
    public function getMapperStructure()
    {
        return $this->mapperStructure;
    }
}