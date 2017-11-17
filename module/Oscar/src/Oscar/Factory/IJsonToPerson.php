<?php

namespace Oscar\Factory;

/**
 * @author Stéphane Bouvry<stephane.bouvry@unicaen.fr>
 * @date: 17-11-17 11:34
 * @copyright Certic (c) 2017
 */
interface IJsonToPerson
{

    /**
     * @param $jsonData Les données source
     * @param string $connectorName
     * @return \Oscar\Entity\Person
     */
    function getInstance($jsonData, $connectorName = null);

    /**
     * @param \Oscar\Entity\Person $object
     * @param stdClass $jsonData
     * @param null|string $connectorName
     * @return \Oscar\Entity\Person
     */
    function hydrateWithDatas($object, $jsonData, $connectorName = null);
}