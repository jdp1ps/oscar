<?php
/**
 * @author Stéphane Bouvry<stephane.bouvry@unicaen.fr>
 * @date: 05/11/15 14:53
 * @copyright Certic (c) 2015
 */

namespace Oscar\Entity;


trait TraitHasPerson
{
    public function addPerson( Person $person )
    {
        $this->persons->add($person);
        return $this;
    }
}