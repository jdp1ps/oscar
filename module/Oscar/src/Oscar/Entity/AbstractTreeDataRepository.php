<?php
/**
 * @author Stéphane Bouvry<stephane.bouvry@unicaen.fr>
 * @date: 18/06/15 12:05
 * @copyright Certic (c) 2015
 */

namespace Oscar\Entity;


use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Mapping\MappedSuperclass;
use Doctrine\ORM\Query\Expr\Literal;
use Oscar\Service\LoggerStdoutColor;
use Zend\ServiceManager\AbstractFactoryInterface;

/**
 * Class AbstractTreeDataRepository
 * @package Oscar\Entity
 */
class AbstractTreeDataRepository extends EntityRepository
{
    /**
     * Ajoute un nouveau Noeud $child dans $parent.
     *
     * @param AbstractTreeData $child
     * @param AbstractTreeData $parent
     */
    public function addTo( AbstractTreeData $child, AbstractTreeData $parent ){
        $parentRight = $parent->getRgt();
        $em = $this->getEntityManager();
        $child->setLft($parentRight);
        $child->setRgt($parentRight+1);


        $qb = $em->createQueryBuilder();

        $updateLeft = $em->createQueryBuilder()->select('e')
            ->from($this->getClassName(), 'e')
            ->where('e.lft > :parentRgt')
            ->getQuery()->execute(array('parentRgt'=>$parentRight));

        foreach($updateLeft as $u){
            $u->setLft($u->getLft()+2);
        }

        $updateRight = $em->createQueryBuilder()->select('e')
            ->from($this->getClassName(), 'e')
            ->where('e.rgt >= :parentRgt')
            ->getQuery()->execute(array('parentRgt'=>$parentRight));

        foreach($updateRight as $u){
            $u->setRgt($u->getRgt()+2);
        }
/*
        $em->createQueryBuilder()->update($this->getClassName(), 'e')
            ->set('e.lft', $qb->expr()->sum('e.lft', 2))
            ->where('e.lft > :parentRgt')
            ->getQuery()->execute(array('parentRgt' => $parentRight));

        $em->createQueryBuilder()->update($this->getClassName(), 'e')
            ->set('e.rgt', $qb->expr()->sum('e.rgt', 2))
            ->where('e.rgt >= :parentRgt')
            ->getQuery()->execute(array('parentRgt' => $parentRight));;
*/
       $em->persist($child);
       $em->flush();
       echo "SAVE";

    }

    public function deleteNode( AbstractTreeData $child ){
        // lft >= :lft / rgt <= :rgt
    }

    public function getRoot()
    {
        $qb = $this->getEntityManager()->createQueryBuilder('e');
        $qb->select('e')
            ->from($this->getClassName(), 'e')
            ->addOrderBy('e.lft', 'ASC');
        return $qb->getQuery()->setMaxResults(1)->getSingleResult();
    }

    public function getPath( AbstractTreeData $node)
    {
        $qb = $this->getEntityManager()->createQueryBuilder('e');

        $qb->select('e')
            ->from($this->getClassName(), 'e')
            ->where('e.lft < :lft AND e.rgt > :rgt')
            ->addOrderBy('e.lft', 'ASC');

        return $qb->getQuery()->setParameters(array(
            'lft', $node->getLft(),
            'rgt', $node->getRgt(),
        ))->setMaxResults(1)->execute();
    }

    public function getChildren( AbstractTreeData $node ){

    }

    public function getAll(){
        $query = $this->createQueryBuilder('t')->select()->addOrderBy('t.lft', 'ASC')->addOrderBy('t.rgt', 'ASC');
        return $query->getQuery()->execute();
    }
}