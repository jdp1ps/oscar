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
use Oscar\Traits\UseServiceContainer;
use Oscar\Traits\UseServiceContainerTrait;
use Oscar\Utils\DateTimeUtils;
use Laminas\Hydrator\HydratorInterface;

class ProjectGrantFormHydrator implements HydratorInterface, UseServiceContainer
{
    use UseServiceContainerTrait;

    private $numbers;
    private $organizations;
    private $organizationRoles;


    public function setNumbers($numbers){
        $this->numbers = $numbers;
    }

    /**
     * Traitement des montants saisis.
     *
     * @param $in
     * @return float
     */
    protected function decimalPointComma($in){
        $in = trim($in);
        $in = str_replace(' ', '', $in);
        return doubleval(str_replace(',', '.', $in));
    }

    public function addOrganizationsLeader( $organizations, $organizationRoles ){
        $this->organizations = $organizations;
        $this->organizationRoles = $organizationRoles;
    }

    /**
     * @param $in
     */
    protected function decimalOrPercent($in){
        $out = $this->decimalPointComma($in);
        if( strpos($in, '%') ){
            return $out.'%';
        } else {
            return $out;
        }
    }

    /**
     * @param array $data
     * @param Activity $object
     */
    public function hydrate(array $data, $object)
    {
        $numbers = [];
        foreach ($data['numbers'] as $key=>$value) {
            if( trim($value) != '' ){
                $numbers[$key] = $value;
            }
        }
        $data['numbers'] = $numbers;

        $object
            ->setLabel($data['label'])
            ->setDescription($data['description'])
            ->setActivityType(array_key_exists('activityType', $data) ? $this->getActivityType($data['activityType']) : null)
            ->setCurrency($this->getCurrency($data['currency']))
            ->setPcruPoleCompetitivite($this->getPcruPoleCompetitivite($data['pcruPoleCompetitivite']))
            ->setPcruSourceFinancement($this->getPcruSourceFinancement($data['pcruSourceFinancement']))
            ->setCodeEOTP($data['codeEOTP'])
            ->setStatus($data['status'])
            ->setAmount($this->decimalPointComma($data['amount']))
            ->setFraisDeGestion($this->decimalOrPercent($data['fraisDeGestion']))
            ->setFraisDeGestionPartHebergeur($this->decimalOrPercent($data['fraisDeGestionPartHebergeur']))
            ->setFraisDeGestionPartUnite($this->decimalOrPercent($data['fraisDeGestionPartUnite']))
            ->setTva($this->getTVA($data['tva']))
            ->setFinancialImpact($this->getFinancialImpact($data['financialImpact']))
            ->setNoteFinanciere($data['noteFinanciere'])
            ->setAssietteSubventionnable($this->decimalPointComma($data['assietteSubventionnable']))
            ->setCentaureNumConvention($data['centaureNumConvention'])
            ->setDateStart(DateTimeUtils::toDatetime($data['dateStart']))
            ->setDateEnd(DateTimeUtils::toDatetime($data['dateEnd']))
            ->setDateSigned(DateTimeUtils::toDatetime($data['dateSigned']))
            ->setDateOpened(DateTimeUtils::toDatetime($data['dateOpened']))
            ->setPcruValidPoleCompetitivite($data['pcruValidPoleCompetitivite'] == "1")
            ->setNumbers(array_key_exists('numbers', $data) ? $data['numbers'] : [])
        ;
        if (isset($data['disciplines'])) {
            $object->setDisciplines($this->getDisciplines($data['disciplines']));
        } else {
            $object->setDisciplines([]);
        }
        return $object;
    }

    protected function getFinancialImpact( $index )
    {
        return Activity::getFinancialImpactValues()[$index];
    }

    protected function getType( $typeId )
    {
        return $this->getProjectGrantService()->getType($typeId);
    }

    protected function getDisciplines( $disciplinesId )
    {
        return $this->getProjectGrantService()->getDisciplinesById($disciplinesId);
    }

    protected function getTVA( $id )
    {
        return $this->getProjectGrantService()->getTVA($id);
    }

    protected function getActivityType( $typeId )
    {
        // Fix : Afficher l'erreur de champs vide si la valeur encoyée est une chaîne vide
        $typeId = intval($typeId);

        return $this->getProjectGrantService()->getActivityTypeById($typeId);
    }

    protected function getCurrency( $currencyId )
    {
        return $this->getProjectGrantService()->getCurrency($currencyId);
    }

    protected function getPcruPoleCompetitivite( $label )
    {
        if( $label == "Aucun" || $label == "" ){
            return null;
        }
        else {
            return $this->getProjectGrantService()->getPcruPoleCompetitiviteByLabel($label);
        }
    }

    protected function getPcruSourceFinancement( $label )
    {
        if( $label == "Aucun" || $label == "" ){
            return null;
        }
        else {
            return $this->getProjectGrantService()->getPcruSourceFinancementByLabel($label);
        }
    }

    /**
     * @return ProjectGrantService
     */
    protected function getProjectGrantService()
    {
        return $this->getServiceContainer()->get(ProjectGrantService::class);
    }

    /**
     * @param Activity $object
     * @return array
     */
    public function extract( object $object ): array
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
            'fraisDeGestionPartHebergeur' => $object->getFraisDeGestionPartHebergeur(),
            'fraisDeGestionPartUnite' => $object->getFraisDeGestionPartUnite(),
            'financialImpact' => array_search($object->getFinancialImpact(), Activity::getFinancialImpactValues()),
            'noteFinanciere' => $object->getNoteFinanciere(),
            'assietteSubventionnable' => $object->getAssietteSubventionnable(),
            'dateStart' => $object->getDateStart()?$object->getDateStart()->format('Y-m-d'):'',
            'dateEnd' => $object->getDateEnd()?$object->getDateEnd()->format('Y-m-d'):'',
            'dateSigned' => $object->getDateSigned()?$object->getDateSigned()->format('Y-m-d'):'',
            'dateOpened' => $object->getDateOpened()?$object->getDateOpened()->format('Y-m-d'):'',
            'currency' => $object->getCurrency() ? $object->getCurrency()->getId() : -1,
            'project' => $object->getProject(),
            'numbers' => $object->getNumbers(),
            'pcruPoleCompetitivite' => $object->getPcruPoleCompetitivite() ? $object->getPcruPoleCompetitivite()->getLabel() : "",
            'pcruSourceFinancement' => $object->getPcruSourceFinancement() ? $object->getPcruSourceFinancement()->getLabel() : "",
            'pcruValidPoleCompetitivite' => $object->isPcruValidPoleCompetitivite(),
            'centaureNumConvention' => $object->getCentaureNumConvention(),
        ];
    }
}
