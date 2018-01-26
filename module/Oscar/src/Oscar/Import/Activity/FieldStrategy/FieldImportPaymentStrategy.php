<?php
/**
 * @author Stéphane Bouvry<stephane.bouvry@unicaen.fr>
 * @date: 17-11-22 15:31
 * @copyright Certic (c) 2017
 */

namespace Oscar\Import\Activity\FieldStrategy;


use Doctrine\ORM\EntityManager;
use Oscar\Entity\Activity;
use Oscar\Entity\ActivityOrganization;
use Oscar\Entity\ActivityPayment;
use Oscar\Entity\Currency;
use Oscar\Entity\Organization;
use Oscar\Entity\OrganizationRepository;
use Oscar\Entity\OrganizationRole;
use Oscar\Entity\OrganizationRoleRepository;
use Oscar\Entity\Person;
use Oscar\Import\Data\DataExtractorDate;

class FieldImportPaymentStrategy extends AbstractFieldImportStrategy
{
    /**
     * @param Activity $activity
     * @param array $datas
     * @param int $index
     * @return mixed
     */
    public function run(&$activity, $datas, $index)
    {
        try {
            $payment = new ActivityPayment();
            $this->getEntityManager()->persist($payment);
            $amount = doubleval($datas[$index+1]);
            $extractor = new DataExtractorDate();
            $date =  $extractor->extract($datas[($index)]);

            $payment->setActivity($activity)
                ->setCurrency($this->getEntityManager()->getRepository(Currency::class)->findOneBy(['rate' => 1]))
                ->setAmount($amount)
                ->setDatePredicted($date);

            $this->getEntityManager()->flush($payment);

            $activity->getPayments()->add($payment);

        } catch( \Exception $e ){
            echo "ERROR : " . $e->getMessage();
        }

        return $activity;
    }
}