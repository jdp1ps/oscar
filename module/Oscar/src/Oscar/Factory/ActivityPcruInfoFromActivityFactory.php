<?php


namespace Oscar\Factory;


use Doctrine\ORM\EntityManager;
use Oscar\Entity\Activity;
use Oscar\Entity\ActivityOrganization;
use Oscar\Entity\ActivityPcruInfos;
use Oscar\Entity\ActivityPerson;
use Oscar\Entity\ContractDocument;
use Oscar\Entity\Organization;
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


    /**
     * Création des données PCRU à partir des données de l'activité.
     *
     * @param Activity $activity
     * @return ActivityPcruInfos
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function createNew(Activity $activity): ActivityPcruInfos
    {
        // Données PCRU
        $activityPcruInfos = new ActivityPcruInfos();

        // Erreurs métiers
        $errors = [];

        // --- Recherche automatique de l'Unité (Laboratoire)

        // Intitulé du rôle "Laboratoire" pour PCRU
        $roleStructureToFind = $this->oscarConfigurationService->getPcruUnitRoles();
        $rolePartnerToFind = $this->oscarConfigurationService->getPcruPartnerRoles();

        // Donnèes trouvées
        $codeUniteLabintel = "";
        $sigleUnit = "";
        $idPartenairePrincipal = "";

        // Récupération des laboratoires

        $structures = $activity->getOrganizationsWithOneRoleIn($roleStructureToFind);

        if (count($structures) == 0) {
            $activityPcruInfos->addError(
                "Aucune structure  " . implode("/", $roleStructureToFind) . " pour cette activité ."
            );
        }

        $organizationsParsed = [];

        /** @var ActivityOrganization $unite */
        foreach ($activity->getOrganizationsWithOneRoleIn($roleStructureToFind) as $unite) {
            $organizationsParsed[] = (string)$unite;
            if ($unite->getLabintel()) {
                $codeUniteLabintel = $unite->getLabintel();
                $sigleUnit = $unite->getShortName();
            }
        }

        $partners = [];
        /** @var Organization $partner */
        foreach ($activity->getOrganizationsWithOneRoleIn($rolePartnerToFind) as $partner) {
            if ($partner->getCodePcru()) {
                if ($idPartenairePrincipal == "") {
                    $idPartenairePrincipal = $partner->getCodePcru();
                } else {
                    $partners[] = $partner->getCodePcru();
                }
            }
        }
        if ($codeUniteLabintel == "") {
            $activityPcruInfos->addError(
                "Aucune des structures trouvées (" .implode("/", $roleStructureToFind).") n'ont de code LABINTEL valide"
            );
        }

        // --- Recherche automatique du responsable scientifique
        $roleRSToFind = $this->oscarConfigurationService->getPcruInChargeRole();
        $responsable = "";

        /** @var ActivityPerson $personActivity */
        foreach ($activity->getPersonsWithRole($roleRSToFind) as $personActivity) {
            $responsable = $personActivity->getPerson()->getFirstname() . " " . strtoupper(
                    $personActivity->getPerson()->getLastname()
                );
        }

        // Document signé
        $typeDocumentSigne = $this->oscarConfigurationService->getPcruContractType();
        $documentSigned = null;
        $documentId = null;

        /** @var ContractDocument $document */
        foreach ($activity->getDocuments() as $document) {
            if ($document->getTypeDocument()->getLabel() == $typeDocumentSigne) {
                // Test sur les versions
                if ($documentSigned != null) {
                    // On regarde si on est pas entrain de parser une ancienne version du fichier
                    if ($documentName == $document->getFileName()) {
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

        if (!$documentSigned) {
            $activityPcruInfos->addError(
                "Oscar n'a pas trouvé de document '$typeDocumentSigne' à utiliser pour la soumission PCRU."
            );
        }

        /** @var PcruTypeContractRepository $pcruContractTypeRepository */
        $pcruContractTypeRepository = $this->entityManager->getRepository(PcruTypeContract::class);

        $activityPcruInfos->setActivity($activity)
            ->setDocumentPath($documentSigned)
            ->setDocumentId($documentId)
            ->setSigleUnite($sigleUnit)
            ->setPartenaires(implode('|', $partners))
            ->setPoleCompetivite($activity->getPcruPoleCompetitiviteStr())
            ->setNumContratTutelleGestionnaire($activity->getOscarNum())
            ->setValidePoleCompetivite($activity->isPcruPolePoleCompetitiviteStr())
            ->setSourceFinancement($activity->getPcruSourceFinancementStr())
            ->setIdPartenairePrincipal($idPartenairePrincipal)
            ->setResponsableScientifique($responsable)
            ->setCodeUniteLabintel($codeUniteLabintel)
            ->setObjet($activity->getLabel())
            ->setAcronyme($activity->getAcronym())
            ->setMontantTotal($activity->getAmount())
            ->setDateDebut($activity->getDateStart())
            ->setDateFin($activity->getDateEnd())
            ->setDateDerniereSignature($activity->getDateSigned())
            ->setReference($activity->getOscarNum());

        // Patch (si le type d'activité est inconnu)
        if ($activity->getActivityType()) {
            $pcruType = $pcruContractTypeRepository->getPcruContractForActivityTypeChained(
                $activity->getActivityType()
            );
            $activityPcruInfos->setTypeContrat($pcruType);
        }

        return $activityPcruInfos;
    }

    public static function getHeaders()
    {
        return [
            'Objet' => "Intitulé de l'activité",
            'CodeUniteLabintel' => 'Code LABINTEL (extrait depuis la fiche organisation)',
            'SigleUnite' => 'Extrait depuis la fiche organisation (nom court)',
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