<?php
/**
 * Created by PhpStorm.
 * User: gauthierb
 * Date: 23/07/15
 * Time: 17:26
 */

namespace UnicaenAppTest\Message;


use UnicaenApp\Message\MessageConfig;

class MessageConfigTest extends \PHPUnit_Framework_TestCase
{
    private $normalizer;

    public function testThatWeCannotCallConstructor()
    {
        $reflector = new \ReflectionMethod($class = 'UnicaenApp\Message\MessageConfig', '__construct');
        $this->assertFalse($reflector->isPublic(), "$class::__construct ne doit pas être public.");
    }

    public function testCanCreateInstance()
    {
        $this->givenNormalizerWillProvideConfig([]);

        $config = MessageConfig::create($this->normalizer);

        $this->assertInstanceOf('UnicaenApp\Message\MessageConfig', $config);
    }

    public function testCanRetrieveMessagesConfig()
    {
        $this->givenNormalizerWillProvideConfig(['messages' => ['peu' => 'importe']]);

        $config = MessageConfig::create($this->normalizer);

        $this->assertEquals(['peu' => 'importe'], $config->getMessagesConfig());
    }

    private function givenNormalizerWillProvideConfig($config)
    {
        $this->normalizer = $this->getMockBuilder('UnicaenApp\Message\MessageConfigNormalizer')
            ->disableOriginalConstructor()
            ->setMethods(['getNormalizedConfig'])
            ->getMock();
        $this->normalizer
            ->expects($this->once())
            ->method('getNormalizedConfig')
            ->willReturn($config);

        return $this;
    }
}
