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
    private $rolesPerson = [];

    private $rolesOrganizations = [];

    private $separator = '~';

    /**
     * @param string[] $rolesPerson Un tableau de chaîne avec les labels des rôles
     */
    public function setRolesPerson($rolesPerson)
    {
        $this->rolesPerson = $rolesPerson;
        return $this;
    }

    /**
     * @param string $separator Chaîne utilisée pour séparer les donnèes multiples
     */
    public function setSeparator($separator)
    {
        $this->separator = $separator;
        return $this;
    }

    /**
     * @param string[] $rolesOrganizations  Un tableau de chaîne avec les labels des rôles
     */
    public function setRolesOrganizations($rolesOrganizations)
    {
        $this->rolesOrganizations = $rolesOrganizations;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getRolesPerson()
    {
        return explode(',', $this->rolesPerson);
    }

    /**
     * @return mixed
     */
    public function getRolesOrganizations()
    {
        return explode(',', $this->rolesOrganizations);
    }

    /**
     * @return string
     */
    public function getSeparator()
    {
        return $this->separator;
    }

    public function format( $object ){
        return $this->formatObject($object);
    }

    public function csvHeaders(){
        $headers = [
            "ID du versement",
            "N° financier de l'activité",
            "Montant de l'activité",
            "N°Oscar d'activité",
            "Intitulé de l'Activité",
            "Début de l'activité",
            "Fin de l'activité",
            "N° de pièce du versement",
            "Montant du versement",
            "Devise du versement",
            "Commentaire du versement",
            "État du versement",
            "Date prévue du versement",
            "Date réalisé du versement"
        ];

        // Entêtes des organisation
        foreach ($this->getRolesOrganizations() as $header) {
            $headers[] = $header;
        }

        // Entêtes des personnes
        foreach ($this->getRolesPerson() as $header) {
            $headers[] = $header;
        }

        return $headers;
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

        $organisations = [];

        foreach( $payment->getActivity()->getOrganizationsDeep() as $partner ){
            $role = (string) $partner->getRole();
            $organisation = (string) $partner->getOrganization();

            if( in_array($role, $this->getRolesOrganizations()) ){
                if( !array_key_exists($role, $organisations) ){
                    $organisations[$role] = [];
                }
                if( !in_array($organisation, $organisations[$role]) ){
                    $organisations[$role][] = $organisation;
                }
            }
        }

        foreach ($this->getRolesOrganizations() as $role) {
            if( array_key_exists($role, $organisations) && count($organisations[$role])>0){
                $datasReturned[$role] = implode($this->getSeparator(), $organisations[$role]);
            } else {
                $datasReturned[$role] = "";
            }
        }

        $persons = [];
        foreach( $payment->getActivity()->getPersonsDeep() as $member ){
            $role = (string) $member->getRole();
            $person = (string) $member->getPerson();

            if( in_array($role, $this->getRolesPerson()) ){
                if( !array_key_exists($role, $persons) ){
                    $persons[$role] = [];
                }
                if( !in_array($person, $persons[$role]) ){
                    $persons[$role][] = $person;
                }
            }
        }

        foreach ($this->getRolesPerson() as $role) {
            if( array_key_exists($role, $persons) && count($persons[$role])>0){
                $datasReturned[$role] = implode($this->getSeparator(), $persons[$role]);
            } else {
                $datasReturned[$role] = "";
            }
        }

        return array_values($datasReturned);
    }
}