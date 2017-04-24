<?php

namespace UnicaenApp\View\Helper\Navigation;

use Zend\View\Helper\Navigation\Menu;
use Zend\Navigation\Page\AbstractPage;
use UnicaenApp\Exception\LogicException;

/**
 * Description of AbstractMenu
 *
 * @author Bertrand GAUTHIER <bertrand.gauthier at unicaen.fr>
 */
class AbstractMenu extends Menu
{
    const CLASS_ICONIFY = 'iconify';
    
    /**
     * Témoin indiquant s'il faut ignorer les filtres courants
     * @var bool
     */
    protected $bypass = false;
    
    /**
     * Témoin indiquant si les filtres courants ont été modifiés
     * @var bool
     */
    protected $updated = false;
    
    /**
     * Paramètres de page à fusionner avec ceux existants
     * @var array
     */
    protected $paramsToMerge = array();

    /**
     * Paramètres de page à faire disparaître
     * @var array
     */
    protected $paramsToRemove = array();

    /**
     * Propriétés de page à ajouter
     * @var array
     */
    protected $propsToAdd = array();
    
    /**
     * Filtre de page.
     * Seules les pages ayant défini le paramètre spécifié (qqsoit sa valeur) sont prises en compte.
     * @var array nom_du_param => valeur_de_substitution ou null
     */
    private $withparams = array();
    
    /**
     * Filtre de page.
     * Seules les pages n'ayant pas défini le paramètre spécifié sont prises en compte.
     * @var array nom_du_param => true
     */
    private $withoutparams = array();
    
    /**
     * Filtre de page.
     * Seules les pages ayant défini la propriété spécifiée (avec une valeur précise éventuelle) sont prises en compte.
     * @var array nom_du_param => valeur_de_substitution ou null
     */
    private $withprops = array();
    
    /**
     * Filtre de page.
     * Seules les pages n'ayant pas défini la propriété spécifiée sont prises en compte.
     * @var array nom_du_param => true
     */
    private $withoutprops = array();
    
    /**
     * Filtre de page.
     * Si <code>true</code> : seules les pages ayant l'attribut 'withtarget' défini
     * à vrai (1, true, '1') sont prises en compte.
     * Si <code>false</code> : seules les pages n'ayant pas défini l'attribut 'withtarget'
     * ou l'ayant défini à faux (0, false, '0') sont prises en compte.
     * Si <code>null</code> : toutes les pages sont prises en compte, qqe soit
     * l'attribut 'withtarget' de chaque page.
     * @var boolean
     */
    private $withtarget = null;
    
    /**
     * Cible éventuelle des pages.
     * @var mixed
     */
    private $target = null;
    
    /**
     * Id de la cible éventuelle des pages.
     * @var mixed
     */
    protected $targetid = null;
    
    /**
     * Filtre de page.
     * Liste des pages (définie par controller/action/params) à inclure.
     * @var array
     */
    private $include = array();
    
    /**
     * Filtre de page.
     * Liste des pages (définie par controller/action/params) à exclure.
     * @var array
     */
    private $except = array();

    /**
     * @var array
     */
    private $acceptCache = [];
    
    /**
     * View helper entry point:
     * Retrieves helper and optionally sets container to operate on
     *
     * @param AbstractContainer $container [optional] container to operate on
     * @return self
     */
    public function __invoke($container = null)
    {
        parent::__invoke($container);
        
        $this->reset();
        
        return $this;
    }



    /**
     * @param AbstractPage $page
     * @param bool         $recursive
     *
     * @return null|boolean
     */
    private function getAcceptCache(AbstractPage $page, $recursive = true)
    {
        foreach( $this->acceptCache as $ac ){
            if ($ac['page'] === $page && $ac['recursive'] === $recursive){
                return $ac['result'];
            }
        }
        return null;
    }


    /**
     * @param AbstractPage $page
     * @param bool         $recursive
     * @param bool         $result
     *
     * @return bool
     */
    private function setAcceptCache(AbstractPage $page, $recursive = true, $result)
    {
        $this->acceptCache[] = [
            'page'      => $page,
            'recursive' => $recursive,
            'result'    => $result,
        ];
        return $result;
    }


    /**
     * {@inheritedDoc}
     */
    public function accept(AbstractPage $page, $recursive = true)
    {
        /* Utilisation du cache */
        $ac = $this->getAcceptCache($page,$recursive);
        if ($ac !== null ) return $ac;

        if (!parent::accept($page, $recursive)) {

            return $this->setAcceptCache($page,$recursive,false);
        } 
        
        if ($this->bypass || !$this->updated) {
            return $this->setAcceptCache($page,$recursive,true);
        }
        
        $accept = true;
        $params = $page->get('params');
        foreach ($this->withprops as $propName => $propValue) {
            if (!($prop = $page->get($propName)) || (null !== $propValue && $prop != $propValue)) {
                $accept = false;
                break;
            }
        }
        foreach ($this->withoutprops as $propName => $propValue) {
            if (null !== $page->get($propName)) {
                $accept = false;
                break;
            }
        }
        foreach ($this->withparams as $paramName => $paramValue) {
            if (!$params || !array_key_exists($paramName, $params)) {
                $accept = false;
                break;
            }
        }
        foreach ($this->withoutparams as $paramName => $paramValue) {
            if ($params && array_key_exists($paramName, $params)) {
                $accept = false;
                break;
            }
        }
        if (true === $this->withtarget && !$page->get('withtarget')) {
            $accept = false;
        }
        if (false === $this->withtarget && $page->get('withtarget')) {
            $accept = false;
        }
        foreach ($this->include as $include) {
            $pageAttribs = array();
            if (isset($include['route']) && null !== $page->get('route')) {
                $pageAttribs['route'] = $page->get('route');
            }
            else {
                if (isset($include['action']) && null !== $page->get('action')) {
                    $pageAttribs['action'] = $page->get('action');
                }
                if (isset($include['controller']) && null !== $page->get('controller')) {
                    $pageAttribs['controller'] = $page->get('controller');
                }
            }
            if (isset($include['params']) && null !== $page->get('params')) {
                $pageAttribs['params'] = $page->get('params');
            }

            if ($pageAttribs !== $include) {
                $accept = false;
                break;
            }
        }
        foreach ($this->except as $except) {
            $pageAttribs = array();
            if (isset($except['route']) && null !== $page->get('route')) {
                $pageAttribs['route'] = $page->get('route');
            }
            else {
                if (isset($except['action']) && null !== $page->get('action')) {
                    $pageAttribs['action'] = $page->get('action');
                }
                if (isset($except['controller']) && null !== $page->get('controller')) {
                    $pageAttribs['controller'] = $page->get('controller');
                }
            }
            if (isset($except['params']) && null !== $page->get('params')) {
                $pageAttribs['params'] = $page->get('params');
            }

            if ($pageAttribs === $except) {
                $accept = false;
                break;
            }
        }
        
        return $this->setAcceptCache($page,$recursive,$accept);
    }
    
    /**
     * {@inheritedDoc}
     */
    public function htmlify(AbstractPage $page, $escapeLabel = true, $addClassToListItem = false)
    {
        $this->processPage($page);
        
        $icon = null;
        if ($page->get('icon')) {
            $icon           = sprintf('<span class="%s"></span>', $page->get('icon'));
            $hasIconifyProp = $this->hasPropToAdd('class', self::CLASS_ICONIFY) || false !== strpos($page->get('class'), self::CLASS_ICONIFY);
            // remplacement du label par l'icône si 'iconify' trouvé, sinon insertion devant
            if ($hasIconifyProp) {
                $page->setLabel($icon);
            }
            else {
                $page->setLabel($icon . '&nbsp;' . $page->getLabel());
            }
            $escapeLabel = false;
        }

        try{
            $page->getHref(); // si on ne parvient pas à construire l'URL alors on sort
            $html = parent::htmlify($page, $escapeLabel, $addClassToListItem);
            return $html;
        }catch(\Exception $e){
            return null;
        }
    }

    /**
     * 
     * 
     * @param AbstractPage $page
     * @return self
     */
    protected function processPage(AbstractPage $page)
    {
        if ($this->target) {
            // title: recherche et remplacement de motif par la valeur de l'attribut correspondant de la cible
            if (($title = $page->get('title'))) {
                if (preg_match_all("!\{(.*)\}!Ui", $title, $matches)) { // recherche d'attribut entre accolades
                    foreach ($matches[1] as $attr) {
                        if (isset($this->target->$attr)) {
                            $title = str_replace('{' . $attr . '}', $this->target->$attr, $title);
                        }
                    }
                    $page->set('title', $title);
                }
            }
        }
        if ($this->paramsToMerge) {
            // fusion des paramètres
            $params = array_merge((array)$page->get('params'), (array)$this->paramsToMerge);
            $page->set('params', $params);

            // title: recherche et remplacement de motif par la valeur du paramètre correspondant
            if (($title = $page->get('title'))) {
                foreach ((array)$this->paramsToMerge as $key => $value) {
                    $title = str_replace('{'.$key.'}', $value, $title);
                }
                $page->set('title', $title);
            }
        }
        if ($this->paramsToRemove) {
            foreach ($this->paramsToRemove as $paramName) {
                $params = $page->getParams();
                if (isset($params[$paramName])) {
                    unset($params[$paramName]);
                    $page->setParams($params);
                }
            }
        }
        if ($this->propsToAdd) {
            // ajout de propriétés
            foreach ($this->propsToAdd as $name => $value) {
                if (($prop = $page->get($name))) {
                    $value = $prop . ' ' . $value;
                }
                $page->set($name, $value);
            }
        }
        
        return $this;
    }
    
    /**
     * Ajoute un filtre : présence requise d'un paramètre de page.
     * Substitution possible de la valeur initiale du paramètre par une autre.
     *
     * @param string $paramName Nom du paramètre requis
     * @param mixed $paramSubstitutionValue Nouvelle valeur à donner au paramètre
     * @return self
     */
    public function withParam($paramName, $paramSubstitutionValue = null)
    {
        $this->updated = true;
        $this->withparams[$paramName] = $paramSubstitutionValue;
        if (null !== $paramSubstitutionValue) {
            $this->paramsToMerge = array_merge($this->paramsToMerge, array($paramName => $paramSubstitutionValue));
        }
        unset($this->withoutparams[$paramName]);

        return $this;
    }

    /**
     * Ajoute un filtre : absence requise d'un paramètre de page.
     *
     * @param string $paramName Nom du paramètre indésirable
     * @return self
     */
    public function withoutParam($paramName)
    {
        $this->updated = true;
        $this->withoutparams[$paramName] = true;
        unset($this->withparams[$paramName]);
        unset($this->paramsToMerge[$paramName]);

        return $this;
    }

    /**
     * Ajoute un filtre : présence requise d'une propriété de page, avec valeur précise éventuelle.
     *
     * @param string $propName Nom de la propriété requise
     * @param mixed $propValue Éventuelle valeur requise pour la propriété
     * @return self
     */
    public function withProp($propName, $propValue = null)
    {
        $this->updated = true;
        $this->withprops[$propName] = $propValue;
        unset($this->withoutprops[$propName]);

        return $this;
    }

    /**
     * Ajoute un filtre : absence requise d'une propriété de page.
     *
     * @param string $propName Nom de la propriété indésirable
     * @return self
     */
    public function withoutProp($propName)
    {
        $this->updated = true;
        $this->withoutprops[$propName] = true;
        unset($this->withprops[$propName]);

        return $this;
    }
   
    /**
     * Ajoute un filtre : présence requise de la propriété de page 'withtarget'.
     * 
     * Si une cible est spécifiée, un paramètre ayant la valeur de cette cible (ou, si c'est un objet,
     * du résultat de sa méthode getId() éventuelle ou sinon de son attribut 'id' éventuel) 
     * est ajouté à chaque page.
     *
     * @param mixed $target Cible de l'action correspondant à la page.
     * Si la cible spécifiée est un objet avec un attribut 'id' on utilise cet attribut ;
     * sinon on utilise sa représentation littéral.
     * @param string $paramName Nom du paramètre à ajouter à chaque page
     * @return self
     */
    public function withTarget($target = null, $paramName = 'id')
    {
        $this->updated    = true;
        $this->withtarget = true;
        $this->targetid   = null;
        if ($target) {
            $this->target = $target;
            $id = "" . $this->target;
            if (method_exists($this->target, 'getId')) {
                $id = $this->target->getId();
            }
            elseif (isset($this->target->id)) {
                $id = $this->target->id;
            }
            if ($id) {
                $this->targetid      = $id;
                $this->paramsToMerge = array_merge($this->paramsToMerge, array($paramName => $this->targetid));
            }
        }
       
        return $this;
    }

    /**
     * Ajoute un filtre : absence requise de la propriété de page 'withtarget'.
     *
     * @param string $paramName Nom du paramètre ayant été ajouté par la méthode "withTarget()"
     * @return self
     */
    public function withoutTarget($paramName = 'id')
    {
        $this->updated = true;
        $this->withtarget = false;
        $this->target = null;
        if (array_key_exists($paramName, $this->paramsToMerge)) {
            unset($this->paramsToMerge[$paramName]);
        }
       
        return $this;
    }

    /**
     * Ajoute un paramètre à toutes les pages systématiquement.
     *
     * @param string $paramName Nom du paramètre à ajouter
     * @param string $paramValue Valeur de ce paramètre
     * @return self
     */
    public function addParam($paramName, $paramValue)
    {
        $this->paramsToMerge = array_merge($this->paramsToMerge, array($paramName => $paramValue));
        
        return $this;
    }

    /**
     * Ajoute plusieurs paramètres à toutes les pages systématiquement.
     *
     * @param array $params Tableau de paramètres au format nom => valeur
     * @return self
     */
    public function addParams(array $params)
    {
        $this->paramsToMerge = array_merge($this->paramsToMerge, $params);
       
        return $this;
    }

    /**
     * Ajoute une propriété toutes les pages systématiquement.
     *
     * @prop string $propName Nom de la propriété à ajouter
     * @prop string $propValue Valeur de cette propriété
     * @return self
     */
    public function addProp($propName, $propValue)
    {
        $this->propsToAdd = array_merge($this->propsToAdd, array($propName => $propValue));

        return $this;
    }

    /**
     * Ajoute plusieurs propriétés à toutes les pages systématiquement.
     *
     * @prop array $props Tableau de propriétés au format nom => valeur
     * @return self
     */
    public function addProps(array $props)
    {
        $this->propsToAdd = array_merge($this->propsToAdd, $props);
       
        return $this;
    }

    /**
     * Cherche une valeur de propriété particulière a été ajoutée.
     * 
     * @param string $name Nom de la propriété
     * @param string $value Valeur de la propriété
     * @return bool
     * @see addProp()
     */
    protected function hasPropToAdd($name, $value)
    {
        return isset($this->propsToAdd[$name]) && false !== strpos($this->propsToAdd[$name], $value);
    }

    /**
     * Supprime un paramètre à toutes les pages systématiquement.
     *
     * @param string $paramName Nom du paramètre à retirer
     * @return self
     */
    public function removeParam($paramName)
    {
        if (array_key_exists($paramName, $this->paramsToMerge)) {
            unset($this->paramsToMerge[$paramName]);
        }
        $this->paramsToRemove[] = $paramName;
       
        return $this;
    }

    /**
     * Spécifie une page à inclure, uniquement si la condition spécifiée est remplie.
     *
     * @param mixed $condition Condition à remplir pour que la page soit inclue
     * @param string $action Nom de l'action éventuelle
     * @param string $controller Nom du contrôleur éventuel
     * @param array $params Paramètres éventuels
     * @return self
     */
    public function includeIf($condition, $action = null, $controller = null, $params = null)
    {
        if (!$condition) {
            return;
        }
       
        $criteres = array();
        if (null !== $action) {
            $criteres['action'] = $action;
        }
        if (null !== $controller) {
            $criteres['controller'] = $controller;
        }
        if (null !== $params) {
            $criteres['params'] = $params;
        }

        if (!$criteres) {
            throw new LogicException ("Aucun critère d'inclusion de pages fourni");
        }
        
        $this->updated = true;
        $this->include[] = $criteres;

        return $this;
    }

    /**
     * Exclut toutes les pages à l'exception de celle spécifiée, si la condition est remplie
     * (principe de la liste blanche).
     * 
     * @param mixed $condition Condition à remplir pour que la page soit inclue
     * @param string $routeName Nom de la route
     * @return MenuContextuel
     */
    public function includeRouteIf($condition, $routeName)
    {
        if (!$condition) {
            return $this;
        }
        
        $this->updated   = true;
        $this->include[] = array('route' => $routeName);

        return $this;
    }
   
    /**
     * Spécifie une page à exclure.
     *
     * @param string $action Nom de l'action éventuelle
     * @param string $controller Nom du contrôleur éventuel
     * @param array $params Paramètres éventuels
     * @return self
     */
    public function except($action = null, $controller = null, $params = null)
    {
        $criteres = array();
        if (null !== $action) {
            $criteres['action'] = $action;
        }
        if (null !== $controller) {
            $criteres['controller'] = $controller;
        }
        if (null !== $params) {
            $criteres['params'] = $params;
        }

        if (!$criteres) {
            throw new LogicException ("Aucun critère d'exclusion de pages fourni");
        }
        
        $this->updated = true;
        $this->except[] = $criteres;
       
        return $this;
    }

    /**
     * Spécifie une page à exclure, si la condition spécifiée est remplie.
     *
     * @param mixed $condition Condition à remplir pour que la page soit exclue
     * @param string $action Nom de l'action éventuelle
     * @param string $controller Nom du contrôleur éventuel
     * @param array $params Paramètres éventuels
     * @return self
     */
    public function exceptIf($condition, $action = null, $controller = null, $params = null)
    {
        if ($condition) {
            $this->except($action, $controller, $params);
        }

        return $this;
    }

    /**
     * Exclut une page (spécifiée par sa route).
     * 
     * @param string $routeName Nom de la route
     * @return MenuContextuel
     */
    public function exceptRoute($routeName)
    {
        $this->updated  = true;
        $this->except[] = array('route' => $routeName);

        return $this;
    }

    /**
     * Exclut une page (spécifiée par sa route), uniquement si la condition spécifiée est remplie.
     * 
     * @param mixed $condition Condition à remplir pour que la page soit exclue
     * @param string $routeName Nom de la route
     * @return MenuContextuel
     */
    public function exceptRouteIf($condition, $routeName)
    {
        if ($condition) {
            $this->exceptRoute($routeName);
        }

        return $this;
    }
   
    /**
     * Remet à zéro tous les filtres de page et la cible des pages.
     *
     * @return self
     */
    public function reset()
    {
        $this->paramsToMerge = array();
        $this->paramsToRemove = array();
        $this->propsToAdd    = array();
        $this->withparams    = array();
        $this->withoutparams = array();
        $this->withprops     = array();
        $this->withoutprops  = array();
        $this->withtarget    = null;
        $this->target        = null;
        $this->include       = array();
        $this->except        = array();
        $this->updated       = true;
       
        return $this;
    }
}