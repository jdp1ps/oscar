<?php
/**
 * Created by PhpStorm.
 * User: bouvry
 * Date: 04/10/19
 * Time: 11:49
 */

namespace Oscar\Command;


use Doctrine\ORM\EntityManager;
use Moment\Moment;
use Oscar\Connector\ConnectorActivityCSVWithConf;
use Oscar\Connector\ConnectorActivityJSON;
use Oscar\Entity\Authentification;
use Oscar\Entity\LogActivity;
use Oscar\Entity\OrganizationRole;
use Oscar\Entity\Person;
use Oscar\Entity\Role;
use Oscar\Entity\RoleRepository;
use Oscar\Exception\OscarException;
use Oscar\Formatter\ConnectorRepportToPlainText;
use Oscar\Service\ConnectorService;
use Oscar\Service\OrganizationService;
use Oscar\Service\OscarConfigurationService;
use Oscar\Service\OscarUserContext;
use Oscar\Service\PersonService;
use Oscar\Service\ProjectGrantService;
use Oscar\Utils\ActivityCSVToObject;
use Oscar\Utils\PhpPolyfill;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class OscarActivityCsvToJsonCommand extends OscarCommandAbstract
{
    protected static $defaultName = 'activity:cvstojson';

    protected function configure()
    {
        $this
            ->setDescription("Synchronisation d'activité de recherche à partir d'un fichier JSON")
            ->addOption('fichier', 'f', InputOption::VALUE_REQUIRED, 'Fichier CSV avec les données')
            ->addOption('config', 'c', InputOption::VALUE_REQUIRED, "Fichier de configuration de l'import")
            ->addOption('skip', 's', InputOption::VALUE_REQUIRED, "Fichier de configuration de l'import", 1)
        ;
    }

    private function getReadablePath($path)
    {
        $realpath = realpath($path);

        if (!$realpath) {
            throw new OscarException(sprintf("Le chemin '%s' n'a aucun sens...",
                $path));
        }


        if (!is_file($realpath)) {
            throw new OscarException(sprintf("Le chemin '%s' n'est pas un fichier...",
                $realpath));
        }

        if (!is_readable($realpath)) {
            throw new OscarException(sprintf("Le chemin '%s' n'est pas lisible...",
                $realpath));
        }

        return $realpath;
    }


    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->addOutputStyle($output);

        /** @var OscarUserContext $oscaruserContext */
        $oscaruserContext = $this->getServicemanager()->get(OscarUserContext::class);

        $io = new SymfonyStyle($input, $output);

        ///////////////////////////////////////////////////////////////
        ////////////////////////////////////////////////////// SERVICES
        /** @var OscarConfigurationService $oscarConfig */
        $oscarConfig = $this->getServicemanager()->get(OscarConfigurationService::class);

        /** @var ProjectGrantService $projectGrantService */
        $projectGrantService = $this->getServicemanager()->get(ProjectGrantService::class);

        /** @var EntityManager $entityManager */
        $entityManager = $this->getServicemanager()->get(EntityManager::class);


        // Fichiers
        try {

            $optFichier = $input->getOption('fichier');
            $optConfig = $input->getOption('config');

            if( !$optConfig || !$optFichier ){
                $io->error("Les options --fichier <SOURCE CSV> et --config <FICHIER PHP> sont requises");
                return 0;
            }

            $sourceFilePath = $this->getReadablePath($input->getOption('fichier'));
            $configurationFilePath = $this->getReadablePath($input->getOption('config'));
            $skip = $input->getOption('skip');

            $configuration = require($configurationFilePath);
            $source = fopen($sourceFilePath, 'r');

            while ($skip > 0) {
                fgetcsv($source);
                $skip--;
            }

            $sync = new ConnectorActivityCSVWithConf($source, $configuration,
                $this->getServicemanager()->get(EntityManager::class));

            $datas = $sync->syncAll();
            $json = PhpPolyfill::jsonEncode($datas, JSON_PRETTY_PRINT);
            $error =  json_last_error();

            if( $error ){
                $io->error($error);
            } else {
                echo $json;
            }

        } catch (\Exception $e) {
            $io->error($e->getMessage());
        }
        /****/
    }
}