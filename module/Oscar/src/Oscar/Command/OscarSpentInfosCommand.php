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
use Symfony\Component\Console\Helper\TableCell;
use Symfony\Component\Console\Helper\TableCellStyle;
use Symfony\Component\Console\Helper\TableSeparator;
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

        $datas = $spentService->getSynthesisDatasPFI([$pfi]);

        $masses = $oscarConfig->getMasses();

        $headers = ["Annexe", "Engagé", "Nbr", "Effectué", "Nbr"];
        $rows = [];

        $styleNumber = new TableCellStyle(['align' => 'right']);
        $money = function($value) use ($styleNumber){
          $amount = number_format($value, 2, ',', ' ');
          return new TableCell($amount, ['style' => $styleNumber]);
        };

        foreach ($masses as $masse=>$label) {
            $rows[] = [
                "$label ($masse)",
                $money($datas['synthesis'][$masse]['total_engage']),
                $datas['synthesis'][$masse]['nbr_engage'],
                $money($datas['synthesis'][$masse]['total_effectue']),
                $datas['synthesis'][$masse]['nbr_effectue'],
            ];
//            $io->text($label . " : <bold>" . $datas[$masse]. "</bold>");
        }

        $rows[] = ["TOTAL",
            $money($datas['synthesis']['totaux']['engage']),
            "-",
            $money($datas['synthesis']['totaux']['effectue']),
            "-",
        ];


        $rows[] = new TableSeparator();
        $rows[] = ["Hors-masse",
            $money($datas['synthesis']['N.B']['total_engage']),
            $datas['synthesis']['N.B']['nbr_engage'],
            $money($datas['synthesis']['N.B']['total_effectue']),
            $datas['synthesis']['N.B']['nbr_effectue'],
        ];
        $rows[] = ["Ignorés",
            $money($datas['synthesis'][0]['total_engage']),
            $datas['synthesis'][0]['nbr_engage'],
            $money($datas['synthesis'][0]['total_effectue']),
            $datas['synthesis'][0]['nbr_effectue'],
        ];
        $rows[] = ["Recettes",
            $money($datas['synthesis'][1]['total_engage']),
            $datas['synthesis'][1]['nbr_engage'],
            $money($datas['synthesis'][1]['total_effectue']),
            $datas['synthesis'][1]['nbr_effectue'],
        ];

        $io->table($headers, $rows);

        return self::SUCCESS;

//        if( count($datas['details']['N.B']) > 0 ){
//            $io->title("Compte Générale dont la masse n'est pas renseignée : ");
//            foreach( $datas['details']['N.B'] as $compte){
//                $io->text( " - <bold>$compte</bold>");
//            }
//        }
    }
}