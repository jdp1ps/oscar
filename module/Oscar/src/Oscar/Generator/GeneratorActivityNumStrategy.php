<?php
/**
 * @author Stéphane Bouvry<stephane.bouvry@unicaen.fr>
 * @date: 04/02/16 15:11
 * @copyright Certic (c) 2016
 */

namespace Oscar\Generator;


use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Id\AbstractIdGenerator;

class GeneratorActivityNumStrategy extends AbstractIdGenerator
{
    public function generate(EntityManager $em, $entity)
    {

        die('GENERATE ID for ' . $entity);
    }

}