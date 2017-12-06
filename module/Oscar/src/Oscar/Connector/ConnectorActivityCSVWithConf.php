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


    function log( $msg ){
        echo "$msg\n";
    }


    public function __construct( $csvDatas, array $config, EntityManager $entityManager )
    {
        $this->csvDatas = $csvDatas;
        $this->config = $config;
        $this->entityManager = $entityManager;
    }

    protected function checkData( $data ){
        return true;
    }

    /**
     * @param $uid
     * @return mixed
     * @throws NoResultException
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    protected function getActivity( $uid ){
        /** @var Query $queryActivity */
        static $queryActivity;
        if( $queryActivity === null ){
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
    protected function getType( $typeLabel ){
        /** @var Query $queryOrganization */
        static $queryType;
        if( $queryType === null ){
            $queryType = $this->entityManager->getRepository(ActivityType::class)
                ->createQueryBuilder('t')
                ->where('t.label = :label')
                ->getQuery();
        }
        try {
            return $queryType->setParameter('label', $typeLabel)->getSingleResult();
        }catch( \Exception $e ){
            return null;
        }
    }

    protected function getHandlerByKey( $key ){
        $split = explode('.', $key);
        switch( $split[0] ){
            case "project":
                return new FieldImportProjectStrategy($this->entityManager);
            case "persons":
                return NEW FieldImportPersonStrategy($this->entityManager, $split[1]);
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

    protected function getHandler( $index ){
        // Si la clef n'existe pas dans la conf on ne fait rien
        if( !array_key_exists($index, $this->config) )
            return;

        // Si la clef existe mais que la valeur de conf est vide on passe
        if( !$this->config[$index] )
            return;

        // Si la clef est une chaîne, on détermine si c'est un appel de setter
        // simple ou un mécanisme plus "avancé"
        $key = $this->config[$index];

        echo "### Traitement de la colonne $index => $key\n";

        // Chaîne
        if( is_string($key) ){

            // Chaîne : setter avancé
            if( stripos($key, '.') > 0 ){
                return $this->getHandlerByKey( $key );
            }

            // Chaîne : setter simple
            else {
                return new FieldImportSetterStrategy($key);
            }
        }

        elseif (is_callable($key)) {
            echo "callable (not implemented !!!)\n";
            return null;
        }

        // Autre ...
        else {
            return null;
        }
    }

    /**
     * @return ConnectorRepport
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function syncAll()
    {
        $repport = new ConnectorRepport();


        // Devise par défaut
        $defaultCurrency = $this->entityManager->getRepository(Currency::class)->find(1);

        while($datas = fgetcsv($this->csvDatas)){
            $activity = new Activity();
            $this->entityManager->persist($activity);
            foreach ($datas as $index => $value ){
                if( !$value ) continue;
                $handler = $this->getHandler($index);
                if( $handler != null )
                    $handler->run($activity, $datas, $index);
            }
            $this->entityManager->flush($activity);
        }
        return $repport;
    }

    public function syncOne($key)
    {
        // TODO: Implement syncOne() method.
    }
}