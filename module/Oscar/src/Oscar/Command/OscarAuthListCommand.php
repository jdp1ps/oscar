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
use Oscar\Service\OscarUserContext;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class OscarAuthListCommand extends OscarCommandAbstract
{
    protected static $defaultName = 'oscar:auth:list';

    /**
     * @return array|null
     */
    protected function getFields(){
        static $fields;
        if( $fields === null ){
            $fields = [
                "ID" => "id",
                "Login" => "username",
                "Identité" => "displayName",
                "Email" => "email",
                "Connexion" => "dateLogin",
                'IDPERSON' => "personId"
            ];
        }
        return $fields;
    }

    protected function configure()
    {
        $this
            ->setDescription("Affiche la liste des authentifications")
            ->setHelp("")
            ->addOption('sort', 's', InputOption::VALUE_OPTIONAL, 'Champ utilisé pour le trie ('. implode(', ', array_values($this->getFields())).')', 'id')
            ->addOption('search', 'e', InputOption::VALUE_OPTIONAL, 'Expression de recherche sur le nom/email/identifiant')
            ->addOption('desc', 'd', InputOption::VALUE_NONE, "Direction du trie DESC")
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->addOutputStyle($output);

        // Récupération des options
        $fieldSortInput = $input->getOption('sort');
        $search = $input->getOption('search');

        // Options de recherche / trie
        $options = [];
        $sort = 'id';

        if( !(in_array($fieldSortInput, array_keys($this->getFields())) || in_array($fieldSortInput, array_values($this->getFields())))  ){
            $output->writeln("<error>Le champ $fieldSortInput n'est pas valide pour le trie, les champs valides sont : ". implode(", ", array_values($this->getFields())) ."</error>");
            return;
        }
        foreach ($this->getFields() as $label=>$key) {
            if( $label == $fieldSortInput || $key == $fieldSortInput ){
                $sort = $key;
            }
        }
        if( $sort === null ){
            $output->writeln(sprintf("<error>Le champ '%s' est invalide</error>", $fieldSortInput));
            return;
        }
        $options = ['sort' => $sort];

        if( $input->getOption('search') ){
            $options['search'] = $input->getOption('search');
        }


        if( $input->getOption('desc') === false ){
            $options['direction'] = 'asc';
        } else {
            $options['direction'] = 'desc';
        }

        $output->writeln("Recherche des authentification", OutputInterface::VERBOSITY_VERBOSE);


        $table = new Table($output);
        $table->setHeaderTitle("Utilisateurs OSCAR©");
        $table->setHeaders(array_keys($this->getFields()));

        $rows = [];

        /** @var OscarUserContext $oscaruserContext */
        $oscaruserContext = $this->getServicemanager()->get(OscarUserContext::class);

        /** @var Authentification $authentification */
        foreach ($oscaruserContext->getAuthentifications($options) as $authentification) {
            $dateLogin = "<none>jamais</none>";
            if($authentification['dateLogin']){
                $m = new Moment($authentification['dateLogin']->format('Y-m-d\TH:i:sO'));
                $dateLogin = $m->format('Y/m/d H:i') . ' (<bold>'. $m->fromNow()->getRelative() . '</bold>)';
            }
            $rows[] = [
                sprintf("<id>%s</id>", $authentification['id']),
                sprintf("<bold>%s</bold>", $authentification['username']),
                $authentification['displayName'],
                sprintf("<link>%s</link>", $authentification['email']),
                $dateLogin,
                $authentification['IDPERSON'] ? sprintf("<id>%s</id>", $authentification['IDPERSON']) : "<none>Non</none>"
            ];
        }
        $table->setRows($rows);
        $table->render();
    }
}