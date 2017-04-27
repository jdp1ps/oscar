<?php
namespace UnicaenAuthTest\Service;

use PHPUnit_Framework_TestCase;
use UnicaenApp\Entity\Ldap\People as LdapPeopleEntity;
use UnicaenAppTest\Entity\Ldap\TestAsset\People as LdapPeopleTestAsset;
use UnicaenAuth\Service\User;
use ZfcUser\Authentication\Adapter\AdapterChainEvent;

/**
 * Description of UserTest
 *
 * @author Bertrand GAUTHIER <bertrand.gauthier at unicaen.fr>
 */
class UserTest extends PHPUnit_Framework_TestCase
{
    protected $service;
    protected $authModuleOptions;
    protected $zfcMapper;
    protected $mapper;
    protected $event;

    protected function setUp()
    {
        $this->authModuleOptions = $authModuleOptions = new \UnicaenAuth\Options\ModuleOptions([
            'save_ldap_user_in_database'   => true,
//            'usurpation_allowed_usernames' => array('usurpateur'),
        ]);

        $this->zfcMapper = $zfcMapper = $this->getMock('ZfcUser\Mapper\User', ['findByUsername', 'insert', 'update']);

        $this->mapper = $mapper = $this->getMock('UnicaenApp\Mapper\Ldap\People', ['findOneByUsername']);

        $serviceManager = $this->getMock('Zend\ServiceManager\ServiceManager', ['get']);
        $serviceManager->expects($this->any())
                       ->method('get')
                       ->will($this->returnCallback(function($serviceName) use ($authModuleOptions, $zfcMapper, $mapper) {
                           if ('zfcuser_module_options' === $serviceName) {
                               return new \ZfcUser\Options\ModuleOptions();
                           }
                           if ('unicaen-auth_module_options' === $serviceName) {
                               return $authModuleOptions;
                           }
                           if ('ldap_people_mapper' === $serviceName) {
                               return $mapper;
                           }
                           if ('zfcuser_user_mapper' === $serviceName) {
                               return $zfcMapper;
                           }
                           return null;
                       }));

        $this->event = new AdapterChainEvent();
        $this->event->setIdentity('username');

        $this->service = new User();
        $this->service->setServiceManager($serviceManager);
    }

    public function testCanSetLdapPeopleMapper()
    {
        $mapper = new \UnicaenApp\Mapper\Ldap\People();
        $this->service->setLdapPeopleMapper($mapper);
        $this->assertSame($mapper, $this->service->getLdapPeopleMapper());
    }

    public function testCanRetrieveDefaultLdapPeopleMapperFromServiceManager()
    {
        $this->assertInstanceOf('UnicaenApp\Mapper\Ldap\People', $this->service->getLdapPeopleMapper());
    }

    public function testCanRetrieveModuleOptionsFromServiceManager()
    {
        $this->assertInstanceOf('UnicaenAuth\Options\ModuleOptions', $this->service->getOptions());
    }

    public function testCanRetrieveZfcModuleOptionsFromServiceManager()
    {
        $this->assertInstanceOf('ZfcUser\Options\ModuleOptions', $this->service->getZfcUserOptions());
    }

    public function testEntryPointReturnsFalseIfOptionFlagIsFalse()
    {
        $this->service->getOptions()->setSaveLdapUserInDatabase(false);
        $this->assertFalse($this->service->userAuthenticated($this->event));
    }

    public function testEntryPointReturnsFalseIfNoIdentitySpecifiedInEvent()
    {
        $this->event->setIdentity(null);
        $this->assertFalse($this->service->userAuthenticated($this->event));
    }

    public function testEntryPointReturnsTrueIfIntegerIdentitySpecifiedInEvent()
    {
        $this->event->setIdentity(12);
        $this->assertTrue($this->service->userAuthenticated($this->event));
    }

    /**
     * @expectedException \UnicaenApp\Exception
     */
    public function testEntryPointThrowsExceptionIfUnexpectedNonEmptyIdentitySpecifiedInEvent()
    {
        $this->event->setIdentity(['content']);
        $this->service->userAuthenticated($this->event);
    }

    public function testEntryPointReturnsFalseIfUsernameNotFoundInLdap()
    {
        $this->mapper->expects($this->once())
                     ->method('findOneByUsername')
                     ->will($this->returnValue(null));

        $this->assertFalse($this->service->userAuthenticated($this->event));
    }

    /**
     * @expectedException \UnicaenApp\Exception
     */
    public function testEntryPointThrowsExceptionIfPDOExceptionThrownDuringFetch()
    {
        $entity = new LdapPeopleEntity(LdapPeopleTestAsset::$data1);
        $this->mapper->expects($this->once())
                     ->method('findOneByUsername')
                     ->will($this->returnValue($entity));

        $this->zfcMapper->expects($this->once())
                        ->method('findByUsername')
                        ->will($this->throwException(new \PDOException()));

        $this->service->userAuthenticated($this->event);
    }

    public function testEntryPointPerformsInsertAndReturnsTrueWhenUserDoesNotExistInDb()
    {
        $entity = new LdapPeopleEntity(LdapPeopleTestAsset::$data1);
        $this->mapper->expects($this->once())
                     ->method('findOneByUsername')
                     ->will($this->returnValue($entity));

        $this->zfcMapper->expects($this->once())
                        ->method('findByUsername')
                        ->will($this->returnValue(null));

        $this->zfcMapper->expects($this->once())
                        ->method('insert')
                        ->with($this->isInstanceOf('ZfcUser\Entity\User'));

        $this->assertTrue($this->service->userAuthenticated($this->event));
    }

    public function testEntryPointPerformsUpdateAndReturnsTrueWhenUserExistsFoundInDb()
    {
        $people = new LdapPeopleEntity(LdapPeopleTestAsset::$data1);
        $this->mapper->expects($this->once())
                     ->method('findOneByUsername')
                     ->will($this->returnValue($people));

        $user = new \ZfcUser\Entity\User();
        $this->zfcMapper->expects($this->once())
                        ->method('findByUsername')
                        ->will($this->returnValue($user));

        $this->zfcMapper->expects($this->once())
                        ->method('update')
                        ->with($this->isInstanceOf('ZfcUser\Entity\User'));

        $this->assertTrue($this->service->userAuthenticated($this->event));
    }

    /**
     * @expectedException \UnicaenApp\Exception
     */
    public function testEntryPointThrowsExceptionIfPDOExceptionThrownDuringSave()
    {
        $entity = new LdapPeopleEntity(LdapPeopleTestAsset::$data1);
        $this->mapper->expects($this->once())
                     ->method('findOneByUsername')
                     ->will($this->returnValue($entity));

        $user = new \ZfcUser\Entity\User();
        $this->zfcMapper->expects($this->once())
                        ->method('findByUsername')
                        ->will($this->returnValue($user));

        $this->zfcMapper->expects($this->once())
                        ->method('update')
                        ->will($this->throwException(new \PDOException()));

        $this->service->userAuthenticated($this->event);
    }
}