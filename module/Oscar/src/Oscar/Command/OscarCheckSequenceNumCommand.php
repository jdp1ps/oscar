<?php
/**
 * Created by PhpStorm.
 * User: bouvry
 * Date: 04/10/19
 * Time: 11:49
 */

namespace Oscar\Command;


use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Query\ResultSetMapping;
use Doctrine\ORM\Tools\SchemaValidator;
use Moment\Moment;
use Oscar\Entity\Authentification;
use Oscar\Entity\LogActivity;
use Oscar\Entity\Person;
use Oscar\Entity\Role;
use Oscar\OscarVersion;
use Oscar\Service\ConfigurationParser;
use Oscar\Service\ConnectorService;
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
use Zend\Config\Reader\Yaml;

class OscarCheckSequenceNumCommand extends OscarCommandAbstract
{
    protected static $defaultName = 'check:sequences-num';

    protected function configure()
    {
        $this
            ->setDescription("Mise à jour automatique de l'incrementation des séquences d'IDs pour les tables")
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->addOutputStyle($output);

        $io = new SymfonyStyle($input, $output);

        $io->title("Mise à jour des séquences pour le calcule des IDS");

        $sequences = [
            "activity",
            "activitydate",
            "activityorganization",
            "activitypayment",
            "activityperson",
            "activitytype",
            "administrativedocument",
            "authentification",
            "contractdocument",
            "currency",
            "datetype",
            "discipline",
            "logactivity",
            "notification",
            "notificationperson",
            "organization",
            "organizationperson",
            "organizationrole",
            "privilege",
            "project",
            "person",
            "tva"
        ];

        $entityManager = $this->getServicemanager()->get(EntityManager::class);

        foreach ($sequences as $sequence) {
            $result = new ResultSetMapping();
            $msg = sprintf(" # MaJ sequence <bold>%s</bold>", $sequence);

            $query = "select setval('" . $sequence . "_id_seq',(SELECT COALESCE((SELECT MAX(id)+1 FROM " . $sequence . "), 1)), false);";
            $msg .= sprintf(" (QUERY : <bold>%s</bold>)", $query);

            try {
                $entityManager->createNativeQuery($query, $result)->execute();
                $io->writeln("<green>   OK</green> $msg");
            } catch (\Exception $e) {
                $io->writeln("<error>ERROR</error> $msg");
                $io->error(sprintf("<bold>[] %s</bold> : %s", $e->getCode(), $e->getMessage(), $e->getTraceAsString()));
            }
        }
    }
}