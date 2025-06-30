<?php
/**
 * Created by PhpStorm.
 * User: bouvry
 * Date: 04/10/19
 * Time: 11:49
 */

namespace Oscar\Command;


use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Query\ResultSetMapping;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class OscarCheckSequenceNumCommand extends OscarCommandAbstract
{
    protected static $defaultName = 'check:sequences-num';

    protected function configure()
    {
        $this
            ->setDescription("Mise à jour automatique de l'incrementation des séquences d'IDs pour les tables")
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output) :int
    {
        $this->addOutputStyle($output);

        $io = new SymfonyStyle($input, $output);

        $io->title("Mise à jour des séquences pour le calcule des IDS");

        $sequences = [
            "activityavenant",
            "activityavenantmodification",
            "activitydate",
            "activitymotcle",
            "activitynote",
            "activityorganization",
            "activitypayment",
            "activitypcruinfos",
            "activityperson",
            "activityrequest",
            "activityrequestfollow",
            "activitytype",
            "administrativedocument",
            "administrativedocumentsection",
            "authentification",
            "categorie_privilege",
            "contractdocument",
            "contracttype",
            "country3166",
            "currency",
            "datetype",
            "discipline",
            "estimatedspentline",
            "logactivity",
            "notification",
            "notificationperson",
            "organization",
            "organizationperson",
            "organizationrole",
            "organizationtype",
            "pcrupolecompetitivite",
            "pcrusourcefinancement",
            "pcrutypecontract",
            "person",
            "privilege",
            "project",
            "projectmember",
            "projectpartner",
            "recalldeclaration",
            "recallexception",
            "referent",
            "spentline",
            "spenttypegroup",
            "tabdocument",
            "tabsdocumentsroles",
            "timesheet",
            "timesheetcommentperiod",
            "tva",
            "typedocument",
            "unicaen_signature_observer",
            "unicaen_signature_process",
            "unicaen_signature_process_step",
            "unicaen_signature_recipient",
            "unicaen_signature_signature",
            "unicaen_signature_signatureflow",
            "unicaen_signature_signatureflowstep",
            "user_role",
            "useraccessdefinition",
            "validationperiod",
            "workpackage",
            "workpackageperson",
        ];

        $entityManager = $this->getServicemanager()->get(EntityManager::class);
        $return = self::SUCCESS;

        foreach ($sequences as $sequence) {
            $result = new ResultSetMapping();
            $msg = sprintf(" # MaJ sequence <bold>%s</bold>", $sequence);

            $query = "select setval('" . $sequence . "_id_seq',(SELECT COALESCE((SELECT MAX(id)+1 FROM " . $sequence . "), 1)), false);";
            $msg .= sprintf(" (QUERY : <bold>%s</bold>)", $query);

            try {
                $entityManager->createNativeQuery($query, $result)->execute();
                $io->writeln("<green>   OK</green> $msg");
            } catch (\Exception $e) {
                $io->writeln("<error>ERROR</error> $msg");
                $io->error(sprintf("<bold>[] %s</bold> : %s", $e->getCode(), $e->getMessage(), $e->getTraceAsString()));
                $return = self::FAILURE;
            }
        }

        return $return;
    }
}