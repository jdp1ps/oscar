<?php
namespace UnicaenApp\Form;

use UnicaenApp\Exception\LogicException;

/**
 * Classe mère des formulaire multi-pages (saisie en plusieurs étapes).
 *
 * @author bertrand.gauthier@unicaen.fr
 */
abstract class MultipageForm extends \Zend\Form\Form
{
    /**
     * Nom de l'élément de formulaire composite de navigation
     */
    const NAME_NAV = \UnicaenApp\Form\Element\MultipageFormNav::NAME;
    
    /**
     * Nom par défaut pour l'action de contrôleur gérant la demande de confirmation
     */
    const ACTION_CONFIRM = 'confirmer';
    /**
     * Nom par défaut pour l'action de contrôleur gérant la demande d'enregistrement
     */
    const ACTION_PROCESS = 'enregistrer';
    /**
     * Nom par défaut pour l'action de contrôleur gérant la demande d'annulation
     */
    const ACTION_CANCEL  = 'annuler';
    
    /**
     * Liste des noms des fieldsets ajoutés.
     * La clé 'first'  est utilisée pour le premier fieldset.
     * La clé 'next_N' est utilisée pour les fieldsets suivants.
     * La clé 'last'   est utilisée pour le dernier fieldset.
     * @var array
     */
    protected $fieldsetNames = array();
    
    /**
     * Préfixe appliqué aux noms des actions de contrôleur.
     * Par défaut: null.
     * @var string
     */
    protected $actionPrefix;

    /**
     * Mapping <nom du fieldset> => <nom de l'action du contrôleur>
     * Par défaut, le nom de l'action est égale au nom du fieldset.
     * @var array 
     */
    protected $fieldsetActionMapping;

    /**
     * Nom de l'action de contrôleur gérant la demande de confirmation
     * de la saisie du formulaire multi-pages.
     * @var array 
     */
    protected $confirmAction;

    /**
     * Nom de l'action de contrôleur gérant la demande d'enregistrement
     * de la saisie du formulaire multi-pages.
     * @var string
     */
    protected $processAction;

    /**
     * Nom de l'action de contrôleur gérant la demande d'annulation
     * de la saisie du formulaire multi-pages.
     * @var string
     */
    protected $cancelAction;
    
    /**
     * Add an element or fieldset
     *
     * If $elementOrFieldset is an array or Traversable, passes the argument on
     * to the composed factory to create the object before attaching it.
     *
     * $flags could contain metadata such as the alias under which to register
     * the element or fieldset, order in which to prioritize it, etc.
     *
     * @param  array|Traversable|ElementInterface $elementOrFieldset
     * @param  array                              $flags
     * @return \Zend\Form\Fieldset|\Zend\Form\FieldsetInterface|\Zend\Form\FormInterface
     */
    public function add($elementOrFieldset, array $flags = array())
    {
        // gère maison pour les fieldsets
        if ($elementOrFieldset instanceof \Zend\Form\Fieldset) {
            return $this->_addFieldset($elementOrFieldset);
        }
        
        return parent::add($elementOrFieldset, $flags);
    }
    
    /**
     * Ajoute un fieldset d'étape à ce formulaire.
     * L'élément composite de navigation adapté à cette étape est ajouté au fieldset.
     *
     * @param \Zend\Form\Fieldset $fieldset
     * @return MultipageForm
     */
    protected function _addFieldset(\Zend\Form\Fieldset $fieldset)
    {
        if (!$this->fieldsetNames) {
            $navigationElement = new Element\MultipageFormNav(self::NAME_NAV);
            $navigationElement->setActivateCancel(true)
                 ->setActivatePrevious(false)
                 ->setActivateNext(true)
                 ->setActivateSubmit(false);
            
            $this->fieldsetNames['first'] = $fieldset->getName();
        }
        else {
            if (in_array($fieldset->getName(), $this->fieldsetNames)) {
                throw new LogicException("Ce fieldset a déjà été ajouté.");
            }
            
            $navigationElement = new Element\MultipageFormNav(self::NAME_NAV);
            $navigationElement->setActivateCancel(true)
                              ->setActivatePrevious(true)
                              ->setActivateNext(false)
                              ->setActivateSubmit(true);
            
            // le dernier fieldset ajouté n'est plus le dernier fieldset du formulaire
            $keys = array_keys($this->fieldsetNames);
            if (end($keys) !== 'first') {
                $lastInsertedFieldsetName = array_pop($this->fieldsetNames);
                $lastInsertedFieldsetNavElem = $this->get($lastInsertedFieldsetName)->get(self::NAME_NAV);
                $lastInsertedFieldsetNavElem->setActivateCancel(true)
                                            ->setActivatePrevious(true)
                                            ->setActivateNext(true)
                                            ->setActivateSubmit(false);
                $this->fieldsetNames['next_' . (count($this->fieldsetNames) - 1)] = $lastInsertedFieldsetName;
            }
            
            $this->fieldsetNames['last'] = $fieldset->getName();
        }

        $fieldset->add($navigationElement);
        
        return parent::add($fieldset);
    }
    
    /**
     * Ajoute le fieldset de la première étape à ce formulaire.
     * L'élément composite de navigation adapté à cette étape est ajouté au fieldset.
     *
     * @param \Zend\Form\Fieldset $fieldset
     * @return MultipageForm
     */
//    public function addFieldsetFirst(\Zend\Form\Fieldset $fieldset)
//    {
//        if (array_key_exists('first', $this->fieldsetNames)) {
//            throw new \UnicaenApp\Exception("Le premier fieldset a déjà été ajouté.");
//        }
//        
//        $elem = new Element\MultipageFormNav(self::NAME_NAV);
//        $elem->setActivateCancel(true)
//             ->setActivatePrevious(false)
//             ->setActivateNext(true)
//             ->setActivateSubmit(false);
//        $fieldset->add($elem);
//
//        $this->add($fieldset);
//        
//        $this->fieldsetNames['first'] = $fieldset->getName();
//        
//        return $this;
//    }
//    
//    /**
//     * Ajoute le fieldset suivant à ce formulaire.
//     * L'élément composite de navigation adapté à cette étape est ajouté au fieldset.
//     *
//     * @param \Zend\Form\Fieldset $fieldset
//     * @return MultipageForm
//     */
//    public function addFieldsetNext(\Zend\Form\Fieldset $fieldset)
//    {
//        if (!$this->fieldsetNames) {
//            throw new \UnicaenApp\Exception("Il faut un premier fieldset pour pouvoir ajouter le suivant.");
//        }
//        if (in_array($fieldset->getName(), $this->fieldsetNames)) {
//            throw new \UnicaenApp\Exception("Ce fieldset a déjà été ajouté.");
//        }
//        
//        $elem = new Element\MultipageFormNav(self::NAME_NAV);
//        $elem->setActivateCancel(true)
//             ->setActivatePrevious(true)
//             ->setActivateNext(true)
//             ->setActivateSubmit(false);
//        $fieldset->add($elem);
//
//        $this->add($fieldset);
//        
//        $this->fieldsetNames['next_' . (count($this->fieldsetNames) - 1)] = $fieldset->getName();
//        
//        return $this;
//    }
//
//    /**
//     * Ajoute le fieldset de la dernière étape à ce formulaire.
//     * L'élément composite de navigation adapté à cette étape est ajouté au fieldset.
//     *
//     * @param \Zend\Form\Fieldset $fieldset
//     * @return MultipageForm
//     */
//    public function addFieldsetLast(\Zend\Form\Fieldset $fieldset)
//    {
//        if (!$this->fieldsetNames) {
//            throw new \UnicaenApp\Exception("Il faut un premier fieldset pour pouvoir ajouter le dernier.");
//        }
//        if (array_key_exists('last', $this->fieldsetNames)) {
//            throw new \UnicaenApp\Exception("Le dernier fieldset a déjà été ajouté.");
//        }
//        if (in_array($fieldset->getName(), $this->fieldsetNames)) {
//            throw new \UnicaenApp\Exception("Ce fieldset a déjà été ajouté.");
//        }
//        
//        $elem = new Element\MultipageFormNav(self::NAME_NAV);
//        $elem->setActivateCancel(true)
//             ->setActivatePrevious(true)
//             ->setActivateNext(false)
//             ->setActivateSubmit(true);
//        $fieldset->add($elem);
//
//        $this->add($fieldset);
//        
//        $this->fieldsetNames['last'] = $fieldset->getName();
//        
//        return $this;
//    }

    /**
     * Autorise ou non le passage du fieldset spécifié au fieldset précédent.
     *
     * @param string|\Zend\Form\Fieldset $fieldset
     * @param boolean $value
     * @return MultipageForm
     */
    public function setEnabledFieldsetPrevious($fieldset, $value = true)
    {
        return $this->_setEnabledFieldsetAction(
                $fieldset,
                Element\MultipageFormNav::PREVIOUS,
                $value);
    }
    /**
     * Autorise ou non le passage du fieldset spécifié au fieldset suivant.
     *
     * @param string|\Zend\Form\Fieldset $fieldset
     * @param boolean $value
     * @return MultipageForm
     */
    public function setEnabledFieldsetNext($fieldset, $value = true)
    {
        return $this->_setEnabledFieldsetAction(
                $fieldset,
                Element\MultipageFormNav::NEXT,
                $value);
    }
    /**
     * Autorise ou non l'annulation complète de la saisie au sein du fieldset spécifié.
     *
     * @param string|\Zend\Form\Fieldset $fieldset
     * @param boolean $value
     * @return MultipageForm
     */
    public function setEnabledFieldsetCancel($fieldset, $value = true)
    {
        return $this->_setEnabledFieldsetAction(
                $fieldset,
                Element\MultipageFormNav::CANCEL,
                $value);
    }
    /**
     * Autorise ou non la validation de la saisie complète au sein du fieldset spécifié.
     *
     * @param string|\Zend\Form\Fieldset $fieldset
     * @param boolean $value
     * @return MultipageForm
     */
    public function setEnabledFieldsetSubmit($fieldset, $value = true)
    {
        return $this->_setEnabledFieldsetAction(
                $fieldset,
                Element\MultipageFormNav::SUBMIT,
                $value);
    }

    /**
     * Autorise ou non une action au sein du fieldset spécifié.
     *
     * @param string|\Zend\Form\Fieldset $fieldset
     * @param string $actionName
     * @param boolean $value
     * @return MultipageForm
     */
    protected function _setEnabledFieldsetAction($fieldset, $actionName, $value = true)
    {
        if ($fieldset instanceof \Zend\Form\Fieldset) {
            $fieldset = $fieldset->getName();
        }
        if (!is_string($fieldset)) {
            throw new LogicException("Format de fieldset spécifié invalide.");
        }
        if (!$this->has($name = $fieldset)) {
            throw new LogicException("Le fieldset '$name' spécifié n'a pas été trouvé dans le formulaire.");
        }

        $fieldset = $this->get($name);
        
        if (!$fieldset->has(self::NAME_NAV)) {
            throw new LogicException("L'élément de navigation n'a pas été trouvé dans le fieldset spécifié.");
        }
        
        $nav = $fieldset->get(self::NAME_NAV); /* @var $nav Element\MultipageFormNav */
        
        switch ($actionName) {
            case Element\MultipageFormNav::PREVIOUS:
                $nav->setActivatePrevious($value);
                break;
            case Element\MultipageFormNav::NEXT:
                $nav->setActivateNext($value);
                break;
            case Element\MultipageFormNav::CANCEL:
                $nav->setActivateCancel($value);
                break;
            case Element\MultipageFormNav::SUBMIT:
                $nav->setActivateSubmit($value);
                break;
        }

        return $this;
    }
    
    /**
     * Retourne le préfixe appliqué aux noms des actions de contrôleur.
     * 
     * @return string
     */
    public function getActionPrefix()
    {
        return $this->actionPrefix;
    }

    /**
     * Retourne le mapping courant <nom du fieldset> => <nom de l'action du contrôleur>.
     * Par défaut, le nom de l'action est égale au nom du fieldset.
     * 
     * @return array <nom du fieldset> => <nom de l'action du contrôleur>
     */
    public function getFieldsetActionMapping()
    {
        if (!$this->fieldsetActionMapping) {
            $mapping = array();
            foreach ($this->getFieldsets() as $fieldset) {
                $mapping[$name = $fieldset->getName()] = $this->prefixedAction($name);
            }
            $this->fieldsetActionMapping = $mapping;
        }
        return $this->fieldsetActionMapping;
    }

    /**
     * Ajoute le prefixe courant éventuel au nom de l'action spécifié.
     * 
     * @param string $action
     * @return string
     */
    protected function prefixedAction($action)
    {
        if ($this->getActionPrefix() && strpos($action, $this->getActionPrefix()) !== 0) {
            $action = $this->getActionPrefix() . $action;
        }
        return $action;
    }
    
    /**
     * Retourne le nom de l'action de contrôleur gérant la demande de confirmation
     * des informations saisies dans le formulaire multi-pages.
     * Par défaut: 'confirmer'.
     * 
     * @return string
     */
    public function getConfirmAction()
    {
        if (null === $this->confirmAction) {
            $this->confirmAction = self::ACTION_CONFIRM;
        }
        return $this->prefixedAction($this->confirmAction);
    }

    /**
     * Retourne le nom de l'action de contrôleur gérant la demande d'enregistrement
     * de la saisie du formulaire multi-pages.
     * Par défaut: 'enregistrer'.
     * 
     * @return string
     */
    public function getProcessAction()
    {
        if (null === $this->processAction) {
            $this->processAction = self::ACTION_PROCESS;
        }
        return $this->prefixedAction($this->processAction);
    }

    /**
     * Retourne le nom de l'action de contrôleur gérant la demande d'annulation
     * de la saisie du formulaire multi-pages.
     * Par défaut: 'annuler'.
     * 
     * @return string
     */
    public function getCancelAction()
    {
        if (null === $this->cancelAction) {
            $this->cancelAction = self::ACTION_CANCEL;
        }
        return $this->prefixedAction($this->cancelAction);
    }

    /**
     * Spécifie le préfixe appliqué aux noms des actions de contrôleur.
     * 
     * @param string $actionPrefix Ex: 'ajouter-'
     * @return MultipageForm
     */
    public function setActionPrefix($actionPrefix)
    {
        $this->actionPrefix = $actionPrefix;
        return $this;
    }
//
//    /**
//     * Spécifie le mapping <nom du fieldset> => <nom de l'action du contrôleur>.
//     * 
//     * @param array $fieldsetActionMapping <nom du fieldset> => <nom de l'action du contrôleur>
//     * @return MultipageForm
//     */
//    public function setFieldsetActionMapping(array $fieldsetActionMapping)
//    {
//        foreach ($fieldsetActionMapping as $name => $action) {
//            $fieldsetActionMapping[$name] = $this->prefixedAction($action);
//        }
//        $this->fieldsetActionMapping = $fieldsetActionMapping;
//        return $this;
//    }
    
    /**
     * Spécifie le nom de l'action de contrôleur (sans préfixe) gérant la demande de confirmation
     * de la saisie du formulaire multi-pages.
     * 
     * @param string $confirmAction Nom de l'action sans préfixe
     * @return \UnicaenApp\Form\MultipageForm
     */
    public function setConfirmAction($confirmAction)
    {
        $this->confirmAction = $confirmAction;
        return $this;
    }

    /**
     * Spécifie le nom de l'action de contrôleur (sans préfixe) gérant la demande d'enregistrement
     * de la saisie du formulaire multi-pages.
     * 
     * @param string $processAction Nom de l'action sans préfixe
     * @return \UnicaenApp\Form\MultipageForm
     */
    public function setProcessAction($processAction)
    {
        $this->processAction = $processAction;
        return $this;
    }

    /**
     * Spécifie le nom de l'action de contrôleur (sans préfixe) gérant la demande d'annulation
     * de la saisie du formulaire multi-pages.
     * 
     * @param string $cancelAction Nom de l'action sans préfixe
     * @return \UnicaenApp\Form\MultipageForm
     */
    public function setCancelAction($cancelAction)
    {
        $this->cancelAction = $cancelAction;
        return $this;
    }

    /**
     * Retourne pour chaque élément d'un fieldset son label et sa valeur.
     * Seuls les éléments visibles et possédant un label sont pris en compte.
     * 
     * @param \Zend\Form\Fieldset $fieldset
     * @param array $data Données saisies au sein de ce fieldset
     * @return array 'element_name' => array('label' => Label de l'élément, 'value' => Valeur saisie au format texte)
     */
    static public function getLabelsAndValues(\Zend\Form\Fieldset $fieldset, $data = null)
    {
        if ($fieldset instanceof MultipageFormFieldsetInterface) {
            return $fieldset->getLabelsAndValues($data);
        }
        if (null === $data) {
            $data = $fieldset->getValue();
        }
        $data = (array)$data;
        if (array_key_exists($fieldset->getName(), $data)) {
            $data = $data[$fieldset->getName()];
        }
        $values = array();
        foreach ($fieldset->getElements() as $e) {
            if (!$e->getLabel() || 'hidden' == $e->getAttribute('type')) {
                continue;
            }
            $label = $e->getLabel();
            $value = isset($data[$e->getName()]) ? $data[$e->getName()] : null;
            if ($e instanceof \Zend\Form\Element\MultiCheckbox || $e instanceof \Zend\Form\Element\Radio
                     || $e instanceof \Zend\Form\Element\Select) {
                $value = array_map(function($v) use ($e) {
                    $options = $e->getValueOptions();
                    return $options[$v] ?: $v;
                }, (array)$value);
                if (count($value) === 1) {
                    $value = current($value);
                }
            }
            elseif ($e instanceof \Zend\Form\Element\Checkbox) {
                $value = $value ? "Oui" : "Non";
            }
            $values[$e->getName()]['label'] = $label;
            $values[$e->getName()]['value'] = $value ?: "Non renseigné(e)";
        }
        return $values;
    }
}
