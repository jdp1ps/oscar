<?php
/**
 * Created by PhpStorm.
 * User: bouvry
 * Date: 04/10/19
 * Time: 11:49
 */

namespace Oscar\Command;

use Exception;
use Oscar\Renderer\ConsoleActivityRenderer;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class OscarActivitySearchCommand extends OscarAdvancedCommandAbstract
{
    protected static $defaultName = OscarCommandAbstract::COMMAND_ACTIVITY_SEARCH;


    const ARG_SEARCH = 'search';

    const OPT_PER = 'members';
    const OPT_ORG = 'organizations';
    const OPT_SRT = 'sort';
    const OPT_DIR = 'direction';
    const OPT_FIL = 'filters';
    const OPT_MUT = 'mute';

    protected function configure()
    {
        parent::configure();
        $this
            ->setDescription("Recherche dans les activités")
            ->addOption(self::OPT_PER, 'p',
                        InputOption::VALUE_NONE,
                        'Afficher les personnes')
            ->addOption(self::OPT_ORG, 's',
                        InputOption::VALUE_NONE,
                        'Afficher les structures')
            ->addOption(self::OPT_SRT, 't',
                        InputOption::VALUE_OPTIONAL,
                        'Trie des activités',
                        'hit')
            ->addOption(self::OPT_DIR, 'd',
                        InputOption::VALUE_OPTIONAL,
                        'Direction du trie des activités',
                        'ASC')
            ->addOption(self::OPT_FIL, 'r',
                        InputOption::VALUE_OPTIONAL,
                        'Filtres',
                        '')
            ->addOption(self::OPT_MUT, 'm',
                        InputOption::VALUE_NONE,
                        "N'affiche pas les activités")

            ->addArgument(self::ARG_SEARCH)
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        parent::execute($input, $output);

        $search = $input->getArgument(self::ARG_SEARCH);
        $sort = $input->getOption(self::OPT_SRT);
        $direction = $input->getOption(self::OPT_DIR);
        $filtersOption = $input->getOption(self::OPT_FIL);
        $muted = $input->getOption(self::OPT_MUT);

        $filters = $filtersOption != "" ? explode('|', $filtersOption) : [];

        $this->getIO()->title("Recherche '$search' (sort: $sort - dir: $direction)");


        try {
            $options = [
                'sort' => $sort,
                'direction' => $direction,
                'filters' => $filters
            ];

            $result = $this->getProjectGrantService()->searchActivities($search, $options);
            $showPersons = $input->getOption(self::OPT_PER);
            $showOrganizations = $input->getOption(self::OPT_ORG);

            if( !$muted ){
                $render = new ConsoleActivityRenderer($this->getIO());
                $render->showPersons($showPersons);
                $render->showOrganizations($showOrganizations);
                foreach ($result['activities'] as $r) {
                    $render->render($r);
                }
            }


            $this->getIO()->writeln(sprintf("version : <bold>%s</bold>", $result['version']));
            $this->getIO()->writeln(sprintf("date : <bold>%s</bold>", $result['date']));
            $this->getIO()->writeln(sprintf("options : <bold>%s</bold>", $result['options']));
            $this->getIO()->writeln(sprintf("search : <bold>%s</bold> (<bold>%s</bold> résultat(s) en %s ms)",
                                            $result['search_text'],
                                            $result['search_text_total'],
                                            $result['search_text_time']));

            $this->getIO()->writeln("FILTRES : ");
            foreach ($result['filters_infos'] as $filter_info) {
                $this->getIO()->writeln(json_encode($filter_info));
            }

            $this->getIO()->writeln(sprintf("Résultats total : <bold>%s</bold> (%s ms)",
                                            $result['total'], $result['total_time']));
            $this->getIO()->writeln(sprintf("Résultats affichés : <bold>%s</bold> (%s ms)",
                                            $result['total_page'], $result['total_page_time']));

        } catch (Exception $e) {
            return $this->finalFatalError($e);
        }
        return $this->finalSuccess("FIN");
    }
}