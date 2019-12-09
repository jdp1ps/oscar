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

class OscarElasticSearchQueryCommand extends OscarCommandAbstract
{
    protected static $defaultName = 'elasticsearch:query';

    protected function configure()
    {
        $this
            ->setDescription("lancement d'une requête brute")
            ->addArgument('search', InputArgument::REQUIRED, 'Expression de recherche')
            ->addOption('json', 'j', InputOption::VALUE_NONE, 'La chaîne de requête est un JSON à envoyer directement à lAPI')
        ;
    }

    /**
     * @return \Elasticsearch\Client
     */
    protected function getClient()
    {
        static $elasticsearchClient;

        if (!$elasticsearchClient)
            $elasticsearchClient = ClientBuilder::create()
                ->setHosts($this->getHosts())
                ->build();
        return $elasticsearchClient;
    }

    protected function getIndex(){
        return 'oscar-person';
    }

    public function getType()
    {
        return 'person';
    }

    public function getHosts(){
        return ['localhost:9200'];
    }

    public function search($search, $limit=10000)
    {
        $params = [
            'index' => $this->getIndex(),
            'type' => $this->getType(),
            'body' => [
                'size' => $limit,
                'query' => [
                    'multi_match' => [
                        'type' => 'phrase_prefix',
                        'fields' => ['fullname^3','lastname^5', 'firstname^2', 'email', 'affectation^3', 'location^3', 'organizations', 'activities', 'connectors'],
                        'query' => $search,
                        /* 'max_expansions' => 20,*/
                        //'fuzziness' => 'AUTO'
                    ]
                ]
            ]
        ];

        $response = $this->getClient()->search($params);
        return $response;
    }

    public function searchJson($json, $limit=10000)
    {
        $query = json_decode($json, JSON_OBJECT_AS_ARRAY);
        if( $query == null ){
            die("JSON ERROR : " . json_last_error() . " . ' dans " . $json);
        }

        $params = [
            'index' => $this->getIndex(),
            'type' => $this->getType(),
            'body' => [
                'size' => $limit,
                'query' => $query
            ]
        ];

        print_r($params);

        $response = $this->getClient()->search($params);
        return $response;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->addOutputStyle($output);

        $io = new SymfonyStyle($input, $output);

        $search = $input->getArgument("search");
        $json = $input->getOption("json") !== false;


        $io->title("Recherche '$search' dans l'index de recherche : ");


        if( true ){
            if( $json != false ){
                $result = $this->searchJson($search);
            } else {
                $result = $this->search($search, 20);
            }
            foreach ($result['hits'] as $k=>$r) {
                if( $k == 'hits' ){
                    foreach ($result['hits'][$k] as $hit) {
                        $io->writeln(sprintf("<bold>%s</bold> %s, %s",$hit['_source']['fullname'], $hit['_source']['affectation'], $hit['_source']['location']));
                    }

                }
            }
        }
//        print_r($this->search($search));

    }
}