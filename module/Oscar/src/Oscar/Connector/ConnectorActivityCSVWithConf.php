<?php
/**
 * Created by PhpStorm.
 * User: jacksay
 * Date: 31/08/17
 * Time: 13:55
 */

namespace Oscar\Connector;


use Doctrine\ORM\EntityManager;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\Query;
use Oscar\Entity\Activity;
use Oscar\Entity\ActivityOrganization;
use Oscar\Entity\ActivityPayment;
use Oscar\Entity\ActivityPerson;
use Oscar\Entity\ActivityType;
use Oscar\Entity\Currency;
use Oscar\Entity\Organization;
use Oscar\Entity\OrganizationRepository;
use Oscar\Entity\OrganizationRole;
use Oscar\Entity\OrganizationRoleRepository;
use Oscar\Entity\Person;
use Oscar\Entity\PersonRepository;
use Oscar\Entity\Project;
use Oscar\Entity\Role;
use Oscar\Entity\RoleRepository;
use Oscar\Exception\OscarException;
use Oscar\Import\Activity\FieldStrategy\FieldImportMilestoneStrategy;
use Oscar\Import\Activity\FieldStrategy\FieldImportOrganizationStrategy;
use Oscar\Import\Activity\FieldStrategy\FieldImportPaymentStrategy;
use Oscar\Import\Activity\FieldStrategy\FieldImportPersonStrategy;
use Oscar\Import\Activity\FieldStrategy\FieldImportProjectStrategy;
use Oscar\Import\Activity\FieldStrategy\FieldImportSetterStrategy;

class ConnectorActivityCSVWithConf implements ConnectorInterface
{
    private $csvDatas;
    private $config;
    private $entityManager;


    function log($msg)
    {
        echo "$msg\n";
    }


    /**
     * ConnectorActivityCSVWithConf constructor.
     * @param $csvDatas
     * @param array $config
     * @param $entityManager
     */
    public function __construct($csvDatas, array $config, $entityManager)
    {
        $this->csvDatas = $csvDatas;
        $this->config = $config;
        $this->entityManager = $entityManager;
    }

    protected function checkData($data)
    {
        return true;
    }

    /**
     * @param $uid
     * @return mixed
     * @throws NoResultException
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    protected function getActivity($uid)
    {
        /** @var Query $queryActivity */
        static $queryActivity;
        if ($queryActivity === null) {
            $queryActivity = $this->entityManager->getRepository(Activity::class)
                ->createQueryBuilder('a')
                ->where('a.centaureId = :uid')
                ->getQuery();
        }
        return $queryActivity->setParameter('uid', $uid)->getSingleResult();
    }

    /**
     * @param $roleId
     * @return mixed
     * @throws NoResultException
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    protected function getType($typeLabel)
    {
        /** @var Query $queryOrganization */
        static $queryType;
        if ($queryType === null) {
            $queryType = $this->entityManager->getRepository(ActivityType::class)
                ->createQueryBuilder('t')
                ->where('t.label = :label')
                ->getQuery();
        }
        try {
            return $queryType->setParameter('label', $typeLabel)->getSingleResult();
        } catch (\Exception $e) {
            return null;
        }
    }

    protected function getHandlerByKey($key)
    {
        $split = explode('.', $key);
        switch ($split[0]) {
            case "project":
                return new FieldImportProjectStrategy($this->entityManager);
            case "persons":
                return new FieldImportPersonStrategy($this->entityManager, $split[1]);
            case "organizations":
                return new FieldImportOrganizationStrategy($this->entityManager, $split[1]);
            case "payments":
                return new FieldImportPaymentStrategy($this->entityManager);
            case "milestones":
                return new FieldImportMilestoneStrategy($this->entityManager, $split[1]);
            default:
                throw new OscarException(sprintf("Les traitements de type %s ne sont pas pris en charge", $split[0]));
        }
    }

    protected function getHandler($index)
    {
        // Si la clef n'existe pas dans la conf on ne fait rien
        if (!array_key_exists($index, $this->config)) {
            return;
        }

        // Si la clef existe mais que la valeur de conf est vide on passe
        if (!$this->config[$index]) {
            return;
        }

        // Si la clef est une chaîne, on détermine si c'est un appel de setter
        // simple ou un mécanisme plus "avancé"
        $key = $this->config[$index];

        echo "### Traitement de la colonne $index => $key\n";

        // Chaîne
        if (is_string($key)) {
            // Chaîne : setter avancé
            if (stripos($key, '.') > 0) {
                return $this->getHandlerByKey($key);
            } // Chaîne : setter simple
            else {
                return new FieldImportSetterStrategy($key);
            }
        } elseif (is_callable($key)) {
            echo "callable (not implemented !!!)\n";
            return null;
        } // Autre ...
        else {
            return null;
        }
    }

    /**
     * Extraction automatique des chaînes de caractère, avec un séparateur optionnel
     * pour le cas des données multiples.
     *
     * 2018-06-06 : Ajout d'un trim
     *
     * @param $value
     * @param $separator
     * @return array
     */
    public function extractArrayString($value, $separator)
    {
        $out = [];
        if ($separator === null) {
            $out = [trim($value)];
        } else {
            $values = explode($separator, $value);
            foreach ($values as $v) {
                $extracted = trim($v);
                if ($extracted != "") {
                    $out[] = trim($v);
                }
            }
        }
        return $out;
    }

    /**
     * @return ConnectorRepport
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function syncAll()
    {
        $out = [];

        $i = 1;
        while ($datas = fgetcsv($this->csvDatas)) {
            $json = [
                "uid" => 'LN-' . ($i++),
                "organizations" => [],
                "status" => Activity::STATUS_ERROR_STATUS,
                "persons" => [],
                "milestones" => [],
                "payments" => [],
                "tva" => null,
                "currency" => null,
                "assietteSubventionnable" => null,
                "financialImpact" => null,
                "disciplines" => [],
                "datepfi" => null,
                "datestart" => null,
                "dateend" => null,
                "amount" => null,
                "datePFI" => null,
                "datesigned" => null,
                "pfi" => null,
            ];

            foreach ($datas as $index => $value) {
                if (!$value) {
                    continue 1;
                }

                if (!array_key_exists($index, $this->config)) {
                    continue 1;
                }

                // Si la clef existe mais que la valeur de conf est vide on passe
                if (!$this->config[$index]) {
                    continue 1;
                }
                /****/

                // Si la clef est une chaîne, on détermine si c'est un appel de setter
                // simple ou un mécanisme plus "avancé"
                $key = $this->config[$index];
                $separator = null;

                // On check si la valeur est un tableau
                // pour les configurations plus complexe
                if (is_array($key)) {
                    $key = $this->config[$index]['key'];
                    $separator = $this->config[$index]['separator'];
                }

                // ORGANIZATIONS
                if (preg_match("/organizations\.(.*)/", $key, $matches)) {
                    $role = $matches[1];

                    // Création de la clef si besoin
                    if (!array_key_exists($role, $json['organizations'])) {
                        $json['organizations'][$role] = [];
                    }
                    if (!in_array($value, $json['organizations'][$role])) {
                        $json['organizations'][$role] = array_merge(
                            $json['organizations'][$role],
                            $this->extractArrayString($value, $separator)
                        );
                    }
                } // PERSONS
                elseif (preg_match("/persons\.(.*)/", $key, $matches)) {
                    $role = $matches[1];

                    // Création de la clef si besoin
                    if (!array_key_exists($role, $json['persons'])) {
                        $json['persons'][$role] = [];
                    }
                    if (!in_array($value, $json['persons'][$role])) {
                        $json['persons'][$role] = array_merge(
                            $json['persons'][$role],
                            $this->extractArrayString($value, $separator)
                        );
                    }
                } // JALONS
                elseif (preg_match("/milestones\.(.*)/", $key, $matches)) {
                    $json['milestones'][] = [
                        "type" => $matches[1],
                        "date" => $value
                    ];
                } // VERSEMENTS
                elseif (preg_match("/payments\.([\-.\d]*)/", $key, $matches)) {
                    $datesPos = explode('.', $matches[1]);

                    // Calcule des positions pour les données
                    $paymentAmountPosition = $index;

                    $paymentDatePaymentPosition = $index + intval($datesPos[0]);
                    $paymentDatePayment = $this->getCheckedDateString($datas[$paymentDatePaymentPosition]);

                    if (count($datesPos) == 2) {
                        $paymentDatePredictedPosition = $index + intval($datesPos[1]);
                        $paymentDatePredicted = $this->getCheckedDateString($datas[$paymentDatePredictedPosition]);
                    } else {
                        $paymentDatePredicted = null;
                    }

                    $json['payments'][] = [
                        "amount" => doubleval(str_replace(',', '.', $datas[$paymentAmountPosition])),
                        "date" => $paymentDatePayment,
                        "predicted" => $paymentDatePredicted,
                    ];
                } // PFI
                elseif ($key == "currency") {
                    $json['currency'] = $value;
                } // TVA
                elseif ($key == "tva") {
                    $json['tva'] = floatval(str_replace(',', '.', $value));
                } // assietteSubventionnable
                elseif ($key == "assietteSubventionnable") {
                    $json['assietteSubventionnable'] = (float)$value;
                } // financialImpact
                elseif ($key == "financialImpact") {
                    $json['financialImpact'] = $value;
                } // PFI
                elseif ($key == "PFI") {
                    $json['pfi'] = $value;
                } // Status
                elseif ($key == "status") {
                    $json['status'] = $value;
                } // Disciplines
                elseif ($key == "disciplines") {
                    $findDisciplines = explode('#', $value);
                    foreach ($findDisciplines as $discipline) {
                        if (!in_array($discipline, $json['disciplines'])) {
                            $json['disciplines'][] = $discipline;
                        }
                    }
                } // datePFI
                elseif ($key == "datePFI") {
                    $json['datepfi'] = $value;
                } // Type
                elseif ($key == "type") {
                    $json['type'] = $value;
                } // Type
                elseif ($key == "amount") {
                    $json['amount'] = doubleval($value);
                } // dateStart
                elseif ($key == "dateStart") {
                    $json['datestart'] = $value;
                } // dateEnd
                elseif ($key == "dateEnd") {
                    $json['dateend'] = $value;
                } // dateSigned
                elseif ($key == "dateSigned") {
                    $json['datesigned'] = $value;
                } // label
                elseif ($key == "label") {
                    $json['label'] = $value;
                } // description
                elseif ($key == "description") {
                    $json['description'] = $value;
                } // uid
                elseif ($key == "uid") {
                    $json['uid'] = $value;
                } // project
                elseif ($key == "project.acronym") {
                    $json['acronym'] = $value;
                } // project
                elseif ($key == "project.label") {
                    $json['projectlabel'] = $value;
                } else {
                    throw new OscarException("La clef '$key' n'est pas géré dans la configuration");
                }
            }

            if (!array_key_exists('projectlabel', $json) && array_key_exists('acronym', $json)) {
                $json['projectlabel'] = $json['acronym'];
            }

            $out[] = $json;
        }

        return $out;
    }

    /**
     * Évalutation d'un date avant de la retourner correctement formattée.
     *
     * @param $string
     */
    protected function getCheckedDateString($string)
    {
        if ($string == "") {
            return "";
        }
        if (!preg_match('/(\d{4})-(\d{2})-(\d{2})/', $string)) {
            throw new OscarException(
                "Format de date '$string' inattendu, assurez vous que la forme ISO YYYY-MM-JJ est respectée."
            );
        }
        $date = new \DateTime($string);
        return $date->format('Y-m-d');
    }

    public function syncOne($key)
    {
        // TODO: Implement syncOne() method.
    }
}