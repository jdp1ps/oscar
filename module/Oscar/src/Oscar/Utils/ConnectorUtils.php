<?php
/**
 * @author Stéphane Bouvry<stephane.bouvry@unicaen.fr>
 * @date: 16-09-30 11:21
 * @copyright Certic (c) 2016
 */

namespace Oscar\Utils;


class ConnectorUtils
{
    const LDAP_ID_FORMAT = 'p%08d';

    public static function getLdapIdFromHarpegeId( $hargepeId ){
        return sprintf(self::LDAP_ID_FORMAT, $hargepeId);
    }

    public static function getHarpegeIdFromLdapId( $ldapId ){
        return preg_replace('/^p0*/', '', $ldapId);
    }
}