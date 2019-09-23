<?php
/**
 * @author Stéphane Bouvry<stephane.bouvry@unicaen.fr>
 * @date: 20/11/15 15:09
 * @copyright Certic (c) 2015
 */

namespace Oscar\Hydrator;


use Oscar\Entity\Activity;
use Oscar\Entity\ActivityDate;
use Oscar\Entity\ActivityPayment;
use Oscar\Entity\ActivityType;
use Oscar\Entity\Currency;
use Oscar\Entity\DateType;
use Oscar\Utils\DateTimeUtils;
use UnicaenApp\Service\EntityManagerAwareInterface;
use UnicaenApp\Service\EntityManagerAwareTrait;
use UnicaenApp\ServiceManager\ServiceLocatorAwareInterface;
use UnicaenApp\ServiceManager\ServiceLocatorAwareTrait;
use Zend\Hydrator\HydratorInterface;

class ActivityPaymentFormHydrator implements HydratorInterface, ServiceLocatorAwareInterface
{
    use ServiceLocatorAwareTrait;

    /**
     * @param ActivityPayment $object
     * @return array
     */
    public function extract($object)
    {
        return [
            'id' => $object->getId(),
            'comment' => $object->getComment(),
            'datePayment' => $object->getDatePayment() ? $object->getDatePayment()->format('Y-m-d') : '',
            'datePredicted' => $object->getDatePredicted() ? $object->getDatePredicted()->format('Y-m-d') : '',
            'currency' => $object->getCurrency() ? $object->getCurrency()->getId() : null,
            'amount'=> $object->getAmount(),
            'codeTransaction'=> $object->getCodeTransaction(),
            'status'=> $object->getStatus(),
            'rate' => $object->getRate()
        ];
    }

    /**
     * @param array $data
     * @param ActivityPayment $object
     */
    public function hydrate(array $data, $object)
    {
        return $object->setComment($data['comment'])
            ->setDatePayment(DateTimeUtils::toDatetime($data['datePayment']))
            ->setDatePredicted(DateTimeUtils::toDatetime($data['datePredicted']))
            ->setCurrency($this->getCurrency($data['currency']))
            ->setAmount($data['amount'])
            ->setCodeTransaction($data['codeTransaction'])
            ->setStatus($data['status'])
            ->setRate($data['rate'])
            ;
    }

    /**
     * @param $id
     * @return null|Currency
     */
    protected function getCurrency( $id ){
        return $this->getServiceLocator()->get('ActivityService')->getCurrency($id);
    }
}