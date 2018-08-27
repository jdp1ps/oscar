<?php
/**
 * @author Stéphane Bouvry<stephane.bouvry@unicaen.fr>
 * @date: 06/10/15 11:09
 * @copyright Certic (c) 2015
 */

namespace Oscar\View\Helpers;

use Zend\View\Helper\AbstractHtmlElement;

/**
 * Mise en forme des dates (\DateTime).
 *
 * @package Oscar\View\Helpers
 */
class Moment extends AbstractHtmlElement
{
    const DEFAULT_FORMAT_DATE = 'l d F Y';
    const DEFAULT_FORMAT_TIME = 'H:m';
    const DEFAULT_FORMAT_DATETIME = 'l d F Y [à] H:m:s';
    const DEFAULT_LOCALE = 'fr_FR';
    /** @var  \Moment\Moment */
    private $moment;
    private $format = self::DEFAULT_FORMAT_DATETIME;

    /**
     * @param string|\DateTime $date
     * @param string|null $format
     * @param string|null $locale
     * @return Moment
     */
    public function format($date=null, $format=null, $locale=null)
    {
        $this->moment = null;

        if ($date instanceof \DateTime) {
            $date = $date->format('c');
        } elseif ($date === null) {
            return $this;
        }
        
        if ($format === null) {
            $format = self::DEFAULT_FORMAT_DATE;
        }

        if ($locale === null) {
            $locale = self::DEFAULT_LOCALE;
        }

        \Moment\Moment::setLocale($locale);
        $this->format = $format;

        $this->moment = new \Moment\Moment($date);
        return $this;
    }

    public function formatMonth( $monthString ){

    }

    /**
     * Rendu sous forme de chaîne.
     *
     * @return string
     */
    public function __toString()
    {
        if ($this->moment === null) {
            return 'jamais';
        }
        return (string) $this->moment->format($this->format);
    }

    /**
     * Retourne le temps écoulé depuis la date $from.
     *
     * @param string|\DateTime $from
     * @return string
     */
    public function since($from='now')
    {
        if ($this->moment === null) {
            return '';
        }
        if ($from instanceof \DateTime) {
            $from = $from->format('c');
        }
        $from = $this->moment->from($from);
        return $from->getRelative();
    }

    /**
     * Retourne le texte complet sous la forme DATE, DEPUIS NOW
     * @return string
     */
    public function full()
    {
        return $this->__toString(). ($this->moment !== null ? ', ' . $this->since() : '');
    }

    /**
     * @param \DateTime|string $date
     * @param string $format
     * @return $this|Moment
     */
    public function __invoke($date, $format=self::DEFAULT_FORMAT_DATE)
    {
        return $this->format($date, $format);
    }
}
