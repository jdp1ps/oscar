<?php

namespace Oscar\Controller;

use Oscar\Service\PersonnelService;
use Zend\View\Model\ViewModel;

/**
 * @author  Stéphane Bouvry<stephane.bouvry@unicaen.fr>
 */
class PrototypeController extends AbstractOscarController
{
    public function __construct()
    {
        /* if($_SERVER['REMOTE_ADDR'] !== '127.0.0.1')
             die("Ceci n'est pas une page web!");*/
    }

    public function ajoutContratAction()
    {
        return new ViewModel(array('url_api_staff' => $this->url()->fromRoute('api_search_staff')));
    }

    public function jsonAction()
    {
    }

    public function testDeleteAction()
    {
        die('testDeleteAction');
    }

    public function testAction()
    {
        $root = $this->getEntityManager()->getRepository('Oscar\Entity\ContractType');

        return array(
            'tree' => $root->getAll(),
        );
    }

    public function traitAction()
    {
        $infinite = new \Oscar\Entity\ProjectMember();

        $infiniteStart = new \Oscar\Entity\ProjectMember();
        $infiniteStart->setDateEnd(new \Datetime('2015-06-15'));

        $infiniteEnd = new \Oscar\Entity\ProjectMember();
        $infiniteEnd->setDateStart(new \Datetime('2015-06-15'));

        $finiteBefore = new \Oscar\Entity\ProjectMember();
        $finiteBefore->setDateStart(new \Datetime('2015-06-01'))
            ->setDateEnd(new \DateTime('2015-06-15'));

        $finiteAfter = new \Oscar\Entity\ProjectMember();
        $finiteAfter->setDateStart(new \Datetime('2015-06-15'))
            ->setDateEnd(new \DateTime('2015-06-30'));

        echo "TRUE: \n";
        var_dump($infinite->intersect($infinite));
        var_dump($infinite->intersect($infiniteStart));
        var_dump($infinite->intersect($infiniteEnd));
        var_dump($infinite->intersect($finiteBefore));
        var_dump($infinite->intersect($finiteAfter));

        var_dump($infiniteStart->intersect($finiteBefore));
        var_dump($infiniteEnd->intersect($finiteAfter));

        echo "FALSE:\n";
        var_dump($infiniteStart->intersect($infiniteEnd));
        var_dump($infiniteStart->intersect($finiteAfter));

        var_dump($infiniteEnd->intersect($infiniteStart));
        var_dump($infiniteEnd->intersect($finiteBefore));

        var_dump($finiteBefore->intersect($finiteAfter));

        echo 'MERGE:';

        $a = new \Oscar\Entity\ProjectMember();
        $a->setDateEnd(new \DateTime('2015-06-20'));

        $b = new \Oscar\Entity\ProjectMember();
        $b->setDateStart(new \DateTime('2015-06-10'));

        var_dump($a->extend($b));

        $a = new \Oscar\Entity\ProjectMember();
        $a->setDateStart(new \DateTime('2015-06-01'))
            ->setDateEnd(new \DateTime('2015-06-20'));

        $b = new \Oscar\Entity\ProjectMember();
        $b->setDateStart(new \DateTime('2015-06-10'))
            ->setDateEnd(new \DateTime('2015-06-30'));

        var_dump($a->extend($b));

        $a = new \Oscar\Entity\ProjectMember();
        $a->setDateStart(new \DateTime('2015-06-01'))
            ->setDateEnd(new \DateTime('2015-06-20'));

        $b = new \Oscar\Entity\ProjectMember();
        $b->setDateStart(null)
            ->setDateEnd(null);

        var_dump($b->extend($a));

        die('done');
    }

    public function activityAction()
    {
        return array();
    }
}
