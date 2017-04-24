<?php

namespace UnicaenApp\Controller\Plugin\Upload;

use UnicaenApp\Util;
use Zend\Mvc\Controller\Plugin\AbstractPlugin;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorAwareTrait;
use Zend\View\Model\JsonModel;

/**
 * Plugin facilitant le dépôt de fichier.
 *
 * @author Bertrand GAUTHIER <bertrand.gauthier at unicaen.fr>
 */
class UploaderPlugin extends AbstractPlugin implements ServiceLocatorAwareInterface
{
    use ServiceLocatorAwareTrait;
    
    /**
     * Magic method.
     * 
     * @return self
     */
    public function __invoke()
    {
        return $this;
    }
    
    /**
     * Dépôt d'un fichier
     * 
     * @todo Améliorations possibles : 
     * - retourner tous les résultats au format JSON ; 
     * - s'inspirer de https://github.com/cgmartin/ZF2FileUploadExamples
     * 
     * @return array|boolean
     */
    public function upload()
    {
        $request = $this->getController()->getRequest();
        $form    = $this->getForm();
        
        if ($request->isPost()) {
            // Make certain to merge the files info!
            $post = array_merge_recursive(
                $request->getPost()->toArray(),
                $request->getFiles()->toArray()
            );
            $form->setData($post);
            if ($form->isValid()) {
                $data = $form->getData();
            }
            else {
                // extraction des messages d'info (ce sont les feuilles du tableau)
                $errors = Util::extractArrayLeafNodes($form->getMessages());
//                $errors = [print_r($form->getMessages(), true)];
                
                return new JsonModel(['errors' => $errors]);
            }

            return $data;
        }
        
        return false;
    }
    
    /**
     * Téléchargement d'un fichier déposé.
     * 
     * @param UploadedFileInterface $fichier
     */
    public function download(UploadedFileInterface $fichier)
    {
        $contenu     = $fichier->getContenu();
        $content     = is_resource($contenu) ? stream_get_contents($contenu) : $contenu;
        $contentType = $fichier->getType() ?: 'application/octet-stream';
        
        header('Content-Description: File Transfer');
        header('Content-Type: ' . $contentType);
        header('Content-Disposition: attachment; filename=' . $fichier->getNom());
        header('Content-Transfer-Encoding: binary');
        header('Content-Length: ' . strlen($content));
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Expires: 0');
        header('Pragma: public');
        
        echo $content;
        exit;
    }
    
    /**
     * @var UploadForm 
     */
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
            $this->form->setUploadMaxFilesize(1024*1024*2); // 2 Mo
        }
        
        return $this->form;
    }
    
    /**
     * Spécifie la taille maxi de chaque fichier uploadable.
     * 
     * @param integer $uploadMaxFilesize Taille max en octets, ex: 1024*1024*2 pour 2 Mo
     * @return self
     */
    private function setUploadMaxFilesize($uploadMaxFilesize)
    {
        $this->getForm()->setUploadMaxFilesize($uploadMaxFilesize);
        
        return $this;
    }
}