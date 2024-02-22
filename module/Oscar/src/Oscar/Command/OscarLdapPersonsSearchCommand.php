<?php
/**
 * Created by PhpStorm.
 * User: Sisomolida HiNG
 * Date: 15/02/24
 * Time: 11:49
 */

namespace Oscar\Command;

use Oscar\Service\OscarUserContext;
use Oscar\Service\PersonService;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
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

        $io = new SymfonyStyle($input, $output);
        $search = $input->getArgument("search");
        $io->title("Recherche LDAP '$search' dans les personnes : ");

        try {
            $moduleOptions = $this->getServicemanager()->get('unicaen-app_module_options');

            $configLdap = $moduleOptions->getLdap();
            $ldap = $configLdap['connection']['default']['params'];

            $dataPeopleFromLdap = new People();
            $dataPeopleFromLdap->setConfig($configLdap);
            $dataPeopleFromLdap->setLdap(new Ldap($ldap));

            $person = $dataPeopleFromLdap->findOneByUid($search);

            if($person){
                $headers = ["ID", "Nom complet", "Prénom", "Nom", "Email"];
                $data = [];

                $data[] = [
                    '<bold>[' . $person->getUid() .']</bold>',
                    $person->getNomComplet(),
                    $person->getCn(),
                    $person->getSn(),
                    $person->getMail()
                ];
                $io->writeln( sprintf('- <bold>[%s]</bold> %s (%s)', $person->getUid(),
                    $person->getNomComplet(), $person->getMail()));
                $io->table($headers, $data);
            } else {
                $io->warning("Aucun résultat");
            }
        } catch ( \Exception $e ){
            $io->error($e->getMessage());
        }
    }
}
