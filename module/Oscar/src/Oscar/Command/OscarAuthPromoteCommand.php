<?php
/**
 * Created by PhpStorm.
 * User: bouvry
 * Date: 04/10/19
 * Time: 11:49
 */

namespace Oscar\Command;


use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Query\ResultSetMapping;
use Oscar\Entity\Role;
use Oscar\Service\OscarUserContext;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Style\SymfonyStyle;

class OscarAuthPromoteCommand extends OscarCommandAbstract
{
    protected static $defaultName = 'auth:promote';

    protected function configure()
    {
        $this
            ->setDescription("Permet d'ajouter un rôle applicatif à une authentification")
            ->addOption('login', 'l', InputOption::VALUE_OPTIONAL, 'Identifiant du compte', null)
            ->addOption('role', 'r', InputOption::VALUE_OPTIONAL, 'Rôle à attribuer', null)
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->addOutputStyle($output);
        $io = new SymfonyStyle($input, $output);
        $io->title("Affection de rôle à un utilisateur");
        $helper = $this->getHelper('question');

        /** @var OscarUserContext $oscarUserContextService */
        $oscarUserContextService = $this->getServicemanager()->get(OscarUserContext::class);

        /** @var EntityManager $em */
        $em = $oscarUserContextService->getEntityManager();

        // -------------------------------------------------------------------------------------------------------------
        // LOGIN
        $io->section("Identifiant : ");
        $login = trim($input->getOption('login'));
        if( !$login ){
            $login = $io->ask("Entrez l'identifiant de connexion : ");
        }

        try {
            $authentification = $oscarUserContextService->getAuthentificationByLogin($login, true);
            $existRoles = [];
            foreach ($authentification->getRoles() as $r) {
                $existRoles[] = $r;
            }
            $io->writeln("Rôle déjà utilisés pour $authentification : ");
            $io->listing($existRoles);

        } catch ( \Exception $e ){
            $io->error(sprintf("Problème d'identifiant (%s) : %s", $login, $e->getMessage()));
            return;
        }

        // -------------------------------------------------------------------------------------------------------------
        // RÔLE
        $io->section("Rôle : ");
        $roleId = $input->getOption('role');

        if (!$roleId) {
            $io->writeln("Liste des rôles : ");
            $roles = [];
            /** @var Role $role */
            foreach ($em->getRepository(Role::class)->findBy([], ['roleId' => 'ASC']) as $role) {
                $roles[] = $role->getRoleId();
            }
            $roleIdSelected = $io->choice("Choississez un rôle : ", $roles);
        }

        $role = $oscarUserContextService->getRoleByRoleId($roleIdSelected);
        if (!$role) {
            $io->error("Impossible de charge ce rôle.");
            return;
        }

        if( in_array($role->getRoleId(), $existRoles) ){
            $io->error("$authentification as déjà le rôle '$role'.");
            return;
        }

        $helper = $this->getHelper('question');
        $question = new ConfirmationQuestion(sprintf("Ajouter le rôle %s à %s(%s) (y|N) ?", $role, $authentification, $login), false);

        if (!$helper->ask($input, $output, $question)) {
            return;
        }

        $userId = $authentification->getId();
        $roleId = $role->getId();

        try {
            //$io->writeln("insert $userId, $roleId");
            $query = $em->createNativeQuery("INSERT INTO authentification_role VALUES($userId, $roleId)",
                new ResultSetMapping());
            $query->execute();
        } catch (UniqueConstraintViolationException $e) {
            $io->error(sprintf("Le compte '%s' a déjà ce rôle.", $authentification));

            return;
        }

    }
}