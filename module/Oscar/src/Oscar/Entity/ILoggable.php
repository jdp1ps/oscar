<?php
/**
 * @author Stéphane Bouvry<stephane.bouvry@unicaen.fr>
 * @date: 18/11/15 17:19
 * @copyright Certic (c) 2015
 */
namespace Oscar\Entity;

interface ILoggable
{
    /**
     * @return string
     */
    public function log();
}