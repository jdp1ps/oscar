<?php
namespace UnicaenApp\Form\Element;

use InvalidArgumentException;
use Zend\Form\Element;
use Zend\InputFilter\InputProviderInterface;
use Zend\Validator\Callback;

/**
 * Elément de formulaire permettant de rechercher/sélectionner quelque chose
 * dans une source de données.
 * 
 * Deux attributs sont encapsulés : 
 * - le "label" : texte saisi dans un champ texte ;
 * - l' "id" : identifiant unique de la chose *sélectionnée* (ex: le persopass/etupass 
 *   pour un individu recherché dans l'annuaire LDAP) dans un champ caché.
 *
 * NB: Il faut utiliser l'aide de vue 'FormSearchAndSelect' pour dessiner cet élément.
 * 
 * @see \UnicaenApp\Form\View\Helper\FormSearchAndSelect
 * @author <bertrand.gauthier@unicaen.fr>
 */
class SearchAndSelect extends Element implements InputProviderInterface
{
    const ID_ELEMENT_NAME    = 'id';
    const LABEL_ELEMENT_NAME = 'label';
    const DEFAULT_SEP        = '|';

    /**
     * @var string 
     */
    protected $valueId;

    /**
     * @var string 
     */
    protected $valueLabel;

    /**
     * @var bool
     */
    protected $required = false;

    /**
     * @var bool
     */
    protected $selectionRequired = false;

    /**
     * @var string
     */
    protected $autocompleteSource;

    /**
     * Change la valeur de cet élément.
     *
     * @param array $value Tableau contenant l'id et le label
     * @return self
     */
    public function setValue($value)
    {
        if (!$value) {
            $value = array(
                self::ID_ELEMENT_NAME    => null,
                self::LABEL_ELEMENT_NAME => null,
            );
        }

        if (is_object($value) && method_exists($value, 'getId')){
            $value = array(
                self::ID_ELEMENT_NAME    => $value->getId(),
                self::LABEL_ELEMENT_NAME => (string)$value,
            );
        }

        if (!is_array($value)) {
            if (func_num_args() === 2) {
                $value = array(
                    self::ID_ELEMENT_NAME    => $value,
                    self::LABEL_ELEMENT_NAME => func_get_arg(1),
                );
            }
            elseif (is_string($value) && stripos($value, $delimiter = self::DEFAULT_SEP) !== false) {
                $value = array_combine(array(self::ID_ELEMENT_NAME, self::LABEL_ELEMENT_NAME), explode($delimiter, $value, 2));
            }
            else {
                throw new InvalidArgumentException("Valeur spécifiée invalide.");
            }
        }
        
        if (!array_key_exists($key = self::ID_ELEMENT_NAME, $value)) {
            throw new InvalidArgumentException("Tableau spécifié invalide, clé '$key' introuvable.");
        }
        if (!array_key_exists($key = self::LABEL_ELEMENT_NAME, $value)) {
            throw new InvalidArgumentException("Tableau spécifié invalide, clé '$key' introuvable.");
        }

        $this->valueId    = $value[self::ID_ELEMENT_NAME];
        $this->valueLabel = $value[self::LABEL_ELEMENT_NAME];
        parent::setValue($this->valueId);

        return $this;
    }

    /**
     * Retourne la valeur de cet élément : par défaut, c'est l'id.
     *
     * @param boolean $returnLabelIfIdIsEmpty Spécifie s'il faut retourner le label dans le cas où l'id est vide
     * @return mixed
     */
    public function getValue($returnLabelIfIdIsEmpty = false)
    {
        if ($returnLabelIfIdIsEmpty && !$this->getValueId()) { 
            return $this->getValueLabel();
        }
        return $this->getValueId();
    }

    /**
     * Retourne l'id.
     *
     * @return string
     */
    public function getValueId()
    {
        return $this->valueId;
    }

    /**
     * Retourne le label.
     *
     * @return string
     */
    public function getValueLabel()
    {
        return $this->valueLabel;
    }

    /**
     * Retourne la valeur composite de cet élément : l'id et le label séparé par 
     * le caractère spécifié.
     *
     * @param string $glue Séparateur
     * @return string
     */
    public function getValueImplode($glue = '|')
    {
        if (!$this->getValueId()) {
            return null;
        }
        return implode($glue, array($this->getValueId(), $this->getValueLabel()));
    }

    /**
     * Indique si cet élément doit être obligatoirement renseigné.
     * 
     * @return bool
     */
    public function getRequired()
    {
        return $this->required;
    }

    /**
     * Spécifie si cet élément doit être obligatoirement renseigné.
     * 
     * @param bool $required
     * @return self
     */
    public function setRequired($required = true)
    {
        $this->required = $required;
        return $this;
    }

    /**
     * Indique si la sélection d'un item est obligatoire, autrement dit
     * si l'id doit être renseigné.
     * 
     * @return bool
     */
    public function getSelectionRequired()
    {
        return $this->selectionRequired;
    }

    /**
     * Spécifie si la sélection d'un item est obligatoire, autrement dit
     * si l'id doit être renseigné (peu importe la valeur du label).
     * 
     * @param bool $selectionRequired
     * @return self
     */
    public function setSelectionRequired($selectionRequired = true)
    {
        $this->selectionRequired = $selectionRequired;
        return $this;
    }

    /**
     * Retourne la source de données dans laquelle est effectuée la recherche.
     * 
     * @return string
     */
    public function getAutocompleteSource()
    {
        return $this->autocompleteSource;
    }

    /**
     * Spécifie la source de données dans laquelle est effectuée la recherche.
     * 
     * @param string $autocompleteSource
     * @return self
     */
    public function setAutocompleteSource($autocompleteSource)
    {
        $this->autocompleteSource = $autocompleteSource;
        return $this;
    }

    /**
     * Tronque un tableau de résultat formatté pour cet élément de formulaire.
     * 
     * @param array $result Tableau à tronquer ?
     * @param integer $length Nombre d'éléments à conserver
     * @param bool $showRemainer Afficher ou pas le nombre d'éléments restant après troncature ?
     * @return array
     */
    static public function truncatedResult($result, $length = 15, $showRemainer = false)
    {
        $count  = count($result);
        $remain = $count - $length;
        if ($length && $remain > 0) {
            $label = $showRemainer ? 
                    "$remain résultats restant sur un total de $count, affinez vos critères, svp." : 
                    "Plus de $length résultats trouvés, affinez vos critères, svp.";
            $result   = array_slice($result, 0, $length);
            $result[] = array(
                'id'    => null, 
                'label' => sprintf("<em><small>%s</small></em>", $label));
        }
        return $result;
    }
        
    /**
     * Retourne l'objet permettant de valider que la sélection est correcte.
     * 
     * Deux cas de figure possibles :
     * 1/ Cet élément doit être obligatoirement renseigné (i.e. getRequired() renvoit true) :
     *    - si la sélection d'un item est requis (i.e. getSelectionRequired() renvoit true) 
     *      mais que l'id n'est pas renseigné, alors le validateur retournera <code>false</code>.
     *    - sinon, le validateur retournera <code>true</code>.
     *
     * 2/ Cet élément ne doit pas être obligatoirement renseigné (getRequired() renvoit false) :
     *    - le validateur retournera <code>true</code> systématiquement.
     * 
     * @return Callback
     * @see getInputSpecification()
     */
    public function getSelectionRequirementValidator()
    {
        $self = $this;
        $functor = function($value) use ($self) {
            $self->setValue($value);
            if ($self->getRequired() && $self->getSelectionRequired() && !$self->getValueId()) {
                return false;
            }
            return true;
        };
        return new Callback(array(
            'callback' => $functor,
            'messages' => array(
                Callback::INVALID_VALUE => "Vous devez rechercher puis sélectionner un item dans la liste proposée."),
        ));
    }
         
    /**
     * Should return an array specification compatible with
     * {@link Zend\InputFilter\Factory::createInput()}.
     *
     * @return array
     * @see \Zend\InputFilter\Factory
     */
    public function getInputSpecification()
    {
        return array(
            'name' => $this->getName(),
            'required' => $this->getRequired(),
            'filters' => array(
                array('name' => 'Zend\Filter\StringTrim'),
            ),
            'validators' => array(
                $this->getSelectionRequirementValidator(),
            ),
        );
    }
}