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
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class OscarActivityImportJsonCommand extends OscarCommandAbstract
{
    protected static $defaultName = 'activity:import-json';

    protected function configure()
    {
        $this
            ->setDescription("Synchronisation d'activité de recherche à partir d'un fichier JSON")
            ->addOption('fichier', 'f', InputOption::VALUE_REQUIRED, 'Fichier JSON');

        $this->addOption(
            'create-missing-project',
            'p',
            InputOption::VALUE_OPTIONAL,
            "Créer automatiquement les projets manquants",
            false
        );
//
        $this->addOption(
            'create-missing-person',
            'e',
            InputOption::VALUE_OPTIONAL,
            "Créer automatiquement les personnes manquantes",
            false
        );

        $this->addOption(
            'create-missing-person-role',
            'r',
            InputOption::VALUE_OPTIONAL,
            "Créer automatiquement les rôles des personnes manquants",
            false
        );

        $this->addOption(
            'create-missing-organization',
            'o',
            InputOption::VALUE_OPTIONAL,
            "Créer automatiquement les organisations manquantes",
            false
        );

        $this->addOption(
            'create-missing-organization-role',
            'l',
            InputOption::VALUE_OPTIONAL,
            "Créer automatiquement les rôles des organisations manquants",
            false
        );

        $this->addOption(
            'create-missing-activity-type',
            'y',
            InputOption::VALUE_OPTIONAL,
            "Créer automatiquement les types d'activités manquantes",
            false
        );
    }

    private function getReadablePath($path)
    {
        $realpath = realpath($path);

        if (!$realpath) {
            throw new OscarException(
                sprintf(
                    "Le chemin '%s' n'a aucun sens...",
                    $path
                )
            );
        }


        if (!is_file($realpath)) {
            throw new OscarException(
                sprintf(
                    "Le chemin '%s' n'est pas un fichier...",
                    $realpath
                )
            );
        }

        if (!is_readable($realpath)) {
            throw new OscarException(
                sprintf(
                    "Le chemin '%s' n'est pas lisible...",
                    $realpath
                )
            );
        }

        return $realpath;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->addOutputStyle($output);

        /** @var OscarUserContext $oscaruserContext */
        $oscaruserContext = $this->getServicemanager()->get(OscarUserContext::class);

        $io = new SymfonyStyle($input, $output);

        $io->title("Synchronisation des activités");

        ///////////////////////////////////////////////////////////////
        ////////////////////////////////////////////////////// SERVICES
        /** @var OscarConfigurationService $oscarConfig */
        $oscarConfig = $this->getServicemanager()->get(OscarConfigurationService::class);

        /** @var ProjectGrantService $projectGrantService */
        $projectGrantService = $this->getServicemanager()->get(ProjectGrantService::class);

        /** @var EntityManager $entityManager */
        $entityManager = $this->getServicemanager()->get(EntityManager::class);


        ///////////////////////////////////////////////////////////////
        ////////////////////////////////////////////////////// OPTIONS
        try {
            $file = $this->getReadablePath($input->getOption('fichier'));
        } catch (\Exception $e) {
            $io->error("Impossible de lire le fichier source : " . $e->getMessage());
            return;
        }

        $options = [
            'create-missing-project' => $input->getOption('create-missing-project', false) !== false,
            'create-missing-person' => $input->getOption('create-missing-person', false) !== false,
            'create-missing-person-role' => $input->getOption('create-missing-person-role', false) !== false,
            'create-missing-organization' => $input->getOption('create-missing-organization', false) !== false,
            'create-missing-organization-role' => $input->getOption(
                    'create-missing-organization-role',
                    false
                ) !== false,
            'create-missing-activity-type' => $input->getOption('create-missing-activity-type', false) !== false,
        ];

        $fileExtension = pathinfo($file)['extension'];

        if ($fileExtension == "csv") {
            $handler = fopen($file, 'r');
            $headers = fgetcsv($handler);

            /** @var RoleRepository $repositoryRole */
            $repositoryRole = $entityManager->getRepository(Role::class);

            // Construction de la correspondance role > colonne
            $rolesPersons = $repositoryRole->getRolesAtActivityArray();
            $correspondanceRolesActivites = [];
            /** @var Role $role */
            foreach ($rolesPersons as $role) {
                $correspondanceRolesActivites[$role] = array_search(
                    $role,
                    $headers
                );
            }

            // Construction de la correspondance role > colonne
            $rolesOrganizations = $entityManager->getRepository(OrganizationRole::class)->findAll();
            $correspondanceRolesOrga = [];
            /** @var OrganizationRole $role */
            foreach ($rolesOrganizations as $role) {
                $correspondanceRolesOrga[$role->getLabel()] = array_search(
                    $role->getLabel(),
                    $headers
                );
            }

            $converteur = new ActivityCSVToObject(
                $correspondanceRolesActivites,
                $correspondanceRolesOrga
            );
            $json = $converteur->convert($file);
        } elseif ($fileExtension == "json") {
            $json = json_decode(file_get_contents($file));
        } else {
            die("ERROR : Format non pris en charge.");
        }

        $importer = new ConnectorActivityJSON(
            $json, $entityManager,
            $options
        );
        $repport = $importer->syncAll();

        $output = new ConnectorRepportToPlainText();
        $output->format($repport);
        /****/
    }
}