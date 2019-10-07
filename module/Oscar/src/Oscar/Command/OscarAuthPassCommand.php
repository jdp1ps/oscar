<?php
/**
 * Created by PhpStorm.
 * User: bouvry
 * Date: 04/10/19
 * Time: 11:49
 */

namespace Oscar\Command;


use Oscar\Entity\Authentification;
use Oscar\Service\OscarConfigurationService;
use Oscar\Service\OscarUserContext;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Style\SymfonyStyle;
use Zend\Crypt\Password\Bcrypt;
use Zend\ServiceManager\ServiceManager;

class OscarAuthPassCommand extends OscarCommandAbstract
{
    protected static $defaultName = 'oscar:auth:pass';

    protected function configure()
    {
        $this
            ->setDescription("Modification du mot de passe")
            ->addOption('login', 'l', InputOption::VALUE_OPTIONAL, 'Identifiant', '')
            ->addOption('ldap', 'p', InputOption::VALUE_NONE, "Définit le mot de passe sur la source LDAP")
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->addOutputStyle($output);

        $io = new SymfonyStyle($input, $output);

        $io->title("Modification du mot de passe");

        $helper = $this->getHelper('question');
        $login = $input->getOption('login');
        $ldap = $input->getOption('ldap');

        if( !$login ){
            $question = new Question("Entrez l'<bold>identifiant</bold> : ");
            $login = $helper->ask($input, $output, $question);
        }

        /** @var OscarUserContext $oscarUserContextService */
        $oscarUserContextService = $this->getServicemanager()->get(OscarUserContext::class);

        try {
            $authentification = $oscarUserContextService->getAuthentificationByLogin($login, true);
            $output->writeln("Modification du mot de passe pour <bold>$authentification</bold>.");
        } catch ( \Exception $e ){
            $output->writeln("<error>Impossible de charger l'authentification : ". $e->getMessage().".</error>");
            return;
        }

        if( $ldap ){
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

        $question = new ConfirmationQuestion("Modifier le mot de passe de <bold>$authentification</bold> (y|N) ?", false);

        if (!$helper->ask($input, $output, $question)) {
            return;
        }

        try {
            $authentification->setPassword($pass);
            $oscarUserContextService->getEntityManager()->persist($authentification);
            $oscarUserContextService->getEntityManager()->flush();
            $io->success("Le mot de passe de $authentification a bien été modifié.</success>");
        } catch (\Exception $e ){
            $output->writeln("<error>Impossible de modifier le mot de passe de $authentification : " . $e->getMessage());
        }





//
//        // -------------------------------------------------------------------------------------------------------------
//        // LOGIN
//        $question = new Question("Entrez un <bold>identifiant</bold> : ");
//        $identifiant = $helper->ask($input, $output, $question);
//
//        /** @var OscarUserContext $oscarUserContextService */
//        $oscarUserContextService = $this->getServicemanager()->get(OscarUserContext::class);
//
//        try {
//            $exist = $oscarUserContextService->getAuthentificationByLogin($identifiant, false);
//            if( $exist ){
//                throw new \Exception("Un utilisateur utilise déjà cet identifiant !");
//            }
//        } catch ( \Exception $e ){
//            $output->writeln("<error>Problème d'identifiant : ". $e->getMessage().".</error>");
//            return;
//        }
//
//        // TODO Vérifier l'email saisi
//
//        // -------------------------------------------------------------------------------------------------------------
//        // EMAIL
//        $question = new Question("Entrez l' <bold>adresse éléctronique</bold> : ");
//        $email = $helper->ask($input, $output, $question);
//
//        // DISPLAYNAME
//        $question = new Question("Entrez le <bold>nom affiché</bold> : ");
//        $displayName = $helper->ask($input, $output, $question);
//
//
//        // -------------------------------------------------------------------------------------------------------------
//        // PASSWORD
//        $options = $this->getServicemanager()->get('zfcuser_module_options');
//        $bcrypt = new Bcrypt();
//        $bcrypt->setCost($options->getPasswordCost());
//        $question = new Question("Entrez un <bold>mot de passe (>=8 caractères)</bold> : ");
//        $question->setHidden(true);
//        $question->setHiddenFallback(true);
//        $password = $helper->ask($input, $output, $question);
//        $passwordCrypted = $bcrypt->create($password);
//
//        $output->writeln("<title>Création d'une authentification : </title>");
//        $output->writeln(" - Identifiant : <bold>$identifiant</bold>");
//        $output->writeln(" - Nom affiché : <bold>$displayName</bold>");
//        $output->writeln(" - Courriel : <bold>$email</bold>");
//        $output->writeln(" - Mot de passe : <bold>******</bold>");
//        $helper = $this->getHelper('question');
//        $question = new ConfirmationQuestion("Créer l'utilisateur (y|N) ?", false);
//
//        if (!$helper->ask($input, $output, $question)) {
//            return;
//        }
//
//        try {
//            $auth = new Authentification();
//            $auth->setPassword($passwordCrypted);
//            $auth->setDisplayName($displayName);
//            $auth->setUsername($identifiant);
//            $auth->setEmail($email);
//            $oscarUserContextService->getEntityManager()->persist($auth);
//            $oscarUserContextService->getEntityManager()->flush();
//            $output->writeln("<success>L'utilisateur $identifiant a bien été créé</success>");
//        } catch (\Exception $e ){
//            $output->writeln("<error>Impossible de créé $identifiant : " . $e->getMessage());
//        }


        /****/
    }
}