<?php
/**
 * @author Stéphane Bouvry<stephane.bouvry@unicaen.fr>
 * @date: 16-03-23 15:45
 * @copyright Certic (c) 2016
 */

namespace Oscar\Service;


class OscarMailerService
{
    private $mailer;

    ////////////////////////////////////////////////////////////////////////////

    /**
     * @var string préfix ajouté aux sujet
     */
    private $subjectPrefix;

    /**
     * @var
     */
    private $from;

    /**
     * @var
     */
    private $copy;



    public function __construct(\Swift_Mailer $mailer, $config = null)
    {
        $this->mailer = $mailer;
        $this->from = $config['from'];
        $this->subjectPrefix = $config['subjectPrefix'];
        $this->copy = $config['copy'];
    }

    public function send( array $to, $subject, $content)
    {
        $message = new \Swift_Message($subject, $content, 'text/html', 'utf-8');
        $message->setCc($this->copy)
            ->setTo($to)
            ->setFrom($this->from)
            ->setSubject(sprintf('%s %s', $this->subjectPrefix, $subject))
            ->setBody($content);

        $this->mailer->send($message);
    }



}