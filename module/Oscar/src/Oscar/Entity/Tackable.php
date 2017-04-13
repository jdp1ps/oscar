<?php
/**
 * @author Stéphane Bouvry<stephane.bouvry@unicaen.fr>
 * @date: 03/11/15 14:36
 * @copyright Certic (c) 2015
 */

namespace Oscar\Entity;


interface Tackable
{
    const STATUS_ACTIVE = 1;
    const STATUS_DELETE = 0;

    /**
     * @return mixed
     */
    public function getStatus();

    /**
     * @return \DateTime
     */
    public function getDateCreated();

    /**
     * @return \DateTime
     */
    public function getDateUpdated();

    /**
     * @return \DateTime
     */
    public function getDateDeleted();
    /**
     * @return Person
     */
    public function getCreatedBy();

    /**
     * @return mixed
     */
    public function getUpdatedBy();

    /**
     * @return mixed
     */
    public function getDeletedBy();
}