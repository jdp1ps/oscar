<?php
/**
 * @author Stéphane Bouvry<stephane.bouvry@unicaen.fr>
 * @date: 18-01-18 16:25
 * @copyright Certic (c) 2018
 */

namespace UnicaenUtils;


class ADEAccessAPI
{
    private $_baseURL;

    public function __construct( $baseURL )
    {
        $this->_baseURL = $baseURL;
    }

    protected function getBaseURL() {
        return $this->_baseURL;
    }

}