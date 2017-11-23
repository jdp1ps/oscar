<?php
/**
 * @author Stéphane Bouvry<stephane.bouvry@unicaen.fr>
 * @date: 17-11-23 14:37
 * @copyright Certic (c) 2017
 */

namespace Oscar\Import\Activity\FieldStrategy;


use Doctrine\ORM\EntityManager;

abstract class AbstractFieldImportStrategy implements IFieldImportStrategy
{
    private $entityManager;
    private $key;

    /**
     * AbstractFieldImportStrategy constructor.
     * @param $entityManager
     * @param $key
     */
    public function __construct($entityManager, $key=null)
    {
        $this->entityManager = $entityManager;
        $this->key = $key;
    }

    /**
     * @return EntityManager
     */
    public function getEntityManager()
    {
        return $this->entityManager;
    }

    /**
     * @return string
     */
    public function getKey()
    {
        return $this->key;
    }

}