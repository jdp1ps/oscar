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
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Question\Question;
use Zend\Crypt\Password\Bcrypt;
use Zend\ServiceManager\ServiceManager;

class OscarAuthAddCommand extends OscarCommandAbstract
{
    protected static $defaultName = 'auth:add';

    protected function configure()
    {
        $this
            ->setDescription("Ajouter un utilisateur en mode interactif")
            ->setHelp("")
            //->addArgument('sort', InputArgument::OPTIONAL, "Champ à utiliser pour le trie")
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->addOutputStyle($output);

        $helper = $this->getHelper('question');

        $output->writeln("<title>### CREATION d'UN UTILISATEUR ###</title>");

        // -------------------------------------------------------------------------------------------------------------
        // LOGIN
        $question = new Question("Entrez un <bold>identifiant</bold> : ");
        $identifiant = $helper->ask($input, $output, $question);

        /** @var OscarUserContext $oscarUserContextService */
        $oscarUserContextService = $this->getServicemanager()->get(OscarUserContext::class);

        try {
            $exist = $oscarUserContextService->getAuthentificationByLogin($identifiant, false);
            if( $exist ){
                throw new \Exception("Un utilisateur utilise déjà cet identifiant !");
            }
        } catch ( \Exception $e ){
            $output->writeln("<error>Problème d'identifiant : ". $e->getMessage().".</error>");
            return;
        }

        // TODO Vérifier l'email saisi

        // -------------------------------------------------------------------------------------------------------------
        // EMAIL
        $question = new Question("Entrez l' <bold>adresse éléctronique</bold> : ");
        $email = $helper->ask($input, $output, $question);

        // DISPLAYNAME
        $question = new Question("Entrez le <bold>nom affiché</bold> : ");
        $displayName = $helper->ask($input, $output, $question);


        // -------------------------------------------------------------------------------------------------------------
        // PASSWORD
        $options = $this->getServicemanager()->get('zfcuser_module_options');
        $bcrypt = new Bcrypt();
        $bcrypt->setCost($options->getPasswordCost());
        $question = new Question("Entrez un <bold>mot de passe (>=8 caractères)</bold> : ");
        $question->setHidden(true);
        $question->setHiddenFallback(true);
        $password = $helper->ask($input, $output, $question);
        $passwordCrypted = $bcrypt->create($password);

        $output->writeln("<title>Création d'une authentification : </title>");
        $output->writeln(" - Identifiant : <bold>$identifiant</bold>");
        $output->writeln(" - Nom affiché : <bold>$displayName</bold>");
        $output->writeln(" - Courriel : <bold>$email</bold>");
        $output->writeln(" - Mot de passe : <bold>******</bold>");
        $helper = $this->getHelper('question');
        $question = new ConfirmationQuestion("Créer l'utilisateur (y|N) ?", false);

        if (!$helper->ask($input, $output, $question)) {
            return;
        }

        try {
            $auth = new Authentification();
            $auth->setPassword($passwordCrypted);
            $auth->setDisplayName($displayName);
            $auth->setUsername($identifiant);
            $auth->setEmail($email);
            $oscarUserContextService->getEntityManager()->persist($auth);
            $oscarUserContextService->getEntityManager()->flush();
            $output->writeln("<success>L'utilisateur $identifiant a bien été créé</success>");
        } catch (\Exception $e ){
            $output->writeln("<error>Impossible de créé $identifiant : " . $e->getMessage());
        }


        /****/
    }
}