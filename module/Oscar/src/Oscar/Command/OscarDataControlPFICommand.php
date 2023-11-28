<?php
/**
 * Created by PhpStorm.
 * User: bouvry
 * Date: 04/10/19
 * Time: 11:49
 */

namespace Oscar\Command;

use Exception;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class OscarDataControlPFICommand extends OscarAdvancedCommandAbstract
{
    protected static $defaultName = OscarCommandAbstract::COMMAND_DATACONTROL_PFI;

    protected function configure()
    {
        parent::configure();
        $this
            ->setDescription("Regarde si les PFI des activités correspondent au format spécifié dans le configuration");
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        parent::execute($input, $output);

        $strict = $this->getOscarConfigurationService()->isPfiStrict();
        $format = "%s";
        if( $strict ){
            $format = "<red>%s</red>";
        }
        $this->getIO()->writeln("Mode strict : <bold>" .($strict ? "<green>OUI</green>" : "<red>non</red>") . "</bold>");
        $regex = $this->getOscarConfigurationService()->getValidationPFI();
        $this->getIO()->writeln("Regex : <bold>" . ($regex) . "</bold>");


        if( $strict && !$regex ){
            $this->getIO()->error("Le mode STRICT a été activé sans regex défini");
        }

        if( !$regex ) {
            $this->getIO()->writeln('<warning>Pas de REGEX défini</warning>');
            $pfis = $this->getProjectGrantService()->getActivityRepository()->getDistinctPFI();
            foreach ($pfis as $pfi) {
                $this->getIO()->writeln(" - <bold>" . $pfi . "</bold>");
                return 0;
            }
        }

        $repport = $this->getProjectGrantService()->checkPFIRegex($regex, false);
        if( $repport['error'] ){
            $this->getIO()->error($repport['error']);
        }
        $sep = "";
        $this->getIO()->write("PFI conforme : ");
        foreach ($repport['valids'] as $pfi) {
            $this->getIO()->write("$sep<green>$pfi</green>");
            $sep = ", ";
        }

        $sep = "";
        $this->getIO()->write("\nPFI non-conforme : ");
        foreach ($repport['warnings'] as $pfi) {
            $this->getIO()->write("$sep<red>$pfi</red>");
            $sep = ", ";
        }
        $this->getIO()->writeln("");


        return 0;
    }
}