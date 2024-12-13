<?php

namespace Oscar\Service;

use Oscar\Entity\Activity;
use Oscar\Entity\ActivityPayment;
use Oscar\Entity\Currency;
use Oscar\Entity\LogActivity;
use Oscar\Entity\Repository\ActivityPaymentRepository;
use Oscar\Exception\OscarException;
use Oscar\Traits\UseActivityLogService;
use Oscar\Traits\UseActivityLogServiceTrait;
use Oscar\Traits\UseEntityManager;
use Oscar\Traits\UseEntityManagerTrait;
use Oscar\Traits\UseLoggerService;
use Oscar\Traits\UseLoggerServiceTrait;
use Oscar\Traits\UseNotificationService;
use Oscar\Traits\UseNotificationServiceTrait;

class ActivityPaymentService implements UseLoggerService, UseEntityManager, UseActivityLogService,
                                        UseNotificationService
{

    use UseLoggerServiceTrait, UseEntityManagerTrait, UseActivityLogServiceTrait, UseNotificationServiceTrait;

    public function getPaymentById(int $paymentId): ActivityPayment
    {
        try {
            return $this->getActivityPaymentRepository()->find($paymentId);
        } catch (\Exception $e) {
            throw new OscarException("Payment with id {$paymentId} not found");
        }
    }

    public function getPaymentsActivity(Activity $activity): array
    {
        throw new \Exception("DELETE Not implemented");
    }

    public function deletePayment(ActivityPayment $payment): void
    {
        $this->getLoggerService()->debug("Suppression d'un versement");
        try {
            $activity = $payment->getActivity();
            $activity->touch();
            $this->getEntityManager()->remove($payment);
            $this->getEntityManager()->flush();

            $this->getActivityLogService()->addUserInfo(
                sprintf(
                    "a supprimer le verserment de %s %s sur l'activité %s",
                    $payment->getAmount(),
                    $payment->getCurrency(),
                    $payment->getActivity()->log()
                ),
                LogActivity::CONTEXT_ACTIVITY,
                $payment->getActivity()->getId()

            );

            $this->getNotificationService()->jobUpdateNotificationsActivity($activity);
        } catch (\Exception $e) {
            $this->getLoggerService()->error($e->getMessage());
            throw new OscarException(
                sprintf("Impossible de supprimer le versement '%s'.", $payment->getId())
            );
        }
    }

    protected function updatePaymentWithData(ActivityPayment $payment, array $datas): void {
        $payment->setAmount($datas['amount'])
            ->setComment($datas['comment'])
            ->setCodeTransaction($datas['codeTransaction'])
            ->setCurrency(
                $this->getEntityManager()
                    ->getRepository(Currency::class)
                    ->find($datas['currencyId'])
            );

        $status = $datas['status'];
        $rate = $datas['rate'];
        $datePredicted = $datas['datePredicted'];
        $datePayment = $datas['datePayment'];

        if ($datePayment) {
            $payment->setDatePayment(new \DateTime($datePayment));
        }
        else {
            $payment->setDatePayment(null);
        }

        if ($datePredicted) {
            $payment->setDatePredicted(new \DateTime($datePredicted));
        }
        else {
            $payment->setDatePredicted(null);
        }
        $payment->setRate($rate)
            ->setStatus($status);
    }

    public function updatePayment( array $datas, Activity $activity): void
    {
        $this->getLoggerService()->debug("versement mis à jour dans '$activity'");

        $payment = $this->getPaymentById($datas['id']);
        $this->getEntityManager()->persist($payment);
        $this->updatePaymentWithData($payment, $datas);
        $this->getEntityManager()->flush($payment);

        $this->getActivityLogService()->addUserInfo(
            sprintf(
                "a modifié le versement de %s %s sur l'activité %s",
                $payment->getAmount(),
                $payment->getCurrency(),
                $payment->getActivity()->log()
            ),
            LogActivity::CONTEXT_ACTIVITY,
            $payment->getActivity()->getId()

        );

        $this->getNotificationService()->jobUpdateNotificationsActivity($payment->getActivity());
    }

    public function createPayment(array $datas, Activity $activity): void
    {
        $this->getLoggerService()->debug("nouveau versement dans '$activity'");

        $payment = new ActivityPayment();
        $this->getEntityManager()->persist($payment);
        $payment->setActivity($activity);

        $this->updatePaymentWithData($payment, $datas);
        $this->getEntityManager()->flush($payment);

        $this->getActivityLogService()->addUserInfo(
            sprintf(
                "a ajouté le versement de %s %s sur l'activité %s",
                $payment->getAmount(),
                $payment->getCurrency(),
                $payment->getActivity()->log()
            ),
            LogActivity::CONTEXT_ACTIVITY,
            $payment->getActivity()->getId()

        );

        $this->getNotificationService()->jobUpdateNotificationsActivity($payment->getActivity());
    }

    public function save(array $paymentData): void
    {
        throw new \Exception("SAVE Not implemented");
    }

    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    /**
     * @return ActivityPaymentRepository
     */
    protected function getActivityPaymentRepository()
    {
        return $this->getEntityManager()->getRepository(ActivityPayment::class);
    }
}