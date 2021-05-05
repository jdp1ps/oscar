<?php


namespace Oscar\Formatter\PCRU;


use Oscar\Entity\Activity;
use Oscar\Utils\StringUtils;

class ActivityToPCRUFormatter
{
    public function format( Activity $activity ) :array {
        $output = [];
        $output['Objet'] = StringUtils::truncate($activity->getLabel(), 1000);

        $output['CodeUniteLabintel'] = '';
        $output['SigleUnite'] = '';

        $output['NumContratTutelleGestionnaire'] = $activity->getOscarNum();
        $output['TypeContrat'] = ''; // TODO Type de contrat PCRU
        $output['Acronyme'] = StringUtils::truncate($activity->getAcronym(), 50);

        $output['ContratsAssocies'] = ''; // TODO Identifiants des tutelles de gestion
        $output['ResponsableScientifique'] = ''; // TODO Identifiants des tutelles de gestion
        $output['EmployeurResponsableScientifique'] = ''; // TODO
        $output['CoordinateurConsortium'] = ''; // TODO
        $output['Partenaires'] = ''; // TODO
        $output['PartenairePrincipal'] = ''; // TODO
        $output['IdPartenairePrincipal'] = ''; // TODO
        $output['SourceFinancement'] = ''; // TODO

        $output['LieuExecution'] = ''; // TODO

        $output['DateDerniereSignature'] = ''; // TODO

        // Temps
        $output['Duree'] = ''; // TODO
        $output['DateDebut'] = ''; // TODO
        $output['DateFin'] = ''; // TODO

        // Tunasse
        $output['MontantPercuUnite'] = ''; // TODO
        $output['CoutTotalEtude'] = ''; // TODO

        return $output;
    }
}