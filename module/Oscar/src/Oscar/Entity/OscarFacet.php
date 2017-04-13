<?php
/**
 * @author Stéphane Bouvry<stephane.bouvry@unicaen.fr>
 * @date: 08/02/16 13:23
 * @copyright Certic (c) 2016
 */

namespace Oscar\Entity;


class OscarFacet
{

    const FACET_GENERAL         = 'Général';
    const FACET_SCIENTIFIC      = 'Scientifique';
    const FACET_ADMINISTRATIVE  = 'Administratif';
    const FACET_LEGAL           = 'Juridique';
    const FACET_FINANCIAL       = 'Financier';

    /**
     * @return array
     */
    public static function getFacets()
    {
        static $facets;
        if( $facets === null ){
            $facets = [
                self::FACET_GENERAL,
                self::FACET_SCIENTIFIC,
                self::FACET_ADMINISTRATIVE,
                self::FACET_LEGAL,
                self::FACET_FINANCIAL,
            ];
        }
        return $facets;
    }

}