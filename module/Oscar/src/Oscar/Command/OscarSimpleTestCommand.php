<?php
/**
 * Created by PhpStorm.
 * User: bouvry
 * Date: 04/10/19
 * Time: 11:49
 */

namespace Oscar\Command;


use Oscar\Entity\Authentification;
use Oscar\Service\OscarUserContext;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Question\Question;
use Laminas\Crypt\Password\Bcrypt;
use UnicaenSignature\Service\SignatureService;

class OscarSimpleTestCommand extends OscarCommandAbstract
{
    protected static $defaultName = 'test:dev';

    protected function configure()
    {
        $this
            ->setDescription("Utilisé pour les tests de développements")
            ->setHelp("")//->addArgument('sort', InputArgument::OPTIONAL, "Champ à utiliser pour le trie")
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->addOutputStyle($output);

        /** @var SignatureService $signatureService */
        $signatureService = $this->getServicemanager()->get(SignatureService::class);

        $signatureService->testTriggerEvent();
        return self::SUCCESS;
    }
}