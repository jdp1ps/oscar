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
use Oscar\Entity\SpentLine;
use Oscar\Service\ConnectorService;
use Oscar\Service\OscarConfigurationService;
use Oscar\Service\OscarUserContext;
use Oscar\Service\PersonService;
use Oscar\Service\SpentService;
use Oscar\Service\TimesheetService;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class OscarSpentAccountListCommand extends OscarCommandAbstract
{
    protected static $defaultName = 'spent:accounts';
    const pfi = 'PFI';

    protected function configure()
    {
        $this
            ->setDescription("Affiche la liste des comptes utilisés dans Oscar par masse")
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->addOutputStyle($output);

        $io = new SymfonyStyle($input, $output);

        $io->title("Comptes utilisés : ");


        /** @var OscarConfigurationService $oscarConfig */
        $oscarConfig = $this->getServicemanager()->get(OscarConfigurationService::class);

        /** @var SpentService $spentService */
        $spentService = $this->getServicemanager()->get(SpentService::class);

        try {
            $masses = $spentService->getMasses();
            // var_dump($masses);



            $accounts = $spentService->getAccountsUsed();
            $table = [];

            foreach ($accounts as $masseCode=>$masse) {

                foreach ($masse['comptes'] as $compte) {
                    $table[] = [
                        $masseCode, $masse['label'], $masse['inherit'], $compte['code'], $compte['label']
                    ];
                }
            }
            $io->table(['Masse(cd)','Masse', 'Hérité', 'Compte', 'Intitulé'], $table);

        } catch (\Exception $e) {
            $io->error($e->getMessage());
        }
    }
}