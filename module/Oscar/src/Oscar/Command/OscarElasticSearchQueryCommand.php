<?php
/**
 * Created by PhpStorm.
 * User: bouvry
 * Date: 04/10/19
 * Time: 11:49
 */

namespace Oscar\Command;


use Elasticsearch\ClientBuilder;
use Moment\Moment;
use Oscar\Entity\Authentification;
use Oscar\Entity\LogActivity;
use Oscar\Entity\Person;
use Oscar\Entity\Role;
use Oscar\Service\ConnectorService;
use Oscar\Service\OrganizationService;
use Oscar\Service\OscarConfigurationService;
use Oscar\Service\OscarUserContext;
use Oscar\Service\PersonService;
use Oscar\Service\ProjectGrantService;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class OscarElasticSearchQueryCommand extends OscarCommandAbstract
{
    // TODO Utiliser la configuration OSCAR.
    // TODO Chercher dans les différents moteurs de recherche.

    protected static $defaultName = 'elasticsearch:query';

    protected function configure()
    {
        $this
            ->setDescription("Recherche dans le moteur de recherche avec affichage des scores")
            ->addArgument('search', InputArgument::REQUIRED, 'Expression de recherche')
            ->addOption('json', 'j', InputOption::VALUE_NONE,
                        'La chaîne de requête est un JSON à envoyer directement à lAPI')
            ->addOption('engine', 'e', InputOption::VALUE_OPTIONAL,
                        'La chaîne de requête est un JSON à envoyer directement à lAPI', 'activity')
            ->addOption('limit', 'l', InputOption::VALUE_OPTIONAL,
                        'Limite de résultat (int)', 5)
        ;
    }

    /**
     * @return ProjectGrantService
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    protected function getProjectGrantService()
    {
        return $this->getServicemanager()->get(ProjectGrantService::class);
    }

    /**
     * @return \Oscar\Strategy\Search\IPersonISearchStrategy
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    protected function getPersonSearchStrategy()
    {
        /** @var PersonService $ps */
        $ps = $this->getServicemanager()->get(PersonService::class);

        return $ps->getSearchEngineStrategy();
    }

    /**
     * @return \Oscar\Strategy\Search\IOrganizationSearchStrategy
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    protected function getOrganizationSearchStrategy()
    {
        /** @var OrganizationService $ps */
        $ps = $this->getServicemanager()->get(OrganizationService::class);

        return $ps->getSearchEngineStrategy();
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    protected function execute(InputInterface $input, OutputInterface $output) :int
    {
        $this->addOutputStyle($output);

        $io = new SymfonyStyle($input, $output);

        $search = $input->getArgument("search");
        $engine = $input->getOption("engine");
        $limit = (int)$input->getOption("limit");

        $json = $input->getOption("json") !== false;



        $io->title("Recherche '<bold>$search</bold>' dans l'index '<bold>$engine</bold>' de recherche (LIMIT <bold>$limit</bold>): ");

        switch ($engine) {
            case 'activity' :
                $searcher = $this->getProjectGrantService()->getSearchEngineStrategy();
                $result = $searcher->searchRaw($search, $limit);
                foreach ($result['hits']['hits'] as $hit) {
                    $io->writeln(sprintf("(score : <green>%s</green>) <bold>[%s]</bold> %s > %s",
                                         $hit['_score'],
                                         $hit['_source']['oscar'],
                                         $hit['_source']['acronym'],
                                         $hit['_source']['label'],
                                         )
                    );
                }
                break;
            case 'person' :
                $result = $this->getPersonSearchStrategy()->searchRaw($search, $limit);
                foreach ($result['hits']['hits'] as $hit) {
                    $io->writeln(sprintf("(score : <green>%s</green>) <bold>[%s]</bold> %s > %s",
                                         $hit['_score'],
                                         $hit['_source']['fullname'],
                                         $hit['_source']['affectation'],
                                         $hit['_source']['email'],
                                 )
                    );
                }
                break;
            case 'organization' :
                $result = $this->getOrganizationSearchStrategy()->searchRaw($search, $limit);
                foreach ($result['hits']['hits'] as $hit) {
                    $io->writeln(sprintf("(score : <green>%s</green>) <bold>[%s]</bold> %s > %s",
                                         $hit['_score'],
                                         $hit['_source']['code'],
                                         $hit['_source']['shortname'],
                                         $hit['_source']['fullname'],
                                 )
                    );
                }
                break;
            default:
                $io->error("Moteur de recherche '$engine' inconnu/non géré");
                return self::FAILURE;

        }

        return self::SUCCESS;

    }
}
/**
 * $io->writeln(sprintf("<bold>%s</bold> (score : <bold>%s</bold>) %s, %s",
$hit['_source']['fullname'],
$hit['_score'],
$hit['_source']['affectation'],
$hit['_source']['location'])
 */