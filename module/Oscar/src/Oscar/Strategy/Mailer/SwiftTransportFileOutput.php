<?php
/**
 * @author Stéphane Bouvry<stephane.bouvry@unicaen.fr>
 * @date: 18-05-24 12:26
 * @copyright Certic (c) 2018
 */

namespace Oscar\Strategy\Mailer;


use Oscar\Exception\OscarException;
use Swift_Events_EventListener;
use Swift_Mime_SimpleMessage;

class SwiftTransportFileOutput implements \Swift_Transport
{
    private $path;

    /**
     * Swift_Transport_FileOutput constructor.
     * @param $path
     */
    public function __construct($path)
    {
        $this->path = $path;
    }


    public function isStarted()
    {
        return is_writeable($this->path);
    }

    public function start()
    {
    }

    public function stop()
    {
    }

    public function ping()
    {
        return $this->isStarted();
    }

    public function send(
        Swift_Mime_SimpleMessage $message,
        &$failedRecipients = null
    ) {
        $recipient = (implode('-', array_keys($message->getTo())));
        $filename = $message->getDate()->format('Y-m-d_H:i:s') .'_' . $recipient . '.eml';

        $w = fopen(realpath($this->path).'/'.$filename, 'w');
        if( !$w ){
            throw new OscarException("Impossible d'écrire le fichier mail");
        }
        fwrite($w, $message->toString());
        fclose($w);
    }

    public function registerPlugin(Swift_Events_EventListener $plugin)
    {

    }
}