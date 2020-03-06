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

        try {

            $grouped = $spentService->getGroupedSpentsDatas($pfi);

            foreach ($grouped as $numPiece=>$infos) {
                $out[] = [
                    $numPiece,
                    //implode(',', $infos['syncIds']),
                    implode(',', $infos['text']),
                    $infos['montant'],
                    implode(',', $infos['compteBudgetaire']),
                    $infos['datecomptable'],
                    $infos['datepaiement'],
                    $infos['annee'],
                    $infos['refPiece'],
                    $infos['ids'][0].' +'.count($infos['ids']),
                ];
            }
            $headers = ['N°Pièce', /*'SIFACID',*/ 'Description', 'Montant', 'Date Comptable', 'Date Paiement', 'Année', 'N° Réf Pièce', 'IDs'];

            $io->table($headers, $out);
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
        }
    }
}