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
use Oscar\Service\OscarUserContext;
use Oscar\Service\PersonService;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class OscarAuthInfoCommand extends OscarCommandAbstract
{
    protected static $defaultName = 'auth:info';

    protected function configure()
    {
        $this
            ->setDescription("Affiche la liste des authentifications")
            ->setProcessTitle("TEST 1")
            ->addArgument("login", InputArgument::REQUIRED, "Identifiant de connexion")
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->addOutputStyle($output);

        /** @var OscarUserContext $oscaruserContext */
        $oscaruserContext = $this->getServicemanager()->get(OscarUserContext::class);

        $io = new SymfonyStyle($input, $output);

        $login = $input->getArgument("login");
        //
        try {
            $authentification = $oscaruserContext->getAuthentificationByLogin($login, true);
        } catch (\Exception $e){
            $io->error($e->getMessage());
            return;
        }

        //////////////////////////////////////////////////////////////////////////////////:
        $io->title("Information sur le compte '$login'");

        $io->section("Informations sur l'authentification");
        $io->writeln(sprintf("ID (BDD) : <bold>%s</bold>", $authentification->getId()));
        $io->writeln(sprintf("Nom complet : <bold>%s</bold>", $authentification->getDisplayName()));
        $io->writeln(sprintf("Identification : <bold>%s</bold>", $authentification->getUsername()));
        $io->writeln(sprintf("Email : <bold>%s</bold>", $authentification->getEmail()));


        $io->section("Dernière activités");
        $lastedConnection = "Jamais";
        if( $authentification->getDateLogin() ){
            $moment = new Moment($authentification->getDateLogin()->format('Y-m-d H:i:s'));
            $lastedConnection = $moment->fromNow()->getRelative();
        }
        $io->writeln("Dernière connexion : <bold>$lastedConnection</bold>");
        $logs = $oscaruserContext->getLogsAuthentification($authentification);
        /** @var LogActivity $log */
        foreach ($logs as $log){
            $io->writeln("<bold>".$log->getDateCreated()->format('Y-m-d H:i:s')."</bold> " . $log->getMessage());
        }

        $io->section("Rôle(s) applicatif : ");
        if( $authentification->getRoles() ){
            $io->writeln(sprintf("%s role(s) applicatif", count($authentification->getRoles())));

            /** @var Role $role */
            foreach ($authentification->getRoles() as $role) {
                $io->writeln(sprintf(" - %s", $role));
            }
        }


        /** @var PersonService $personService */
        $personService = $this->getServicemanager()->get(PersonService::class);

        /** @var Person $person */
        $person = $personService->getPersonByLdapLogin($login);

        $io->section("Personne associée");

        if( !$person ){
            $io->warning("Aucune personne associée à cette authentification");
        } else {
            $io->writeln(sprintf("Fiche personne N°%s", $person->getId()));
        }

    }
}