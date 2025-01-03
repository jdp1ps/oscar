<?php
/**
 * Created by PhpStorm.
 * User: bouvry
 * Date: 04/10/19
 * Time: 11:49
 */

namespace Oscar\Command;


use Doctrine\ORM\EntityManager;
use Laminas\Crypt\Password\Bcrypt;
use Oscar\Connector\ConnectorAuthentificationJSON;
use Oscar\Formatter\ConnectorRepportToPlainText;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class OscarAuthSyncCommand extends OscarCommandAbstract
{
    protected static $defaultName = 'auth:sync';

    protected function configure()
    {
        $this
            ->setDescription("Synchronisation des authentifications depuis un fichier JSON")
            ->addArgument('jsonPath', InputArgument::REQUIRED, 'Emplacement du fichier JSON');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->addOutputStyle($output);

        $io = new SymfonyStyle($input, $output);

        $io->title("Importation d'authentification");

        try {
            $jsonpath = $input->getArgument('jsonPath');

            if (!$jsonpath) {
                $io->error("ERR : Vous devez spécifier le chemin complet vers le fichier JSON");
                return self::FAILURE;
            }

            if (!file_exists($jsonpath)) {
                $io->error("ERR : '$jsonpath' n'est pas un emplacement de fichier valide");
                return self::FAILURE;
            }

            $fileContent = file_get_contents($jsonpath);
            if (!$fileContent) {
                $io->error("ERR : Oscar n'a pas réussi à charger le contenu du fichier '$jsonpath'");
                return self::FAILURE;
            }

            $datas = json_decode($fileContent);
            if (!$datas) {
                $io->error("ERR : Les données du fichier '$jsonpath' n'ont pas pu être converties au format JSON.");
                return self::FAILURE;
            }

            // Système pour crypter les mots de pass (Zend)
            $options = $this->getServicemanager()->get('zfcuser_module_options');
            $bcrypt = new Bcrypt();
            $bcrypt->setCost($options->getPasswordCost());

            $em = $this->getServicemanager()->get(EntityManager::class);

            $connectorAuthentification = new ConnectorAuthentificationJSON($datas, $em, $bcrypt);

            $repport = $connectorAuthentification->syncAll();
            $connectorFormatter = new ConnectorRepportToPlainText();

            echo $connectorFormatter->format($repport);
        } catch (\Exception $ex) {
            $io->error("ERR : " . $ex->getMessage());
            return self::FAILURE;
        }
        return self::SUCCESS;
    }
}