<?php
/**
 * @author Stéphane Bouvry<stephane.bouvry@unicaen.fr>
 * @date: 19/11/15 08:57
 * @copyright Certic (c) 2015
 */

namespace Oscar\Entity;


interface ITrackable
{
    // Tous les status différents de 1 doivent être exclus des résultats courant.
    const STATUS_DELETE = 0;
    const STATUS_ACTIVE = 1;
    const STATUS_DRAFT = 2;
    const STATUS_CONFLICT = 3;
    const STATUS_TOVALIDATE = 5;

    /**
     * @return integer|null
     */
    public function getId();

    /**
     * @return integer
     */
    public function getStatus();

    /**
     * @param $status
     * @return mixed
     */
    public function setStatus($status);

    /**
     * @return \DateTime
     */
    public function getDateCreated();

    /**
     * @param \DateTime $dateCreated
     * @return mixed
     */
    public function setDateCreated($dateCreated);

    /**
     * @return \DateTime
     */
    public function getDateUpdated();

    /**
     * @param \DateTime $dateUpdated
     * @return mixed
     */
    public function setDateUpdated($dateUpdated);

    /**
     * @return \DateTime
     */
    public function getDateDeleted();

    /**
     * @param \DateTime $dateDeleted
     * @return mixed
     */
    public function setDateDeleted($dateDeleted);

    /**
     * @return Person
     */
    public function getCreatedBy();

    /**
     * @param \DateTime $createdBy
     * @return mixed
     */
    public function setCreatedBy($createdBy);

    /**
     * @return mixed
     */
    public function getUpdatedBy();

    /**
     * @param \DateTime $updatedBy
     * @return mixed
     */
    public function setUpdatedBy($updatedBy);

    /**
     * @return mixed
     */
    public function getDeletedBy();

    /**
     * @param \DateTime $deletedBy
     * @return mixed
     */
    public function setDeletedBy($deletedBy);
}