<?php
/**
 * Created by PhpStorm.
 * User: jacksay
 * Date: 12/05/15
 * Time: 12:38
 */

namespace Oscar\Entity;


use UnicaenApp\Entity\Ldap\People;

/**
 * Class PublicPeople
 *
 * Class Bridge, utilisé pour convertir les données de la classe People en
 * donnée 'publique' (service JSON).
 *
 * @package Oscar\Entity
 */
class PublicPeople
{

    private $people;

    /**
     * @var array
     *
     * Données publiques, version dépouillée des données de l'objet
     */
    private $json;

    function __construct(People $people)
    {
        $this->people = $people;
    }

    public function toJson()
    {

        if (null === $this->json) {
            $this->json = array(
                'id' => $this->people->getUid(),
                "nom" => $this->people->getNomUsuel(),
                "prenom" => $this->people->getGivenName(),
                'displayname' => $this->people->getNomComplet(true),
                'text' => $this->people->getNomComplet(true),
                'mailMd5' => md5($this->people->getMail()),
                'mail' => $this->people->getMail(),
                'affectation' => $this->people->getEduPersonOrgUnitDN(),
                'ucbnFonctionStructurelle' => $this->people->getUcbnFonctionStructurelle(),
                'ucbnSousStructure' => $this->people->getUcbnSousStructure(),
                'ucbnStatus' => $this->people->getUcbnStatus(),
                'ucbnSiteLocalisation' => $this->people->getUcbnSiteLocalisation(),
                'ucbnStructureRecherche' => $this->people->getUcbnStructureRecherche(),
            );
        }
        return $this->json;
    }
}