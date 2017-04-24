<?php

namespace UnicaenApp\View\Helper;

use Zend\Escaper\Escaper;
use Zend\View\Helper\AbstractHtmlElement;

/**
 * Dessine un tag HTML avec ses attributs
 *
 * @author Laurent LÉCLUSE <laurent.lecluse at unicaen.fr>
 */
class TagViewHelper extends AbstractHtmlElement
{
    const AUTOCLOSE_NO    = 0;
    const AUTOCLOSE_SHORT = 1;
    const AUTOCLOSE_FULL  = 2;

    /**
     * Nom du tag
     *
     * @var string
     */
    protected $name;

    /**
     * Attributs du tag
     *
     * @var array
     */
    protected $attributes = [];

    /**
     * @var Escaper
     */
    protected $escaper;



    /**
     *
     * @param string $name
     * @param array  $attributes
     *
     * @return self
     */
    public function __invoke($name = null, array $attributes = [])
    {
        $tag = new self;
        $tag->setView($this->getView());

        //$this->reset();
        if (!empty($name)) {
            $tag->setName($name);
        }
        if (!empty($attributes)) {
            $tag->setAttributes($attributes);
        }

        return $tag;
    }



    /**
     * The __toString method allows a class to decide how it will react when it is converted to a string.
     *
     * @return string
     * @link http://php.net/manual/en/language.oop5.magic.php#language.oop5.magic.tostring
     */
    function __toString()
    {
        return $this->open();
    }



    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }



    /**
     * @param string $name
     *
     * @return TagViewHelper
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }



    /**
     * Set new attribute.
     *
     * @param string $attrName
     * @param string $attrValue
     *
     * @return self
     */
    public function setAttribute($attrName, $attrValue)
    {
        $this->attributes[$attrName] = $attrValue;

        return $this;
    }



    /**
     * Add new or overwrite the existing attributes.
     *
     * @param array $attribs
     *
     * @return self
     */
    public function setAttributes(array $attribs)
    {
        foreach ($attribs as $name => $value) {
            $this->setAttribute($name, $value);
        }

        return $this;
    }



    /**
     * @return array
     */
    public function getAttributes()
    {
        return $this->attributes;
    }



    /**
     * Render opening tag.
     *
     * @return string
     */
    public function open($autoClose = self::AUTOCLOSE_NO)
    {
        $result = sprintf(
            '<%s%s%s>',
            $this->getEscaper()->escapeHtml($this->getName()),
            $this->htmlAttribs($this->getAttributes()),
            self::AUTOCLOSE_SHORT == $autoClose ? ' /' : ''
        );
        if (self::AUTOCLOSE_FULL == $autoClose) {
            $result .= $this->close();
        }

        return $result;
    }



    /**
     * Render closing tag.
     *
     * @return string
     */
    public function close()
    {
        return sprintf('</%s>', $this->getEscaper()->escapeHtml($this->getName()));
    }



    /**
     * Affiche le tag complet, avec du contenu!!
     *
     * @param string $content Contenu HTML
     *
     * @return string
     */
    public function html($content = null, array $authorizedTags = ['ALL_TAGS'])
    {
        $content = (string)$content;

        if ($authorizedTags !== ['ALL_TAGS']) {
            $content = strip_tags($content, '<' . implode('><', $authorizedTags) . '>');
        }

        return $this->open() . $content . $this->close();
    }



    /**
     * Afficha un texte sans AUCUN Tag HTML
     *
     * @param string $content
     *
     * @return string
     */
    public function text($content = null)
    {
        return $this->html($content, []); // on interdit TOUS les tags
    }



    /**
     * Retourne un code HTML automatiquement échappé
     *
     * @param string $content
     *
     * @return string
     */
    public function escaped($content = null)
    {
        return $this->text($this->getEscaper()->escapeHtml($content));
    }



    /**
     * Met l'aide de vue à 0
     */
    public function reset()
    {
        $this->attributes = [];
        $this->name       = null;
    }



    /**
     * @return Escaper
     */
    public function getEscaper()
    {
        if (!$this->escaper) {
            $this->escaper = new Escaper('utf-8');
        }

        return $this->escaper;
    }
}
