<?php

namespace UnicaenApp\View\Helper\TabAjax;

use Zend\View\Helper\AbstractHtmlElement;

/**
 * Implémentation en PHP du composant Tab de Bootstrap
 *
 *
 *
 *
 * @author Laurent LÉCLUSE <laurent.lecluse at unicaen.fr>
 */
class TabAjaxViewHelper extends AbstractHtmlElement
{

    /**
     * @var Tab[]
     */
    protected $tabs;

    /**
     * @var Tab|null
     */
    protected $selected = null;

    /**
     * Identifiant du Widget
     *
     * @var string
     */
    protected $id;

    /**
     * Classe(s) du widget
     *
     * @var mixed
     */
    protected $class;



    /**
     * @return Tab[]
     */
    public function getTabs()
    {
        return $this->tabs;
    }



    /**
     * @param array|Tab $tab
     *
     * @return $this
     */
    public function addTab($tab)
    {
        if ($tab instanceof Tab) {
            $this->tabs[] = $tab;
        } else {
            $this->tabs[] = Tab::createFromOptions($tab);
        }

        return $this;
    }



    /**
     * @param string|Tab $tab
     *
     * @return $this
     */
    public function removeTab($tab)
    {
        $tabIndex = $this->getTabIndex($tab);
        if ($tabIndex !== null) {
            if ($this->selected === $this->tabs[$tabIndex]) $this->selected = null;
            unset($this->tabs[$tabIndex]);
        }

        return $this;
    }



    /**
     * @return null|Tab
     */
    public function getSelected()
    {
        if ($this->selected){
            return $this->selected;
        }else{
            foreach( $this->tabs as $tab ) return $tab; // retourne la première de la liste
        }

    }



    /**
     * @param null|Tab $selected
     */
    public function setSelected($selected)
    {
        $tabIndex = $this->getTabIndex($selected);
        if (null !== $tabIndex) {
            $this->selected = $this->tabs[$tabIndex];
        }

        return $this;
    }



    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }



    /**
     * @param string $id
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }



    /**
     * @return mixed
     */
    public function getClass()
    {
        return $this->class;
    }



    /**
     * @param mixed $class
     */
    public function setClass($class)
    {
        $this->class = $class;

        return $this;
    }



    /**
     * @param string|Tab $tab
     *
     * @return int|null
     */
    private function getTabIndex($tab)
    {
        if ($tab instanceof Tab) {
            $tab = $tab->getId();
        }

        foreach ($this->tabs as $tabIndex => $t) {
            if ($t->getId() == $tab) return $tabIndex;
        }

        return null;
    }



    /**
     * Helper entry point.
     *
     * @return self
     */
    public function __invoke( $tabs=null )
    {
        if (is_array($tabs)){
            foreach( $tabs as $tab ){
                $this->addTab($tab);
            }
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
        } catch (\Exception $exc) {
            var_dump($exc->getMessage(), \UnicaenApp\Util::formatTraceString($exc->getTraceAsString()));
            die;
        }
    }



    /**
     * Génère le code HTML.
     *
     * @return string
     */
    public function render()
    {
        $attrs = [
            'class' => implode(' ', ['tab-ajax'] + (array)$this->class),
        ];
        if ($this->getId()) $attrs['id'] = $this->getId();


        $h = "\n<div " . $this->htmlAttribs($attrs) . ">\n";

        $h .= "\t<ul class=\"nav nav-tabs\" role=\"tablist\">\n";
        $index = 1;
        foreach ($this->tabs as $tab) {
            $h .= $this->renderTabNav($tab, $index);
            $index++;
        }
        $h .= "\t</ul>\n";

        $h .= "\t<div class=\"tab-content\">\n";
        $index = 1;
        foreach ($this->tabs as $tab) {
            $h .= $this->renderTabPane($tab, $index);
            $index++;
        }
        $h .= "\t</div>\n";
        $h .= "\t</div>\n";
        return $h;
    }



    protected function renderTabNav(Tab $tab, $index = 0)
    {
        $id = $tab->getId() ?: 'tab' . $index;

        $label = $tab->getLabel() ?: $id;

        $attrs = [
            'role'  => 'presentation',
            'class' => (array)$tab->getClass(),
        ];
        if ($this->getSelected() === $tab) $attrs['class'][] = 'active';


        $aattrs = [
            'href'          => $tab->getUrl() ?: '#'.$id,
            'role'          => 'tab',
            'data-toggle'   => 'tab',
            'aria-controls' => $id,
        ];
        if ($tab->getTitle()) $aattrs['title'] = $tab->getTitle();
        if ($tab->getUrl()) $aattrs['data-target'] = '#'.$id;
        if ($tab->getIsLoaded()) $aattrs['data-is-loaded'] = $tab->getIsLoaded() ? '1' : '0';
        if ($tab->getForceRefresh()) $aattrs['data-force-refresh'] = '1';

        $h = "\t\t<li " . $this->htmlAttribs($attrs) . ">"
            . "<a " . $this->htmlAttribs($aattrs) . ">" . $label . "</a>"
            . "</li>\n";

        return $h;
    }



    protected function renderTabPane(Tab $tab, $index = 0)
    {
        $id = $tab->getId() ?: 'tab' . $index;

        $attrs = [
            'role'  => 'tabpanel',
            'class' => ['tab-pane'],
            'id'    => $id,
        ];
        if ($this->getSelected() === $tab) $attrs['class'][] = 'active';

        $h     = "\t\t<div " . $this->htmlAttribs($attrs) . ">" . $tab->getContent() . "</div>\n";

        return $h;
    }

}