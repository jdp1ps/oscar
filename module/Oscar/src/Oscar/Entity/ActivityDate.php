<?php
/**
 * @author Stéphane Bouvry<stephane.bouvry@unicaen.fr>
 * @date: 27/01/16 16:15
 * @copyright Certic (c) 2016
 */

namespace Oscar\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Dates des Activités (Jalons)
 *
 * @ORM\Entity
 * @ORM\Entity(repositoryClass="Oscar\Entity\ActivityDateRepository")
 */
class ActivityDate implements ITrackable
{
    use TraitTrackable;

    const PROGRESSION_VALID         = 'valid';
    const PROGRESSION_UNVALID       = 'unvalid';
    const PROGRESSION_CANCEL        = 'cancel';
    const PROGRESSION_REFUSED       = 'refused';
    const PROGRESSION_INPROGRESS    = 'inprogress';


    // Jalon terminé
    const VALUE_TODO        = 0;
    const VALUE_INPROGRESS  = 50;
    const VALUE_VALIDED     = 100;
    const VALUE_CANCELED    = 200;
    const VALUE_REFUSED     = 400;

    static public function progressInfos() {
        return [
            self::VALUE_TODO => "A faire",
            self::VALUE_INPROGRESS => "En cours",
            self::VALUE_VALIDED => "Validé",
            self::VALUE_CANCELED => "Annulé",
            self::VALUE_REFUSED => "Refusé"
        ];
    }

    /**
     * Données (la date)
     *
     * @var datetime
     * @ORM\Column(type="date", nullable=false)
     */
    private $dateStart;

    /**
     * @var
     * @ORM\ManyToOne(targetEntity="DateType", inversedBy="milestones")
     */
    private $type;

    /**
     * @var
     * @ORM\ManyToOne(targetEntity="Activity", inversedBy="milestones")
     */
    private $activity;

    /**
     * @var
     * @ORM\Column(type="text", nullable=true)
     */
    private $comment;

    /**
     * Un entier représentant le niveau de complétion (en %)
     *
     * @var integer
     * @ORM\Column(type="integer", nullable=true)
     */
    private $finished;

    /**
     * Chaîne contenant la personne [ID] DisplayName
     *
     * @var string
     * @ORM\Column(type="string", nullable=true)
     */
    private $finishedBy;



    /**
     * Date à laquelle le jalon a été complété.
     *
     * @var \DateTime
     * @ORM\Column(type="date", nullable=true)
     */
    private $dateFinish;


    public function finish( $value = 100, $date = null ){
        $this->finished = $value;
        if( $this->finished >= self::VALUE_VALIDED ){
            $this->dateFinish = $date === null ? new \DateTime() : $date;
        }
    }

    public function isFinishable(){
        if( $this->getType() ){
            return $this->getType()->isFinishable();
        }
        return false;
    }

    /**
     * @return string
     */
    public function getFinishedBy(): string
    {
        return $this->finishedBy;
    }

    /**
     * @param string $finishedBy
     */
    public function setFinishedBy(string $finishedBy)
    {
        $this->finishedBy = $finishedBy;
        return $this;
    }



    public function isFinished(){
        return $this->getFinished() >= self::VALUE_VALIDED;
    }

    /**
     * Retourne TRUE sie le jalon doit être complété et qu'il est en retard.
     */
    public function isLate(){
        $now = new \DateTime('now');
        return  $this->isFinishable() && !$this->isFinished() && ($now > $this->getDateStart());
    }

    /**
     * @return integer
     */
    public function getFinished()
    {
        return $this->finished;
    }

    /**
     * @param Un $finished
     */
    public function setFinished($finished)
    {
        $this->finished = $finished;

        return $this;
    }

    /**
     * @return datetime
     */
    public function getDateFinish()
    {
        return $this->dateFinish;
    }

    /**
     * @param datetime $dateFinish
     */
    public function setDateFinish($dateFinish)
    {
        $this->dateFinish = $dateFinish;

        return $this;
    }

    /**
     * Test
     * @return array
     */
    public function getRecursivity(){
        if( $this->getType() ){
            return $this->getType()->getRecursivityArray();
        }
        return [];
    }

    /**
     * @return array
     *
     * Retourne les dates de notification du jalon.
     */
    public function getRecursivityDate(){
        $dates = [];
        foreach ( $this->getRecursivity() as $days ){
            $date = new \DateTime($this->getDateStart()->format('Y-m-d'));
            $interval = new \DateInterval('P'.$days.'D');
            $interval->invert = 1;
            $date->add($interval);
            $dates[] = $date;
        }
        return $dates;
    }

    /**
     * @return datetime
     */
    public function getDateStart()
    {
        return $this->dateStart;
    }

    public function getDateStartStr( $format = 'Y-m-d' ) :string
    {
        if( $this->getDateStart() )
            return $this->getDateStart()->format($format);
        return "";
    }

    /**
     * @param datetime $dateStart
     */
    public function setDateStart($dateStart)
    {
        $this->dateStart = $dateStart;

        return $this;
    }

    /**
     * @return DateType
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param mixed $type
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @return Activity
     */
    public function getActivity()
    {
        return $this->activity;
    }

    /**
     * @param mixed $activity
     */
    public function setActivity($activity)
    {
        $this->activity = $activity;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getComment()
    {
        return $this->comment;
    }

    /**
     * @param mixed $comment
     */
    public function setComment($comment)
    {
        $this->comment = $comment;

        return $this;
    }

    function __toString()
    {
        return $this->getDateStart()->format('d M Y').' ('.$this->getType().')';
    }

    function toArray(){
        return [
            'id' => $this->getId(),
            'type' => $this->getType()->getLabel(),
            'type_id' => $this->getType()->getId(),
            'type_facet' => $this->getType()->getFacet(),
            'comment' => $this->getComment(),
            'dateStart' => $this->getDateStart()
        ];
    }

}
