<?php
/**
 * Created by PhpStorm.
 * User: jacksay
 * Date: 08/07/2016
 * Time: 10:12
 */

namespace Oscar\Formatter;


use Oscar\Entity\ActivityOrganization;
use Oscar\Entity\ActivityPayment;
use Oscar\Entity\Organization;

class ActivityPaymentFormatter extends AbstractCSVFormatter implements IFormatter
{
    public function format( $object ){
        return $this->formatObject($object);
    }

    public function csvHeaders(){
        return [
            'ID du versement',

            "PFI de l'activité",
            "Montant de l'activité",
            "N°Oscar d'activité",
            'Intitulé de l\'Activité',
            "Début de l'activité",
            "Fin de l'activité",

            'N° de pièce du versement',
            'Montant du versement',
            'Devise du versement',
            'Commentaire du versement',
            'État du versement',
            'Date prévue du versement',
            'Date réalisé du versement',
            'Financeur(s)',
            'Composante de gestion',
            'Laboratoire(s)'
        ];
    }

    /**
     * Prépare les données pour l'export en CSV.
     *
     * @param ActivityPayment $payment
     * @return array
     */
    public function formatObject(ActivityPayment $payment)
    {
        $datasReturned = [
            'paymentID' => $payment->getId(),

            'activityPFI' => $payment->getActivity()->getCodeEOTP(),
            'activityAmount' => $this->formatMoney($payment->getActivity()->getAmount()),
            'activityID' => $payment->getActivity()->getOscarNum(),
            'activityLabel' => $payment->getActivity()->getLabel(),
            'activityStart' => $this->formatDate($payment->getActivity()->getDateStart()),
            'activityEnd' => $this->formatDate($payment->getActivity()->getDateEnd()),
            'paymentCodeTransaction' => $payment->getCodeTransaction(),
            'paymentAmount' => $this->formatMoney($payment->getAmount()),
            'paymentCurrency' => (string)$payment->getCurrency(),
            'paymentComment' => $payment->getComment(),
            'paymentStatus' => $payment->getStatusLabel(),
            'paymentDatePredicted' => $this->formatDate($payment->getDatePredicted()),
            'paymentDateEffective' => $this->formatDate($payment->getDatePayment()),
        ];

        $financeurs = [];
        $composantesGestion = [];
        $labos = [];

        foreach( $payment->getActivity()->getOrganizationsDeep() as $partner ){
            if( $partner->getRole() == Organization::ROLE_FINANCEUR || $partner->getRole() == Organization::ROLE_CO_FINANCEUR ){
                $financeurs[] = (string)$partner->getOrganization();
            }

            if( $partner->getRole() == Organization::ROLE_LABORATORY ){
                $labos[] = (string)$partner->getOrganization();
            }

            if( $partner->getRole() == Organization::ROLE_COMPOSANTE_GESTION ){
                $composantesGestion[] = (string)$partner->getOrganization();
            }
        }

        $datasReturned['financeurs'] = implode('$$ ', $financeurs);
        $datasReturned['cgestion'] = implode('$$ ', $composantesGestion);
        $datasReturned['labos'] = implode('$$ ', $labos);

        return $datasReturned;
    }
}