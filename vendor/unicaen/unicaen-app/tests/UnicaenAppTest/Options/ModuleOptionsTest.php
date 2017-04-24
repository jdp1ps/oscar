<?php
namespace UnicaenAppTest\Options;

use PHPUnit_Framework_TestCase;
use UnicaenApp\Options\ModuleOptions;

/**
 * Description of ModuleOptionsTest
 *
 * @author Bertrand GAUTHIER <bertrand.gauthier at unicaen.fr>
 */
class ModuleOptionsTest extends PHPUnit_Framework_TestCase
{
    protected $moduleOptions;
    
    protected $appInfos = array(
        'nom'     => "MétéoSI",
        'desc'    => "Interruptions et perturbations de services numériques",
        'version' => "0.0.1",
        'date'    => "26/11/2012",
        'contact' => array(
            'mail' => array(
                "contact.application@unicaen.fr", 
                "support.application@unicaen.fr"
            ),
            'tel'  => "01 02 03 04 05",
        ),
        'mentionsLegales'        => "http://www.unicaen.fr/outils-portail-institutionnel/mentions-legales/",
        'informatiqueEtLibertes' => "http://www.unicaen.fr/outils-portail-institutionnel/informatique-et-libertes/",
    );
    
    protected $ldap = array(
        'connection' => array(
            'default' => array(
                'params' => array(
                    'host'                => 'ldap.unicaen.fr',
                    'username'            => "uid=xxxxxxxx,ou=system,dc=unicaen,dc=fr",
                    'password'            => "xxxxxxxxxx",
                    'baseDn'              => "ou=people,dc=unicaen,dc=fr",
                    'bindRequiresDn'      => true,
                    'accountFilterFormat' => "(&(objectClass=posixAccount)(supannAliasLogin=%s))",
                )
            )
        ),
    );
    
    protected $db = array(
        'connection' => array(
            'orm_default' => array(
                'driverClass' => 'Doctrine\DBAL\Driver\PDOMySql\Driver',
                'params' => array(
                    'host'     => 'localhost',
                    'port'     => '3306',
                    'user'     => 'root',
                    'password' => 'root',
                    'dbname'   => 'squelette',
                )
            ),
        ),
    );
    
    protected $mail = array(
        'transport_options' => array(
            'host' => 'smtp.unicaen.fr',
            'port' => 25,
        ),
        'redirect_to' => array('dsi.applications@unicaen.fr'),
        'do_not_send' => false,
    );
                    
    /**
     * Sets up the fixture, for example, open a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $this->moduleOptions = new ModuleOptions();
    }
    
    /**
     * 
     */
    public function testConstructByDefaultPopulatesOptionsWithDefaultValues()
    {
        // app infos
        $this->assertInternalType('array', $array = $this->moduleOptions->getAppInfos());
        $this->assertArrayHasKey('nom', $array);
        $this->assertArrayHasKey('version', $array);
        $this->assertArrayHasKey('date', $array);
        $this->assertArrayHasKey('contact', $array);
        $this->assertArrayHasKey('mentionsLegales', $array);
        $this->assertArrayHasKey('informatiqueEtLibertes', $array);
        $this->assertInternalType('string', $array['nom']);
        $this->assertInternalType('string', $array['version']);
        $this->assertInternalType('string', $array['date']);
        $this->assertInternalType('array', $array['contact']);
        $this->assertInternalType('string', $array['mentionsLegales']);
        $this->assertInternalType('string', $array['informatiqueEtLibertes']);
        $this->assertNotEmpty($array['nom']);
        $this->assertNotEmpty($array['version']);
        $this->assertNotEmpty($array['date']);
        $this->assertNotEmpty($array['contact']);
        $this->assertNotEmpty($array['mentionsLegales']);
        $this->assertNotEmpty($array['informatiqueEtLibertes']);
        
        // ldap
        $this->assertInternalType('array', $array = $this->moduleOptions->getLdap());
        $this->assertEmpty($array);
        
        // db
        $this->assertInternalType('array', $array = $this->moduleOptions->getDb());
        $this->assertEmpty($array);
        
        // mail
        $this->assertInternalType('array', $array = $this->moduleOptions->getMail());
        $this->assertEmpty($array);
    }
    
    public function testCanSetAppInfosOptionViaConstructor()
    {
        $moduleOptions = new ModuleOptions(array('app_infos' => $this->appInfos));
        $this->assertEquals($this->appInfos, $moduleOptions->getAppInfos());
    }
    
    public function testCanSetAppInfosOption()
    {
        $this->moduleOptions->setAppInfos($this->appInfos);
        $this->assertEquals($this->appInfos, $this->moduleOptions->getAppInfos());
    }
    
    public function testCanSetLdapOptionViaConstructor()
    {
        $moduleOptions = new ModuleOptions(array('ldap' => $this->ldap));
        $this->assertEquals($this->ldap, $moduleOptions->getLdap());
    }
    
    public function testCanSetLdapOption()
    {
        $this->moduleOptions->setLdap($this->ldap);
        $this->assertEquals($this->ldap, $this->moduleOptions->getLdap());
    }
    
    public function testCanSetDbOptionViaConstructor()
    {
        $moduleOptions = new ModuleOptions(array('db' => $this->db));
        $this->assertEquals($this->db, $moduleOptions->getDb());
    }
    
    public function testCanSetDbOption()
    {
        $this->moduleOptions->setDb($this->db);
        $this->assertEquals($this->db, $this->moduleOptions->getDb());
    }
    
    public function testCanSetMailOptionViaConstructor()
    {
        $moduleOptions = new ModuleOptions(array('mail' => $this->mail));
        $this->assertEquals($this->mail, $moduleOptions->getMail());
    }
    
    public function testCanSetMailOption()
    {
        $this->moduleOptions->setMail($this->mail);
        $this->assertEquals($this->mail, $this->moduleOptions->getMail());
    }
    
    public function testSettingLdapOptionPerformsMergeWithExistingValues()
    {
        $newLdap = array(
            'connection' => array(
                'default' => array(
                    'params' => array(
                        'host'                => 'ldap.server.fr',
                        'username'            => "uid=yyyyyyyyy,ou=system,dc=unicaen,dc=fr",
                        'password'            => "yyyyyyyyy",
                    )
                )
            ),
        );
        $this->moduleOptions->setLdap($this->ldap)
                            ->setLdap($newLdap);
        
        $expected = array(
            'connection' => array(
                'default' => array(
                    'params' => array(
                        'host'                => 'ldap.server.fr',
                        'username'            => "uid=yyyyyyyyy,ou=system,dc=unicaen,dc=fr",
                        'password'            => "yyyyyyyyy",
                        'baseDn'              => "ou=people,dc=unicaen,dc=fr",
                        'bindRequiresDn'      => true,
                        'accountFilterFormat' => "(&(objectClass=posixAccount)(supannAliasLogin=%s))",
                    )
                )
            ),
        );
        $this->assertEquals($expected, $this->moduleOptions->getLdap());
    }
    
    public function testSettingDbOptionPerformsMergeWithExistingValues()
    {
        $newDb = array(
            'connection' => array(
                'orm_default' => array(
                    'params' => array(
                        'host'     => 'db.server.fr',
                        'user'     => 'admin',
                        'password' => 'password',
                    )
                ),
            )
        );
        $this->moduleOptions->setDb($this->db)
                            ->setDb($newDb);
        
        $expected = array(
            'connection' => array(
                'orm_default' => array(
                    'driverClass' => 'Doctrine\DBAL\Driver\PDOMySql\Driver',
                    'params' => array(
                        'host'     => 'db.server.fr',
                        'port'     => '3306',
                        'user'     => 'admin',
                        'password' => 'password',
                        'dbname'   => 'squelette',
                    )
                ),
            )
        );
        $this->assertEquals($expected, $this->moduleOptions->getDb());
    }
    
    /**
     * 
     * @return array
     */
    public function provideValidCompleteOptionValues()
    {
        $options = array(
            'app_infos' => array(
                'nom'     => "MétéoSI",
                'desc'    => "Interruptions et perturbations de services numériques",
                'version' => "0.0.1",
                'date'    => "26/11/2012",
                'contact' => array(
                    'mail' => array(
                        "contact.application@unicaen.fr", 
                        "support.application@unicaen.fr"
                    ),
                    'tel'  => "01 02 03 04 05",
                ),
                'mentionsLegales'        => "http://www.unicaen.fr/outils-portail-institutionnel/mentions-legales/",
                'informatiqueEtLibertes' => "http://www.unicaen.fr/outils-portail-institutionnel/informatique-et-libertes/",
            ),
            'ldap' => array(
                'connection' => array(
                    'default' => array(
                        'params' => array(
                            'host'                => 'ldap.unicaen.fr',
                            'username'            => "uid=xxxxxxxx,ou=system,dc=unicaen,dc=fr",
                            'password'            => "xxxxxxxxxx",
                            'baseDn'              => "ou=people,dc=unicaen,dc=fr",
                            'bindRequiresDn'      => true,
                            'accountFilterFormat' => "(&(objectClass=posixAccount)(supannAliasLogin=%s))",
                        )
                    )
                ),
            ),
            'db' => array(
                'connection' => array(
                    'orm_default' => array(
                        'driverClass' => 'Doctrine\DBAL\Driver\PDOMySql\Driver',
                        'params' => array(
                            'host'     => 'localhost',
                            'port'     => '3306',
                            'user'     => 'root',
                            'password' => 'root',
                            'dbname'   => 'squelette',
                        )
                    ),
                ),
            ),
            'mail' => array(
                'transport_options' => array(
                    'host' => 'smtp.unicaen.fr',
                    'port' => 25,
                ),
                'redirect_to' => array('dsi.applications@unicaen.fr'),
                'do_not_send' => false,
            ),
        );
        return array(
            array($options),
        );
    }
    
//    /**
//     * @dataProvider provideValidCompleteOptionValues
//     * @param array $values
//     */
//    public function testConstructWithValidCompleteOptionValues($values)
//    {
//        $values = new \UnicaenApp\Options\ModuleOptions($values);
//        // app infos
//        $this->assertInternalType('array', $array = $values->getAppInfos());
//        $this->assertArrayHasKey('nom',                     $tmp = $array);
//        $this->assertArrayHasKey('version',                 $tmp['app_infos']);
//        $this->assertArrayHasKey('date',                    $tmp['app_infos']);
//        $this->assertArrayHasKey('contact',                 $tmp['app_infos']);
//        $this->assertArrayHasKey('mentionsLegales',         $tmp['app_infos']);
//        $this->assertArrayHasKey('informatiqueEtLibertes',  $tmp['app_infos']);
//        // ldap
//        $this->assertInternalType('array', $array = $values->getLdap());
//        $this->assertArrayHasKey('connection', $tmp = $array);
//        $this->assertArrayHasKey('default',    $tmp = $tmp['connection']);
//        $this->assertArrayHasKey('params',     $tmp = $tmp['default']);
//    }
}