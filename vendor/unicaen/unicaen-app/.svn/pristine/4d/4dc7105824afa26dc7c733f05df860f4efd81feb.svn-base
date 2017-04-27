<?php
namespace UnicaenApp\View\Helper;

use Exception;
use UnicaenApp\Util;
use Zend\View\Helper\AbstractHelper;

/**
 * Aide de vue qui génère un icône cliquable en forme de point d'interrogation (par défaut) 
 * permettant d'afficher ou de masquer un conteneur. 
 *
 * @author Bertrand GAUTHIER <bertrand.gauthier@unicaen.fr>
 */
class ToggleDetails extends AbstractHelper
{
    /**
     * @var bool
     */
    protected static $inlineJsAppended = false;
    
    /**
     * @var string
     */
    protected $detailsDivId = null;
    
    /**
     * @var string
     */
    protected $iconClass = 'glyphicon-question-sign';
    
    /**
     * @var string
     */
    protected $title = "Cliquez pour afficher/masquer les détails";

    /**
     * Helper entry point.
     *
     * @return self
     */
    public function __invoke($detailsDivId, $title = null, $iconClass = null)
    {
        $this->detailsDivId = $detailsDivId;
        
        if ($title) {
            $this->title = $title;
        }
        
        if ($iconClass) {
            $this->iconClass = $iconClass;
        }
        
        return $this;
    }
    
    /**
     * Retourne le code HTML généré par cette aide de vue.
     * 
     * @return string
     */
    public function __toString()
    {
        try {
            return $this->render();
        }
        catch (Exception $exc) {
            var_dump($exc->getMessage(), Util::formatTraceString($exc->getTraceAsString()));die;
        }
    }

    /**
     * Génère le code HTML.
     * 
     * @return string
     */
    protected function render()
    {
        $html = <<<EOS
<a href="#" class="toggle-details" data-target="#{$this->detailsDivId}" title="{$this->title}">
    <span class="glyphicon {$this->iconClass}" aria-hidden="true"></span>
</a>
EOS;
    
        $this->includeJs($html);
        
        return $html . PHP_EOL;
    }
    
    /**
     * 
     * @param string $html
     * @return \UnicaenApp\View\Helper\ToggleDetails
     */
    protected function includeJs(&$html)
    {
        $js = <<<EOS
$(function() {
    $(".toggle-details").click(function(e) { 
        var target = $($(this).data('target')); 
        target.slideToggle(); 
        e.preventDefault(); 
    });
});
EOS;
        
        $request          = $this->getView()->getHelperPluginManager()->getServiceLocator()->get('request');
        $isXmlHttpRequest = $request->isXmlHttpRequest();
        
        if ($isXmlHttpRequest) {
            // pour une requête AJAX on ne peut pas utilser le plugin "inlineScript"
            if (!static::$inlineJsAppended) {
                $html .= PHP_EOL . "<script>" . PHP_EOL . $js . PHP_EOL . "</script>";
                static::$inlineJsAppended = true;
            }
        }
        else {
            $this->getView()->inlineScript()->offsetSetScript(100, $js);
        }
        
        return $this;
    }
}