<?php
/**
 * @author Stéphane Bouvry<stephane.bouvry@unicaen.fr>
 * @date: 17-11-16 17:42
 * @copyright Certic (c) 2017
 */

namespace Oscar\Connector;


interface GetJsonDataStrategy
{
    /**
     * @return mixed un tableau de stdObject
     */
    public function getAll();

    /**
     * @param $id
     * @return mixed stdObject
     */
    public function getOne($id);
}