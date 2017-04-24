<?php

namespace UnicaenApp\View\Helper\Upload;

use UnicaenApp\Controller\Plugin\Upload\UploadedFileInterface;
use UnicaenApp\Controller\Plugin\Upload\UploadForm;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorAwareTrait;
use Zend\View\Helper\AbstractHelper;
use Zend\View\Resolver\TemplatePathStack;

/**
 * Aide de vue simplifiant l'upload de fichier.
 * 
 * @author Bertrand GAUTHIER <bertrand.gauthier at unicaen.fr>
 * @see \UnicaenApp\Controller\Plugin\Upload\Upload
 */
class UploaderHelper extends AbstractHelper implements ServiceLocatorAwareInterface
{
    use ServiceLocatorAwareTrait;
    
    /**
     * URL de l'action permettant de déposer un nouveau fichier.
     * C'est l'URL à laquelle est POSTé le formulaire d'upload.
     * 
     * Si elle est null, le formulaire n'est pas affiché.
     * 
     * @var string
     */
    protected $url;
    
    /**
     * 
     * @return UploaderHelper
     */
    public function __invoke()
    {
        $this->getView()->resolver()->attach(new TemplatePathStack(array('script_paths' => array(__DIR__ . "/script"))));
        
        // Utilisation du plugin jQuery "Form"
        $this->getView()->inlineScript()->offsetSetFile(100, $this->getView()->basePath("/js/jquery.form.js"));
        
        // Javascript de cette aide de vue
        // NB: offsetSetScript() garantie que même si l'aide de vue est appelée N fois, le javascript n'est inclu qu'une seule fois 
        $this->getView()->inlineScript()->offsetSetScript(101, file_get_contents(__DIR__ . "/script/uploader.js"));
        
        return $this;
    }

    /**
     * Spécifie l'URL permettant de déposer un nouveau fichier.
     * C'est l'URL à laquelle est POSTé le formulaire de dépôt.
     * 
     * Si elle est null, aucun formulaire ne sera affiché.
     * 
     * @param string $url
     * @return self
     */
    public function setUrl($url)
    {
        $this->url = $url;
        
        return $this;
    }
        
    /**
     * Retourne le code HTML.
     * 
     * @return string Code HTML
     */
    public function __toString()
    {
        return $this->renderForm();
    }
    
    /**
     * Génrère le code HTML du_ formulaire de dépôt.
     * NB: le formulaire s'affiche ssi une URL de dépôt a été spécifiée.
     * 
     * @return string Code HTML
     */
    public function renderForm()
    {
        $form = $this->getForm();
        
        $html = $this->getView()->render("upload-form.phtml", [
            'form' => $form,
            'url'  => $this->url,
        ]);
        
        return $html;
    }
    
    /**
     * Génère le code HTML de la DIV destionée à afficher la liste des fichiers déposés.
     * 
     * @return string Code HTML
     */
    public function renderUploadedFiles($url)
    {
        $html = $this->getView()->render("uploaded-files.phtml", [
            'url' => $url,
        ]);
        
        return $html;
    }
    
    /**
     * Génère le code HTML du lien permettant de télécharger un fichier déposé.
     * Sauf si aucune URL n'est spécifiée, auquel cas ce n'est pas un lien mais simplement le nom du fichier.
     * 
     * @return string Code HTML
     */
    public function renderUploadedFile(UploadedFileInterface $fichier, $url = null)
    {
        $html = $this->getView()->render("uploaded-file.phtml", [
            'fichier' => $fichier,
            'url'     => $url,
        ]);
        
        return $html;
    }
    
    /**
     * Génère le code HTML du lien permettant de supprimer un fichier déposé.
     * Sauf si aucune URL n'est spécifiée, auquel cas aucun lien n'est généré.
     * 
     * @param UploadedFileInterface $fichier Fichier à supprimer
     * @param string $url URL de la requête de suppression du fichier
     * @param boolean $confirm Faut-il afficher une demande de confirmation avant suppression ? Oui, par défaut.
     * @return string Code HTML
     */
    public function renderDeleteFile(UploadedFileInterface $fichier, $url = null, $confirm = true)
    {
        $html = $this->getView()->render("delete-file.phtml", [
            'fichier' => $fichier,
            'url'     => $url,
            'confirm' => $confirm,
        ]);
        
        return $html;
    }
    
    protected $form;
    
    /**
     * Retourne le formulaire de dépôt de fichier.
     * 
     * @return UploadForm
     */
    public function getForm()
    {
        if (null === $this->form) {
            $this->form = $this->getServiceLocator()->getServiceLocator()->get('form_element_manager')->get('UploadForm');
        }
        
        return $this->form;
    }
}