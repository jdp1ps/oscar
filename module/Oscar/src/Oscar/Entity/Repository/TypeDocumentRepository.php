<?php
/**
 * @author Stéphane Bouvry<stephane.bouvry@unicaen.fr>
 * @date: 29/05/15 12:01
 * @copyright Certic (c) 2015
 */

namespace Oscar\Entity\Repository;

use Doctrine\ORM\EntityRepository;

class TypeDocumentRepository extends EntityRepository
{
    /**
     * Retourne les types de document sous la forme ID => LABEL
     * @return array
     */
    public function getTypesArrayFlat() :array {
        $types = $this->getTypes();
        $out = [];
        foreach ($types as $type) {
            $out[$type->getId()] = $type->getLabel();
        }
        return $out;
    }

    /**
     * @return array
     */
    public function getTypes() :array {
        return $this->findBy([], ['label' => 'ASC']);
    }

}
