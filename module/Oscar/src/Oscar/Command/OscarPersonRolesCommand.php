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
use Oscar\Service\ConnectorService;
use Oscar\Service\OscarConfigurationService;
use Oscar\Service\OscarUserContext;
use Oscar\Service\PersonService;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class OscarPersonRolesCommand extends OscarCommandAbstract
{
    protected static $defaultName = 'person:roles';

    protected function configure()
    {
        $this
            ->setDescription("Affiche la liste des rôles endossés par la personne dans l'application.")
            ->addArgument('person', InputArgument::REQUIRED, 'ID ou LOGIN de la personne')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->addOutputStyle($output);

        /** @var OscarUserContext $oscaruserContext */
        $oscaruserContext = $this->getServicemanager()->get(OscarUserContext::class);

        /** @var PersonService $personService */
        $personService = $this->getServicemanager()->get(PersonService::class);

        $io = new SymfonyStyle($input, $output);

        $personArgument = $input->getArgument('person');
        $integerArgument = intval($personArgument);

        if( $integerArgument ){
            try {
                $person = $personService->getPerson($personArgument);
            } catch (\Exception $e) {
                $io->error("Impossible de charger la personne avec l'ID $personArgument");
                return;
            }
        }
        else {
            try {
                $person = $personService->getPersonByLdapLogin($personArgument);
            } catch (\Exception $e) {
                $io->error("Impossible de charger la personne avec l'identifiant de connexion '$personArgument'");
                return;
            }
        }

        $io->title("Rôles de $person dans les activités de recherche : ");
        $roles = $personService->getRolesPersonInActivities($person);
        if( count($roles) == 0 ){
            $io->writeln("<bold>$person</bold> n'a pas de rôle qualifié dans des Projets/Activités de recherche");
        } else {
            $io->writeln("<bold>$person</bold> est présent sur des projets/activités de recherche en tant que : ");
            foreach ($roles as $role) {
                $io->writeln(" - <bold>$role</bold>");
            }
        }

        $io->title("Rôles de $person dans les organisations : ");
        $roles = $personService->getRolesPersonInOrganizations($person);
        if( count($roles) == 0 ){
            $io->writeln("<bold>$person</bold> n'a pas de rôle qualifié dans des organisations");
        } else {
            $io->writeln("La personne <bold>$person</bold> est présente sur des organisations en tant que : ");
            foreach ($roles as $role) {
                $io->writeln(" - <bold>$role</bold>");
            }
        }

        $io->title("Rôles de $person dans l'application : ");
        $roles = $personService->getRolesPersonInApplication($person);
        if( count($roles) == 0 ){
            $io->writeln("<bold>$person</bold> n'a pas de rôle qualifié dans l'application");
        } else {
            $io->writeln("La personne <bold>$person</bold> est présente dans l'application en tant que : ");
            foreach ($roles as $role) {
                $io->writeln(" - <bold>$role</bold>");
            }
        }

    }
}