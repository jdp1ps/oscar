<?php
/**
 * @author Stéphane Bouvry<stephane.bouvry@unicaen.fr>
 * @date: 25/09/15 12:25
 * @copyright Certic (c) 2015
 */

namespace Oscar\Utils;


use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Traversable;
use Zend\Stdlib\ArrayObject;

class UnicaenDoctrinePaginator implements \Countable, \IteratorAggregate
{
    /** @var integer Page courante (commence à 1) */
    private $currentPage;

    /** @var integer Résultats par page */
    private $resultsByPage;

    private $count = null;

    /** @var null Iterator */
    private $iterator = null;

    function __construct(QueryBuilder $qb, $currentPage=1, $resultsByPage=50, Query $countQuery = null )
    {
        $this->currentPage = $currentPage;
        $this->resultsByPage = $resultsByPage;

        $paginator = new Paginator($qb, true);
        $paginator->getQuery()
            ->setFirstResult(($this->getCurrentPage()-1) * $this->getResultsByPage())
            ->setMaxResults($this->getResultsByPage());

        $this->iterator = $paginator->getIterator();
        $this->count = $paginator->count();

        /*
        if( $countQuery ) {
            $this->count = $countQuery->getSingleScalarResult();
            $this->iterator = $paginator->getIterator();
        } else {
            //die('nop');
            $this->iterator = $paginator->getIterator();
            $this->count = $paginator->count();
        }*/
    }



    public function getCountPage()
    {
        return ceil($this->count() / $this->getResultsByPage());
    }

    /**
     * @return int
     */
    public function getResultsByPage()
    {
        return $this->resultsByPage;
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Retrieve an external iterator
     * @link http://php.net/manual/en/iteratoraggregate.getiterator.php
     * @return Traversable An instance of an object implementing <b>Iterator</b> or
     * <b>Traversable</b>
     */
    public function getIterator()
    {
        return $this->iterator;
    }

    /**
     * @return int
     */
    public function getCurrentPage()
    {
        return $this->currentPage;
    }



    /**
     * (PHP 5 &gt;= 5.1.0)<br/>
     * Count elements of an object
     * @link http://php.net/manual/en/countable.count.php
     * @return int The custom count as an integer.
     * </p>
     * <p>
     * The return value is cast to an integer.
     */
    public function count()
    {
        return $this->count;
    }
}