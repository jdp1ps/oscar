<?php


namespace UnicaenLdap;

use Countable;
use Iterator;
use UnicaenLdap\Service\Service;
use UnicaenLdap\Entity\Entity;


/**
 * Liste d'entités
 */
class Collection implements Iterator, Countable
{

    /**
     *
     *
     * @var Service
     */
    private $service;

    /**
     * Liste des identifiants à parcourir
     *
     * @var string[]
     */
    private $data;

    /**
     *
     * @var integer
     */
    private $index = 0;




    /**
     * Constructor.
     *
     * @param Service $service
     * @param string[] $data
     */
    public function __construct( Service $service, array $data )
    {
        $this->service = $service;
        $this->data = $data;
        $this->index = 0;
    }

    /**
     * Returns the number of items in current result
     * Implements Countable
     *
     * @return int
     */
    public function count()
    {
        return count($this->data);
    }

    /**
     * Return the current result item
     * Implements Iterator
     *
     * @return Entity
     */
    public function current()
    {
        $current = $this->data[$this->index];
        return $this->service->get($current);
    }

    /**
     * Return the current result item key
     * Implements Iterator
     *
     * @return int|null
     */
    public function key()
    {
        return $this->index;
    }

    /**
     * Move forward to next result item
     * Implements Iterator
     *
     */
    public function next()
    {
        ++$this->index;
    }

    /**
     * Rewind the Iterator to the first result item
     * Implements Iterator
     *
     * @throws Exception\LdapException
     */
    public function rewind()
    {
        $this->index = 0;
    }

    /**
     * Check if there is a current result item
     * after calls to rewind() or next()
     * Implements Iterator
     *
     * @return bool
     */
    public function valid()
    {
        return isset($this->data[$this->index]);
    }
}
