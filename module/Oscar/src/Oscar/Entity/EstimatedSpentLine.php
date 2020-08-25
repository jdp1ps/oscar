<?php
/**
 * @author Stéphane Bouvry<stephane.bouvry@unicaen.fr>
 * @date: 01/06/15 12:44
 * @copyright Certic (c) 2015
 */

namespace Oscar\Entity;

use Cocur\Slugify\Slugify;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Oscar\Import\Data\DataExtractorDate;
use Oscar\Service\ActivityTypeService;
use Zend\Permissions\Acl\Resource\ResourceInterface;

/**
 * @package Oscar\Entity
 * @ORM\Entity
 */
class EstimatedSpentLine
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @var Activity
     * @ORM\ManyToOne(targetEntity="Activity")
     */
    private $activity;

    /**
     * @ORM\Column(type="integer")
     */
    private $year;

    /**
     * @ORM\Column(type="float")
     */
    private $amount;

    /**
     * @ORM\Column(type="string")
     */
    private $account;

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return Activity
     */
    public function getActivity(): Activity
    {
        return $this->activity;
    }

    /**
     * @param Activity $activity
     */
    public function setActivity(Activity $activity): void
    {
        $this->activity = $activity;
    }

    /**
     * @return mixed
     */
    public function getPfi()
    {
        return $this->pfi;
    }

    /**
     * @param mixed $pfi
     */
    public function setPfi($pfi)
    {
        $this->pfi = $pfi;
    }

    /**
     * @return mixed
     */
    public function getYear()
    {
        return $this->year;
    }

    /**
     * @param mixed $year
     */
    public function setYear($year)
    {
        $this->year = $year;
    }

    /**
     * @return mixed
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * @param mixed $amount
     */
    public function setAmount($amount)
    {
        $this->amount = $amount;
    }

    /**
     * @return mixed
     */
    public function getAccount()
    {
        return $this->account;
    }

    /**
     * @param mixed $account
     */
    public function setAccount($account)
    {
        $this->account = $account;
    }
}
