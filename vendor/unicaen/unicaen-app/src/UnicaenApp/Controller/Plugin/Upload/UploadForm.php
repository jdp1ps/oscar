<?php

namespace UnicaenApp\Controller\Plugin\Upload;

use UnicaenApp\Exception\LogicException;
use UnicaenApp\Filter\BytesFormatter;
use UnicaenApp\Util;
use Zend\Form\Element\Hidden;
use Zend\Form\Form;
use Zend\InputFilter\FileInput;
use Zend\InputFilter\InputFilter;
use Zend\Validator\File\Size;

/**
 * Formulaire de dépôt de fichier.
 *
 * @author Bertrand GAUTHIER <bertrand.gauthier at unicaen.fr>
 */
class UploadForm extends Form
{
    /**
     * Constructeur.
     */
    public function __construct($name = null, $options = array())
    {
        parent::__construct($name, $options);
        
        $this->setAttribute('id', "upload-form");
    }
    
    /**
     * 
     */
    public function init()
    {
        $this->setUploadMaxFilesize(2*1024*1024); // 2 Mo
         
        $this
                ->addElements()
                ->addInputFilter();
    }
    
    /**
     * 
     */
    private function addElements()
    {
        /**
         * Id
         */
        $this->add(new Hidden('id'));
        
        /**
         * Fichiers
         */
        $this->add([
            'name' => 'files',
            'type' => 'File',
            'options' => [
                'label' => "Déposer un fichier :",
                'label_attributes' => [
                    'title' => "Niveau",
                    'disable_html_escape' => true,
                ],
                'label_options' => ['disable_html_escape' => true],
            ],
            'attributes' => [
                'id' => 'files',
                'multiple' => true,
            ],
        ]);
        
        return $this;
    }
    
    /**
     * 
     * @return self
     */
    private function addInputFilter()
    {
        $inputFilter = new InputFilter();

        // File Input
        $fileInput = new FileInput('files');
        $fileInput->setRequired(true);

        // You only need to define validators and filters
        // as if only one file was being uploaded. All files
        // will be run through the same validators and filters
        // automatically.
        $fileInput->getValidatorChain()
            ->attach($this->getFileSizeValidator())
//            ->attachByName('filesize', array('max' => 1024*1024*2 )) // 2 Mo
//            ->attachByName('filemimetype', array('mimeType' => 'image/bmp'))
//            ->attachByName('fileimagesize', array('maxWidth' => 100, 'maxHeight' => 100))
        ;

        $inputFilter->add($fileInput);

        $this->setInputFilter($inputFilter);
        
        return $this;
    }
    
    /**
     * @var Size
     */
    private $fileSizeValidator;
    
    /**
     * Retourne le validateur de taille de fichier uploadable.
     * NB: met à jour systématiquement le paramètre 'max' du validateur à partir de l'attribut correspondant.
     * 
     * @return Size
     */
    private function getFileSizeValidator()
    {
        if (null === $this->fileSizeValidator) {
            $this->fileSizeValidator = new Size();
        }
        
        $fileSizeTooBigMessage = sprintf("Vous ne pouvez pas déposer de fichier dont la taille excède %s", 
                $this->getUploadMaxFilesizeFormatted());
        
        $this->fileSizeValidator
                ->setMax($this->getUploadMaxFilesize())
                ->setMessage($fileSizeTooBigMessage, Size::TOO_BIG);
        
        return $this->fileSizeValidator;
    }
    
    /**
     * @var integer
     */
    private $uploadMaxFilesize;
    
    /**
     * Spécifie la taille maxi de chaque fichier uploadable.
     * 
     * @param integer $uploadMaxFilesize Taille max en octets
     * @return self
     * @throws LogicException Si la taille max spécifiée dépasse OU ÉGALE la valeur du paramètre de config 'upload_max_filesize'
     */
    public function setUploadMaxFilesize($uploadMaxFilesize)
    {
        $uploadMaxFilesizeIni = $this->getUploadMaxFilesizeIni();
        
        if ($uploadMaxFilesizeIni && $uploadMaxFilesizeIni <= $uploadMaxFilesize) {
            throw new LogicException(sprintf(
                    "La taille max spécifiée (%s) doit être inférieure STRICTEMENT à la valeur du paramètre de "
                    . "config 'upload_max_filesize' (%s) sinon le validateur ne peut entrer en action.",
                    $uploadMaxFilesize, 
                    $uploadMaxFilesizeIni));
        }
        
        $this->uploadMaxFilesize = $uploadMaxFilesize;
        
        return $this;
    }
    
    /**
     * Retourne la taille maxi de chaque fichier uploadable.
     * 
     * @return integer
     */
    public function getUploadMaxFilesize()
    {
        return $this->uploadMaxFilesize;
    }
    
    /**
     * Retourne la taille maxi de chaque fichier uploadable.
     * 
     * @return integer
     */
    public function getUploadMaxFilesizeFormatted()
    {
        $f = new BytesFormatter();
        
        return $f->filter($this->getUploadMaxFilesize());
    }
    
    /**
     * Retourne la valeur numérique du paramètre de config 'upload_max_filesize'.
     * 
     * @return integer
     */
    private function getUploadMaxFilesizeIni()
    {
        $max = ini_get('upload_max_filesize');
        if ($max == -1) {
            return null;
        }
        
        return Util::convertAsBytes($max);
    }
}