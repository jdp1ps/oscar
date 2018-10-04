<?php

/**
 * @author Stéphane Bouvry<stephane.bouvry@unicaen.fr>
 * @date: 22/06/15 13:44
 *
 * @copyright Certic (c) 2015
 */
namespace Oscar\Service;

use Oscar\Entity\Project;
use Oscar\Exception\OscarException;

class SessionService
{
    private $tokenKey = "OSCAR-TOKEN";

    public function createToken(){
        if( !array_key_exists($this->tokenKey, $_SESSION) ){
            $_SESSION[$this->tokenKey] = [];
        }

        $name = uniqid('token-', true);
        $salt = uniqid('', true);
        $value = uniqid('', true);

        $_SESSION[$this->tokenKey][$name] = [
            'salt' => $salt,
            'value' => $value
        ];

        return ['name' => $name, 'value' => crypt($value, $salt) ];
    }

    public function checkToken( $name, $value, $remove=true ){
        if( !array_key_exists($this->tokenKey, $_SESSION) ){
            throw new OscarException("Erreur de session");
        }

        if( !array_key_exists($name, $_SESSION[$this->tokenKey]) ){
            throw new OscarException("Jeton de sécurité expiré");
        }

        $token = $_SESSION[$this->tokenKey][$name];

        $valueSend = crypt($token['value'], $token['salt']);

        if( $value != $valueSend ){
            throw new OscarException("Jeton de sécurité invalide");
        }

        if( $remove ){
            unset($_SESSION[$this->tokenKey][$name]);
        }

        return true;
    }

}
