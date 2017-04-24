<?php

namespace UnicaenAppTest\Controller\Plugin;

/**
 * Description of LdapGroupServiceFactoryTest
 *
 * @author Bertrand GAUTHIER <bertrand.gauthier at unicaen.fr>
 */
class LdapStructureServiceFactoryTest extends BaseLdapServiceFactoryTest
{
    protected $ldapServiceName  = 'ldap_structure_service';
    protected $ldapServiceClass = 'UnicaenApp\Service\Ldap\Structure';
    protected $factoryClass     = 'UnicaenApp\Controller\Plugin\LdapStructureServiceFactory';
    protected $serviceClass     = 'UnicaenApp\Controller\Plugin\LdapStructureService';
}