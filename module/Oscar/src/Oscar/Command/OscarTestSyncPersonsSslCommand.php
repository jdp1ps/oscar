<?php
/**
 * Created by PhpStorm.
 * User: hmarie
 * Date: 27/08/20
 * Time: 10:26
 */

namespace Oscar\Command;


use Oscar\Connector\Access\ConnectorAccessCurlCertificat;
use Oscar\Service\ConnectorService;
use Oscar\Service\OscarConfigurationService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Zend\ServiceManager\ServiceManager;

class OscarTestSyncPersonsSslCommand extends OscarCommandAbstract
{
    protected static $defaultName = 'dev:commandtestsslsyncpersons';

    protected function configure()
    {
        $this
            ->setDescription("Permet de tester l'appel curl ssl syncPersons OSCAR")
            ->setHelp("Permet de tester le retour du connector ssl syncPersons en passant le nom du connecteur (rest) en argument 1,
            l'url de base api en argument 2, et un argument 3 optionnel eppn dans le cadre d'une url avec un appel api sur un seul enregistement")
            ->addArgument("connectorname", InputArgument::REQUIRED, "Connector (restssl)")
            ->addArgument('baseUrl', InputArgument::REQUIRED, "L'argument baseUrl est requis")
            ->addArgument('eppn', InputArgument::OPTIONAL, "L'argument eppn est falcutatif")

        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $oscarConfigurationService = $this->getServicemanager()->get(OscarConfigurationService::class);
        $output->writeln($oscarConfigurationService->getVersion());
        $connectorName = $input->getArgument('connectorname');

        /** @var ConnectorService $connectorService */
        $connectorService = $this->getServicemanager()->get(ConnectorService::class); // Facto restPerson
        //Get du connector implémenté
        $connector = $connectorService->getConnector("person.".$connectorName);
        //Get de l'url de base
        $urlArgument = $input->getArgument('baseUrl');
        //Get optionnal argument eppn
        $eppnArgument = (is_null($input->getArgument('eppn')))?"":"/".$input->getArgument('eppn');

        $io = new SymfonyStyle($input, $output);
        $io->title("TEST APPEL CURLSSLGUZZLE SYNC PERSONS");
        $io->section("Connector infos : ");
        $io->writeln("Connecteur : $connectorName");

        $getApiRequest = new ConnectorAccessCurlCertificat($connector, ['url'=>$urlArgument.$eppnArgument]);
        $results = $getApiRequest->getDatas();
        if ($eppnArgument != ""){
            dump($results);
        }else{
            var_dump($results);
        }
    }
}