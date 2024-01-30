<?php
/**
 * Created by PhpStorm.
 * User: bouvry
 * Date: 04/10/19
 * Time: 11:49
 */

namespace Oscar\Command;


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
use UnicaenApp\Mapper\Ldap\People;
use Zend\Ldap\Ldap;

class OscarLdapPersonsSearchCommand extends OscarCommandAbstract
{
    protected static $defaultName = 'ldap:persons:search';

    protected function configure()
    {
        $this
            ->setDescription("Recherche LDAP dans l'index de recherche des personnes")
            ->addArgument('search', InputArgument::REQUIRED, 'Expression de recherche')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->addOutputStyle($output);

        /** @var OscarUserContext $oscaruserContext */
        $oscaruserContext = $this->getServicemanager()->get(OscarUserContext::class);

        $io = new SymfonyStyle($input, $output);
        $search = $input->getArgument("search");
        $io->title("Recherche LDAP '$search' dans les personnes : ");

        /** @var PersonService $personService */
        $personService = $this->getServicemanager()->get(PersonService::class);

        try {
            $moduleOptions = $this->getServicemanager()->get('unicaen-app_module_options');

            $configLdap = $moduleOptions->getLdap();
            $ldap = $configLdap['connection']['default']['params'];

            $dataPeopleFromLdap = new People();
            $dataPeopleFromLdap->setConfig($configLdap);
            $dataPeopleFromLdap->setLdap(new Ldap($ldap));

            //$ids = $personService->getSearchEngineStrategy()->search($search);
            $ids = $dataPeopleFromLdap->findAllByNameOrUsername($search, $configLdap['filters']['UID_FILTER'], $ldap['accountFilterFormat'], true);

            if( count($ids) ){
                $persons = $personService->getPersonsByIds($ids);
                $headers = ["ID", "SYNC", "Nom complet", "Prénom", "Nom", "Affectation", "Email"];
                $data = [];
                var_dump($data);
                foreach ($persons as $person) {
                    $data[] = [
                        '<bold>[' . $person->getId() .']</bold>',
                        $person->getConnectorsDatasStr(),
                        $person->getDisplayName(),
                        $person->getFirstname(),
                        $person->getLastname(),
                        $person->getLdapAffectation(),
                        $person->getEmail()
                    ];
//                    $io->writeln( sprintf('- <bold>[%s]</bold> %s (%s)', $person->getId(), $person->getDisplayName(), $person->getEmail()));
                }
                $io->table($headers, $data);
            } else {
                $io->warning("Aucun résultat");
            }
        } catch ( \Exception $e ){
            $io->error($e->getMessage());
        }
    }
}