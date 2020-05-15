<?php
/**
 * Created by PhpStorm.
 * User: bouvry
 * Date: 04/10/19
 * Time: 11:49
 */

namespace Oscar\Command;


use Oscar\Connector\ConnectorSpentSifacOCI;
use Oscar\Entity\Authentification;
use Oscar\Entity\SpentLine;
use Oscar\Service\OscarConfigurationService;
use Oscar\Service\OscarUserContext;
use Oscar\Service\SpentService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Style\SymfonyStyle;
use Zend\Crypt\Password\Bcrypt;
use Zend\ServiceManager\ServiceManager;

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
    }
}