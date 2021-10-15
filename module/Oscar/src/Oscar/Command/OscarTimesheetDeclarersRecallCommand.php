<?php
/**
 * Created by PhpStorm.
 * User: bouvry
 * Date: 04/10/19
 * Time: 11:49
 */

namespace Oscar\Command;


use Doctrine\ORM\NoResultException;
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

class
OscarTimesheetDeclarersRecallCommand extends OscarCommandAbstract
{
    protected static $defaultName = 'timesheets:declarers-recall';

    const OPT_FORCE         = "force";
    const OPT_DECLARER      = "declarer";
    const OPT_PERIOD        = "period";
    const OPT_PROCESSDATE        = "processdate";

    const ARG_ALL        = "all";

    protected function configure()
    {
        $this
            ->setDescription("Système de relance des déclarants")
            ->addOption(self::OPT_FORCE, 'f', InputOption::VALUE_NONE, "Forcer le mode non-interactif")
            ->addOption(self::OPT_DECLARER, 'd', InputOption::VALUE_REQUIRED, "Identifiant du déclarant")
            ->addOption(self::OPT_PERIOD, 'p', InputOption::VALUE_OPTIONAL, "Période")
            ->addOption(self::OPT_PROCESSDATE, 'c', InputOption::VALUE_OPTIONAL, "Date de relance (par défaut date actuelle)")
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
        $declarerLogin = $input->getOption(self::OPT_DECLARER);
        $processDate = $input->getOption(self::OPT_PROCESSDATE);

        if( $processDate ){
            $processDate = new \DateTime($processDate);
        } else {
            $processDate = new \DateTime();
        }

        $io->writeln("Date effective de la relance : <bold>". $processDate->format('D d M Y') ."</bold>");
        //die();

        if( $all == false && $declarerLogin == null ){
            $io->writeln("Utiliser l'option --declarant=ID");
            $this->declarersList($input, $output);
            return;
        }

        if( $all != 'all' ){
            try {
                $person = $this->getPersonService()->getPersonByLdapLogin($declarerLogin);
                if( !$person ){
                    throw new \Exception("Nop !");
                }
                $personId = $person->getId();

            } catch (NoResultException $e){
                $io->error("Impossible de charger la personne à partir de son identifiant de connexion : $declarerLogin");
                return;
            }
            $declarerId = $personId;
            $all = false;
        }

        $declarerPeriod = $input->getOption(self::OPT_PERIOD);
        if( !$declarerPeriod ){
            $today = date('Y-m-d');
            $time = strtotime($today);
            $final = date("Y-m-d", strtotime("-1 month", $time));
            $declarerPeriod = DateTimeUtils::getPeriodStrFromDateStr($final);
        }

        $io->writeln("Pour la période : <bold>". $declarerPeriod ."</bold>.");


        $do = false;

        if( $all == true ){
            $declarants = $this->getTimesheetService()->getDeclarers();
            $io->writeln(sprintf("Nombre de déclarants pour cette période : <bold>%s</bold>", count($declarants['persons'])));
            $out = [];

            /** @var Person $declarer */
            foreach ($declarants['persons'] as $personId=>$datas) {
                $io->text("Traitement pour " . $personId);
                try {
                    $result = $this->recallDeclarer($personId, $declarerPeriod, $io, $processDate);
                } catch (\Exception $e) {
                    $io->warning($e->getMessage());
                }
            }
        } else {
            try {
                $result = $this->recallDeclarer($personId, $declarerPeriod, $io, $processDate);
            } catch (\Exception $e) {
                $io->warning($e->getMessage());
            }
        }
        $io->comment("Opération terminée");
        return;
    }

    protected function recallDeclarer($declarerId, $declarerPeriod, SymfonyStyle $io, $processDate = null)
    {
        /** @var Person $declarer */
        $declarer = $this->getPersonService()->getPersonById($declarerId);

        $result = $this->declarerPeriod($declarerId, $declarerPeriod, $processDate);

        if( $result ) {
            $io->title($result['person']);
            $io->writeln(sprintf("Message : <options=bold>%s</>", $result['message']));
            $io->writeln(sprintf("Mail ? <options=bold>%s</>", $result['needSend'] ? 'Oui' : 'Non'));
            $io->writeln(sprintf("Déclaration (heures) : <options=bold>%s/%s</>", $result['total'], $result['needed']));
            $io->writeln(sprintf("Max : <options=bold>%s</>", $result['max']));
            $io->writeln(sprintf("Min : <options=bold>%s</>", $result['min']));
            $io->writeln(sprintf("Mail requis : <options=bold>%s</>", $result['mailRequired'] ? "Oui" : "Non"));
            $io->writeln(sprintf("Mail envoyé : <options=bold>%s</>", $result['mailSend'] ? "Oui" : "Non"));
            $io->writeln(sprintf("Dernier envoi : <options=bold>%s</>", $result['lastSend']));
            $io->writeln(sprintf("Prochain envoi : <options=bold>%s</>", $result['nextSend']));
            $io->writeln(sprintf("Status : <options=bold>%s</>", $result['status']));
        }
        else {
            $io->warning("Pas de données pour $declarer à la période $declarerPeriod");
        }
        return;
    }

    /**
     * @return MailingService
     */
    protected function getMailer()
    {
        return $this->getServicemanager()->get(MailingService::class);
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

    public function declarerPeriod( $declarerId, $period, $processDate = null ){
        if( $processDate == null ){
            $processDate = new \DateTime();
        }
        return $this->getTimesheetService()->recallProcess($declarerId, $period, $processDate);
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
                $out[] = [$personId, $datas['displayname'], $datas['login'], $datas['affectation'], count($datas['declarations'])];
            }
            $headers = ['ID', 'login', 'Déclarant', 'Affectation', 'Déclaration(s)'];
            $io->table($headers, $out);

            $io->comment("Entrez la commande '".self::getName()." --". self::OPT_DECLARER . "=<ID>' pour afficher les détails");
        } catch (\Exception $e) {
            $io->error($e->getMessage());
        }
    }
}