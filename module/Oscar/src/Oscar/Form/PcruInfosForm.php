<?php
/**
 * @author Stéphane Bouvry<stephane.bouvry@unicaen.fr>
 * @date October 16, 2015 15:34
 * @copyright Certic (c) 2015
 */
namespace Oscar\Form;

use Oscar\Entity\Activity;
use Oscar\Entity\TimeSheet;
use Oscar\Form\Element\KeyValue;
use Oscar\Service\ActivityTypeService;
use Oscar\Service\OscarConfigurationService;
use Oscar\Service\ProjectGrantService;
use Oscar\Traits\UseServiceContainer;
use Oscar\Traits\UseServiceContainerTrait;
use Oscar\Validator\EOTP;
use UnicaenApp\Util;
use Zend\Filter\StringTrim;
use Zend\Form\Form;
use Zend\InputFilter\InputFilterProviderInterface;
use Oscar\Hydrator\ProjectGrantFormHydrator;
use UnicaenApp\ServiceManager\ServiceLocatorAwareInterface;
use UnicaenApp\ServiceManager\ServiceLocatorAwareTrait;

class PcruInfosForm extends Form implements InputFilterProviderInterface, UseServiceContainer
{

    use UseServiceContainerTrait;

    /**
     * @return ProjectGrantService
     */
    public function getProjectGrantService(){
        return $this->getServiceContainer()->get(ProjectGrantService::class);
    }



    public function init()
    {

        $this->add([
            'type' => 'Hidden',
            'name' => 'id'
        ]);

        // LABEL
        $label = _("Titre/Objet du contrat");
        $this->add([
            'name'   => 'objet',
            'options' => [
                'label' => $label,
            ],
            'attributes' => [
                'class' => 'form-control input-lg',
                'placeholder' => $label,
            ],
            'type'=>'Text'
        ]);

        $label = _("Le code labintel de l’unité du contrat");
        $this->add([
            'name'   => 'codeUniteLabintel',
            'options' => [
                'label' => $label,
            ],
            'attributes' => [
                'class' => 'form-control',
                'placeholder' => $label,
            ],
            'type'=>'Text'
        ]);

        $label = _("Le sigle de l’unité du contrat");
        $this->add([
            'name'   => 'sigleUnite',
            'options' => [
                'label' => $label,
            ],
            'attributes' => [
                'class' => 'form-control',
                'placeholder' => $label,
            ],
            'type'=>'Text'
        ]);

        $label = _("Le numéro de contrat pour la tutelle qui
en assure la gestion");
        $this->add([
            'name'   => 'numContratTutelleGestionnaire',
            'options' => [
                'label' => $label,
            ],
            'attributes' => [
                'class' => 'form-control',
                'placeholder' => $label,
            ],
            'type'=>'Text'
        ]);

        $label = _("Le sigle de l’unité du contrat");
        $this->add([
            'name'   => 'sigleUnite',
            'options' => [
                'label' => $label,
            ],
            'attributes' => [
                'class' => 'form-control',
                'placeholder' => $label,
            ],
            'type'=>'Text'
        ]);

        $label = _("Le nom de l’équipe concernée par le contrat (ou de la sous-unité)");
        $this->add([
            'name'   => 'equipe',
            'options' => [
                'label' => $label,
            ],
            'attributes' => [
                'class' => 'form-control',
                'placeholder' => $label,
            ],
            'type'=>'Text'
        ]);

        $label = _("Type du contrat");
        $this->add([
            'name'   => 'typeContrat',
            'options' => [
                'label' => $label,
                'value_options' => $this->getProjectGrantService()->getPcruTypeContractArray()
            ],
            'attributes' => [
                'class' => 'form-control select2',
                'placeholder' => $label,
            ],
            'type'=>'Select'
        ]);


        $label = _("Acronyme du contrat");
        $this->add([
            'name'   => 'acronyme',
            'options' => [
                'label' => $label,
            ],
            'attributes' => [
                'class' => 'form-control input-lg',
                'placeholder' => $label,
            ],
            'type'=>'Text'
        ]);

        $label = _("Le sigle de l’unité du contrat");
        $this->add([
            'name'   => 'contratsAssocies',
            'options' => [
                'label' => $label,
            ],
            'attributes' => [
                'class' => 'form-control',
                'placeholder' => $label,
            ],
            'type'=>'Text'
        ]);

        $label = _("Responsable scientifique");
        $this->add([
            'name'   => 'responsableScientifique',
            'options' => [
                'label' => $label,
            ],
            'attributes' => [
                'class' => 'form-control',
                'placeholder' => $label,
            ],
            'type'=>'Text'
        ]);

        $label = _("L’employeur scientifique");
        $this->add([
            'name'   => 'employeurResponsableScientifique',
            'options' => [
                'label' => $label,
            ],
            'attributes' => [
                'class' => 'form-control',
                'placeholder' => $label,
            ],
            'type'=>'Text'
        ]);

        $label = _("Le responsable scientifique est aussi coordinateur du consortium");
        $this->add([
            'name'   => 'coordinateurConsortium',
            'options' => [
                'label' => $label,
            ],
            'attributes' => [
                'class' => '',
                'placeholder' => $label,
            ],
            'type'=>'Checkbox'
        ]);

        $label = _("Partenaires");
        $this->add([
            'name'   => 'partenaires',
            'options' => [
                'label' => $label,
            ],
            'attributes' => [
                'class' => 'form-control',
                'placeholder' => $label,
            ],
            'type'=>'Text'
        ]);

        $label = _("Le partenaire principal");
        $this->add([
            'name'   => 'partenairePrincipal',
            'options' => [
                'label' => $label,
            ],
            'attributes' => [
                'class' => 'form-control',
                'placeholder' => $label,
            ],
            'type'=>'Text'
        ]);

        $label = _("SIRET/SIREN/TVA Intra/DUN du partenaire principal");
        $this->add([
            'name'   => 'idPartenairePrincipal',
            'options' => [
                'label' => $label,
            ],
            'attributes' => [
                'class' => 'form-control',
                'placeholder' => $label,
            ],
            'type'=>'Text'
        ]);

        $label = _("Source de financement");
        $this->add([
            'name'   => 'sourceFinancement',
            'options' => [
                'label' => $label,
                'value_options' => $this->getProjectGrantService()->getPcruSourceFinancement()
            ],
            'attributes' => [
                'class' => 'form-control',
                'placeholder' => $label,
            ],
            'type'=>'Select'
        ]);

        $label = _("Lieu d'éxécution");
        $this->add([
            'name'   => 'lieuExecution',
            'options' => [
                'label' => $label,
            ],
            'attributes' => [
                'class' => 'form-control',
                'placeholder' => $label,
            ],
            'type'=>'Text'
        ]);

        $label = _("La date de dernière signature du contrat");
        $this->add([
            'name'   => 'dateDerniereSignature',
            'options' => [
                'label' => $label,
            ],
            'attributes' => [
                'class' => 'form-control',
                'placeholder' => $label,
            ],
            'type'=>'Date'
        ]);

        $label = _("Durée en mois");
        $this->add([
            'name'   => 'duree',
            'options' => [
                'label' => $label,
            ],
            'attributes' => [
                'class' => 'form-control',
                'placeholder' => $label,
            ],
            'type'=>'Text'
        ]);

        $label = _("Date de début");
        $this->add([
            'name'   => 'dateDebut',
            'options' => [
                'label' => $label,
            ],
            'attributes' => [
                'class' => 'form-control',
                'placeholder' => $label,
            ],
            'type'=>'Text'
        ]);

        $label = _("Date de fin");
        $this->add([
            'name'   => 'dateFin',
            'options' => [
                'label' => $label,
            ],
            'attributes' => [
                'class' => 'form-control',
                'placeholder' => $label,
            ],
            'type'=>'Text'
        ]);

        $label = _("Montant perçu pour l'unité");
        $this->add([
            'name'   => 'montantPercuUnite',
            'options' => [
                'label' => $label,
            ],
            'attributes' => [
                'class' => 'form-control',
                'placeholder' => $label,
            ],
            'type'=>'Text'
        ]);

        $label = _("Coût complet");
        $this->add([
            'name'   => 'coutTotalEtude',
            'options' => [
                'label' => $label,
            ],
            'attributes' => [
                'class' => 'form-control',
                'placeholder' => $label,
            ],
            'type'=>'Text'
        ]);

        $label = _("Montant total");
        $this->add([
            'name'   => 'montantTotal',
            'options' => [
                'label' => $label,
            ],
            'attributes' => [
                'class' => 'form-control',
                'placeholder' => $label,
            ],
            'type'=>'Text'
        ]);

        $label = _("Validé par le pôle de compétitivité");
        $this->add([
            'name'   => 'validePoleCompetivite',
            'options' => [
                'label' => $label,
            ],
            'attributes' => [
                'class' => 'form-control',
                'placeholder' => $label,
            ],
            'type'=>'Text'
        ]);

        $label = _("Pôle de compétitivité");
        $this->add([
            'name'   => 'poleCompetivite',
            'options' => [
                'label' => $label,
                'value_options' => $this->getProjectGrantService()->getPcruPoleCompetitiviteArray()
            ],
            'attributes' => [
                'class' => 'form-control select2',
                'placeholder' => $label,
            ],
            'type'=>'Select'
        ]);

        $label = _("Commentaire du gestionnaire");
        $this->add([
            'name'   => 'commentaires',
            'options' => [
                'label' => $label,
            ],
            'attributes' => [
                'class' => 'form-control',
                'placeholder' => $label,
            ],
            'type'=>'Text'
        ]);

        $label = _("Programme d'Investissement Avenir");
        $this->add([
            'name'   => 'pia',
            'options' => [
                'label' => $label,
            ],
            'attributes' => [
                'class' => 'form-control',
                'placeholder' => $label,
            ],
            'type'=>'Text'
        ]);

        $label = _("N° Oscar");
        $this->add([
            'name'   => 'reference',
            'options' => [
                'label' => $label,
            ],
            'attributes' => [
                'class' => 'form-control',
                'placeholder' => $label,
            ],
            'type'=>'Text'
        ]);

        $label = _("Accord cadre");
        $this->add([
            'name'   => 'accordCadre',
            'options' => [
                'label' => $label,
            ],
            'attributes' => [
                'class' => 'form-control',
                'placeholder' => $label,
            ],
            'type'=>'Text'
        ]);

        $label = _("CIFRE");
        $this->add([
            'name'   => 'cifre',
            'options' => [
                'label' => $label,
            ],
            'attributes' => [
                'class' => 'form-control',
                'placeholder' => $label,
            ],
            'type'=>'Text'
        ]);

        $label = _("Chaire industrielle ?");
        $this->add([
            'name'   => 'chaireIndustrielle',
            'options' => [
                'label' => $label,
            ],
            'attributes' => [
                'class' => 'form-control',
                'placeholder' => $label,
            ],
            'type'=>'Text'
        ]);

        $label = _("Présence d'un partenaire industrielle");
        $this->add([
            'name'   => 'presencePartenaireIndustriel',
            'options' => [
                'label' => $label,
            ],
            'attributes' => [
                'class' => 'form-control',
                'placeholder' => $label,
            ],
            'type'=>'Text'
        ]);
    }

    public function getInputFilterSpecification()
    {
        return [

        ];
    }
}
