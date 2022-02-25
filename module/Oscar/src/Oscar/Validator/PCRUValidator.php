<?php


namespace Oscar\Validator;


use Doctrine\ORM\EntityManager;
use Oscar\Entity\ActivityPcruInfos;
use Oscar\Service\OscarConfigurationService;

/**
 * Validation des données PCRU
 * Class PCRUValidator
 * @package Oscar\Validator
 */
class PCRUValidator
{
    const VALIDATION_VALID = 'valid';
    const VALIDATION_DISABLED = 'disabled';
    const VALIDATION_ERROR = 'error';
    const VALIDATION_EMPTY = 'empty';

    private OscarConfigurationService $oscarConfigurationService;

    private EntityManager $entityManager;

    private string $status;

    private array $errors;

    private array $warnings;

    /**
     * PCRUValidator constructor.
     * @param OscarConfigurationService $oscarConfigurationService
     * @param EntityManager $entityManager
     */
    public function __construct(OscarConfigurationService $oscarConfigurationService, EntityManager $entityManager)
    {
        $this->oscarConfigurationService = $oscarConfigurationService;
        $this->entityManager = $entityManager;
        $this->errors = [];
        $this->warnings = [];
        $this->status = ActivityPcruInfos::STATUS_DRAFT;
    }

    protected function addError( $message ) :self
    {
        $this->errors[] = $message;
        $this->status = self::VALIDATION_ERROR;
        return $this;
    }

    protected function addWarning( $message ) :self
    {
        $this->errors[] = $message;
        return $this;
    }

    public function getErrors() :array
    {
        return $this->errors;
    }

    public function getWarnings() :array
    {
        return $this->warnings;
    }

    public function hasErrors() :bool
    {
        return count($this->errors) > 0;
    }

    public function hasWarnings() :bool
    {
        return count($this->warnings) > 0;
    }

    public function validate( ActivityPcruInfos $activityPcruInfos ) :array
    {
        $this->error = [];
        $datas = $activityPcruInfos->toArray();

        $out = [];
        $out['Objet'] = self::VALIDATION_ERROR;                             // Requis
        $out['CodeUniteLabintel'] = self::VALIDATION_ERROR;                 // Requis
        $out['SigleUnite'] = self::VALIDATION_EMPTY;
        $out['NumContratTutelleGestionnaire'] = self::VALIDATION_ERROR;     // Requis
        $out['Equipe'] = self::VALIDATION_EMPTY;
        $out['TypeContrat'] = self::VALIDATION_ERROR;
        $out['Acronyme'] = self::VALIDATION_EMPTY;
        $out['ContratsAssocies'] = self::VALIDATION_EMPTY;
        $out['ResponsableScientifique'] = self::VALIDATION_ERROR;           // Requis
        $out['EmployeurResponsableScientifique'] = self::VALIDATION_EMPTY;
        $out['CoordinateurConsortium'] = self::VALIDATION_EMPTY;
        $out['Partenaires'] = self::VALIDATION_EMPTY;
        $out['PartenairePrincipal'] = self::VALIDATION_ERROR;               // Requis
        $out['IdPartenairePrincipal'] = self::VALIDATION_EMPTY;
        $out['SourceFinancement'] = self::VALIDATION_ERROR;                 // Requis
        $out['LieuExecution'] = self::VALIDATION_ERROR;                     // Requis
        $out['DateDerniereSignature'] = self::VALIDATION_ERROR;             // Requis
        $out['Duree'] = self::VALIDATION_EMPTY;
        $out['DateDebut'] = self::VALIDATION_ERROR;                         // Requis
        $out['DateFin'] = self::VALIDATION_ERROR;                           // Requis
        $out['MontantPercuUnite'] = self::VALIDATION_ERROR;                 // Requis
        $out['CoutTotalEtude'] = self::VALIDATION_EMPTY;
        $out['MontantTotal'] = self::VALIDATION_EMPTY;
        $out['ValidePoleCompetivite'] = self::VALIDATION_EMPTY;
        $out['PoleCompetivite'] = self::VALIDATION_EMPTY;
        $out['Commentaires'] = self::VALIDATION_EMPTY;
        $out['Pia'] = self::VALIDATION_EMPTY;
        $out['Reference'] = self::VALIDATION_EMPTY;
        $out['AccordCadre'] = self::VALIDATION_EMPTY;
        $out['Cifre'] = self::VALIDATION_EMPTY;
        $out['ChaireIndustrielle'] = self::VALIDATION_EMPTY;
        $out['PresencePartenaireIndustriel'] = self::VALIDATION_EMPTY;
        $out['contract_signed'] = self::VALIDATION_ERROR;


        $disabledFields = [
            'Equipe','ContratsAssocies', 'EmployeurResponsableScientifique',
            'CoordinateurConsortium','Partenaires', 'PartenairePrincipal',
            'IdPartenairePrincipal','LieuExecution', 'Duree',
            'MontantPercuUnite','CoutTotalEtude', 'Commentaires',
            'Pia','AccordCadre', 'Cifre',
            'ChaireIndustrielle','PresencePartenaireIndustriel'
        ];

        foreach ($disabledFields as $unusedField) {
            $out[$unusedField] = self::VALIDATION_DISABLED;
        }

        if( $datas['Objet'] )
            $out['Objet'] = self::VALIDATION_VALID;

        if( $datas['CodeUniteLabintel'] )
            $out['CodeUniteLabintel'] = self::VALIDATION_VALID;

        if( $datas['SigleUnite'] )
            $out['SigleUnite'] = self::VALIDATION_VALID;

        if( $datas['NumContratTutelleGestionnaire'] )
            $out['NumContratTutelleGestionnaire'] = self::VALIDATION_VALID;

        if( $datas['NumContratTutelleGestionnaire'] )
            $out['NumContratTutelleGestionnaire'] = self::VALIDATION_VALID;

        if( $datas['TypeContrat'] != 'Aucun' )
            $out['TypeContrat'] = self::VALIDATION_VALID;

        if( $datas['Acronyme'] )
            $out['Acronyme'] = self::VALIDATION_VALID;

        if( $datas['ResponsableScientifique'] )
            $out['ResponsableScientifique'] = self::VALIDATION_VALID;

        if( $datas['EmployeurResponsableScientifique'] )
            $out['EmployeurResponsableScientifique'] = self::VALIDATION_VALID;

        if( $datas['PartenairePrincipal'] )
            $out['PartenairePrincipal'] = self::VALIDATION_VALID;

        if( $datas['SourceFinancement'] )
            $out['SourceFinancement'] = self::VALIDATION_VALID;

        if( $datas['LieuExecution'] )
            $out['LieuExecution'] = self::VALIDATION_VALID;

        if( $datas['DateDerniereSignature'] )
            $out['DateDerniereSignature'] = self::VALIDATION_VALID;

        if( $datas['DateDebut'] )
            $out['DateDebut'] = self::VALIDATION_VALID;

        if( $datas['DateFin'] )
            $out['DateFin'] = self::VALIDATION_VALID;

        if( $datas['MontantTotal'] )
            $out['MontantTotal'] = self::VALIDATION_VALID;


        if( $datas['ValidePoleCompetivite'] == true ){
            $out['ValidePoleCompetivite'] = self::VALIDATION_VALID;
            $out['PoleCompetivite'] = self::VALIDATION_ERROR;
            if( $out['PoleCompetivite'] ){
                $out['PoleCompetivite'] = self::VALIDATION_VALID;
            }
        }

        if( $datas['Reference'] )
            $out['Reference'] = self::VALIDATION_VALID;


        if( $activityPcruInfos->getDocumentId() ){
            $out['document_signed'] = self::VALIDATION_VALID;
            $out['contrat_signed'] = self::VALIDATION_VALID;
        } else {
            $this->addError("Le document du contrat est indisponible");
        }

        foreach ($out as $champ=>$state) {
            if( $champ == 'contract_signed') continue;
            if( $state == self::VALIDATION_ERROR ){
                $this->status = self::VALIDATION_ERROR;
                $this->addError($this->getMessage($champ));
            }
        }

        return $out;
    }


    protected function getMessage( $champ ) :string
    {
        static $messages;
        if( $messages === null ) {
            $messages = [];
            $messages['SourceFinancement'] = "La source de financement est manquante, vous pouvez la renseigner depuis la fiche activité";
            $messages['TypeContrat'] = "Le type de contrat est manquant. Vous pouvez automatiser la correspondance Oscar/PCRU depuis l'interface d'administration et éditer le type manuellement";
            $messages['DateDerniereSignature'] = "La date de signature est manquante, vous pouvez la renseigner depuis la fiche activité";
            $messages['CodeUniteLabintel'] = "Le code de l'unité de recherche (Labintel) est manquant, il peut être complété depuis la fiche organisation (type UMR XXXX)";
        }

        if( array_key_exists($champ, $messages) ){
            return $messages[$champ];
        } else {
            return sprintf("Le champ %s n'est pas renseigné.", $champ);
        }
    }
}