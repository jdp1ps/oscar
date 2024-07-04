<?php
/**
 * Created by PhpStorm.
 * User: bouvry
 * Date: 04/10/19
 * Time: 11:49
 */

namespace Oscar\Command;


use Oscar\Service\OscarUserContext;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Style\SymfonyStyle;
use Laminas\Crypt\Password\Bcrypt;

class OscarAuthPassCommand extends OscarCommandAbstract
{
    protected static $defaultName = 'auth:pass';

    protected function configure()
    {
        $this
            ->setDescription("Modification du mot de passe")
            ->addOption('login', 'l', InputOption::VALUE_OPTIONAL, 'Identifiant', '')
            ->addOption('ldap', 'p', InputOption::VALUE_NONE, "Définit le mot de passe sur la source LDAP");
    }

    protected function execute(InputInterface $input, OutputInterface $output) :int
    {
        $this->addOutputStyle($output);

        $io = new SymfonyStyle($input, $output);

        $io->title("Modification du mot de passe");

        $helper = $this->getHelper('question');
        $login = $input->getOption('login');
        $ldap = $input->getOption('ldap');

        if (!$login) {
            $question = new Question("Entrez l'<bold>identifiant</bold> : ");
            $login = $helper->ask($input, $output, $question);
        }

        /** @var OscarUserContext $oscarUserContextService */
        $oscarUserContextService = $this->getServicemanager()->get(OscarUserContext::class);

        try {
            $authentification = $oscarUserContextService->getAuthentificationByLogin($login, true);
            $output->writeln("Modification du mot de passe pour <bold>$authentification</bold>.");
        } catch (\Exception $e) {
            $output->writeln("<error>Impossible de charger l'authentification : " . $e->getMessage() . ".</error>");
            return self::FAILURE;
        }

        if ($ldap) {
            $pass = 'ldap';
            $output->writeln("Le mot de passe sera <bold>issue du LDAP</bold>.");
        } else {
            // PASSWORD
            $options = $this->getServicemanager()->get('zfcuser_module_options');
            $bcrypt = new Bcrypt();
            $bcrypt->setCost($options->getPasswordCost());
            $question = new Question("Entrez un <bold>mot de passe (>=8 caractères)</bold> : ");
            $question->setHidden(true);
            $question->setHiddenFallback(true);
            $password = $helper->ask($input, $output, $question);
            $pass = $bcrypt->create($password);
        }

        $question = new ConfirmationQuestion(
            "Modifier le mot de passe de <bold>$authentification</bold> (y|N) ?", false
        );

        if (!$helper->ask($input, $output, $question)) {
            return self::FAILURE;
        }

        try {
            $authentification->setPassword($pass);
            $oscarUserContextService->getEntityManager()->persist($authentification);
            $oscarUserContextService->getEntityManager()->flush();
            $io->success("Le mot de passe de $authentification a bien été modifié.");
            return self::SUCCESS;
        } catch (\Exception $e) {
            $output->writeln(
                "<error>Impossible de modifier le mot de passe de $authentification : " . $e->getMessage()
            );
        }
    }
}