<?php
/**
 * Created by PhpStorm.
 * User: bouvry
 * Date: 04/10/19
 * Time: 11:49
 */

namespace Oscar\Command;


use Oscar\Service\OscarConfigurationService;
use Oscar\Service\OscarUserContext;
use Oscar\Service\SpentService;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class OscarSpentInfosCommand extends OscarCommandAbstract
{
    protected static $defaultName = 'spent:infos';

    protected function configure()
    {
        $this
            ->setDescription("Permet d'obtenir les informations sur les dépenses d'un PFI")
            ->setHelp("")
            ->addArgument('pfi', InputArgument::REQUIRED, "PFI à synchroniser")
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->addOutputStyle($output);

        /** @var OscarUserContext $oscaruserContext */
        $oscaruserContext = $this->getServicemanager()->get(OscarUserContext::class);

        $io = new SymfonyStyle($input, $output);

        /** @var OscarConfigurationService $oscarConfig */
        $oscarConfig = $this->getServicemanager()->get(OscarConfigurationService::class);

        $pfi = $input->getArgument('pfi');
        $io->title("Dépenses pour $pfi");

        /** @var SpentService $spentService */
        $spentService = $this->getServicemanager()->get(SpentService::class);

        $datas = $spentService->getSynthesisDatasPFI($pfi);

        $masses = $oscarConfig->getMasses();

        $headers = ["Annexe", "Total"];
        $rows = [];

        foreach ($masses as $masse=>$label) {
            $rows[] = ["$label ($masse)", $datas[$masse]];
//            $io->text($label . " : <bold>" . $datas[$masse]. "</bold>");
        }
        $rows[] = [];

        $rows[] = ["Hors-masse", $datas['N.B']];
        $rows[] = ["Nbr d'enregistrements", $datas['entries']];
        $rows[] = ["TOTAL", $datas['total']];

        $io->table($headers, $rows);

        if( count($datas['details']['N.B']) > 0 ){
            $io->title("Compte Générale dont la masse n'est pas renseignée : ");
            foreach( $datas['details']['N.B'] as $compte){
                $io->text( " - <bold>$compte</bold>");
            }
        }
    }
}