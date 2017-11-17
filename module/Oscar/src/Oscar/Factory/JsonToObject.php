<?php
/**
 * @author Stéphane Bouvry<stephane.bouvry@unicaen.fr>
 * @date: 17-11-16 14:37
 * @copyright Certic (c) 2017
 */

namespace Oscar\Factory;


use Oscar\Exception\OscarException;

abstract class JsonToObject
{

    private $requiredFieldsName = [];

    public function __construct(array $requiredFields = [])
    {
        $this->requiredFieldsName = $requiredFields;
    }

    /**
     * @param $fieldName
     * @return bool
     */
    protected function isRequired($fieldName): bool
    {
        return in_array($fieldName, $this->requiredFieldsName);
    }

    /**
     * @param $object L'objet contenant les données
     * @param $fieldName Le nom de la propriété
     * @param null $defaultValue La valeur par défaut
     * @return La valeur trouvée
     * @throws OscarException
     */
    protected function getFieldValue(
        $object,
        string $fieldName,
        $defaultValue = null
    ) {
        if ($this->isRequired($fieldName) && !property_exists($object,
                $fieldName)) {
            throw new OscarException(sprintf("La clef '%s' est manquante dans la source",
                $fieldName));
        }

        return property_exists($object,
            $fieldName) ? $object->$fieldName : $defaultValue;
    }

    abstract function hydrateWithDatas(
        $object,
        $jsonData,
        $connectorName = null
    );
}