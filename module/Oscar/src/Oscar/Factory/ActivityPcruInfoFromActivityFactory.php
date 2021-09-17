<?php


namespace Oscar\Factory;


use Doctrine\ORM\EntityManager;
use Oscar\Entity\Activity;
use Oscar\Entity\ActivityOrganization;
use Oscar\Entity\ActivityPcruInfos;
use Oscar\Entity\ActivityPerson;
use Oscar\Entity\ContractDocument;
use Oscar\Entity\PcruTypeContract;
use Oscar\Entity\PcruTypeContractRepository;
use Oscar\Exception\OscarException;
use Oscar\Service\OscarConfigurationService;

class ActivityPcruInfoFromActivityFactory
{
    /** @var OscarConfigurationService */
    private $oscarConfigurationService;

    /** @var EntityManager */
    private $entityManager;

    /**
     * ActivityPcruInfoFromActivityFactory constructor.
     * @param OscarConfigurationService $oscarConfigurationService
     * @param EntityManager $entityManager
     */
    public function __construct(OscarConfigurationService $oscarConfigurationService, EntityManager $entityManager)
    {
        $this->oscarConfigurationService = $oscarConfigurationService;
        $this->entityManager = $entityManager;
    }


    public function createNew(Activity $activity): ActivityPcruInfos
    {
        // Données PCRU
        $activityPcruInfos = new ActivityPcruInfos();

        // Erreurs métiers
        $errors = [];

        // --- Recherche automatique de l'Unité (Laboratoire)

        // Intitulé du rôle "Laboratoire" pour PCRU
        $roleStructureToFind = $this->oscarConfigurationService->getOptionalConfiguration('pcru_unite_role', 'Laboratoire');
        $rolePartnerToFind = $this->oscarConfigurationService->getOptionalConfiguration('pcru_partner_role', 'Financeur');

        // Donnèes trouvées
        $codeUniteLabintel = "";
        $sigleUnit = "";

        // Récupération des laboratoires
        $structures = $activity->getOrganizationsWithRole($roleStructureToFind);
        if( count($structures) == 0 ){
            $activityPcruInfos->addError("Aucune structure $roleStructureToFind pour cette activité.");
        }

        $organizationsParsed = [];
        /** @var ActivityOrganization $unite */
        foreach ($activity->getOrganizationsWithRole($roleStructureToFind) as $unite) {
            $organizationsParsed[] = (string)$unite->getOrganization();
            if( $unite->getOrganization()->getLabintel() ){
                $codeUniteLabintel = $unite->getOrganization()->getLabintel();
                $sigleUnit = $unite->getOrganization()->getShortName();
            }
        }

        $partners = [];
        /** @var ActivityOrganization $partner */
        foreach ($activity->getOrganizationsWithRole($rolePartnerToFind) as $partner) {
            if( $partner->getOrganization()->getCodePcru() ){
                $partners[] = $partner->getOrganization()->getCodePcru();
            }
        }
        if( $codeUniteLabintel == "" ){
            $activityPcruInfos->addError("Le $roleStructureToFind (".implode(', ', $organizationsParsed).") n'a pas de code LABINTEL");
        }

        // Recherche automatique du responsable scientifique
        $roleRSToFind = $this->oscarConfigurationService
            ->getOptionalConfiguration('pcru_respscien_role', 'Responsable scientifique');
        $responsable = "";

        /** @var ActivityPerson $personActivity */
        foreach ($activity->getPersonsWithRole($roleRSToFind) as $personActivity) {
            $responsable = $personActivity->getPerson()->getFirstname() . " " . strtoupper($personActivity->getPerson()->getLastname());
        }

        // Document signé
        $typeDocumentSigne = $this->oscarConfigurationService
            ->getOptionalConfiguration('pcru_contrat_type', "Contrat Version Définitive Signée");;
        $documentSigned = null;
        $documentId = null;

        /** @var ContractDocument $document */
        foreach ($activity->getDocuments() as $document){
            if( $document->getTypeDocument()->getLabel() == $typeDocumentSigne ){
                // Test sur les versions
                if(  $documentSigned != null ){
                    // On regarde si on est pas entrain de parser une ancienne version du fichier
                    if( $documentName == $document->getFileName() ){
                        continue;
                    }
                    $activityPcruInfos->addError("Il y'a plusieurs $typeDocumentSigne sur cette activité");
                } else {
                    $documentSigned = $document->getPath();
                    $documentName = $document->getFileName();
                    $documentId = $document->getId();
                }
            }
        }

        if( !$documentSigned ){
            $activityPcruInfos->addError("Oscar n'a pas trouvé de document '$typeDocumentSigne' à utiliser pour la soumission PCRU.");
        }

        /** @var PcruTypeContractRepository $pcruContractTypeRepository */
        $pcruContractTypeRepository = $this->entityManager->getRepository(PcruTypeContract::class);

        /** @var string $pcruContract */
        $pcruType = $pcruContractTypeRepository->getPcruContractForActivityTypeChained($activity->getActivityType());


        $activityPcruInfos->setActivity($activity)
            ->setDocumentPath($documentSigned)
            ->setDocumentId($documentId)
            ->setSigleUnite($sigleUnit)
            ->setPartenaires(implode('|', $partners))
            ->setPoleCompetivite($activity->getPcruPoleCompetitiviteStr())
            ->setNumContratTutelleGestionnaire($activity->getOscarNum())
            ->setValidePoleCompetivite($activity->isPcruPolePoleCompetitiviteStr())
            ->setSourceFinancement($activity->getPcruSourceFinancementStr())
            ->setResponsableScientifique($responsable)
            ->setCodeUniteLabintel($codeUniteLabintel)
            ->setTypeContrat($pcruType)
            ->setObjet($activity->getLabel())
            ->setAcronyme($activity->getAcronym())
            ->setMontantTotal($activity->getAmount())
            ->setDateDebut($activity->getDateStart())
            ->setDateFin($activity->getDateEnd())
            ->setDateDerniereSignature($activity->getDateSigned())
            ->setReference($activity->getOscarNum());

        return $activityPcruInfos;
    }

    public static function getHeaders(){
        return [
            'Objet' => "Intitulé de l'activité",
            'CodeUniteLabintel' => 'Code LABINTEL (extrait depuis la fiche organisation du laboratoire)',
            'SigleUnite' =>'Extrait depuis la fiche organisation du laboratoire (nom court)',
            'NumContratTutelleGestionnaire' => 'N°Oscar',
            'Equipe' => 'off',
            'TypeContrat' => '',
            'Acronyme' => "Acronyme du projet de l'activité",
            'ContratsAssocies' => 'off',
            'ResponsableScientifique' => 'Nom de la personne ayant le rôle "Responsable scientifique"',
            'EmployeurResponsableScientifique' => 'off',
            'CoordinateurConsortium' => 'off',
            'Partenaires' => 'off',
            'PartenairePrincipal' => 'off',
            'IdPartenairePrincipal' => 'off',
            'SourceFinancement' => 'Extrait de la fiche activité',
            'LieuExecution' => 'off',
            'DateDerniereSignature' => 'Extrait de la fiche activité',
            'Duree' => 'off',
            'DateDebut' => 'Extrait de la fiche activité',
            'DateFin' => 'Extrait de la fiche activité',
            'MontantPercuUnite' => 'off',
            'CoutTotalEtude' => 'off',
            'MontantTotal' => 'Extrait de la fiche activité',
            'ValidePoleCompetivite' => 'Extrait de la fiche activité',
            'PoleCompetivite' => 'Extrait de la fiche activité',
            'Commentaires' => 'off',
            'Pia' => 'off',
            'Reference' => '',
            'AccordCadre' => 'off',
            'Cifre' => 'off',
            'ChaireIndustrielle' => 'off',
            'PresencePartenaireIndustriel' => 'off',
        ];
    }
}