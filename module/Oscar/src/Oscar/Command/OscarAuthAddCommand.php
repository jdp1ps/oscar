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
use Symfony\Component\Console\Question\Question;
use Zend\ServiceManager\ServiceManager;

class OscarAuthAddCommand extends OscarCommandAbstract
{
    protected static $defaultName = 'oscar:auth:add';

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

//        $question = new ChoiceQuestion(
//            'Entrez un identifiant : ',
//            ['red', 'green', 'blue'],
//            '0,1'
//        );
        $question = new Question("Entrez l'identifiant : ");
        $identifiant = $helper->ask($input, $output, $question);
        $output->writeln("Saisie : " . $identifiant);

        $question = new Question("Entrez le mot de passe : ");
        $question->setHidden(false);
        $question->setHiddenFallback(false);
        $question->setMaxAttempts(2);
        $password = $helper->ask($input, $output, $question);
        $output->writeln("Password : " . $password);





    }
}