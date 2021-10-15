<?php


namespace Oscar\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Gestion des whites/black listes
 *
 * @ORM\Entity
 * @ORM\Entity(repositoryClass="RecallExceptionRepository")
 */
class RecallException
{

    const TYPE_EXCLUDED = 'excluded';
    const TYPE_INCLUDED = 'included';


    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
     */
    private $id;


    /**
     * @var Person
     * @ORM\ManyToOne(targetEntity="Person")
     */
    private Person $person;

    /**
     * @var string
     * @ORM\Column(type="string")
     */
    private string $type = self::TYPE_EXCLUDED;

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
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
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @param string $type
     */
    public function setType(string $type): self
    {
        $this->type = $type;
        return $this;
    }
}