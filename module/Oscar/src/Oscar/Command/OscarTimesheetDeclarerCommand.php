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

class OscarTimesheetDeclarerCommand extends OscarCommandAbstract
{
    protected static $defaultName = 'timesheets:declarer';
    const ARG_DECLARANT = "declarant";

    protected function configure()
    {
        $this
            ->setDescription("Affiche la liste des déclarants")
            ->addArgument(self::ARG_DECLARANT, InputArgument::REQUIRED, "Identifiant du déclarant", null)
            ->addOption('period', 'p', InputOption::VALUE_REQUIRED, "Période sous la forme ANNEE-MOIS")
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

        $io = new SymfonyStyle($input, $output);

        // détails
        $declarantId = $input->getArgument(self::ARG_DECLARANT);

        if( $declarantId ){
            $person = $personService->getPerson($declarantId);
            $io->title((string)$person);
            $period = $input->getOption('period');
            try {
                if ($timesheetService->personIsDeclarantPeriod($declarantId, $period) ){
                    $state = $timesheetService->personDeclarationState($declarantId, $period);
                    //var_dump($state);
                    $io->success("État : $state");
                } else {
                    $io->warning(sprintf("%s n'est pas déclarant pour cette période.", $person));
                }
            } catch (\Exception $e) {
                $io->error($e->getMessage());
                exit(1);
            }
        }
    }
}