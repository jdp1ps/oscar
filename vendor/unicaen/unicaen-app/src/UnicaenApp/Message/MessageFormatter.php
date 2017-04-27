<?php
/**
 * Created by PhpStorm.
 * User: gauthierb
 * Date: 22/07/15
 * Time: 13:40
 */

namespace UnicaenApp\Message;

use DateTime;
use UnicaenApp\Util;


class MessageFormatter
{
    const DATETIME_FORMAT = 'd/m/Y à H:i';

    static public function format(Message $message, array $parameters = [])
    {
        $messageText = $message->getTextForContext();

        $mergedParameters = array_merge(
            (array) $message->getSatisfiedSpecificationSentBackData(),
            $parameters);

        return Util::tokenReplacedString($messageText, self::normalizedParameters($mergedParameters));
    }

    static protected function normalizedParameters(array $parameters = [])
    {
        $normalizedParameters = $parameters;

        foreach ($parameters as $name => $value) {
            if ($value instanceof DateTime) {
                $normalizedParameters[$name] = $value->format(self::DATETIME_FORMAT);
            }
        }

        return $normalizedParameters;
    }
}