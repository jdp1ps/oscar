<?php
/**
 * @author Stéphane Bouvry<stephane.bouvry@unicaen.fr>
 * @date: 17-12-06 10:04
 * @copyright Certic (c) 2017
 */

namespace Oscar\Import\Data;

/**
 * Extraction d'un objet DateTime à partir de la donnée.
 *
 * @package Oscar\Import\Data
 */
class DataExtractorDate extends AbstractDataExtractor
{
    /**
     * @param $data
     * @param null $params
     * @return \DateTime
     */
    function extract($data, $params = null)
    {
        try {
            return new \DateTime($data);
        }
        catch (\Exception $e){
            $this->setError(sprintf("Impossible de convertir '%s' en date : %s", $data, $e->getMessage()));
            return null;
        }
    }
}