<?php
/**
 * @author Stéphane Bouvry<stephane.bouvry@unicaen.fr>
 * @date: 29/06/15 09:55
 * @copyright Certic (c) 2015
 */

namespace Oscar\Import;


use Doctrine\ORM\EntityManager;
use Monolog\Logger;

abstract class AbstractImportStrategy
{
    private $centaureConnexion;
    private $entityManager;
    private $logger;

    function __construct($centaureConnexion, EntityManager $entityManager, Logger $logger=null)
    {
        $this->centaureConnexion = $centaureConnexion;
        $this->entityManager = $entityManager;
        $this->logger = $logger;
    }

    abstract function import();

    protected function getConnexion()
    {
        return $this->centaureConnexion;
    }

    protected function getLogger()
    {
        return $this->logger;
    }

    /**
     * @return EntityManager
     */
    protected function getEntityManager()
    {
        return $this->entityManager;
    }

    protected function cleanCache(){
        $this->syncCachePdo()->query('DELETE FROM Discipline; DELETE FROM Person; DELETE FROM Project; DELETE FROM GrantSource; DELETE FROM Grant; ');
    }


    ////////////////////////////////////////////////////////////////////////////

    protected function syncCachePdo()
    {
        static $pdo;
        if (null === $pdo) {
            $this->getLogger()->debug("Récupération de la connection au cache");
            $sqllitePath = realpath(__DIR__ . '/../../../../../data/oscar_centaure.sqlite');
            if (!file_exists($sqllitePath)) {
                throw new \Exception(sprintf('Base de données de synchronisation %s absente ! ',
                    $sqllitePath));
            }

            $pdo = new \PDO('sqlite:' . $sqllitePath);
        }

        return $pdo;
    }

    private $_correspondance = array();

    protected function getCentaureId($dataType, $oscarId)
    {
        if (!isset($this->_correspondance[$dataType])) {
            $this->_correspondance[$dataType] = $this->getCorrespondance($dataType);
        }
        if (isset($this->_correspondance[$dataType]['OC'][$oscarId])) {
            return $this->_correspondance[$dataType]['OC'][$oscarId];
        }

        return null;
    }

    protected function getOscarId($dataType, $centaureId)
    {
        if (!isset($this->_correspondance[$dataType])) {
            $this->_correspondance[$dataType] = $this->getCorrespondance($dataType);
        }
        if (isset($this->_correspondance[$dataType]['CO'][$centaureId])) {
            return $this->_correspondance[$dataType]['CO'][$centaureId];
        }

        return null;
    }

    /**
     * @param $dataType Nom du model
     * @param $centaureId ID côté centaure
     * @return string | null
     * @throws \Exception
     */
    protected function getSum($dataType, $centaureId)
    {
        if (!isset($this->_correspondance[$dataType])) {
            $this->_correspondance[$dataType] = $this->getCorrespondance($dataType);
        }
        if (isset($this->_correspondance[$dataType]['SUM'][$centaureId])) {
            return $this->_correspondance[$dataType]['SUM'][$centaureId];
        }
        return null;
    }

    private function getCorrespondance($data)
    {

        $pdo = $this->syncCachePdo();

        $stt = $pdo->query('SELECT * FROM ' . $data);
        if (!$stt) {
            throw new \Exception('Impossible de charger le cache pour ' . $data);
        }
        $table = [
            'SUM' => [],
            'CO' => [],
            'OC' => [],
        ];
        $datas = $stt->fetchAll(\PDO::FETCH_ASSOC);
        $this->getLogger()->info(sprintf("Cache pour '%s' chargé avec %s enregistrement(s).", $data, count($datas)));

        foreach ($datas as $row) {
            $table['SUM'][$row['centaure_id']] = $row['checksum'];
            $table['CO'][$row['centaure_id']] = $row['oscar_id'];
            $table['OC'][$row['oscar_id']] = $row['centaure_id'];
        }


        return $table;
    }


    protected function setCorrespondance($data, $oscarId, $centaureId, $sum)
    {
        $this->getLogger()->debug(sprintf("Écriture du cache %s : oscar:%s <> centaure:%s", $data, $oscarId, $centaureId));
        $pdo = $this->syncCachePdo();
        $stt = $pdo->prepare('INSERT INTO ' . $data . '(centaure_id, oscar_id, checksum) VALUES(:c, :o, :s)');

        $stt->execute(array(
            'c' => $centaureId,
            'o' => $oscarId,
            's' => $sum
        ));
    }


    ////////////////////////////////////////////////////////////////////////////
    protected function cleanBullshitStr($bullshitStr)
    {
        return trim($bullshitStr);
    }

    protected function extractDate($bullshitDate)
    {
        $date = \DateTime::createFromFormat('Ymd', $bullshitDate);

        return $date ? $date : null;

    }

}