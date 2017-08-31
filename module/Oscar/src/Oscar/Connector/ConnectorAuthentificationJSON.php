<?php
/**
 * Created by PhpStorm.
 * User: jacksay
 * Date: 31/08/17
 * Time: 10:13
 */

namespace Oscar\Connector;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Query\ResultSetMapping;
use Oscar\Entity\Authentification;
use Oscar\Entity\Role;
use Zend\Crypt\Password\Bcrypt;
use Zend\ServiceManager\ServiceLocatorAwareTrait;

/**
 * Cette classe assure le chargement d'authentification en base de données
 * à partir d'une source JSON.
 *
 * Class ConnectorAuthentificationJSON
 * @package Oscar\Connector
 */
class ConnectorAuthentificationJSON implements ConnectorInterface
{
    private $jsonDatas;
    private $entityManager;
    private $bcrypt;

    /**
     * ConnectorAuthentificationJSON constructor.
     * @param array $jsonData
     * @param EntityManager $entityManager
     */
    public function __construct( array $jsonData, EntityManager $entityManager, Bcrypt $bcrypt )
    {
        $this->jsonDatas = $jsonData;
        $this->entityManager = $entityManager;
        $this->bcrypt = $bcrypt;
    }

    /**
     * @return \Doctrine\ORM\EntityRepository
     */
    protected function getAuthentificationRepository(){
        return $this->entityManager->getRepository(Authentification::class);
    }

    protected function checkEntry( $entry ){
        $this->checkEntryField($entry, 'login');
        $this->checkEntryField($entry, 'email');
        $this->checkEntryField($entry, 'password');
        $this->checkEntryField($entry, 'displayname');
    }

    protected function checkEntryField( $entry, $field ){
        if( ! $entry->$field ){
            throw new \Exception(sprintf("Le champs %s doit être renseigné.", $field));
        }
    }


    public function syncAll()
    {
        $repport = new ConnectorRepport();
        foreach ($this->jsonDatas as $entry ){
            try {
                $this->synchronize($entry, $repport);
            } catch (\Exception $e ){
                $repport->adderror($e->getMessage());
            }
        }
        return $repport;
    }

    public function syncOne($key)
    {
        // TODO: Implement syncOne() method.
    }

    /**
     * Syncronisation des données d'une entrée.
     *
     * @param $entry
     * @param ConnectorRepport $repport
     */
    protected function synchronize( $entry, ConnectorRepport &$repport ){
        $this->checkEntry($entry);

        $action = "mettre à jour";

        /** @var Authentification $auth */
        $auth = $this->getAuthentificationRepository()->findOneBy([
            'username' => $entry->login
        ]);

        // Si non-trouvé, on le cré
        if( !$auth ) {
            $action = 'créé';
            $auth = new Authentification();
            $this->entityManager->persist($auth);
        }

        // Affectation des données
        if( strpos($entry->password, 'bcrypt:') === 0 ){
            $password = str_replace('bcrypt:', '', $entry->password);
        } else {
            $password = $this->bcrypt->create($entry->password);
        }
        $auth->setPassword($password);
        $auth->setDisplayName($entry->displayname);
        $auth->setUsername($entry->login);
        $auth->setEmail($entry->email);

        try {
            $this->entityManager->flush();
            if( $action == 'créé' ){
                $repport->addadded(sprintf('Ajout de %s(%s)', $entry->login, $entry->email));
            } else {
                $repport->addupdated(sprintf('Mise à jour de %s(%s)', $entry->login, $entry->email));
            }
        } catch (\Exception $e ){
            $repport->adderror(sprintf('Impossible de %s %s(%s) : %s', $action, $entry->login, $entry->email, $e->getMessage()));
            return;
        }

        // Traitement des rôles
        if( $entry->approles && count($entry->approles) > 0 ){
            foreach ($entry->approles as $roleStr ){

                $role = $this->entityManager
                    ->getRepository(Role::class)
                    ->findOneBy(['roleId' => $roleStr ]);

                if( !$role ){
                    $repport->adderror(sprintf("Le role %s n'existe pas dans Oscar, impossible de l'ajouter pour %s.", $roleStr, $auth->getDisplayName()));
                } else if( !$auth->hasRole($role) ){
                    $authId = $auth->getId();
                    $roleId = $role->getId();
                    try {
                        $query = $this->entityManager
                            ->createNativeQuery("INSERT INTO authentification_role VALUES($authId, $roleId)", new ResultSetMapping());
                        $query->execute();
                        $repport->addadded(sprintf("%s a le rôle %s", $auth->getDisplayName(), $roleStr));
                    } catch (\Exception $e ){
                        $repport->adderror(sprintf("BD ERROR : Impossible d'ajouter le rôle %s pour %s : %s", $roleStr, $auth->getDisplayName(), $e->getMessage()));
                    }
                }
            }
        }
    }
}