<?php
/**
 * Created by PhpStorm.
 * User: bouvry
 * Date: 04/11/19
 * Time: 12:18
 */

namespace Oscar\Strategy\Activity;


use Oscar\Entity\Activity;
use Oscar\Entity\DateType;
use Oscar\Entity\OrganizationRole;
use Oscar\Entity\Role;
use Oscar\Exception\OscarException;
use Oscar\Provider\Privileges;
use Oscar\Service\OscarConfigurationService;
use Oscar\Service\OscarUserContext;
use Oscar\Service\OscarUserContextFactory;
use Oscar\Service\ProjectGrantService;
use Oscar\Traits\UseEntityManager;
use Oscar\Traits\UseEntityManagerTrait;
use Oscar\Traits\UseOscarConfigurationService;
use Oscar\Traits\UseOscarConfigurationServiceTrait;
use Oscar\Traits\UseOscarUserContextService;
use Oscar\Traits\UseOscarUserContextServiceTrait;
use Oscar\Traits\UseProjectGrantService;
use Oscar\Traits\UseProjectGrantServiceTrait;

class ExportDatas implements UseOscarConfigurationService, UseProjectGrantService, UseEntityManager, UseOscarUserContextService
{
    use UseOscarConfigurationServiceTrait, UseProjectGrantServiceTrait, UseEntityManagerTrait, UseOscarUserContextServiceTrait;


    /**
     * ExportDatas constructor.
     */
    public function __construct( ProjectGrantService $pgs, OscarUserContext $ouc)
    {
        $this->setProjectGrantService($pgs);
        $this->setOscarConfigurationService($pgs->getOscarConfigurationService());
        $this->setEntityManager($pgs->getEntityManager());
        $this->setOscarUserContextService($ouc);
    }

    public function output( $paramID, $fields = null, $perimeter = null ){

        // Séparateur d'aggrégation pour des données multiples
        $separator = $this->getOscarConfigurationService()->getExportSeparator();

        // Format des dates
        $dateFormat = $this->getOscarConfigurationService()->getExportDateFormat();

        $parameters = [];

        $qb = $this->getEntityManager()->createQueryBuilder()->select('a')
            ->from(Activity::class, 'a');

        $organizationsPerimeter = [];

        if ($this->getOscarUserContextService()->hasPrivileges(Privileges::ACTIVITY_EXPORT)) {

        } else {
            // On réduit le périmètre en fonction de l'organisation
            $organizationsPerimeter = $this->getOscarUserContextService()
                ->getOrganisationsPersonPrincipal($this->getOscarUserContextService()->getCurrentPerson(),
                    true);

            $qb->leftJoin('a.project', 'pr')
                ->leftJoin('pr.partners', 'o1')
                ->leftJoin('a.organizations', 'o2')
                ->where('o1.organization IN(:perimeter) OR o2.organization IN(:perimeter)');

            $parameters = [
                'perimeter' => $organizationsPerimeter
            ];
        }

        if ($paramID) {
            $ids = explode(',', $paramID);
            $qb->andWhere('a.id IN (:ids)');
            $parameters['ids'] = $ids;
        }

        $entities = $qb->getQuery()->setParameters($parameters)->getResult();

        if (!count($entities)) {
            throw new OscarException("Aucun résultat à exporter");
        }

        $keep = true;
        if( $fields ){
            $keep = explode(',', $fields);
        }

        $columns = [];
        $headers = [];

        foreach(Activity::csvHeaders() as $header){
            if( $keep === true || in_array($header, $keep) ){
                $columns[$header] = true;
                $headers[] = $header;
            } else {
                $columns[$header] = false;
            }
        }

        // Tableau avec les rôles possibles
        $rolesOrganizationsQuery = $this->getEntityManager()->createQueryBuilder()
            ->select('r.label')
            ->from(OrganizationRole::class, 'r')
            ->getQuery()
            ->getResult();

        $rolesOrganisations = [];

        // Construction des en-têtes / colonnes
        foreach( $rolesOrganizationsQuery as $role ){
            $header = $role['label'];
            if( $keep === true || in_array($header, $keep) ){
                $columns[$header] = true;
                $headers[] = $header;
            } else {
                $columns[$header] = false;
            }
            $rolesOrganisations[$header] = [];
        }

        $rolesOrga = $this->getEntityManager()->getRepository(Role::class)->getRolesAtActivityArray();
        $rolesPersons = [];

        foreach( $rolesOrga as $role ){
            $header = $role;
            if( $keep === true || in_array($header, $keep) ){
                $columns[$header] = true;
                $headers[] = $header;
            } else {
                $columns[$header] = false;
            }
            $rolesPersons[$role] = [];
        }

        // Numérotation
        $numbers = [];
        foreach( $this->getOscarConfigurationService()->getNumerotationKeys() as $key ){
            $header = $key;
            if( $keep === true || in_array($header, $keep) ){
                $columns[$header] = true;
                $headers[] = $header;
            } else {
                $columns[$header] = false;
            }
            $numbers[$header] = [];
        }

        ////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        // --- JALONS
        // Récupération des différents types de jalons
        $jalonsQuery = $this->getEntityManager()->getRepository(DateType::class)->findAll();
        $jalons = [];
        $jalonsFait = [];

        /** @var DateType $jalon */
        foreach ($jalonsQuery as $jalon) {
            $header = $jalon->getLabel();
            $jalons[$header] = [];
            if( $keep === true || in_array($header, $keep) ){
                $columns[$header] = true;
                $headers[] = $header;

                if( $jalon->isFinishable() ){
                    $headers[] = "Fait";
                    $columns[$header."_fait"] = true;
                    $jalonsFait[$header] = [];
                }
            } else {
                $columns[$header] = false;
            }
        }

        // Ajout des en-têtes calculées
        $computed = $this->getProjectGrantService()->getExportComputedFields();
        foreach ($computed as $key=>$headerInfos) {
            $header = $headerInfos['label'];
            if( $keep === true || in_array($header, $keep) ){
                $columns[$header] = true;
                $headers[] = $header;
            } else {
                $columns[$header] = false;
            }
        }

        $outputDatas = [];

        /** @var Activity $entity */
        foreach ($entities as $entity) {
            $datas = [];
            $rolesCurrent = $rolesOrganisations;
            $rolesPersonsCurrent = $rolesPersons;
            $jalonsCurrent = $jalons;
            $jalonsFaitCurrent = $jalonsFait;
            $computedValues = [];

            if ($this->getOscarUserContextService()->hasPrivileges(Privileges::ACTIVITY_EXPORT,
                $entity)
            ) {

                // Champs calculés
                foreach ($computed as $computedField) {
                    $computedValues[$computedField['label']] = $computedField['handler']($entity);
                }


                /** @var ActivityOrganization $org */
                foreach( $entity->getOrganizationsDeep() as $org ){
                    $rolesCurrent[$org->getRole()][] = (string)$org->getOrganization()->fullOrShortName();
                }

                foreach( $entity->getPersonsDeep() as $per ){
                    $rolesPersonsCurrent[$per->getRole()][] = (string)$per->getPerson();
                }

                /** @var ActivityDate $mil */
                foreach( $entity->getMilestones() as $mil ){

                    $jalonKey = $mil->getType()->getLabel();

                    $jalonsCurrent[$jalonKey][] = $mil->getDateStart() ?
                        $mil->getDateStart()->format($dateFormat) :
                        '';

                    if( array_key_exists($jalonKey, $jalonsFaitCurrent) ){
                        // Calcule de l'état du jalon
                        $dn = "";
                        if( $mil->isFinishable() ){
                            $dn = "non";
                            if( $mil->getFinished() > 0 ){
                                $dn = "en cours";
                            }
                            if( $mil->isFinished() ){
                                $dn = $mil->getDateFinish() ? $mil->getDateFinish()->format($dateFormat) : 'oui';
                            }
                        }
                        $jalonsFaitCurrent[$jalonKey][] = $dn;
                    }
                }


                foreach ( $entity->csv($dateFormat) as $col=>$value ){
                    if( $columns[$col] === true )
                        $datas[] = $value;
                }

                foreach( $rolesCurrent as $role=>$organisations ){
                    if( $columns[$role] === true )
                        $datas[] = ($organisations ? implode($separator, array_unique($organisations)) : '');
                }

                foreach( $rolesPersonsCurrent as $role=>$persons ){
                    if( $columns[$role] === true )
                        $datas[] =  $persons ? implode($separator, array_unique($persons)) : '';
                }

                foreach ( $numbers as $key=>$value ){
                    if( $columns[$key] === true )
                        $datas[] = $entity->getNumber($key);
                }

                foreach( $jalonsCurrent as $jalon2=>$date ){
                    if( $columns[$jalon2] === true ) {
                        $datas[] =  implode($separator, $date);
                        if( array_key_exists($jalon2, $jalonsFaitCurrent) ){
                            $done = $jalonsFaitCurrent[$jalon2];
                            $datas[] =  implode($separator, $done);
                        }
                    }
                }

                foreach ( $computedValues as $col=>$value ){

                    if( $columns[$col] === true ) {
                        $datas[] = $value;
                    }
                }

                $outputDatas[] = $datas;

            } else {
                //$this->getLoggerService()->warn("Pas le droit d'exporter : " . $entity->getId() . $entity->getLabel());
            }
        }
        return [
            'headers' => $headers,
            'datas' => $outputDatas
        ];

//        fputcsv($handler, $headers);
//        foreach ($outputDatas as $data) {
//            fputcsv($handler, $datas);
//        }
    }


}