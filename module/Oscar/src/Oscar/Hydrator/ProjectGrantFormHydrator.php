<?php
/**
 * @author Stéphane Bouvry<stephane.bouvry@unicaen.fr>
 * @date: 16/10/15 15:31
 * @copyright Certic (c) 2015
 */

namespace Oscar\Hydrator;

use Oscar\Entity\Activity;
use Oscar\Hydrator\Hydrator;
use Oscar\Service\ProjectGrantService;
use Oscar\Utils\DateTimeUtils;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorAwareTrait;
use Zend\Stdlib\Hydrator\HydratorInterface;

class ProjectGrantFormHydrator implements HydratorInterface, ServiceLocatorAwareInterface
{

    use ServiceLocatorAwareTrait;

    protected function decimalPointComma($in){
        return doubleval(str_replace(',', '.', $in));
    }

    /**
     * @param array $data
     * @param Activity $object
     */
    public function hydrate(array $data, $object)
    {
        $object
            ->setLabel($data['label'])
            ->setDescription($data['description'])
            ->setActivityType(array_key_exists('activityType', $data) ? $this->getActivityType($data['activityType']) : null)
            ->setCurrency($this->getCurrency($data['currency']))
            ->setCodeEOTP($data['codeEOTP'])
            ->setStatus($data['status'])
            ->setAmount($this->decimalPointComma($data['amount']))
            ->setFraisDeGestion($this->decimalPointComma($data['fraisDeGestion']))
            ->setTva($this->getTVA($data['tva']))
            ->setFinancialImpact($this->getFinancialImpact($data['financialImpact']))
            ->setNoteFinanciere($data['noteFinanciere'])
            ->setAssietteSubventionnable($this->decimalPointComma($data['assietteSubventionnable']))
            ->setCentaureNumConvention($data['centaureNumConvention'])
            ->setDateStart(DateTimeUtils::toDatetime($data['dateStart']))
            ->setDateEnd(DateTimeUtils::toDatetime($data['dateEnd']))
            ->setDateSigned(DateTimeUtils::toDatetime($data['dateSigned']))
            ->setDateOpened(DateTimeUtils::toDatetime($data['dateOpened']))
            ->setNumbers(array_key_exists('numbers', $data) ? $data['numbers'] : [])

        ;
        if (isset($data['disciplines'])) {
            $object->setDisciplines($this->getDisciplines($data['disciplines']));
        }
        return $object;
    }

    protected function getFinancialImpact( $index )
    {
        return Activity::getFinancialImpactValues()[$index];
    }

    protected function getType( $typeId )
    {
        return $this->getServiceLocator()->get('ProjectGrantService')->getType($typeId);
    }

    protected function getDisciplines( $disciplinesId )
    {
        return $this->getServiceLocator()->get('ProjectGrantService')->getDisciplinesById($disciplinesId);
    }

    protected function getTVA( $id )
    {
        return $this->getProjectGrantService()->getTVA($id);
    }

    protected function getActivityType( $typeId )
    {
        return $this->getServiceLocator()->get('ActivityTypeService')->getActivityType($typeId);
    }

    protected function getCurrency( $currencyId )
    {
        return $this->getServiceLocator()->get('ProjectGrantService')->getCurrency($currencyId);
    }

    /**
     * @return ProjectGrantService
     */
    protected function getProjectGrantService()
    {
        return $this->getServiceLocator()->get('ProjectGrantService');
    }

    /**
     * @param Activity $object
     * @return array
     */
    public function extract( $object )
    {
        return [
            'id' => $object->getId() ? $object->getId() : '',
            'label' => $object->getLabel(),
            'description' => $object->getDescription(),
            'status' => $object->getStatus(),
            'activityType' => $object->getActivityType() ? $object->getActivityType()->getId() : -1,
            'tva' => $object->getTva() ? $object->getTva()->getId() : -1,
            'codeEOTP' => $object->getCodeEOTP(),
            'disciplines' => $object->getDisciplinesIds(),
            'amount' => $object->getAmount(),
            'fraisDeGestion' => $object->getFraisDeGestion(),
            'financialImpact' => array_search($object->getFinancialImpact(), Activity::getFinancialImpactValues()),
            'dateStart' => $object->getDateStart(),
            'noteFinanciere' => $object->getNoteFinanciere(),
            'assietteSubventionnable' => $object->getAssietteSubventionnable(),
            'dateEnd' => $object->getDateEnd(),
            'dateSigned' => $object->getDateSigned(),
            'dateOpened' => $object->getDateOpened(),
            'currency' => $object->getCurrency() ? $object->getCurrency()->getId() : -1,
            'project' => $object->getProject(),
            'numbers' => $object->getNumbers(),
            'centaureNumConvention' => $object->getCentaureNumConvention(),
        ];
    }
}
