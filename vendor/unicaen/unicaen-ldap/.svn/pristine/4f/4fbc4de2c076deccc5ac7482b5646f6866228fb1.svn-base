<?php
namespace UnicaenLdap\Filter;

/**
 * Filtres pour les structures
 *
 * @author Laurent Lécluse <laurent.lecluse at unicaen.fr>
 */
class Structure extends Filter
{

    public static function pedagogique()
    {
        return self::orFilter(
            self::equals('supannTypeEntite', '{SUPANN}F100'), // Diplômes
            self::equals('supannTypeEntite', '{SUPANN}F200'), // ? ?
            self::equals('supannTypeEntite', '{SUPANN}F300')  // Unités
        );
    }

    /**
     * Code ou liste de codes Harpège
     *
     * @param string|array $codes
     */
    public static function codeHarpege( $codes )
    {
        if (is_string($codes)){
            return self::equals('supannCodeEntite', 'HS_'.$codes);
        }else{
            $filters = array();
            foreach( $codes as $code ){
                $filters[] = self::codeHarpege($code);
            }
            return call_user_func_array(__CLASS__.'::orFilter', $filters);
        }
    }

}