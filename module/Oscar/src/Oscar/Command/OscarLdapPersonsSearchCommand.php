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
            $person = $dataPeopleFromLdap->findOneByUid($search);

            if($person){
                //$persons = $personService->getPersonsByIds(array($ids));
                $headers = ["ID", "Nom complet", "Prénom", "Nom", "Email"];
                $data = [];

                /*$io->writeln("Id :".$data["uidnumber"]);
                $io->writeln("Nom complet :".$data["cn"]);
                $io->writeln("Prénom :".$data["givenname"]);
                $io->writeln("Nom :".$data["sn"]);
                $io->writeln("Uid :".$data["supannaliaslogin"]);
                $io->writeln("Email :".$data["edupersonprincipalname"]);
                $io->writeln("Affectation :".$data["edupersonprimaryaffiliation"]);*/

                //foreach ($ids as $person) {
                    $data[] = [
                        '<bold>[' . $person->getUid() .']</bold>',
                        $person->getNomComplet(),
                        $person->getCn(),
                        $person->getSn(),
                        $person->getMail()
                    ];
                    $io->writeln( sprintf('- <bold>[%s]</bold> %s (%s)', $person->getUid(), $person->getNomComplet(), $person->getMail()));
                //}
                $io->table($headers, $data);
            } else {
                $io->warning("Aucun résultat");
            }
        } catch ( \Exception $e ){
            $io->error($e->getMessage());
        }
    }
}