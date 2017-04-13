<?php
/**
 * @author Stéphane Bouvry<stephane.bouvry@unicaen.fr>
 * @date: 29/06/15 09:56
 * @copyright Certic (c) 2015
 */

namespace Oscar\Import;


use Oscar\Entity\Organization;
use Oscar\Entity\ProjectPartner;
use Oscar\Utils\EntityHydrator;

class ImportPartners extends AbstractImportStrategy
{

    function import()
    {
        $c = $this->getConnexion();
        /** @var ContractTypeRepository $contractTypeRepo */
        $repo = $this->getEntityManager()->getRepository('Oscar\Entity\Organization');

        $this->getLogger()->debug("Récupération des données depuis la base CENTAURE");


        $hydrator = new EntityHydrator([
            'CITY' => array(
                'property'  => 'city',
                'cleaner'   => function( $data ) { return $this->cleanBullshitStr($data);}
            ),
            'CODE' => array(
                'property'  => 'code',
                'cleaner'   => function( $data ) { return $this->cleanBullshitStr($data);}
            ),
            'DATECREATED' => array(
                'property'  => 'dateCreated',
                'cleaner'   => function( $data ) { return $this->extractDate($data);}
            ),
            'DATEUPDATED' => array(
                'property'  => 'dateUpdated',
                'cleaner'   => function( $data ) { return $this->extractDate($data);}
            ),
            'EMAIL' => array(
                'property'  => 'email',
                'cleaner'   => function( $data ) { return $this->cleanBullshitStr($data);}
            ),
            'FULLNAME' => array(
                'property'  => 'fullName',
                'cleaner'   => function( $data ) { return $this->cleanBullshitStr($data);}
            ),
            'SHORTNAME' => array(
                'property'  => 'shortName',
                'cleaner'   => function( $data ) { return $this->cleanBullshitStr($data);}
            ),
            'STREE1' => array(
                'property'  => 'street1',
                'cleaner'   => function( $data ) { return $this->cleanBullshitStr($data);}
            ),
            'STREE2' => array(
                'property'  => 'street2',
                'cleaner'   => function( $data ) { return $this->cleanBullshitStr($data);}
            ),
            'STREE3' => array(
                'property'  => 'street3',
                'cleaner'   => function( $data ) { return $this->cleanBullshitStr($data);}
            ),
            'PHONE' => array(
                'property'  => 'phone',
                'cleaner'   => function( $data ) { return $this->cleanBullshitStr($data);}
            ),
            'URL' => array(
                'property'  => 'url',
                'cleaner'   => function( $data ) { return $this->cleanBullshitStr($data);}
            ),
            'ZIPCODE' => array(
                'property'  => 'zipCode',
                'cleaner'   => function( $data ) { return $this->cleanBullshitStr($data);}
            ),
        ]);
        $query = sprintf("SELECT CLEUNIK, TYPE, %s FROM OSCAR_PARTENAIRE_AVEC_UFR", implode($hydrator->listFields(), ','));
        $this->getLogger()->info($query);
        $stid = oci_parse($c, $query);
        oci_execute($stid);

        $proceded = 0;
        $start = microtime(true);

        $doublons = array();

        while ($row = oci_fetch_array($stid, OCI_ASSOC + OCI_RETURN_NULLS)) {

            $proceded++;


            // Récupération des données
            $centaureID = $row['TYPE']. $row['CLEUNIK'];
            $oscarId = $this->getOscarId('Participant', $centaureID);

            $this->getLogger()->debug(sprintf("# Traitement de '%s' > '%s'.", $centaureID, $oscarId));


            $sum = serialize($row);

            // Création / Récupération de l'entité
            if( $oscarId ){

                // Test du cache
                $cache = $this->getSum('Participant', $centaureID);
                if( $cache && $cache == $sum ){
                    $this->getLogger()->debug(sprintf(" - Données de '%s' a jour.", $centaureID));
                    continue;
                }

                $this->getLogger()->info(sprintf(" - Mise à jour pour '%s'.", $centaureID));
                $organisation = $repo->find($oscarId);
                if( !$organisation ){
                    $this->getLogger()->warn(sprintf(" - OscarID présent en cache mais pas en BDD pour '%s' !", $centaureID));
                    $organisation = new Organization();
                    $this->getEntityManager()->persist($organisation);
                }

            } else {
                $this->getLogger()->info(sprintf(" - Création de '%s'.", $centaureID));
                $organisation = new Organization();
                $this->getEntityManager()->persist($organisation);
            }

            $hydrator->hydrate($row, $organisation);

            $this->getEntityManager()->flush($organisation);
            $this->setCorrespondance('Participant', $organisation->getId(), $centaureID, serialize($row));

        }
        $this->getLogger()->notice(sprintf("%s traitement en %s secondes.", $proceded, (microtime(true)-$start)));

        //$this->importProjectPartners();
        //$this->importLaboratory();
        $this->importComposanteResponsable();
    }

    protected function importProjectPartners()
    {

        $c = $this->getConnexion();

        /** @var ContractTypeRepository $contractTypeRepo */
        $repoOrga       = $this->getEntityManager()->getRepository('Oscar\Entity\Organization');
        $repoContrat    = $this->getEntityManager()->getRepository('Oscar\Entity\ProjectGrant');

        $this->getLogger()->debug("Récupération des données depuis la base CENTAURE");

        $query = "SELECT C.NUM_CONVENTION, P.CONV_CLEUNIK, P.PART_CLEUNIK, P.PRINCIPAL_SECONDAIRE, P.DATE_DEBUT, P.DATE_FIN, P.DATE_CREE, P.DATE_MAJ, P.CODE_ROLE_PART FROM PARTICIPANT P LEFT JOIN CONVENTION C ON C.CONV_CLEUNIK = P.CONV_CLEUNIK";

        $roles = [
            'CONS' => Organization::ROLE_CONSEILLER,
            'COORD' => Organization::ROLE_COORDINATEUR,
            '0000000000' => null,
            'ND' => null,
            'SCIENT' => Organization::ROLE_SCIENTIFIQUE,
            'LICENCIE' => Organization::ROLE_LICENCIE,
            'CLIENT' => Organization::ROLE_CLIENT,
            'SCIENT_R' => Organization::ROLE_SCIENTIFIQUE_R,
            'CO_CONT' => Organization::ROLE_CO_CONTRACTANT,
            'FINAN' => Organization::ROLE_CO_FINANCEUR,
            'NA' => null,
        ];

        $this->getLogger()->info($query);
        $stid = oci_parse($c, $query);
        oci_execute($stid);
        $proceded = 0;
        while ($row = oci_fetch_array($stid, OCI_ASSOC + OCI_RETURN_NULLS)) {
            // Numéro de convention côté centaure
            $contractNumConvention = $this->cleanBullshitStr($row['NUM_CONVENTION']);

            // ID d'enregistrement côté centaure
            $organisationId = 'PART'.$this->cleanBullshitStr($row['PART_CLEUNIK']);

            $partnaireOscarId = $this->getOscarId('Participant', $organisationId);
            if( !$partnaireOscarId ){
                $this->getLogger()->warn(sprintf("Le partenaire '%s' n'est pas en cache (aurait dû être créé à l'étape précédente)...", $organisationId));
                continue;
            }


            $partnaireOscar = $this->getEntityManager()->getRepository('Oscar\Entity\Organization')->find($partnaireOscarId);
            if( !$partnaireOscar ){
                $this->getLogger()->warn(sprintf("Le partenaire '%s' est dans le cache mais absent d'oscar !", $partnaireOscarId));
                continue;
            }

            // Récupération du projet à partir du contrat
            $contrat = $repoContrat->getContractByNumConvention($contractNumConvention);

            if( !$contrat ){
                $this->getLogger()->warn(sprintf("Pas de contrat avec le N° de convention '%s'", $contractNumConvention));
                continue;
            }

            if( !$contrat->getProject() ){
                $this->getLogger()->warn(sprintf("Contrat '%s' sans projet", $contractNumConvention));
                continue;
            }

            // Récupération des infos
            $principal = $this->cleanBullshitStr($row['PRINCIPAL_SECONDAIRE']);
            $roleDb = $this->cleanBullshitStr($row['CODE_ROLE_PART']);
            $role = isset($roles[$roleDb]) ? $roles[$roleDb] : null;

            // $this->getLogger()->warn(sprintf("---\n### Projet :  '%s'\n### Organisme : '%s'\n - Role : '%s'\n - Principale : '%s'\n", $contrat->getProject(), $partnaireOscar, $role, $principal));



            if( !$contrat->getProject()->hasPartner($partnaireOscar, $role) ){
                $this->getLogger()->notice(sprintf("Ajout du partenaire '%s' avec le rôle '%s' au projet '%s'.", $partnaireOscar, $role, $contrat->getProject()));
                $projectPartner = new ProjectPartner();
                $this->getEntityManager()->persist($projectPartner);
                $projectPartner->setProject($contrat->getProject())
                    ->setOrganization($partnaireOscar)
                    ->setMain(($principal == '1'))
                    ->setRole($role);
                $this->getEntityManager()->flush($projectPartner);
                $this->getEntityManager()->refresh($contrat->getProject());

            } else {
                $this->getLogger()->debug(sprintf(" Déjà présent '%s' \t '%s'.", $partnaireOscar, $contrat->getProject()));
            }

            $proceded++;

        }
    }

    protected function importLaboratory()
    {

        $c = $this->getConnexion();

        /** @var ContractTypeRepository $contractTypeRepo */
        $repoOrga       = $this->getEntityManager()->getRepository('Oscar\Entity\Organization');
        $repoContrat    = $this->getEntityManager()->getRepository('Oscar\Entity\ProjectGrant');

        $this->getLogger()->debug("Récupération des données depuis la base CENTAURE");

        $query = "SELECT NUM_CONVENTION, LABO_CLEUNIK FROM CONVENTION";



        $this->getLogger()->info($query);
        $stid = oci_parse($c, $query);
        oci_execute($stid);
        $proceded = 0;
        while ($row = oci_fetch_array($stid, OCI_ASSOC + OCI_RETURN_NULLS)) {
            // Numéro de convention côté centaure
            $contractNumConvention = $this->cleanBullshitStr($row['NUM_CONVENTION']);

            // ID d'enregistrement côté centaure
            $laboratoireCleunik = 'LAB'.$this->cleanBullshitStr($row['LABO_CLEUNIK']);


            $organisationId = $this->getOscarId('Participant', $laboratoireCleunik);


            $this->getLogger()->debug(sprintf("Laboratoire centaure:'%s' > oscar:'%s'", $laboratoireCleunik, $organisationId));

            if( !$organisationId ){
                $this->getLogger()->warn(sprintf("Le laboratoire '%s' n'est pas en cache (aurait dû être créé à l'étape précédente)...", $organisationId));
                continue;
            }


            $organisation = $this->getEntityManager()->getRepository('Oscar\Entity\Organization')->find($organisationId);
            if( !$organisation ){
                $this->getLogger()->warn(sprintf("Le laboratoire '%s' est dans le cache mais absent d'oscar !", $organisationId));
                continue;
            }

            // Récupération du projet à partir du contrat
            $contrat = $repoContrat->findOneBy(array( 'centaureNumConvention' => $contractNumConvention ));

            if( !$contrat ){
                $this->getLogger()->warn(sprintf("Pas de contrat avec le N° de convention '%s'", $contractNumConvention));
                continue;
            }

            if( !$contrat->getProject() ){
                $this->getLogger()->warn(sprintf("Contrat '%s' sans projet", $contractNumConvention));
                continue;
            }

            // Récupération des infos
            $role = Organization::ROLE_LABORATORY;

            if( !$contrat->getProject()->hasPartner($organisation, $role) ){
                $this->getLogger()->notice(sprintf("Ajout du laboratoire '%s' au projet '%s'.", $organisation, $contrat->getProject()));
                $projectPartner = new ProjectPartner();
                $this->getEntityManager()->persist($projectPartner);
                $projectPartner->setProject($contrat->getProject())
                    ->setOrganization($organisation)
                    ->setMain(true)
                    ->setRole($role);
                $this->getEntityManager()->flush($projectPartner);
                $this->getEntityManager()->refresh($contrat->getProject());
            } else {
                $this->getLogger()->notice(sprintf("L'organisation '%s' \t '%s' est déjà associée au projet en tant que labo.", $organisation, $contrat->getProject()));
            }

            $proceded++;

        }
    }

    public function importComposanteResponsable()
    {
        $c = $this->getConnexion();

        /** @var ContractTypeRepository $contractTypeRepo */
        $repoOrga       = $this->getEntityManager()->getRepository('Oscar\Entity\Organization');
        $repoContrat    = $this->getEntityManager()->getRepository('Oscar\Entity\ProjectGrant');

        $this->getLogger()->debug("Récupération des données depuis la base CENTAURE");

        $query = "SELECT NUM_CONVENTION, G_N2_COD, G_N3_COD FROM CONVENTION";



        $this->getLogger()->info($query);
        $stid = oci_parse($c, $query);
        oci_execute($stid);
        $proceded = 0;
        while ($row = oci_fetch_array($stid, OCI_ASSOC + OCI_RETURN_NULLS)) {
            // Numéro de convention côté centaure
            $contractNumConvention = $this->cleanBullshitStr($row['NUM_CONVENTION']);

            // ID d'enregistrement côté centaure
            $composanteIdGestion = $this->cleanBullshitStr($row['G_N2_COD']);
            $composanteIdRespons = $this->cleanBullshitStr($row['G_N3_COD']);

            $contrat = $repoContrat->findOneBy(array( 'centaureNumConvention' => $contractNumConvention ));

            if( !$contrat ){
                $this->getLogger()->warn(sprintf("Pas de contrat avec le N° de convention '%s'", $contractNumConvention));
                continue;
            }

            if( !$contrat->getProject()  ){
                $this->getLogger()->warn(sprintf("Contrat '%s' sans projet", $contractNumConvention));
                continue;
            }

            // Ajout composant de gestion
            $organisation = $repoOrga->findOneBy(array('code' => $composanteIdGestion));
            if( !$organisation ){
                $this->getLogger()->warn(sprintf("L'organisation ayant pour code '%s' ,'existe pas.", $composanteIdGestion));
                continue;
            }


            // Récupération des infos
            $role = Organization::ROLE_COMPOSANTE_RESPONSABLE;


            if( !$contrat->getProject()->hasPartner($organisation, $role) ){
                $this->getLogger()->notice(sprintf("Ajout du laboratoire '%s' au projet '%s'.", $organisation, $contrat->getProject()));
                $projectPartner = new ProjectPartner();
                $this->getEntityManager()->persist($projectPartner);
                $projectPartner->setProject($contrat->getProject())
                    ->setOrganization($organisation)
                    ->setMain(true)
                    ->setRole($role);
                $this->getEntityManager()->flush($projectPartner);
                $this->getEntityManager()->refresh($contrat->getProject());
            } else {
                $this->getLogger()->notice(sprintf("L'organisation '%s' \t '%s' est déjà associée au projet en tant que labo.", $organisation, $contrat->getProject()));
            }
        }
    }
}