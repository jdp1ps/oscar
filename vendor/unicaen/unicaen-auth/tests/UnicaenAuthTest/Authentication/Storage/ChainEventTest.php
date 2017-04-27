<?php
namespace UnicaenAuthTest\Authentication\Storage;

use PDOException;
use PHPUnit_Framework_TestCase;
use UnicaenAuth\Authentication\Storage\Db;
use Zend\ServiceManager\Exception\ServiceNotFoundException;
use ZfcUser\Entity\User;
use UnicaenAuth\Authentication\Storage\ChainEvent;

/**
 * Description of ChainEventTest
 *
 * @author Bertrand GAUTHIER <bertrand.gauthier at unicaen.fr>
 */
class ChainEventTest extends PHPUnit_Framework_TestCase
{
    protected $event;

    /**
     * Sets up the fixture, for example, open a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $this->event = new ChainEvent();
    }

    public function testContentsIsEmptyAtConstruction()
    {
        $this->assertEquals([], $this->event->getContents());
    }

    public function testCanAddContents()
    {
        $this->event->addContents('key 1', 'content 1');
        $this->event->addContents('key 2', 'content 2');
        $expected = [
            'key 1' => 'content 1',
            'key 2' => 'content 2',
        ];
        $this->assertEquals($expected, $this->event->getContents());
    }

    public function testCanClearContents()
    {
        $this->event->addContents('key 1', 'content 1.1');
        $this->event->clearContents();
        $this->assertEquals([], $this->event->getContents());
    }
}