<?php

namespace Oscar\Service;

use Doctrine\ORM\Exception\NotSupported;
use Exception;
use Laminas\Http\Request;
use Oscar\Entity\Activity;
use Oscar\Entity\DateType;
use Oscar\Entity\Person;
use Oscar\Entity\Project;
use Oscar\Exception\OscarException;
use Oscar\Traits\UseEntityManager;
use Oscar\Traits\UseEntityManagerTrait;
use Oscar\Traits\UseLoggerService;
use Oscar\Traits\UseLoggerServiceTrait;
use Oscar\Traits\UseOscarConfigurationService;
use Oscar\Traits\UseOscarConfigurationServiceTrait;
use Oscar\Traits\UsePersonService;
use Oscar\Traits\UsePersonServiceTrait;
use Oscar\Traits\UseProjectGrantService;
use Oscar\Traits\UseProjectGrantServiceTrait;
use Oscar\Traits\UseSpentService;
use Oscar\Traits\UseSpentServiceTrait;
use Oscar\Utils\ArrayUtils;
use Oscar\Utils\DateTimeUtils;

/**
 * Cette classe gère la recherche avancée des activités
 */
class ProjectGrantSearchService implements UseEntityManager, UsePersonService, UseOscarConfigurationService,
                                           UseProjectGrantService, UseLoggerService, UseSpentService
{

    use UseEntityManagerTrait, UsePersonServiceTrait, UseOscarConfigurationServiceTrait, UseProjectGrantServiceTrait, UseLoggerServiceTrait, UseSpentServiceTrait;

    const FILTER_PERSON_ROLLED = 'ap';
    const FILTER_PERSON_ROLLED_OUT = 'sp';
    const FILTER_PERSONS = 'pm';
    const FILTER_ORGANIZATION_ROLLED = 'ao';
    const FILTER_ORGANIZATION_ROLLED_OUT = 'so';
    const FILTER_ORGANIZATIONS = 'om';
    const FILTER_ACTIVITY_STATUS = 'as';
    const FILTER_ACTIVITY_STATUS_OUT = 'ss';
    const FILTER_ORGANIZATION_COUNTRY = 'cnt';
    const FILTER_ORGANIZATION_TYPE = 'tnt';

    const FILTER_ACTIVITY_FINANCIAL_IMPACT = 'af';
    const FILTER_ACTIVITY_FINANCIAL_IMPACT_OUT = 'sf';
    const FILTER_ACTIVITY_AMOUNT = 'mp';
    const FILTER_ACTIVITY_TYPE = 'at';
    const FILTER_ACTIVITY_TYPE_OUT = 'st';
    const FILTER_ACTIVITY_DOCUMENT_TYPE = 'td';
    const FILTER_ACTIVITY_DATE_START = 'add';
    const FILTER_ACTIVITY_DATE_END = 'adf';
    const FILTER_ACTIVITY_DATE_CREATED = 'adc';
    const FILTER_ACTIVITY_DATE_UPDATED = 'adm';
    const FILTER_ACTIVITY_DATE_SIGNED = 'ads';
    const FILTER_ACTIVITY_DATE_FINANCIAL_OPENED = 'adp';
    const FILTER_ACTIVITY_NO_PROJECT = 'pp';
    const FILTER_ACTIVITY_TIMESHEET = 'fdt';
    const FILTER_ACTIVITY_DISCIPLINE = 'ds';
    const FILTER_ACTIVITY_MILESTONE = 'aj';
    const FILTER_ACTIVITY_ACCOUNT = 'cb';
    const FILTER_ACTIVITY_NUMBERS = 'num';
    const SORT_HIT = 'hit';
    const SORT_DATE_CREATED = 'dateCreated';
    const SORT_DATE_START = 'dateStart';
    const SORT_DATE_END = 'dateEnd';
    const SORT_DATE_UPDATED = 'dateUpdated';
    const SORT_DATE_SIGNED = 'dateSigned';
    const SORT_DATE_OPENED = 'dateOpened';
    const SORT_DIRECTION_DESC = 'desc';
    const SORT_DIRECTON_ASC = 'asc';
    const QUERY_PARAM_PAGE = 'page';
    const QUERY_PARAM_SEARCH = 'q';
    const QUERY_PARAM_SORT_IGNORE_NULL = 'sortIgnoreNull';
    const QUERY_PARAM_SORT_DIRECTION = 'sortDirection';
    const QUERY_PARAM_PROJECTVIEW = 'projectview';
    const QUERY_PARAM_CRITERIA = 'criteria';
    const QUERY_PARAM_SORT = 'sort';

    /**
     * Filtres de recherche disponibles pour la recherche avancée.
     *
     * @return string[]
     */
    public function getFiltersTypes(): array
    {
        return [
            // Personnes
            self::FILTER_PERSON_ROLLED                  => "Personne - (avec rôle) impliqué",
            self::FILTER_PERSON_ROLLED_OUT              => "Personne - NON impliquée",
            self::FILTER_PERSONS                        => "Personnes (plusieurs) - impliquées",
            // Organisations
            self::FILTER_ORGANIZATION_ROLLED            => "Organisation (avec rôle) - impliquée",
            self::FILTER_ORGANIZATION_ROLLED_OUT        => "Organisation - NON impliquée",
            self::FILTER_ORGANIZATIONS                  => "Organisations (plusieurs) - impliquées",
            self::FILTER_ORGANIZATION_COUNTRY           => "Organisation - Pays",
            self::FILTER_ORGANIZATION_TYPE              => "Organisation - Type",
            // Basique
            self::FILTER_ACTIVITY_STATUS                => 'Statut - AVEC',
            self::FILTER_ACTIVITY_STATUS_OUT            => 'Statut - SANS',
            self::FILTER_ACTIVITY_TYPE                  => 'Type - est de type',
            self::FILTER_ACTIVITY_TYPE_OUT              => 'Type - n\'est pas de type',
            self::FILTER_ACTIVITY_DISCIPLINE            => 'Ayant pour discipline',
            self::FILTER_ACTIVITY_MILESTONE             => 'Ayant le jalon',
            self::FILTER_ACTIVITY_DOCUMENT_TYPE         => 'Ayant ce type de document',
            self::FILTER_ACTIVITY_TIMESHEET             => 'Activités soumise à feuille de temps',
            // Dates
            self::FILTER_ACTIVITY_DATE_START            => 'Date de début',
            self::FILTER_ACTIVITY_DATE_END              => 'Date de fin',
            self::FILTER_ACTIVITY_DATE_CREATED          => 'Date de création',
            self::FILTER_ACTIVITY_DATE_UPDATED          => 'Date de dernière mise à jour',
            self::FILTER_ACTIVITY_DATE_SIGNED           => 'Date de signature',
            self::FILTER_ACTIVITY_DATE_FINANCIAL_OPENED => 'Date d\'ouverture du numéro financier (' . $this->getOscarConfigurationService(
                )->getFinancialLabel() . ')',
            self::FILTER_ACTIVITY_NUMBERS               => 'Ayant une numérotation',
            // Finances
            self::FILTER_ACTIVITY_FINANCIAL_IMPACT      => 'Incidence financière - AVEC',
            self::FILTER_ACTIVITY_FINANCIAL_IMPACT_OUT  => "Incidence financière (n'est pas)",
            self::FILTER_ACTIVITY_AMOUNT                => 'Montant prévu',
            self::FILTER_ACTIVITY_ACCOUNT               => 'Impliquant le compte',
            // Autres
            self::FILTER_ACTIVITY_NO_PROJECT            => 'Activités sans projet',
        ];
    }


    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    /// UTILS
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    /**
     * Extraction d'un entier positif depuis une chaine de caractère.
     * @param string|null $input
     * @param int|null $defaultValue
     * @return int|null
     */
    protected function extractValuePositiveInt(?string $input, ?int $defaultValue = null): ?int
    {
        $int = (int)$input;
        if ($int > 0) {
            return $int;
        }
        return $defaultValue;
    }


    /**
     * Retourne $input si cette valeur est bien présente comme clef dans le tableau $arrayRef. Sinon, retourne la
     * première clef du tableau ou la valeur par défaut si elle est spécifiée.
     * @param string|null $input
     * @param array $arrayRef
     * @param string|null $defaultValue
     * @return string
     */
    protected function extractValueInArrayKey(?string $input, array $arrayRef, ?string $defaultValue = null): string
    {
        if ($input && array_key_exists($input, $arrayRef)) {
            return $arrayRef[$input];
        }
        elseif ($defaultValue !== null) {
            return array_key_first($arrayRef);
        }
        else {
            return $defaultValue;
        }
    }


    /**
     * Point d'entrée depuis le contrôleur. Analyse des donnèes reçues.
     * @param Request $request
     * @param array|null $organizationsPerimeter IDs des organisations de référence
     * @return array
     */
    public function searchFromRequest(Request $request, ?array $organizationsPerimeter = null): array
    {
        // Traitement des données reçues
        $params = [];

        // PARAMETRES DE BASE

        // --- Page
        $params[self::QUERY_PARAM_PAGE] =
            $this->extractValuePositiveInt($request->getQuery(self::QUERY_PARAM_PAGE), 1);

        // --- Recherche textuelle
        $search = $request->getQuery(self::QUERY_PARAM_SEARCH, null);
        if ($search !== null) {
            $search = trim($search);
        }
        $params['search'] = $search;

        // --- Champ de tri
        $params[self::QUERY_PARAM_SORT] =
            $request->getQuery(self::QUERY_PARAM_SORT, 'dateUpdated');

        // --- Ignorer les valeurs nulles
        $params[self::QUERY_PARAM_SORT_IGNORE_NULL] =
            (bool)$request->getQuery(self::QUERY_PARAM_SORT_IGNORE_NULL, false);

        // --- Sens du tri
        $params[self::QUERY_PARAM_SORT_DIRECTION] =
            $request->getQuery(self::QUERY_PARAM_SORT_DIRECTION, 'desc');

        // --- Mode PROJET
        $params[self::QUERY_PARAM_PROJECTVIEW] =
            $request->getQuery(self::QUERY_PARAM_PROJECTVIEW, '') == "on";
        //
        $params['startEmpty'] = $search === true;

        // --- Périmètre de recherche
        $params['include'] = $request->getQuery('include', null);

        // --- Format
        $params['format'] = $request->getQuery('f', null);


        // --- Récupération des filtres
        $params['search'] =
            $request->getQuery(self::QUERY_PARAM_SEARCH, null);

        $criteria = $request->getQuery(self::QUERY_PARAM_CRITERIA, []);

        // Extraction des filtres
        $filters = [];
        $filtersError = false;
        foreach ($criteria as $criterion) {
            $filterParams = explode(';', $criterion);
            $type = $filterParams[0];

            // Par défaut, les deux valeurs reçues sont des entiers
            // MAIS sur certains critères non
            $value1 = (int)$filterParams[1];
            $value2 = (int)$filterParams[2];

            // Mise au propre des paramètres reçus
            $param = [
                'raw'      => $criterion, // la chaîne reçue
                'type'     => $type,
                'key'      => uniqid('filter_'),
                'val1'     => $value1,
                'val2'     => $value2,
                'error'    => null,
                'took'     => null, // Temps d'exécution du filtre
                'filtered' => null // Nombre de résultats pour ce filtre
            ];

            switch ($type) {
                case 'pp' :
                    $param['val1'] = '';
                    $param['val2'] = '';
                    break;

                case 'mp':
                    if (!$value1 && !$value2) {
                        $filtersError = true;
                        $param['error'] = 'Plage numérique farfelue...';
                    }
                    break;

                // Personne (plusieurs)
                case 'pm' :
                    $value1 = ArrayUtils::explodeIntegerFromString($filterParams[1]);
                    if ($value1 < 1) {
                        $filtersError = true;
                        $param['error'] = "Identifiant de personne incorrecte";
                    }
                    $param['val1'] = $value1;
                    break;

                case 'om' :
                    // TODO extraction d'un tableau d'entiers positifs
                    $value1 = explode(',', $filterParams[1]);
                    $param['val1'] = $value1;
                    break;

                ////////////////////////// PERSONNE (Avec/Sans)
                case 'ap' :
                case 'sp' :
                    if (!$value1 && !$value2) {
                        $filtersError = true;
                        $param['error'] = "Aucun critère pour ce filtre";
                    }
                    break;

                // --- Impliquant un compte


                // --- Type de document
                case 'td':
                    if ($param['val1'] == "null" || !$param['val1']) {
                        $param['val1'] = [];
                    }
                    else {
                        // TODO extraction d'un tableau d'entiers positifs
                        $param['val1'] = explode(',', $filterParams[1]);
                    }
                    break;

                case 'ao' :
                case 'so' :
                    $param['val1Label'] = "Non déterminé";
                    break;

                // Filtre où value1 est un tableau
                case 'num' :
                case 'as' :
                case 'ss' :
                case 'cnt' :
                case 'tnt' :
                case 'ds' :
                case 'cb':
                case 'cb2':
                    // TODO A TESTER
                    $param['val1'] = explode(',', $filterParams[1]);

                    break;
                case 'at' :
                case 'st' :
                case 'af' :
                case 'sf' :
                    break;

                case 'aj':
                    $progressStr = $filterParams[2];
                    $progressArray = null;

                    if ($progressStr != null && $progressStr != "" && $progressStr != 'null' && $progressStr != 'undefined') {
                        $progressArray = explode(',', $progressStr);
                        $param['val1'] = $value1;
                        $param['val2'] = ArrayUtils::implode(',', $progressArray);
                    }
                    else {
                        $param['val2'] = '';
                    }
                    break;

                case 'add' :
                case 'adf' :
                case 'adm' :
                case 'adc' :
                case 'ads' :
                case 'adp' :
                    $start = DateTimeUtils::toDatetime($filterParams[1]);
                    $end = DateTimeUtils::toDatetime($filterParams[2]);
                    $value1 = $start ? $start->format('Y-m-d') : '';
                    $value2 = $end ? $end->format('Y-m-d') : '';
                    $param['val1'] = $value1;
                    $param['val2'] = $value2;

                    // TODO tester les dates incohérentes

                    if (($start && $end) && ($start > $end)) {
                        $param['error'] = "plage de date incohérente";
                    }
                    break;
            }
            $filters[] = $param;
        }

        $params['filters'] = $filters;

        // Périmètre de recherche
        $params['organizationsPerimeter'] = $organizationsPerimeter;

        /* TODO En cas de périmètre organization, calculer les affectations de la personne pour les retourner */
        $affectationsDetails = [];
        $output = $this->searchAPI($params);

        $output["affectationsDetails"] = $affectationsDetails;


        return $output;
    }

    public function searchAPI(array $params): array
    {
        $time_start = microtime(true);

        $criterias = [];

        // Personnes pour afficher le filtre des personnes
        $filterPersons = [];
        $persons = []; // Idem, doublon ?

        // Organisations pour afficher le filtre des organisations
        $filterOrganizations = [];

        // Pour les responsables de structures, détermine le cadre de recherche (IDs des organisations)
        $include = [];

        $error = null;
        $search = $params['search'];
        $startEmpty = $params['startEmpty'];
        $page = $params['page'];
        $organizationsPerimeter = $params['organizationsPerimeter'];
        $projectView = $params['projectview'];

        // Résultats
        $activitiesIds = null;
        $projectsIds = null;

        // --- Périmètre (Organization)
        if ($organizationsPerimeter !== null) {
            $include = $params['include'];
            if ($include) {
                $includeClear = [];
                foreach ($include as $index => $value) {
                    if ($value > 0) {
                        $includeClear[] = intval($value);
                    }
                }
                $include = array_intersect(
                    $includeClear,
                    $organizationsPerimeter
                );
            }
            else {
                $include = $params['organizationsPerimeter'];
            }

            // IDS concernés
            $organizationsIdsPerimeter = $this->getProjectGrantService()
                ->getActivityRepository()
                ->getIdsWithOrganizations($include);

            // FIX
            if (count($organizationsIdsPerimeter) == 0) {
                $organizationsIdsPerimeter = [0];
                $activitiesIds = [];
            }
            else {
                $activitiesIds = $organizationsIdsPerimeter;
            }
        }

        ////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        // RECHERCHE TEXTUELLE
        ////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        if ($search) {
            $this->getLoggerService()->debug("Recherche textuelle '$search'");

            //////////////////////////
            // Recherche NUMERO OSCAR
            $oscarNumSeparator = $this->getOscarConfigurationService()->getConfiguration("oscar_num_separator");
            if (preg_match("/^[0-9]{4}" . $oscarNumSeparator . ".*/mi", $search)) {
                $this->getLoggerService()->debug("Recherche stricte sur le Numéro OSCAR : $search");
                $idsActivitySearch = $this->getProjectGrantService()
                    ->getActivityRepository()
                    ->getActivityIdsByOscarNumLike($search . "%");

                $this->getLoggerService()->debug(" > " . count($idsActivitySearch) . " résultat(s)");

                $activitiesIds = $activitiesIds === null ? $idsActivitySearch :
                    array_intersect($idsActivitySearch, $activitiesIds);
            }

            //////////////////////////
            // Recherche PFI
            elseif ($this->getOscarConfigurationService()->isPfiStrict()
                && preg_match($this->getOscarConfigurationService()->getValidationPFI(), $search)) {
                $this->getLoggerService()->debug(
                    "Recherche sur le PFI (regex: " . $this->getOscarConfigurationService()->getValidationPFI() . ")"
                );;

                $idsActivitySearch = $this->getProjectGrantService()
                    ->getActivityRepository()
                    ->getActivityIdsByPFI($search);

                $this->getLoggerService()->debug(" > " . count($idsActivitySearch) . " résultat(s)");

                $activitiesIds = $activitiesIds === null ? $idsActivitySearch :
                    array_intersect($idsActivitySearch, $activitiesIds);
            }

            //////////////////////////
            // Recherche ELASTIC
            else {
                try {
                    $this->getLoggerService()->debug(
                        "Recherche textuelle '" . $search . "')"
                    );;
                    $idsSearched = $this->getProjectGrantService()->search($search, true);
                    $idsActivitySearch = $idsSearched['activity_ids'];
                    $this->getLoggerService()->debug(
                        "La recherche textuelle a retournée " . count($idsActivitySearch) . " résultat(s) en " .
                        (microtime(true) - $time_start) . " ms"
                    );;

                    $activitiesIds = $activitiesIds === null ? $idsActivitySearch :
                        array_intersect($idsActivitySearch, $activitiesIds);
                } catch (Exception $e) {
                    $error = "Erreur Elasticsearch : " . $e->getMessage();
                    $activitiesIds = [];
                }
            }
        }
        ////////////////////////////////////////////////////////////////////////////////////////////////////////////////


        ////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        // FILTRES de RECHERCHE
        ////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        foreach ($params['filters'] as &$filter) {
            $this->getLoggerService()->debug("Traitement du filtre '" . $filter['raw'] . "'");
            $value1 = $filter['val1'];
            $value2 = $filter['val2'];
            $type = $filter['type'];
            $raw = $filter['raw'];
            $filter_time_start = microtime(true);

            // On range dans cette variable les IDS obtenus avec le
            // filtre. En sortie du SWITCH/CASE de l'enfer ci-après,
            // On actualise les IDs, on enregistre les résultats
            // donnés par le filtre.
            $filteredIds = null;

            try {
                switch ($type) {
                    ////////////////////////////////////////////////////////////////////
                    ///////////////////////////////// Structure de RECHERCHE - PLUSIEURS
                    case 'om':
                        try {
                            $value1 = explode(',', $params[1]);
                            $filter['val1'] = $value1;
                            $organisationsRequire = $this->getOrganizationService()->getOrganizationsByIds($value1);

                            foreach ($organisationsRequire as $organisation) {
                                $filterOrganizations[$organisation->getId()] = (string)$organisation;
                            }

                            $filteredIds = $this->getProjectGrantService()->getActivityRepository(
                            )->getIdsWithOneOfOrganizationsRoled(
                                $value1
                            );
                        } catch (\Exception $e) {
                            throw new OscarException("Erreur en filtrant les structures");
                        }
                        break;

                    /////////////////////////////////////////////////////////////////////
                    ///////////////////////////////// Structure de RECHERCHE/ROLE (et NOT)
                    case 'ao' :
                    case 'so' :
                        try {
                            $filter['val1Label'] = "Non déterminé";
                            $organization = null;
                            $organizationId = (int)$value1;
                            $roleId = (int)$value2;

                            // Récupération de l'organisation
                            if ($organizationId > 0) {
                                try {
                                    $organization = $this->getOrganizationService()->getOrganization($organizationId);
                                    $organizations[$organization->getId()] = $organization;
                                    $filter['val1Label'] = (string)$organization;
                                } catch (Exception $e) {
                                    $this->getLoggerService()->warning($e->getMessage());
                                    throw new OscarException("Erreur en filtrant l'organisation");
                                }
                            }

                            try {
                                $filteredIds = $this->getProjectGrantService()->getActivityRepository(
                                )->getIdsWithOrganizationAndRole(
                                    $organizationId,
                                    $roleId
                                );

                                if ($type == 'so') {
                                    $filteredIds = $this->getProjectGrantService()->getActivityRepository()
                                        ->getIdsInverse($filteredIds);
                                }
                            } catch (Exception $e) {
                                $this->getLoggerService()->warning("Filter $raw error : " . $e->getMessage());
                                throw new OscarException("Impossible de filtrer sur le rôle");
                            }
                        } catch (Exception $exception) {
                            throw new OscarException($exception->getMessage());
                        }
                        break;

                    ///////////////////////////////// Structure (PAYS)
                    case 'cnt' :
                        if ($value1) {
                            try {
                                $filteredIds = $this->getProjectGrantService()->getActivityRepository()
                                    ->getIdsWithOrganizationOfCountry($value1);
                            } catch (\Exception $e) {
                                $this->getLoggerService()->warning($e->getMessage());
                                throw new OscarException("Erreur en filtrant le pays");
                            }
                        }
                        else {
                            throw new OscarException("Erreur en filtrant le pays");
                        }
                        break;

                    ///////////////////////////////// Structure (TYPE de STRUCTURE)
                    case 'tnt' :
                        if ($value1) {
                            $typeIds = $this->getOrganizationService()->getTypesIdsByLabel($value1);
                            $filteredIds = $this->getProjectGrantService()->getActivityRepository()
                                ->getIdsWithOrganizationOfType($typeIds);
                        }
                        else {
                            throw new OscarException("Donnée incorrect");
                        }
                        break;

                    ///////////////////////////////// Plusieurs PERSONNE
                    case 'pm' :
                        $value1 = \Oscar\Utils\ArrayUtils::explodeIntegerFromString($params[1]);
                        $filter['val1'] = $value1;
                        $filterPersons = $this->getProjectGrantService()->getPersonService()->getPersonRepository(
                        )->getPersonsByIds_idValue(
                            $value1
                        );
                        $idsActivity = $this->getProjectGrantService()->getActivityRepository()
                            ->getIdsForPersons(array_keys($filterPersons));

                        $activitiesIds = $activitiesIds == null ? $idsActivity :
                            array_intersect($activitiesIds, $idsActivity);

                        $filter['filtered'] = count($idsActivity);
                        $this->getLoggerService()->debug(
                            "Filtre $type : " . count($idsActivity) . ' activité(s) trouvée(s)'
                        );
                        break;
                    ///////////////////////////////// Filtre MONTANT
                    case 'mp':
                        try {
                            $min = null;
                            $max = null;

                            if ($value1) {
                                $min = $value1;
                            }
                            if ($value2) {
                                $max = $value2;
                            }
                            $this->getLoggerService()->debug("Filtre $type : min($min) / max($max)");

                            if (!$value1 && !$value2) {
                                $filter['error'] = 'Plage numérique farfelue...';
                            }
                            elseif ($value1 && $value2 && $value1 > $value2) {
                                $filter['error'] = 'Le montant est un nombre imaginaire ?';
                            }
                            else {
                                $idsActivity = $this->getProjectGrantService()->getActivityRepository()
                                    ->getIdsAmount($min, $max);

                                $activitiesIds = $activitiesIds == null ? $idsActivity :
                                    array_intersect($activitiesIds, $idsActivity);

                                $filter['filtered'] = count($idsActivity);
                                $this->getLoggerService()->debug(
                                    "Filtre $type : " . count($idsActivity) . ' activité(s) trouvée(s)'
                                );
                            }
                        } catch (Exception $exception) {
                            $this->getLoggerService()->error(
                                "Erreur filtre 'montant : " . $exception->getMessage()
                            );
                            $filter['error'] = "Impossible de filtrer sur le montant";
                        }
                        break;

                    ///////////////////////////////// Filtre PERSONNE/ROLE
                    case 'ap' :
                    case 'sp' :
                        try {
                            $personIds = [];
                            $roleId = 0;
                            $role = null;

                            // Personne
                            if ($value1) {
                                try {
                                    $person = $this->getProjectGrantService()->getPersonService()->getPerson($value1);
                                    $personIds = [$person->getId()];
                                    $persons[$person->getId()] = $person;
                                    $filter['val1Label'] = $person->getDisplayName();
                                } catch (Exception $e) {
                                    $this->getLoggerService()->error(
                                        "Erreur filtre 'sur la personne/role, impossible de charger la personne '$value1'' : " . $e->getMessage(
                                        )
                                    );
                                    $filter['error'] = "Impossible de trouver la personne";
                                }
                            }
                            if ($value2) {
                                try {
                                    $roles = $this->getProjectGrantService()->getOscarUserContextService(
                                    )->getAllRoleIdPerson();
                                    if (array_key_exists($value2, $roles)) {
                                        $roleId = $value2;
                                        $role = $roles[$value2];
                                        $filter['val2Label'] = $role;
                                    }
                                } catch (Exception $e) {
                                    $this->getLoggerService()->error(
                                        "Erreur filtre 'sur la personne/role, impossible de charger le rôle '$value2'' : " . $e->getMessage(
                                        )
                                    );
                                    $filter['error'] = "Impossible de trouver le rôle";
                                }
                            }

                            $filteredIds = $this->getProjectGrantService()->getActivityRepository()
                                ->getIdsForPersonAndOrWithRole($personIds, $roleId);

                            // Filtre inversé
                            if ($type == 'sp') {
                                // TODO optimisation, si le résultat est vide (l'inverse c'est tout), on peut garder la liste des IDS initiale pour éviter une requête.
                                // On inverse
                                $filteredIds = $this->getProjectGrantService()->getActivitiesIdsInverse($filteredIds);
                            }
                        } catch (Exception $e) {
                            $this->getLoggerService()->error(
                                "Erreur filtre 'sur la personne '$value1'/role '$roleId' : " . $e->getMessage()
                            );
                            throw new OscarException("Impossible de filtrer sur la personne");
                        }
                        break;

                    ///////////////////////////////// ACTIVITE / STATUS
                    case 'as' :
                    case 'ss' :
                        try {
                            $filteredIds = $this->getProjectGrantService()->getActivityRepository()->getIdsWithStatus(
                                $value1
                            );
                            if ($type == 'ss') {
                                $filteredIds = $this->getProjectGrantService()->getActivitiesIdsInverse($filteredIds);
                            }
                        } catch (Exception $e) {
                            $filter['error'] = "Impossible de filtrer sur le status";
                        }

                        break;


                    ///////////////////////////////// ACTIVITE / DISCIPLINE
                    case 'ds' :

                        try {
                            $filteredIds = $this->getProjectGrantService()->getActivityRepository()->getIdsDisciplines(
                                $value1
                            );
                        } catch (Exception $e) {
                            $this->getLoggerService()->warning($e->getMessage());
                            $filter['error'] = "Impossible de filtrer sur les disciplines";
                        }
                        break;

                    ///////////////////////////////// ACTIVITE / JALON
                    case 'aj':
                        $progressStr = $value2;
                        $progressArray = null;

                        if ($progressStr != null && $progressStr != "" && $progressStr != 'null' && $progressStr != 'undefined') {
                            $progressArray = explode(',', $progressStr);
                            $filter['val1'] = $value1;
                            $filter['val2'] = ArrayUtils::implode(',', $progressArray);
                        }
                        else {
                            $filter['val2'] = '';
                        }
                        $filteredIds = $this->getProjectGrantService()->getActivityRepository()->getIdsMilestone(
                            $value1,
                            $progressArray
                        );
                        break;

                    ///////////////////////////////// ACTIVITE / TYPE de DOCUMENT
                    case 'td':

                        try {
                            $reverse = $value2 == "1";
                            $filteredIds = $this->getProjectGrantService()->getActivitiesIdsWithTypeDocument(
                                $value1,
                                $reverse
                            );
                        } catch (Exception $e) {
                            $this->getLoggerService()->warning($e->getMessage());
                            throw new OscarException("Un problème est survenu en filtrant sur les types de documents");
                        }
                        break;

                    ///////////////////////////////// ACTIVITY / FEUILLE de TEMPS
                    case 'fdt' :
                        $filteredIds = $this->getProjectGrantService()
                            ->getActivityRepository()->getActivityIdsWithWorkpackage();
                        break;

                    ///////////////////////////////// ACTIVITY / DATES
                    case 'add' :
                    case 'adf' :
                    case 'adm' :
                    case 'adc' :
                    case 'ads' :
                    case 'adp' :
                        // Correspondance Filtre/Champ de date
                        $field = $this->getFiltersDateMatch()[$type];
                        try {
                            $filteredIds = $this->getProjectGrantService()->getActivityRepository()->getIdsByDate(
                                $field,
                                $value1,
                                $value2
                            );
                        } catch (Exception $e) {
                            $this->getLoggerService()->warning($e->getMessage());
                            $filter['error'] = $e->getMessage();
                        }
                        break;


                    ///////////////////////////////// ACTIVITY / NUMERATION
                    case 'num' :
                        try {
                            $filteredIds = $this->getProjectGrantService()->getActivityRepository(
                            )->getActivitiesIdsWithNumerotations($value1);
                        } catch (Exception $e) {
                            $this->getLoggerService()->warning($e->getMessage());
                            throw new OscarException("Le filtre sur les numérotations a provoqué une erreur");
                        }
                        break;

                    ///////////////////////////////// ACTIVITY / INCIDENCE FINANCIERS
                    case 'af' :
                    case 'sf' :
                        try {
                            $filteredIds = $this->getProjectGrantService()->getActivityRepository(
                            )->getIdsFinancialImpact($value1, $type == 'sf');
                        } catch (Exception $exception) {
                            $this->getLoggerService()->warning($exception->getMessage());
                            $filter['error'] = "Un problème est survenu en filtrant sur l'incidence financière'";
                        }
                        break;

                    ///////////////////////////////// ACTIVITY / COMPTE
                    case 'cb':
                        try {
                            $accountsInfos = $this->getSpentService()->getAccountsInfosUsed();
                            $compteGeneralList = $accountsInfos->getCompteGeneralListByAccountIds($value1);
                            $filteredIds = $this->getSpentService()->getIdsActivitiesForAccounts($compteGeneralList);
                        } catch (Exception $e) {
                            $this->getLoggerService()->warning($e->getMessage());
                            throw new Exception("Impossible de filtrer sur le compte");
                        }
                        break;

                    ///////////////////////////////// ACTIVITY / PAS de PROJET
                    case 'pp' :
                        try {
                            $filteredIds = $this->getProjectGrantService()->getActivityRepository(
                            )->getIdsWithoutProject();
                        } catch (Exception $e) {
                            $this->getLoggerService()->warning($e->getMessage());
                            $filter['error'] = "Impossible de filtrer les activités sans projet";
                        }
                        break;

                    ///////////////////////////////// ACTIVITY / TYPE
                    case 'at' :
                    case 'st' :

                        if ($value2 == 1) {
                            $types = [$value1];
                        }
                        else {
                            $types = $this->getProjectGrantService()->getActivityTypeService()->getTypeIdsInside(
                                $value1
                            );
                        }

                        $filteredIds = $this->getProjectGrantService()->getActivityRepository()
                            ->getIdsWithTypes($types, $type === 'st');

                        break;

                    ///////////////////////////////// ACTIVITY / COMPTE STRICT
                    /// (Note : Accessible depuis l'interface d'administration uniquement)
                    case 'cb2':

                        try {
                            $filteredIds = $this->getSpentService()->getIdsActivitiesForCompteGeneral($value1);
                        } catch (Exception $e) {
                            $this->getLoggerService()->warning($e->getMessage());
                            throw new OscarException("Impossible de filtrer sur le compte");
                        }
                        break;
                }

                if ($filteredIds !== null) {
                    $this->getLoggerService()->debug(
                        "Filtre $type : " . count($filteredIds) . ' activité(s) trouvée(s)'
                    );
                    // On filtre les IDS à garder sur au filtrage
                    $activitiesIds = $activitiesIds == null ? $filteredIds :
                        array_intersect($activitiesIds, $filteredIds);

                    $filter['filtered'] = count($filteredIds);
                }
                else {
                    $filter['error'] = $filter['error'] ?: "Filtre non-appliqué";
                }

                $filter['took'] = (int)((microtime(true) - $time_start) * 1000);
                $criterias[] = $filter;
            } catch (Exception $e) {
                $filter['error'] = $e->getMessage();
            }
        }

        /////////////////////////////
        $sorted = $params['sort'] == 'hit'; // Les IDS sont-t-ils déjà triés ?


        if (!$search && count($criterias) == 0) {
            if ($activitiesIds === null) {
                $sorted = true;

                // AFFICHER TOUS
                $queryAll = $this->getEntityManager()->createQueryBuilder()
                    ->select('c.id, c.dateUpdated')
                    ->from(Activity::class, 'c');

                if ($params['sort'] !== 'hit') {
                    $queryAll->orderBy('c.' . $params['sort'], $params['sortDirection']);
                    if ($params['sortIgnoreNull'] == 'on') {
                        $queryAll->where('c.' . $params['sort'] . ' IS NOT NULL');
                    }
                }

                $allIds = $queryAll
                    ->getQuery()
                    ->getResult();

                $activitiesIds = array_map('current', $allIds);
            }
        }


        if ($activitiesIds === null) {
            $activitiesIds = [];
        }
        else {
            // On trie les IDS si besoin
            if ($sorted === false) {
                $this->getLoggerService()->debug("Trie des " . (count($activitiesIds)) . " résultat(s");
                $activitiesIds = $this->getProjectGrantService()->getActivityRepository()
                    ->getIdsOrderedBy(
                        $activitiesIds,
                        $params['sort'],
                        $params['sortDirection'],
                        $params['sortIgnoreNull'] == 'on'
                    );
            }
        }


        $idsRef = $activitiesIds;

        // Vue projet
        if ($projectView) {
            if ($search) {
                // TODO Ajouter les projets vides aux résultats
                $idsProject = $this->getProjectGrantService()->getActivityRepository()
                    ->getIdsProjectsForActivityAndEmpty(
                        $search,
                        $activitiesIds,
                        $params['sort'],
                        $params['sortDirection'],
                        $params['sortIgnoreNull'] == 'on'
                    );
                $this->getLoggerService()->debug("Projet(s) : " . (count($idsProject)) . " projet(s)");
            } else {
                $idsProject = $this->getProjectGrantService()->getActivityRepository()
                    ->getIdsProjectsForActivity(
                        $activitiesIds,
                        $params['sort'],
                        $params['sortDirection'],
                        $params['sortIgnoreNull'] == 'on'
                    );
            }
            $idsRef = $idsProject;
        }

        $totalResult = count($idsRef);
        $limit = 50;
        $totalPages = ceil($totalResult / $limit);
        $offset = ($page - 1) * $limit;
        $orderedIds = array_slice($idsRef, $offset, $limit);

        if ($projectView) {
            $projectsIds = $idsRef;
            $qb = $this->getEntityManager()->createQueryBuilder()
                ->select('p, c')
                ->from(Project::class, 'p')
                ->leftJoin('p.grants', 'c')
                ->where('p.id IN(:ids)')
                ->setParameter('ids', $orderedIds);
        }
        else {
            $qb = $this->getEntityManager()->createQueryBuilder()
                ->select('c')
                ->from(Activity::class, 'c')
                ->leftJoin('c.persons', 'm1')
                ->leftJoin('m1.person', 'pers1')
                ->leftJoin('c.disciplines', 'dis')
                ->leftJoin('c.activityType', 't1')
                ->leftJoin('c.organizations', 'p1')
                ->leftJoin('p1.organization', 'orga1')
                ->leftJoin('c.documents', 'd1')
                ->leftJoin('c.project', 'pr')
                ->leftJoin('pr.members', 'm2')
                ->leftJoin('pr.partners', 'p2')
                ->leftJoin('m2.person', 'pers2')
                ->leftJoin('p2.organization', 'orga2')
                ->where('c.id IN(:ids)')
                ->setParameter('ids', $orderedIds);
        }

        if ($params['sort'] !== 'hit') {
            $qb->orderBy('c.' . $params['sort'], $params['sortDirection']);
        }

        $activities = $qb->getQuery()->getResult();

        usort($activities, function ($a, $b) use ($orderedIds) {
            $indexA = array_search($a->getId(), $orderedIds);
            $indexB = array_search($b->getId(), $orderedIds);
            return $indexA - $indexB;
        });

        if ($params['format'] == 'json') {
            $json = [];
            foreach ($activities as $activity) {
                $json[] = $activity->toArray();
            }
            $activities = $json;
        }

        $output = [
            "params_requested"       => $params,
            "errors"                 => null,
            "page"                   => $params['page'] ?? 1,
            "totalResult"            => $totalResult,
            'totalPages'             => $totalPages,
            'projectview'            => $params['projectview'],
            'exportIds'              => [], //ArrayUtils::implode(',', $idsExport),
            'error'                  => $error,
            'criteria'               => $criterias,
            'persons'                => $persons,
            'activities'             => $activities,
            'search'                 => $params['search'],
            'filterPersons'          => $filterPersons,
            'filterOrganizations'    => $filterOrganizations,
            'include'                => $include,
            'organizationsPerimeter' => $params['organizationsPerimeter'],
            'sort'                   => $params['sort'],
            'sortDirection'          => $params['sortDirection'],
            'sortIgnoreNull'         => $params['sortIgnoreNull'],
            // Valeurs pour les champs de formulaire
            'filtersType'            => $this->getFiltersTypes(),
            'fieldsCSV'              => $this->getFilterOptionsFieldCSV(),
            'typeorgas'              => $this->getFilterOptionsOrganizationTypes(),
            'sortCriteria'           => $this->getSortOptions(),
            'countries'              => $this->getFilterOptionsCountries(),
            'filterJalons'           => $this->getFilterOptionsMilestones(),
            'numerotations'          => $this->getFilterOptionsActivityNumbers(),
            'accounts'               => $this->getFilterOptionsAccountsUsed(),
            'types'                  => $this->getFilterOptionsActivityTypes(),
            'documentsTypes'         => $this->getFilterOptionsDocumentTypes(),
            'disciplines'            => $this->getFilterOptionsDisciplines(),
            'projectsIds'            => $projectsIds,
        ];

        $time_end = microtime(true);

        $output['took'] = intval(($time_end - $time_start) * 1000);

        if ($params['format'] == 'json') {
            echo json_encode($output, JSON_PRETTY_PRINT);
            die();
        }

        return $output;
    }

    /**
     * Retourne les options pour les jalons.
     * @throws NotSupported
     */
    public function getFilterOptionsMilestones(): array
    {
        /* TODO Utiliser la méthode 'ProjectGrantService->getDateTypesSelect()'
            pour afficher les facettes */
        $jalonsFilters = [];
        $jalons = $this->getEntityManager()->getRepository(DateType::class)->findAll();
        /** @var DateType $jalon */
        foreach ($jalons as $jalon) {
            $jalonsFilters[] = [
                'id'         => $jalon->getId(),
                'label'      => $jalon->getLabel(),
                'finishable' => $jalon->isFinishable()
            ];
        }
        return $jalonsFilters;
    }

    /**
     * Retourne la liste des comptes (Plan comptable) utilisés dans les dépenses.
     * @return array
     */
    private function getFilterOptionsAccountsUsed(): array
    {
        return $this->getSpentService()->getAccountsInfosUsed()->getAccounts();
    }

    /**
     * Retourne la liste des numérotations personnalisées dans les activités.
     * @return array
     */
    private function getFilterOptionsActivityNumbers(): array
    {
        return $this->getOscarConfigurationService()->getNumerotationKeys();
    }

    /**
     * Liste des types de document.
     * @return array
     */
    private function getFilterOptionsDocumentTypes(): array
    {
        return $this->getProjectGrantService()->getDocumentTypes();
    }

    /**
     * Liste des types d'activités.
     * @return array
     */
    private function getFilterOptionsActivityTypes(): array
    {
        return $this->getProjectGrantService()->getActivityTypes(true);
    }

    /**
     * Liste des disciplines.
     * @return array
     */
    private function getFilterOptionsDisciplines(): array
    {
        return $this->getProjectGrantService()->getDisciplines();
    }

    /**
     * Liste des pays.
     * @return array
     */
    private function getFilterOptionsCountries(): array
    {
        return $this->getProjectGrantService()->getOrganizationService()->getCountriesList();
    }

    /**
     * Liste des types d'organisations.
     * @return array
     */
    private function getFilterOptionsOrganizationTypes(): array
    {
        return $this->getProjectGrantService()->getOrganizationService()->getOrganizationTypesSelect();
    }

    private function getFilterOptionsFieldCSV(): array
    {
        return $this->getProjectGrantService()->getFieldsCSV();
    }

    /**
     * Correspondance entre les filtres de date et les champs.
     * @return string[]
     */
    public function getFiltersDateMatch(): array
    {
        // Correspondance des champs de type date
        return [
            'add' => self::SORT_DATE_START,
            'adc' => self::SORT_DATE_CREATED,
            'adf' => self::SORT_DATE_END,
            'adm' => self::SORT_DATE_UPDATED,
            'ads' => self::SORT_DATE_SIGNED,
            'adp' => self::SORT_DATE_OPENED,
        ];
    }

    /**
     * Retourne les options de tri disponibles (Champs).
     * @return string[]
     */
    public function getSortOptions(): array
    {
        // Critères de tri
        return [
            self::SORT_HIT          => 'Pertinence (Recherche textuelle)',
            self::SORT_DATE_CREATED => 'Date de création',
            self::SORT_DATE_START   => 'Date début',
            self::SORT_DATE_END     => 'Date fin',
            self::SORT_DATE_UPDATED => 'Date de mise à jour',
            self::SORT_DATE_SIGNED  => 'Date de signature',
            self::SORT_DATE_OPENED  => "Date d'ouverture du " . $this->getOscarConfigurationService(
                )->getFinancialLabel()
        ];
    }

    /**
     * Retourne les options de tri disponibles (Directions).
     * @return string[]
     */
    public function getSortDirections(): array
    {
        // Trie
        return [
            self::SORT_DIRECTION_DESC => 'Décroissant',
            self::SORT_DIRECTON_ASC   => 'Croissant'
        ];
    }

    public function getOrganizationService(): OrganizationService
    {
        return $this->getProjectGrantService()->getOrganizationService();
    }
}
