<?php
/**
 * Created by PhpStorm.
 * User: gauthierb
 * Date: 21/07/15
 * Time: 13:35
 */

namespace UnicaenAppTest\Message;


use PHPUnit_Framework_TestCase;
use UnicaenApp\Message\Message;
use UnicaenAppTest\Message\Specification\ByRoleSpecificationTestAsset;

class MessageTest extends PHPUnit_Framework_TestCase
{
    /**
     * @expectedException \UnicaenApp\Message\Exception\ConfigException
     * @expectedExceptionMessage L'identifiant d'une message doit être
     */
    public function testCannotConstructWithInvalidId()
    {
        new Message(12, ["Texte 1" => true]);
    }

    /**
     * @expectedException \UnicaenApp\Message\Exception\ConfigException
     * @expectedExceptionMessage Aucune donnée spécifiée
     */
    public function testCannotConstructWithEmptyData()
    {
        new Message('MESSAGE_ID', []);
    }

    /**
     * @expectedException \UnicaenApp\Message\Exception\ConfigException
     * @expectedExceptionMessage Le texte d'un message doit être
     */
    public function testCannotConstructWithNonValidText()
    {
        new Message('MESSAGE_ID', [12 => true]);
    }

    /**
     * @dataProvider getDatasetOfInvalidSpecification
     * @expectedException \UnicaenApp\Message\Exception\ConfigException
     * @expectedExceptionMessage La spécification d'un message doit être
     * @param array $data
     */
    public function testCannotConstructWithInvalidSpecification(array $data)
    {
        new Message('MESSAGE_ID', $data);
    }

    public function testCanCreateInstancesFromConfig()
    {
        $config = [
            [
                'id' => 'ID_1',
                'data' => [
                    "Texte 1" => true,
                ]
            ],
            [
                'id' => 'ID_2',
                'data' => [
                    "Texte 2" => true,
                ]
            ],
        ];

        $messages = Message::createInstancesFromConfig($config);

        $this->assertContainsOnlyInstancesOf('UnicaenApp\Message\Message', $messages);
        $this->assertCount(2, $messages);
    }

    /**
     * @@expectedException \UnicaenApp\Exception\LogicException
     */
    public function testGettingTextBeforeApplyingContextThrowsException()
    {
        $message = new Message("MESSAGE_ID", ["Texte 1" => true]);
        $message->getTextForContext();
    }

    /**
     * @dataProvider getDatasetForReturningFirstSatisfiableSpecificationFound
     * @param array $data
     * @param mixed $context
     * @param string $expectedText
     */
    public function testReturningFirstSatisfiableSpecificationFound(array $data, $context, $expectedText)
    {
        $message = new Message("MESSAGE_ID", $data);
        $message->applyContext($context);
        $this->assertEquals($expectedText, $message->getTextForContext($context));
    }

    /**
     * @dataProvider getDatasetForCrashingIfNoSatisfiableSpecificationFound
     * @expectedException \UnicaenApp\Message\Exception\MessageTextNotFoundException
     * @expectedExceptionMessage Aucun texte trouvé pour le message
     * @param $data
     */
    public function testCrashingIfNoSatisfiableSpecificationFound(array $data)
    {
        $message = new Message("MESSAGE_ID", $data);
        $message->applyContext(null);
        $message->getTextForContext();
    }

    /**
     * @expectedException \UnicaenApp\Message\Exception\ConfigException
     * @expectedExceptionMessage doit retourner un booléen
     */
    public function testCrashingWhenSpecificationDoesNotReturnABoolean()
    {
        $data = [
            "Texte 1" => function($context) { return 1; },
        ];

        $message = new Message("MESSAGE_ID", $data);
        $message->applyContext(null);
        $message->getTextForContext();
    }

    public function testGettingDataSentBackBySpecification()
    {
        $specification = function($context, &$sentBackData) {
            if ($context <= 2) {
                $sentBackData = [
                    "age" => (int) ($context * 12.0),
                    "unite" => "mois",
                    "statut" => "un bébé"
                ];
            }
            elseif ($context > 2 && $context < 18) {
                $sentBackData = [
                    "age" => $context,
                    "unite" => "ans",
                    "statut" => "mineur"
                ];
            }
            else {
                $sentBackData = [
                    "age" => $context,
                    "unite" => "ans",
                    "statut" => "majeur"
                ];
            }
            return true;
        };

        $message = new Message("MESSAGE_ID", ["Texte" => $specification]);

//        $message = new Message("MESSAGE_ID", ["Vous avez {age} {unite} donc vous êtes {statut}." => $specification]);
//
//        $message->applyContext(1.5);
//        $this->assertEquals("Vous avez 36 mois donc vous êtes un bébé.", $message->getTextForContext());
//        $message->applyContext(12);
//        $this->assertEquals("Vous avez 12 ans donc vous êtes mineur.", $message->getTextForContext());
//        $message->applyContext(21);
//        $this->assertEquals("Vous avez 21 ans donc vous êtes majeur.", $message->getTextForContext());

        $this->assertEquals([
            "age" => 18,
            "unite" => "mois",
            "statut" => "un bébé"
        ], $message->applyContext(1.5)->getSatisfiedSpecificationSentBackData());

        $this->assertEquals([
            "age" => 12,
            "unite" => "ans",
            "statut" => "mineur"
        ], $message->applyContext(12)->getSatisfiedSpecificationSentBackData());

        $this->assertEquals([
            "age" => 21,
            "unite" => "ans",
            "statut" => "majeur"
        ], $message->applyContext(21)->getSatisfiedSpecificationSentBackData());
    }





    public function getDatasetOfInvalidSpecification()
    {
        return [
            /*
            [
                [
                    text => specification,
                ],
            ],
            */
            [
                [
                    "Texte 1" => false,
                ],
            ],
            [
                [
                    "Texte 1" => 'specification',
                ],
            ],
            [
                [
                    "Texte 1" => 12,
                ],
            ],
        ];
    }

    public function getDatasetForReturningFirstSatisfiableSpecificationFound()
    {
        return [
            /*
            [
                [
                    text => specification,
                ],
                context,
                expected text,
            ],
            */
            [
                [
                    "Texte 1" => function($context) { return false; },
                    "Texte 2" => function($context) { return false; },
                    "Texte 3" => true,
                ],
                null,
                "Texte 3",
            ],
            [
                [
                    "Texte 1" => function($context) { return false; },
                    "Texte 2" => true,
                    "Texte 3" => function($context) { return true; },
                ],
                null,
                "Texte 2",
            ],
            [
                [
                    "Texte 1" => function($context) { return true; },
                    "Texte 2" => function($context) { return true; },
                    "Texte 3" => true,
                ],
                null,
                "Texte 1",
            ],
            [
                [
                    "Texte 1" => function($context) { return false; },
                    "Texte 2" => function($context) { return true; },
                    "Texte 3" => true,
                ],
                null,
                "Texte 2",
            ],
            [
                [
                    "Texte 1" => true,
                    "Texte 2" => function($context) { return true; },
                    "Texte 3" => function($context) { return true; },
                ],
                null,
                "Texte 1",
            ],
            [
                [
                    "Texte 1" => function($context) { return $context['role'] === 'guest'; },
                    "Texte 2" => function($context) { return $context['role'] === 'admin'; },
                    "Texte 3" => true,
                ],
                ['role' => 'admin'],
                "Texte 2",
            ],
            [
                [
                    "Texte 1" => new ByRoleSpecificationTestAsset('guest'),
                    "Texte 2" => new ByRoleSpecificationTestAsset('admin'),
                    "Texte 3" => true,
                ],
                ['role' => 'admin'],
                "Texte 2",
            ],
            [
                [
                    "Texte 1" => new ByRoleSpecificationTestAsset('guest'),
                    "Texte 2" => new ByRoleSpecificationTestAsset('admin'),
                    "Texte 3" => true,
                ],
                ['role' => 'user'],
                "Texte 3",
            ],
        ];
    }

    public function getDatasetForCrashingIfNoSatisfiableSpecificationFound()
    {
        return [
            [
                [
                    "Texte 1" => function($context) { return false; },
                ],
            ],
            [
                [
                    "Texte 1" => function($context) { return false; },
                    "Texte 2" => function($context) { return false; },
                ],
            ],
        ];
    }
}