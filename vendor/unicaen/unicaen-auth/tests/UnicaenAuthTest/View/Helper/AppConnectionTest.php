<?php
namespace UnicaenAuthTest\View\Helper;

use UnicaenAuthTest\View\Helper\AbstractTest;
use UnicaenAuth\View\Helper\AppConnection;

/**
 * Description of AppConnectionTest
 *
 * @property AppConnection $helper Description
 * @author Bertrand GAUTHIER <bertrand.gauthier at unicaen.fr>
 */
class AppConnectionTest extends AbstractTest
{
    protected $helperClass = 'UnicaenAuth\View\Helper\AppConnection';
    protected $renderer;
    protected $userCurrentHelper;
    protected $userConnectionHelper;

    /**
     * Sets up the fixture, for example, open a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        parent::setUp();

        $this->renderer = $this->getMock('Zend\View\Renderer\PhpRenderer', ['plugin']);

        $this->helper->setView($this->renderer);
    }

    protected function getUserCurrentHelper()
    {
        if (null === $this->userCurrentHelper) {
            $this->userCurrentHelper = $this->getMock('UnicaenAuth\View\Helper\UserCurrent', ['__toString']);
            $this->userCurrentHelper
                    ->expects($this->any())
                    ->method('__toString')
                    ->will($this->returnValue('UserCurrent markup'));
        }
        return $this->userCurrentHelper;
    }

    protected function getUserConnectionHelper()
    {
        if (null === $this->userConnectionHelper) {
            $this->userConnectionHelper = $this->getMock('UnicaenAuth\View\Helper\UserConnection', ['__toString']);
            $this->userConnectionHelper
                    ->expects($this->any())
                    ->method('__toString')
                    ->will($this->returnValue('UserConnection markup'));
        }
        return $this->userConnectionHelper;
    }

    public function getHelpersAndExpectedResult()
    {
        return [
            [
                null,
                null,
                ''],
            [
                null,
                $this->getUserConnectionHelper(),
                'UserConnection markup'],
            [
                $this->getUserCurrentHelper(),
                null,
                'UserCurrent markup'],
            [
                $this->getUserCurrentHelper(),
                $this->getUserConnectionHelper(),
                "UserCurrent markup | UserConnection markup"],
        ];
    }

    /**
     * @dataProvider getHelpersAndExpectedResult
     */
    public function testToStringMethodReturnsCorrectMarkup($userCurrentHelper, $userConnectionHelper, $expectedMarkup)
    {
        $this->renderer
                ->expects($this->any())
                ->method('plugin')
                ->will($this->returnCallback(function ($plugin) use ($userCurrentHelper, $userConnectionHelper) {
                    if ('usercurrent' === strtolower($plugin)) {
                        return $userCurrentHelper;
                    }
                    if ('userconnection' === strtolower($plugin)) {
                        return $userConnectionHelper;
                    }
                    return null;
                }));


        $this->assertEquals($expectedMarkup, (string) $this->helper);
    }
}