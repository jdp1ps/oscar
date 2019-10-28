<?php
/**
 * Created by PhpStorm.
 * User: bouvry
 * Date: 04/10/19
 * Time: 11:49
 */

namespace Oscar\Command;


use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\SchemaValidator;
use Moment\Moment;
use Oscar\Entity\Authentification;
use Oscar\Entity\LogActivity;
use Oscar\Entity\Person;
use Oscar\Entity\Role;
use Oscar\OscarVersion;
use Oscar\Service\ConfigurationParser;
use Oscar\Service\ConnectorService;
use Oscar\Service\MailingService;
use Oscar\Service\OrganizationService;
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
use Symfony\Component\Yaml\Parser;
use UnicaenApp\Service\Mailer\MailerService;
use Zend\Config\Reader\Yaml;

class OscarCheckMailerCommand extends OscarCommandAbstract
{
    protected static $defaultName = 'oscar:check:mailer';

    protected function configure()
    {
        $this
            ->setDescription("Vérification du mailer")
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->addOutputStyle($output);

        /** @var OscarUserContext $oscaruserContext */
        $oscaruserContext = $this->getServicemanager()->get(OscarUserContext::class);

        $io = new SymfonyStyle($input, $output);

        $io->title("Vérification de la distribution des mails");

        try {
            /** @var MailingService $mailer */
            $mailer = $this->getServicemanager()->get(MailingService::class);

            /** @var OscarConfigurationService $oscarConfigurationService */
            $oscarConfigurationService = $this->getServicemanager()->get(OscarConfigurationService::class);

            $administrators = $oscarConfigurationService->getConfiguration('mailer.administrators');
            $mails = implode(',', $administrators);

            $confirm = $io->confirm("Envoi effectif vers " . $mails . " ?");

            if( $confirm ){
                try {
                    $message = $mailer->newMessage("Test de mail", [
                        'body' => "Si vous lisez ce message, c'est que la configuration de l'envoi de courriel fonctionne."
                        // Ou que vous êtes entrains de lire le code source
                    ])->setTo($oscarConfigurationService->getConfiguration('mailer.administrators'));
                    $mailer->send($message, true);
                    $io->section("Oscar a bien envoyé le mail de test");
                } catch (\Exception $err) {
                    $io->error($err->getMessage() . "\n" . $err->getTraceAsString());
                }
            }

        } catch (\Exception $e) {
            $io->error($e->getMessage());
            $io->error($e->getTraceAsString());
        }

    }
}