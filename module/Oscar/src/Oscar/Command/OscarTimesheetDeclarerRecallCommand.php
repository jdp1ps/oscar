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
use Oscar\Entity\WorkPackage;
use Oscar\Entity\WorkPackagePerson;
use Oscar\Service\ConnectorService;
use Oscar\Service\MailingService;
use Oscar\Service\OscarConfigurationService;
use Oscar\Service\OscarMailerService;
use Oscar\Service\OscarUserContext;
use Oscar\Service\PersonService;
use Oscar\Service\TimesheetService;
use Oscar\Utils\DateTimeUtils;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use UnicaenApp\Service\Mailer\MailerService;
use Zend\Validator\Date;

class OscarTimesheetDeclarerRecallCommand extends OscarCommandAbstract
{
    protected static $defaultName = 'timesheets:declarers-recall';

    const OPT_FORCE         = "force";
    const OPT_DECLARER      = "declarer";
    const OPT_PERIOD        = "period";

    const ARG_ALL        = "all";

    protected function configure()
    {
        $this
            ->setDescription("Système de relance des déclarants")
            ->addOption(self::OPT_FORCE, 'f', InputOption::VALUE_NONE, "Forcer le mode non-interactif")
            ->addOption(self::OPT_DECLARER, 'd', InputOption::VALUE_REQUIRED, "Identifiant du déclarant")
            ->addOption(self::OPT_PERIOD, 'p', InputOption::VALUE_OPTIONAL, "Période")
            ->addArgument(self::ARG_ALL, InputArgument::OPTIONAL, "Déclencher pour tous les déclarants", false);
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->addOutputStyle($output);

        $io = new SymfonyStyle($input, $output);

        // Argument ALL
        $all = $input->getArgument(self::ARG_ALL);

        /// OPTIONS and PARAMETERS
        $declarerId = $input->getOption(self::OPT_DECLARER);

        if( $all == false && $declarerId == null ){
            $io->writeln("Utiliser l'option --declarant=ID");
            $this->declarersList($input, $output);
            return;
        }

        $declarerPeriod = $input->getOption(self::OPT_PERIOD);
        if( !$declarerPeriod ){
            $today = date('Y-m-d');
            $time = strtotime($today);
            $final = date("Y-m-d", strtotime("-1 month", $time));
            $declarerPeriod = DateTimeUtils::getPeriodStrFromDateStr($final);
        }

        $do = false;

        if( $all == true ){
            $declarants = $this->getTimesheetService()->getDeclarers();
            $out = [];
            /** @var Person $declarer */
            foreach ($declarants['persons'] as $personId=>$datas) {
                try {
                    $result = $this->recallDeclarer($personId, $declarerPeriod, $io);
                } catch (\Exception $e) {
                    $io->warning($e->getMessage());
                }
            }
        } else {
            $result = $this->recallDeclarer($declarerId, $declarerPeriod, $io);
        }
    }

    protected function recallDeclarer($declarerId, $declarerPeriod, SymfonyStyle $io)
    {
        /** @var Person $declarer */
        $declarer = $this->getPersonService()->getPersonById($declarerId);
        $result = $this->declarerPeriod($declarerId, $declarerPeriod);
        $io->title($result['person']);
        $io->writeln(sprintf("Message : <options=bold>%s</>", $result['message']));
        $io->writeln(sprintf("Mail ? <options=bold>%s</>", $result['needSend'] ? 'Oui' : 'Non'));
        $io->writeln(sprintf("Déclaration (heures) : <options=bold>%s/%s</>", $result['total'], $result['needed']));
        $io->writeln(sprintf("Max : <options=bold>%s</>", $result['max']));
        $io->writeln(sprintf("Min : <options=bold>%s</>", $result['min']));

        if( $result['needSend'] ){
            $message = $this->getMailer()->newMessage("[OSCAR] Déclaration de temps $declarerPeriod", "CONTENU : " . $result['message']);
            $message->setTo($declarer->getEmail());
            $message->setBody("Message : " . $result['message']);
            $this->getMailer()->send($message);
            echo "MAIL !!!! \n";
        }
    }

    /**
     * @return MailingService
     */
    protected function getMailer()
    {
        static $mailer;
        if( $mailer === null ){
            $mailer = $this->getServicemanager()->get(MailingService::class);
        }
        return $mailer;
    }

    /**
     * @return TimesheetService
     */
    protected function getTimesheetService(){
        return $this->getServicemanager()->get(TimesheetService::class);
    }

    /**
     * @return PersonService
     */
    protected function getPersonService(){
        return $this->getServicemanager()->get(PersonService::class);
    }

    //public function declarerRecall

    public function declarerPeriod( $declarerId, $period ){
        return $this->getTimesheetService()->getPersonRecallDeclarationPeriod($declarerId, $period);
    }

    public function declarer( InputInterface $input, OutputInterface $output, $declarerId ){

        $io = new SymfonyStyle($input, $output);

        try {
            $declarer = $this->getPersonService()->getPerson($declarerId);

            $io->title("Système de relance pour $declarer");
            $periods = $this->getTimesheetService()->getPersonRecallDeclaration($declarer);

            $io->table(["Période", "Durée", "état"], $periods);

        } catch (\Exception $e) {
            $io->error('Impossible de charger le déclarant : ' . $e->getMessage());
            exit(0);
        }
    }

    public function declarersList( InputInterface $input, OutputInterface $output ){
        $io = new SymfonyStyle($input, $output);
        $io->title("Lite des déclarants");
        try {
            $declarants = $this->getTimesheetService()->getDeclarers();
            $out = [];
            /** @var Person $declarer */
            foreach ($declarants['persons'] as $personId=>$datas) {
                $out[] = [$personId, $datas['displayname'], $datas['affectation'], count($datas['declarations'])];
            }
            $headers = ['ID', 'Déclarant', 'Affectation', 'Déclaration(s)'];
            $io->table($headers, $out);

            $io->comment("Entrez la commande '".self::getName()." --". self::OPT_DECLARER . "=<ID>' pour afficher les détails");
        } catch (\Exception $e) {
            $io->error($e->getMessage());
        }
    }
}