<?php
/**
 * @author Stéphane Bouvry<stephane.bouvry@unicaen.fr>
 * @date: 23/10/15 10:02
 * @copyright Certic (c) 2015
 */

namespace Oscar\Entity;

use Cocur\Slugify\Slugify;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\JoinTable;
use Doctrine\ORM\Mapping\ManyToMany;
use UnicaenSignature\Entity\Db\Process;
use UnicaenSignature\Entity\Db\SignatureFlow;

/**
 * @ORM\Entity(repositoryClass="ContractDocumentRepository")
 */
class ContractDocument extends AbstractVersionnedDocument
{

    const LOCATION_LOCAL_FILE = 'local';
    const LOCATION_URL = 'url';

    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @var Activity
     * @ORM\ManyToOne(targetEntity="Activity", inversedBy="documents")
     */
    private $grant;

    /**
     * Centaure ID.
     *
     * @var string
     * @ORM\Column(type="string", nullable=true)
     */
    private $centaureId;

    /**
     * Type d'activité
     *
     * @var TypeDocument
     * @ORM\ManyToOne(targetEntity="TypeDocument")
     */
    private $typeDocument;

    /**
     * Onglet où est affecté ce document
     *
     * @var ?TabDocument
     * @ORM\ManyToOne(targetEntity=TabDocument::class)
     */
    private ?TabDocument $tabDocument = null;

    /**
     * Personnes associées pour visualiser le document (cas de figure document privé)
     *
     * @ManyToMany(targetEntity="Person", inversedBy="documents")
     * @JoinTable(name="persons_documents")
     */
    private $persons;

    /**
     * Ce document est privé ou non false par défaut
     *
     * @var boolean
     * @ORM\Column(type="boolean", nullable=true)
     */

    private ?bool $private = false;

    /**
     * Ce document est éligible à un flow de signature
     *
     * @var boolean
     * @ORM\Column(type="boolean", nullable=false, options={"default":"false"})
     */

    private bool $signable = false;

    /**
     * Flow de signature
     *
     * @var Process|null
     * @ORM\ManyToOne(targetEntity="UnicaenSignature\Entity\Db\Process")
     */
    private Process|null $process = null;

    /**
     * Date de dépôt du fichier.
     *
     * @var \DateTime|null
     * @ORM\Column(type="date", nullable=true)
     */
    private \DateTime|null $dateDeposit = null;

    /**
     * Date d'envoi du fichier.
     *
     * @var \DateTime|null
     * @ORM\Column(type="date", nullable=true)
     */
    private \DateTime|null $dateSend = null;

    /**
     * @var string Emplacement (URL ou fichier physique)
     *
     * @ORM\Column(type="string", options={"default":"local"})
     */
    private $location;


    public function __construct()
    {
        parent::__construct();
        $this->persons = new ArrayCollection();
        $this->location = self::LOCATION_LOCAL_FILE;
    }



    ////////////////////////////////////////////////////////////////////////////
    //
    // GETTER / SETTER
    //
    ////////////////////////////////////////////////////////////////////////////

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getLocation(): string
    {
        return $this->location;
    }

    /**
     * @param string $location
     */
    public function setLocation(string $location): self
    {
        $this->location = $location;
        return $this;
    }

    /**
     * @return Activity
     */
    public function getGrant()
    {
        return $this->grant;
    }

    /**
     * Methode similaire à getGrant juste pour avoir un nom sémantique parlant
     * Historique Grant lié à l'origine d'Oscar
     *
     * @return Activity
     */
    public function getActivity(): Activity
    {
        return $this->getGrant();
    }

    /**
     * @param Activity $grant
     */
    public function setGrant($grant)
    {
        $this->grant = $grant;

        return $this;
    }

    /**
     * @return string
     */
    public function getCentaureId()
    {
        return $this->centaureId;
    }

    /**
     * @param string $centaureId
     */
    public function setCentaureId($centaureId)
    {
        $this->centaureId = $centaureId;

        return $this;
    }

    /**
     * @return TypeDocument
     */
    public function getTypeDocument()
    {
        return $this->typeDocument;
    }

    /**
     * @param TypeDocument $typeDocument
     */
    public function setTypeDocument($typeDocument)
    {
        $this->typeDocument = $typeDocument;

        return $this;
    }

    /**
     * @return Process|null
     */
    public function getProcess(): ?Process
    {
        return $this->process;
    }

    /**
     * @param Process|null $process
     */
    public function setProcess(?Process $process): self
    {
        $this->process = $process;
        return $this;
    }

    /**
     * @return \DateTime|null
     */
    public function getDateDeposit(): ?\DateTime
    {
        return $this->dateDeposit;
    }

    /**
     * @param \DateTime|null $dateDeposit
     */
    public function setDateDeposit(?\DateTime $dateDeposit): self
    {
        $this->dateDeposit = $dateDeposit;
        return $this;
    }

    /**
     * @return \DateTime|null
     */
    public function getDateSend(): ?\DateTime
    {
        return $this->dateSend;
    }

    /**
     * @param \DateTime|null $dateSend
     */
    public function setDateSend(?\DateTime $dateSend): self
    {
        $this->dateSend = $dateSend;
        return $this;
    }

    /**
     * @return TabDocument|null
     */
    public function getTabDocument(): ?TabDocument
    {
        return $this->tabDocument;
    }

    public function hasTabDocument(): bool
    {
        return $this->getTabDocument() != null;
    }


    /**
     * @param ?TabDocument $tabDocument
     * @return $this
     */
    public function setTabDocument(?TabDocument $tabDocument): self
    {
        $this->tabDocument = $tabDocument;
        return $this;
    }

    /**
     * @return bool
     */
    public function isPrivate(): bool
    {
        return (is_null($this->private)) ? false : $this->private;
    }

    /**
     * @param bool|null $private
     * @return $this
     */
    public function setPrivate(?bool $private): self
    {
        $this->private = $private;
        return $this;
    }

    /**
     * @return bool
     */
    public function isSignable(): ?bool
    {
        return $this->signable;
    }

    /**
     * @param bool $signable
     */
    public function setSignable(?bool $signable): self
    {
        $this->signable = $signable;
        return $this;
    }

    /**
     * @return ArrayCollection
     */
    public function getPersons()
    {
        return $this->persons;
    }

    /**
     * @param Person $person
     * @return ContractDocument
     */
    public function addPerson(Person $person): self
    {
        if (!$this->persons->contains($person)) {
            $this->persons[] = $person;
            $person->addDocument($this);
        }
        return $this;
    }

    /**
     * @param Person $person
     * @return $this
     */
    public function removePerson(Person $person): self
    {
        if ($this->persons->contains($person)) {
            $this->persons->removeElement($person);
            $person->removeDocument($this);
        }
        return $this;
    }


    /**
     * @param $options
     * @return array
     */
    public function toJson($options = false)
    {
        $defaultOptions = [
            'urlDelete'   => false,
            'urlDownload' => false,
            'urlReupload' => false,
            'urlPerson'   => false,
        ];
        if (!$options) {
            $options = $defaultOptions;
        } else {
            foreach ($defaultOptions as $key => $value) {
                if (!key_exists($key, $options)) {
                    $options[$key] = $defaultOptions[$key];
                }
            }
        }

        $fileName = $this->getPath();

        // process
        $process = false;
        if($this->getProcess()){
            $process = $this->getProcess()->toArray();
            $process_sendable = $this->getProcess()->isSendable();
        }

        return [
            'id'          => $this->getId(),
            'version'     => $this->getVersion(),
            'information' => $this->getInformation(),
            'fileName'    => $fileName,
            'process'    => $process,
            'process_sendable'    => $process_sendable,
//            'fileName'    => $this->getFileName(),
            'fileSize'    => $this->getFileSize(),
            'typeMime'    => $this->getFileTypeMime(),
            'dateUpload'  => $this->getDateUpdoad()->format('Y-m-d H:i:s'),
            'dateDeposit' => $this->getDateDeposit() ? $this->getDateDeposit()->format('Y-m-d') : null,
            'dateSend'    => $this->getDateSend() ? $this->getDateSend()->format('Y-m-d') : null,
            'extension'   => $this->getExtension(),
            'category'    => $this->getTypeDocument() ? $this->getTypeDocument()->toJson() : null,
            'tabDocument' => $this->getTabDocument() ? $this->getTabDocument()->toJson() : null,
            'private'     => $this->isPrivate(),
            'persons'     => $this->personsToJson(),
            'location'     => $this->getLocation(),
            'urlDelete'   => $options['urlDelete'],
            'urlDownload' => $options['urlDownload'],
            'urlReupload' => $options['urlReupload'],
            'uploader'    => $this->getPerson() ? $this->getPerson()->toJson(['urlPerson' => $options['urlPerson']]) : null,
        ];
    }

    /**
     * @return array
     */
    private function personsToJson()
    {
        $personsJson = [];
        /* @var Person $person */
        foreach ($this->getPersons() as $key => $person) {
            $entityPerson = [];
            $entityPerson ["personId"] = $person->getId();
            $entityPerson ["personName"] = $person->getFullname();
            $entityPerson ["affectation"] = $person->getLdapAffectation() ? $person->getLdapAffectation() : "";
            $personsJson [] = $entityPerson;
        }
        return $personsJson;
    }

    ////////////////////////////////////////////////////////////////////////////
    //
    // Calculated getter
    //
    ////////////////////////////////////////////////////////////////////////////
    public function getExtension()
    {
        return preg_replace("/.*\\.(.*)/", "$1", $this->getFileName());
    }


    public function generatePath() :string
    {
        return $this->generateName();
    }

    /**
     * Génère le nom du fichier et non son emplacement
     *
     * @return string
     */
    public function generateName() :string
    {
        $slugify = new Slugify();
        return sprintf("oscar-%s-%s-%s", $this->getGrant()->getId(), $this->getVersion(), $slugify->slugify($this->getFileName()));
    }

    public function islink() :bool
    {
        return $this->getLocation() == self::LOCATION_URL;
    }



    ////////////////////////////////////////////////////////////////////////////
    //
    // METHODS
    //
    ////////////////////////////////////////////////////////////////////////////
    function __toString()
    {
        return "" . ($this->getFileName() ?: "sans-nom");
    }
}
