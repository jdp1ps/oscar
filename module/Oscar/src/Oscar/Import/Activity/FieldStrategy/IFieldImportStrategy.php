<?php
/**
 * @author Stéphane Bouvry<stephane.bouvry@unicaen.fr>
 * @date: 17-11-22 15:27
 * @copyright Certic (c) 2017
 */

namespace Oscar\Import\Activity\FieldStrategy;


use Oscar\Entity\Activity;

interface IFieldImportStrategy
{
    /**
     * Application du traitement
     *
     * @var Activity $activity
     * @var array $datas
     * @var integer $index
     */
    public function run( &$activity, $datas, $index);
}