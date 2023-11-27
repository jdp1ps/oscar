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
use Oscar\Entity\Organization;
use Oscar\Entity\Person;
use Oscar\Entity\Role;
use Oscar\Service\ConnectorService;
use Oscar\Service\OrganizationService;
use Oscar\Service\OscarConfigurationService;
use Oscar\Service\OscarUserContext;
use Oscar\Service\PersonService;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class OscarOrganizationsSyncRnsrCommand extends OscarCommandAbstract
{
    protected static $defaultName = 'organizations:sync-rnsr';

    protected function configure()
    {
        $this
            ->setDescription("Execute la synchronisation via le service RNSR");
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->addOutputStyle($output);

        $io = new SymfonyStyle($input, $output);

        $io->title("Synchronisation des RNSR");
        $url = 'https://data.enseignementsup-recherche.gouv.fr/api/records/1.0/search/?dataset=fr-esr-structures-recherche-publiques-actives&q=%s';



        /** @var OrganizationService $organizationService */
        $organizationService = $this->getServicemanager()->get(OrganizationService::class);

        $ch = curl_init();
        try {
            $organizations = $organizationService->getOrganizationWithRnsr();
            /** @var Organization $organization */
            foreach ($organizations as $organization) {
                $rnsr = $organization->getRnsr();

                echo sprintf("%s [%s] %s\n",
                    $rnsr,
                    $organization->getShortName(),
                    $organization->getFullName());

                try {
                    curl_setopt($ch, CURLOPT_URL, sprintf($url, $rnsr));
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                    $output = curl_exec($ch);
                    $data = json_decode($output, true);
                    $record = null;

                    if(array_key_exists('records', $data)){
                        $records = $data['records'];

                        if( count($records) == 0 ){
                            throw new \Exception("Pas de donnée dans l'API pour $rnsr");
                        }

                        if( count($records) > 1 ){
                            foreach ($records as $r) {
                                if( $r['fields']['numero_national_de_structure'] == $rnsr ){
                                    $record = $r;
                                }
                            }
                            if( $record == null ){
                                throw new \Exception("Trop de donnée dans l'API pour $rnsr, aucune entrée ne correspond");
                            }
                        }

                        if( count($records) == 1 ){
                            $record = $records[0];
                        }

                        $umr = "";
                        $umr_data = $record['fields']['label_numero'];
                        if( strpos(',', $umr_data) >= 0 ){
                            $split = explode(';', $umr_data);
                            foreach ($split as $num){
                                if($umr == "" && $num != ""){
                                    $umr = $num;
                                }
                            }
                        } else {
                            $umr = $umr_data;
                        }
                        if( $organization->getLabintel() != $umr ){
                            echo " - set '$umr' to '$organization'\n";
                            $organization->setLabintel($umr);
                        }
                    }

                    $organizationService->getEntityManager()->flush();

                } catch (\Exception $e){
                    echo "Error : " . $e->getMessage()."\n";
                }
            }
            curl_close($ch);
        } catch (\Exception $e) {
            $noRebuild = true;
            $io->error($e->getMessage());
        }
    }
}