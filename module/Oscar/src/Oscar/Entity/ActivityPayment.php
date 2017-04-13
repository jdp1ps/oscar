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
 */
class ActivityPayment implements ITrackable
{
    use TraitTrackable;

    const STATUS_PREVISIONNEL   = 1;
    const STATUS_REALISE        = 2;
    const STATUS_ECART          = 3;

    /**
     * Retourne le liste des status disponibles.
     *
     * @return array
     */
    public static function getStatusPayments()
    {
        return [
            self::STATUS_PREVISIONNEL => "Prévisionnel",
            self::STATUS_REALISE => "Réalisé",
            self::STATUS_ECART => "Écart",
        ];
    }

    public function getStatusLabel()
    {
        return isset( self::getStatusPayments()[$this->getStatus()] ) ?
            self::getStatusPayments()[$this->getStatus()] : '';
    }

    public function __toString()
    {
        return sprintf("Versement %s de %s%s",
            self::getStatusPayments()[$this->getStatus()],
            $this->getAmount(), $this->getCurrency()
            );
    }

    /**
     * La date effective du payement.
     *
     * @var datetime
     * @ORM\Column(type="date", nullable=true)
     */
    private $datePayment;

    /**
     * La date prévue du payement.
     *
     * @var datetime
     * @ORM\Column(type="date", nullable=true)
     */
    private $datePredicted;

    /**
     * @var
     * @ORM\ManyToOne(targetEntity="Activity", inversedBy="payments")
     */
    private $activity;

    /**
     * @var
     * @ORM\Column(type="float", nullable=false)
     */
    private $amount;

    /**
     * @var
     * @ORM\Column(type="float", nullable=true)
     */
    private $rate;

    /**
     * @var
     * @ORM\Column(type="string", nullable=true)
     */
    private $codeTransaction;

    /**
     * @var
     * @ORM\ManyToOne(targetEntity="Currency")
     */
    private $currency;

    /**
     * @var
     * @ORM\Column(type="text", nullable=true)
     */
    private $comment;

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

        return $this;
    }

    /**
     * @return datetime
     */
    public function getDatePredicted()
    {
        return $this->datePredicted;
    }

    /**
     * @param datetime $datePredicted
     */
    public function setDatePredicted($datePredicted)
    {
        $this->datePredicted = $datePredicted;

        return $this;
    }

    /**
     * @return datetime
     */
    public function getDatePayment()
    {
        return $this->datePayment;
    }

    /**
     * @param datetime $datePayment
     */
    public function setDatePayment($datePayment)
    {
        $this->datePayment = $datePayment;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getCodeTransaction()
    {
        return $this->codeTransaction;
    }

    /**
     * @param mixed $codeTransaction
     */
    public function setCodeTransaction($codeTransaction)
    {
        $this->codeTransaction = $codeTransaction;

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

    /**
     * @return Currency
     */
    public function getCurrency()
    {
        return $this->currency;
    }

    /**
     * @param mixed $currency
     */
    public function setCurrency($currency)
    {
        $this->currency = $currency;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getRate()
    {
        return $this->rate;
    }

    /**
     * @param mixed $rate
     */
    public function setRate($rate)
    {
        $this->rate = $rate;

        return $this;
    }

    public function json()
    {
        return [
            'id' => $this->getId(),
            'activity_id' => $this->getActivity()->getId(),
            'datePayment' => $this->getDatePayment(),
            'datePredicted' => $this->getDatePredicted(),
            'amount' => $this->getAmount(),
            'rate' => $this->getRate(),
            'currency' => $this->getCurrency() ? $this->getCurrency()->asArray() : null,
            'codeTransaction' => $this->getCodeTransaction(),
            'comment' => $this->getComment(),
            'status' => $this->getStatus(),
            'statusLabel' => $this->getStatusLabel(),
            'late' => $this->isLate()
        ];
    }

    /**
     * Retourn true si le versement est prévisionnel et en retard.
     */
    public function isLate(){
        return $this->getStatus() == self::STATUS_PREVISIONNEL && $this->getDatePredicted() < new \DateTime();
    }

    function __construct()
    {
        $this->datePayment = new \DateTime();
        $this->setRate(1.0);
        $this->setAmount(0.0);
    }


}