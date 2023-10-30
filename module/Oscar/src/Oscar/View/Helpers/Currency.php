<?php
/**
 * @author Stéphane Bouvry<stephane.bouvry@unicaen.fr>
 * @date: 06/10/15 11:09
 * @copyright Certic (c) 2015
 */

namespace Oscar\View\Helpers;

use Oscar\Entity\Activity;
use Oscar\Utils\FilesizeFormatter;
use Laminas\View\Helper\AbstractHtmlElement;

/**
 * Mise en forme des dates (\DateTime).
 *
 * @package Oscar\View\Helpers
 */
class Currency extends AbstractHtmlElement
{
    public static $LC_MONETARY = 'fr_FR';
    public static $FORMAT = '%i';
    public static $TEMPLATE_HTML = '<span class="currency" %s><span class="value">%s</span><span class="currency">%s</span></span>';

    public function initLocale( $locale = 'fr_FR' )
    {
        setlocale(LC_MONETARY, $locale);
    }

    function __construct()
    {
        $this->initLocale();
    }


    /**
     * @param integer $size
     * @return string
     */
    public function format( $amount, $devise='€', $pattern='%i' )
    {
       return number_format($amount, 2, ',', ' ');
    }

    /**
     * @param integer $size
     * @return string
     */
    public function __invoke($data)
    {
        $currency = '€';
        $euroEqui = 1;
        $euro = '';

        if( $data instanceof Activity ){
            $value = $data->getAmount();

            if( $data->getCurrency() ){
                $currency = $data->getCurrency()->getSymbol();
                $euroEqui = $data->getCurrency()->getRate();
            }
            if( $euroEqui !== 1 ){
                $euro = 'title="'.($this->format($data->getAmount()*$euroEqui)).' €"';
            }
        }
        else {
            $value = floatval($data);
        }

        return sprintf(self::$TEMPLATE_HTML, $euro, $this->format($value), $currency);
    }
}
