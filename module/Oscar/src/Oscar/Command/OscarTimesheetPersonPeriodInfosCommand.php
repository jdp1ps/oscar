<?php
/**
 * Created by PhpStorm.
 * User: bouvry
 * Date: 04/10/19
 * Time: 11:49
 */

namespace Oscar\Command;


use Moment\Moment;
use Oscar\Entity\Authentification;
use Oscar\Entity\LogActivity;
use Oscar\Entity\Person;
use Oscar\Entity\Role;
use Oscar\Entity\WorkPackage;
use Oscar\Entity\WorkPackagePerson;
use Oscar\Service\ConnectorService;
use Oscar\Service\OscarConfigurationService;
use Oscar\Service\OscarUserContext;
use Oscar\Service\PersonService;
use Oscar\Service\TimesheetService;
use Oscar\Utils\DateTimeUtils;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Zend\Validator\Date;

class OscarTimesheetPersonPeriodInfosCommand extends OscarCommandAbstract
{
    protected static $defaultName = 'timesheets:person-period-infos';

    const ARG_DECLARER      = "declarer";
    const ARG_PERIOD        = "period";

    protected function configure()
    {
        $this
            ->setDescription("Fourni des informations sur les informations de temps pour un déclarant à la période donnée")
            ->addArgument(self::ARG_DECLARER, InputArgument::REQUIRED, "login du déclarant")
            ->addArgument(self::ARG_PERIOD, InputArgument::REQUIRED, "periode sous la forme YYYY-MM")
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        /// OPTIONS and PARAMETERS
        $declarerLogin = $input->getArgument(self::ARG_DECLARER);
        $periodStr = $input->getArgument(self::ARG_PERIOD);
        $serialize = false;

        $person = $this->getPersonService()->getPersonByLdapLogin($declarerLogin);

        //$io->title("Période '$periodStr' pour '$person' : ");


        $datas = $this->getTimesheetService()->getPersonTimesheetsDatas($person, $periodStr);

        if( $serialize ){
            $serialized = serialize($datas);
            die($serialized);
        }

        $io->title("Période '$periodStr' pour '$person' : ");
        dump($datas);

        $headers = [""];
        $rows = [];
        $totalDays = $datas['totalDays'];
        foreach ($datas['daysInfos'] as $day=>$dayData) {
            $headers[] = sprintf("%s%s", substr($dayData['label'], 0, 1), $day);
        }
        $headers[] = 'Total';

        foreach ($datas['declarations'] as $itemKey=>$itemData) {
            $rows[] = ["+++ $itemKey"];
            foreach ($itemData as $subItem=>$subItemDatas) {

                $rows[] = ['+' . $subItemDatas['acronym']];

                foreach ($subItemDatas['subgroup'] as $subGroupKey=>$subGroupdatas) {
                    $row = [substr($subGroupdatas['label'],0,7)];
                    for($i=1; $i<=$totalDays; $i++){
                        $totalDay = 0;
                        if( array_key_exists($i, $subGroupdatas['days']) )
                            $totalDay = floatval($subGroupdatas   ['days'][$i]);
                        if( $datas['daysInfos'][$i]['locked'] )
                            $totalDay = $totalDay == 0 ? "." : "!".$totalDay;
                        $row[] = $totalDay;
                    }
                    $row[] = $subGroupdatas['total'];
                    $rows[] = $row;
                    $rows[] = ['---'];
                }
                $rows[] = [" = " . $subItemDatas['total']];
            }
        }

        $rows[] = ["---"];
        $row = ["Actif"];
        for($i=1; $i<=$totalDays; $i++){
            $totalDay = 0;
            if( array_key_exists($i, $datas['active']['days']) )
                $totalDay = floatval($datas['active']['days'][$i]);
            if( $datas['daysInfos'][$i]['locked'] )
                $totalDay = $totalDay == 0 ? "." : "!".$totalDay;
            $row[] = $totalDay;
        }
        $row[] = $datas['active']['total'];
        $rows[] = $row;

        $io->table($headers, $rows);
    }

    /**
     * @return TimesheetService
     */
    protected function getTimesheetService(){
        return $this->getServicemanager()->get(TimesheetService::class);
    }

    /**
     * @return PersonService
     */
    protected function getPersonService(){
        return $this->getServicemanager()->get(PersonService::class);
    }

    public function declarerPeriod( InputInterface $input, OutputInterface $output, $declarerId, $period ){
        // TODO Faire un rendu text des déclarations mensuelles des déclarants
        $datas = $this->getTimesheetService()->getTimesheetDatasPersonPeriod($this->getPersonService()->getPerson($declarerId), $period);
        echo "Non-disponible";
    }

    public function declarer( InputInterface $input, OutputInterface $output, $declarerId ){

        $io = new SymfonyStyle($input, $output);

        try {
            $declarer = $this->getPersonService()->getPerson($declarerId);

            $io->title("Système de relance pour $declarer");
            $periods = $this->getTimesheetService()->getPersonRecallDeclaration($declarer);

            $io->table(["Période", "Durée", "état"], $periods);

        } catch (\Exception $e) {
            $io->error('Impossible de charger le déclarant : ' . $e->getMessage());
            exit(0);
        }
    }

    public function declarersList( InputInterface $input, OutputInterface $output ){
        $io = new SymfonyStyle($input, $output);
        $io->title("Lite des déclarants");
        try {
            $declarants = $this->getTimesheetService()->getDeclarers();
            $out = [];
            /** @var Person $declarer */
            foreach ($declarants['persons'] as $personId=>$datas) {
                $out[] = [$personId, $datas['displayname'], $datas['affectation'], count($datas['declarations'])];
            }
            $headers = ['ID', 'Déclarant', 'Affectation', 'Déclaration(s)'];
            $io->table($headers, $out);

            $io->comment("Entrez la commande '".self::getName()." <ID> [PERIOD]' pour afficher les détails");
        } catch (\Exception $e) {
            $io->error($e->getMessage());
        }
    }
}