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
use Oscar\Entity\SpentLine;
use Oscar\Service\ConnectorService;
use Oscar\Service\OscarConfigurationService;
use Oscar\Service\OscarUserContext;
use Oscar\Service\PersonService;
use Oscar\Service\SpentService;
use Oscar\Service\TimesheetService;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Helper\TableCell;
use Symfony\Component\Console\Helper\TableCellStyle;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class OscarSpentListCommand extends OscarCommandAbstract
{
    protected static $defaultName = 'spent:list';
    const pfi = 'PFI';

    protected function configure()
    {
        $this
            ->setDescription("Affiche la liste des dépenses")
            ->addArgument(self::pfi, InputArgument::REQUIRED, "PFI")
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->addOutputStyle($output);

        /** @var OscarUserContext $oscaruserContext */
        $oscaruserContext = $this->getServicemanager()->get(OscarUserContext::class);

        $io = new SymfonyStyle($input, $output);

        $pfi = $input->getArgument(self::pfi);

        $io->title("Dépenses pour $pfi");


        /** @var OscarConfigurationService $oscarConfig */
        $oscarConfig = $this->getServicemanager()->get(OscarConfigurationService::class);

        /** @var SpentService $spentService */
        $spentService = $this->getServicemanager()->get(SpentService::class);
// 07 64 41 68 47 - 6k
        try {

            $styleNumber = new TableCellStyle(['align' => 'right']);
            $money = function($value) use ($styleNumber){
                $amount = number_format($value, 2, ',', ' ');
                return new TableCell($amount, ['style' => $styleNumber]);
            };

            $grouped = $spentService->getGroupedSpentsDatas($pfi);
            foreach ($grouped as $numPiece=>$infos) {
                if( $numPiece === 'byMasses' ){
                    continue;
                }
                if( !array_key_exists('ids', $infos) || !is_array($infos['ids']) ) {
                    var_dump($infos);
                    die('ici');
                }
                $text = is_array($infos['text']) ? implode(',', $infos['text']) : $infos['text'];
                if( strlen($text) > 40 ){
                    $text = substr($text, 0, 40) . '...';
                }

                $out[] = [
                    $numPiece,
                    //implode(',', $infos['syncIds']),
                    $text,
                    $money($infos['montant']),
                    implode(',',$infos['masse']),
                    is_array($infos['compteBudgetaire']) ? implode(',', $infos['compteBudgetaire']) : $infos['compteBudgetaire'],
                    $infos['datecomptable'],
                    $infos['datepaiement'],
                    $infos['annee'],
                    $infos['refPiece'],
                    ( array_key_exists('ids', $infos) && is_array($infos['ids']) ? count($infos['ids']) : $infos['ids']),
                ];
            }
            $headers = ['N°Pièce', /*'SIFACID',*/ 'Description', 'Montant','Masse','Compte', 'Date Comptable', 'Date Paiement', 'Année', 'N° Réf Pièce', 'IDs'];

            $io->table($headers, $out);

//            $nb = count($grouped['byMasses']['N.B']['spents']);
//            if( $nb ){
//                $io->warning(sprintf("Il y a %s ligne(s) de dépenses non-attribuée(s) à une masse comptable", $nb));
//
//                $out = [];
//                foreach ($grouped['byMasses']['N.B']['spents'] as $numPiece=>$infos) {
//
//                    $out[] = [
//                        $numPiece,
//                        //implode(',', $infos['syncIds']),
//                        is_array($infos['text']) ? implode(',', $infos['text']) : $infos['text'],
//                        $money($infos['montant']),
//                        implode(',',$infos['masse']),
//                        is_array($infos['compteBudgetaire']) ? implode(',', $infos['compteBudgetaire']) : $infos['compteBudgetaire'],
//                        $infos['datecomptable'],
//                        $infos['datepaiement'],
//                        $infos['annee'],
//                        $infos['refPiece'],
//                        ( array_key_exists('ids', $infos) && is_array($infos['ids']) ? count($infos['ids']) : $infos['ids']),
//                    ];
//                }
//                $io->table($headers, $out);
//            }
            return self::SUCCESS;
//            $repport = $connector->execute();
//            foreach ($repport->getRepportStates() as $type => $out) {
//                $short = substr($type, 0, 3);
//                $io->section( "Opération " . strtoupper($type));
//                foreach ($out as $line) {
//                    $io->writeln("$short\t " . date('Y-m-d H:i:s', $line['time']) . " " . $line['message'] );
//                }
//            }
        } catch (\Exception $e) {
            $io->error($e->getMessage());
            return self::FAILURE;
        }
    }
}