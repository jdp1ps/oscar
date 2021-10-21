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
use Oscar\Utils\PeriodInfos;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use UnicaenApp\Service\Mailer\MailerService;
use Zend\Validator\Date;

class OscarTimesheetValidatorRecallCommand extends OscarAdvancedCommandAbstract
{
    protected static $defaultName = 'timesheets:validator-recall';

    const OPT_FORCE = "force";
    const OPT_VALIDATOR = "validator";
    const OPT_PERIOD = "period";
    const OPT_PROCESSDATE = "processdate";

    const ARG_PERSONID = "personid";

    protected function configure()
    {
        $this
            ->setDescription("Relance ponctuelle d'un déclarant")
            ->addOption(
                self::OPT_PROCESSDATE,
                null,
                InputOption::VALUE_OPTIONAL,
                "Date effective du rappel",
                null
            )
            ->addOption(
                self::OPT_PERIOD,
                null,
                InputOption::VALUE_OPTIONAL,
                "Période (par défaut, période en cours)",
                null
            )
            ->addOption(
                self::OPTION_FORCE,
                null,
                InputOption::VALUE_OPTIONAL,
                "Forcer l'envoi du mail même si ça n'est pas necessaire",
                false
            )
            ->addArgument(
                self::ARG_PERSONID,
                InputArgument::OPTIONAL,
                "ID du déclarant"
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        parent::execute($input, $output);

        // Argument ALL
        $personId = $input->getArgument(self::ARG_PERSONID);

        /// OPTIONS and PARAMETERS
        $processDate = $input->getOption(self::OPT_PROCESSDATE);
        $processDate = new \DateTime($processDate);

        $force = $input->getOption(self::OPTION_FORCE) === null;

        // Période
        $period = $input->getOption(self::OPT_PERIOD);

        $helps = [];

        if (!$period) {
            $period = null;
            $periodText = " (Période non-spécifée)";
            $helps[] = "l'option '--period=<PERIOD>' permet de spécifier la période sous la forme YYYY-MM (ou 'now' pour la période en cours)";
        } else {
            if ($period == 'now') {
                $period = (new \DateTime())->format('Y-m');
            }
            $periodInfos = DateTimeUtils::periodBounds($period);
            $periodlabel = $periodInfos['periodLabel'];
            $periodText = " (Pour la période $periodlabel)";
        }

        if ($personId == null) {
            $this->getIO()->title("Liste des validateurs $periodText");

            $validators = $this->getPersonService()->getPersonsByIds(
                $period == null ?
                    $this->getPersonService()->getValidatorsIds() :
                    $this->getPersonService()->getValidatorsIdsPeriod($period)
            );
            $headers = ['ID', 'Personne', 'Email', "Identifiant"];
            $rows = [];
            foreach ($validators as $validator) {
                $rows[] = [
                    $validator->getId(),
                    $validator->getDisplayName(),
                    $validator->getEmail(),
                    $validator->getLadapLogin()
                ];
            }
            $this->getIO()->table($headers, $rows);
            $this->getIO()->comment("Vous pouvez utiliser l'ID de la personne pour afficher les détails");
        } else {

            $validator = $this->getPersonService()->getPersonById($personId, true);

            if (!$period) {
                $periods = $this->getTimesheetService()->getPeriodsValidator($validator);
                $rows = [];
                foreach ($periods as $period) {
                    $periodInfos = PeriodInfos::getPeriodInfosObj($period);
                    $rows[] = [$periodInfos->getPeriodCode(), $periodInfos->getPeriodLabel()];
                }
                $this->getIO()->title("Périodes pour $validator où des validations sont à faire");
                $headers = ["Code", "Période"];
                $this->getIO()->table($headers, $rows);
                $this->getIO()->comment("Utiliser --period=CODE_PERIOD pour déclencher le rappel");
            } else {
                $this->getIO()->title("Rappel pour $validator $periodText");

            }
        }

        return 1;
    }

    /**
     * @return TimesheetService
     */
    protected function getTimesheetService(): TimesheetService
    {
        return $this->getServicemanager()->get(TimesheetService::class);
    }
}