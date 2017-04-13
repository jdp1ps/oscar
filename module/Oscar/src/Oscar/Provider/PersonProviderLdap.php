<?php
/**
 * @author Stéphane Bouvry<stephane.bouvry@unicaen.fr>
 * @date: 16-08-25 10:41
 * @copyright Certic (c) 2016
 */

namespace Oscar\Provider;


use Oscar\Entity\Person;
use UnicaenAuth\Entity\Ldap\People;

class PersonProviderLdap implements IPersonProvider
{
    /**
     * @var \UnicaenApp\Mapper\Ldap\People
     */
    private $ldapConnector;

    /**
     * PersonProviderLdap constructor.
     *
     * @param \UnicaenApp\Mapper\Ldap\People $ldapConnector
     */
    public function __construct(\UnicaenApp\Mapper\Ldap\People $ldapConnector)
    {
        $this->ldapConnector = $ldapConnector;
    }

    /**
     * @return \UnicaenApp\Mapper\Ldap\People
     */
    protected function getLdapConnector()
    {
        return $this->ldapConnector;
    }

    protected function extractData( $key, $array )
    {
        if( array_key_exists($key, $array) ){
            return $array[$key];
        } else {
            return null;
        }
    }

    /**
     * @param array $data
     * @return PersonProviderData
     */
    protected function getProviderData( array $data )
    {
        $out = new PersonProviderData();
        $out->setId($data['uid'])
            ->setFirstName($this->extractData('givenname', $data))
            ->setLastName($this->extractData('sn', $data))
            ->setEmail($this->extractData('mail', $data))
            ->setEmailPrivate($this->extractData('mailforwardingaddress', $data))
            ;
        return $out;
    }

    public function newPerson( PersonProviderData $providerData ){
        $person = new Person();
        $person->setCodeLdap($providerData->getId());
        $person->setCodeHarpege(self::convertCodeLdapToHarpege($providerData->getId()));

        return $person;
    }

    public function getPerson($id)
    {
        // TODO: Implement getPerson() method.
    }

    public function getPersons()
    {
        // TODO: Implement getPersons() method.
    }

    public function getName()
    {
        return "ldap";
    }

    ////////////////////////////////////////////////////////////////////////////
    /**
     * Retourne le N° Harpège en fonction du code LDAP donné.
     *
     * @param $codeLdap
     * @return mixed
     */
    public static function convertCodeLdapToHarpege( $codeLdap ){
        return preg_replace("/p0*([0-9]*)/", "$1", $codeLdap);
    }

    /**
     * Retourne le N°LDAP en fonction du code Harpège donné.
     *
     * @param $codeLdap
     * @return mixed
     */
    public static function convertCodeHarpegeToLdap( $codeHarpege ){
        return 'p' . str_pad("".intval($codeHarpege), 8, '0', STR_PAD_LEFT);
    }
}