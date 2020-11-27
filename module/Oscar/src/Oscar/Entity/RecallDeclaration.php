<?php


namespace Oscar\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Cette classe référence les rôles GLOBAUX sur l'application.
 *
 * @ORM\Entity
 * @ORM\Entity(repositoryClass="RecallDeclarationRepository")
 */
class RecallDeclaration
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * Année de référence
     * @var integer
     * @ORM\Column(type="integer")
     */
    private $periodYear;

    /**
     * Mois de référence
     * @var integer
     * @ORM\Column(type="integer", nullable=false)
     */
    private $periodMonth;


    /**
     * @var Person
     * @ORM\ManyToOne(targetEntity="Person")
     */
    private $person;

    /**
     * @var string
     * @ORM\Column(type="string")
     */
    private $context = "declarer";

    /**
     * @var \DateTime
     * @ORM\Column(type="datetime")
     *
     */
    private $startProcess;

    /**
     * @var \DateTime
     * @ORM\Column(type="datetime")
     */
    private $lastSend;

    /**
     * @var string
     * @ORM\Column(type="text")
     */
    private $history;

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return int
     */
    public function getPeriodYear(): int
    {
        return $this->periodYear;
    }

    /**
     * @param int $periodYear
     */
    public function setPeriodYear(int $periodYear): self
    {
        $this->periodYear = $periodYear;
        return $this;
    }

    /**
     * @return int
     */
    public function getPeriodMonth(): int
    {
        return $this->periodMonth;
    }

    /**
     * @param int $periodMonth
     */
    public function setPeriodMonth(int $periodMonth): self
    {
        $this->periodMonth = $periodMonth;
        return $this;
    }

    /**
     * @return Person
     */
    public function getPerson(): Person
    {
        return $this->person;
    }

    /**
     * @param Person $person
     */
    public function setPerson(Person $person): self
    {
        $this->person = $person;
        return $this;
    }

    /**
     * @return string
     */
    public function getContext(): string
    {
        return $this->context;
    }

    /**
     * @param string $context
     */
    public function setContext(string $context): self
    {
        $this->context = $context;
        return $this;
    }


    /**
     * @return \DateTime
     */
    public function getStartProcess(): \DateTime
    {
        return $this->startProcess;
    }

    /**
     * @param \DateTime $startProcess
     */
    public function setStartProcess(\DateTime $startProcess): self
    {
        $this->startProcess = $startProcess;
        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getLastSend(): \DateTime
    {
        return $this->lastSend;
    }

    /**
     * @param \DateTime $lastSend
     */
    public function setLastSend(\DateTime $lastSend): self
    {
        $this->lastSend = $lastSend;
        return $this;
    }

    /**
     * @return string
     */
    public function getHistory(): string
    {
        return $this->history;
    }

    /**
     * @param string $historyLine
     * @return $this
     */
    public function addHistory( string $historyLine ) :self
    {
        if( $this->history == null ){
            $this->history = "";
        }
        $this->history .= $historyLine."\n";
        return $this;
    }

    public function __toString()
    {
        return sprintf('[%s] Envoyé le %s à %s pour la période %s-%s', $this->getId(), $this->getLastSend()->format('Y-m-d H:i:s'), $this->getPerson(), $this->getPeriodYear(), $this->getPeriodMonth());
    }


}