<?php

namespace Oscar\Factory;

/**
 * @author Stéphane Bouvry<stephane.bouvry@unicaen.fr>
 * @date: 17-11-17 11:30
 * @copyright Certic (c) 2017
 */
interface IJsonToOrganisation
{

    /**
     * @param $jsonData Les données source
     * @param string $connectorName
     * @return \Oscar\Entity\Organization
     */
    function getInstance($jsonData, $connectorName = null);

    /**
     * @param \Oscar\Entity\Organization $object
     * @param stdClass $jsonData
     * @param null|string $connectorName
     * @return \Oscar\Entity\Organization
     */
    function hydrateWithDatas($object, $jsonData, $connectorName = null);
}