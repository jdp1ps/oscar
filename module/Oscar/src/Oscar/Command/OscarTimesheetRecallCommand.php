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

class OscarTimesheetRecallCommand extends OscarCommandAbstract
{
    protected static $defaultName = 'timesheets:declarers-recall';

    const OPT_FORCE         = "force";
    const OPT_DECLARER      = "declarer";

    protected function configure()
    {
        $this
            ->setDescription("Système de relance des déclarants")
            ->addOption(self::OPT_FORCE, 'f', InputOption::VALUE_NONE, "Forcer le mode non-interactif")
            ->addOption(self::OPT_DECLARER, 'd', InputOption::VALUE_OPTIONAL, "Identifiant du déclarant");
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->addOutputStyle($output);

        /** @var OscarUserContext $oscaruserContext */
        $oscaruserContext = $this->getServicemanager()->get(OscarUserContext::class);

        /** @var PersonService $personService */
        $personService = $this->getServicemanager()->get(PersonService::class);

        /** @var TimesheetService $timesheetService */
        $timesheetService = $this->getServicemanager()->get(TimesheetService::class);

        /// OPTIONS and PARAMETERS
        $force = $input->getOption(self::OPT_FORCE);
        $declarerId = $input->getOption(self::OPT_DECLARER);

        $io = new SymfonyStyle($input, $output);

        // Récupération du déclarant
        if( $declarerId ){
            try {
                $declarer = $personService->getPerson($declarerId);

                $io->title("Système de relance pour $declarer");
                $periods = $timesheetService->getPersonRecallDeclaration($declarer);

                $io->table(["Période", "Durée", "état"], $periods);

            } catch (\Exception $e) {
                $io->error('Impossible de charger le déclarant : ' . $e->getMessage());
                exit(0);
            }
        } else {
            $io->title("Lite des déclarants");

            try {
                $declarants = $timesheetService->getDeclarers();
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
}