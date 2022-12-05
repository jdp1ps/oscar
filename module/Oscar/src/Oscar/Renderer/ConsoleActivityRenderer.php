<?php


namespace Oscar\Renderer;


use Oscar\Entity\Activity;
use Oscar\Utils\StringUtils;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Style\SymfonyStyle;

class ConsoleActivityRenderer
{
    private SymfonyStyle $output;

    // Options
    private bool $showPersons = false;
    private bool $showOrganizations = false;

    /**
     * ConsoleActivityRenderer constructor.
     * @param SymfonyStyle $output
     */
    public function __construct(SymfonyStyle $output)
    {
        $this->output = $output;

        // (black, red, green, yellow, blue, magenta, cyan, white, default)

        $output->getFormatter()->setStyle('acronym', new OutputFormatterStyle('white', 'blue', ['bold']));
        $output->getFormatter()->setStyle('date', new OutputFormatterStyle('cyan', 'default'));
        $output->getFormatter()->setStyle('member', new OutputFormatterStyle('green', 'default', ['bold']));
    }

    public function showPersons( bool $show ) :void
    {
        $this->showPersons = $show;
    }

    public function showOrganizations( bool $show ) :void
    {
        $this->showOrganizations = $show;
    }

    public function render( Activity $activity ) :void
    {

        $margin = ' | ';
        $this->output->write($margin
                             . '<acronym>[' . $activity->getId() . ' : '
                             . $activity->getAcronym() . ' > '
                             . $activity->getOscarNum() .']</acronym>');
        $this->output->writeln(StringUtils::hyphenize($activity->getLabel()));


        // DATES
        $this->output->write(sprintf($margin . "Du <date>%s</date> au <date>%s</date>",
                                     $activity->getDateStartStr(),
                                     $activity->getDateEndStr()));

        $this->output->write(sprintf(" (Créé le <date>%s</date> / Maj : <date>%s</date>)",
                                     $activity->getDateCreated()->format('Y-m-d'),
                                     $activity->getDateUpdated()->format('Y-m-d')
                             ));
        if( $activity->getDateSigned() ) {
            $this->output->write(sprintf(", Signé le <date>%s</date>",
                                     $activity->getDateSignedStr()));
        } else {
            $this->output->write(" , <red>Non Signé</red>");
        }
        $this->output->write("\n");

        if( $this->showPersons ){
            // RESPONSABLES
            $this->output->write($margin . "RESPONSABLES : \n");
            $sep = '';
            foreach ($activity->getPersonsDeep(true) as $personActivity) {
                $this->output->write($margin . " - <member>" . $personActivity->getPerson()
                                     . "</member> (". $personActivity->getRoleObj() .")\n");
                $sep = ', ';
            }
        }

        if( $this->showOrganizations ){
            $this->output->write($margin . "ORGANISATIONS : \n");
            $sep = '';
            foreach ($activity->getOrganizationsDeep(true) as $orga) {
                $this->output->write($margin . " - <member>" . $orga->getOrganization() . "</member>(". $orga->getRoleObj() .") ");
                $sep = ', ';
                $this->output->write("\n");
            }
        }

        $this->output->writeln(" + ---------------\n");
    }
}