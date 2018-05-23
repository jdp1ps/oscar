<?php
/**
 * Created by PhpStorm.
 * User: jacksay
 * Date: 27/03/18
 * Time: 18:23
 */

namespace Oscar\Service;


use Moment\Moment;
use Oscar\Exception\OscarException;
use UnicaenApp\Service\EntityManagerAwareInterface;
use UnicaenApp\Service\EntityManagerAwareTrait;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorAwareTrait;

/**
 * Ce service centralise toutes les opérations liées à l'envoi d'informations par EMAIL.
 *
 * Class MailingService
 * @package Oscar\Service
 */
class MailingService implements ServiceLocatorAwareInterface, EntityManagerAwareInterface
{
    use ServiceLocatorAwareTrait, EntityManagerAwareTrait;

    /**
     * Retourne la configuration Oscar pour l'envoi des mails.
     *
     * @return ConfigurationParser
     */
    protected function getConfig(){
        static $config;
        if( $config === null )
            $config = new ConfigurationParser($this->getServiceLocator()->get('Config')['oscar']['mailer']);

        return $config;
    }

    /**
     * Le mailer.
     *
     * @return \Swift_Mailer
     */
    protected function getMailer(){
        static $mailer;
        if( $mailer === null )
            $mailer = new \Swift_Mailer($this->getTransport());
        return $mailer;
    }

    /**
     * Sytème de distribution des mails.
     *
     * @return \Swift_SendmailTransport|\Swift_SmtpTransport
     * @throws OscarException
     */
    protected function getTransport(){
        static $transport;
        if( $transport === null ){

            switch( $this->getConfig()->getConfiguration('transport.type') ){
                case 'smtp':
                    $transport = (new \Swift_SmtpTransport(
                        $this->getConfig()->getConfiguration('transport.host'),
                        $this->getConfig()->getConfiguration('transport.port'),
                        $this->getConfig()->getConfiguration('transport.security')))
                        ->setUsername($this->getConfig()->getConfiguration('transport.username'))
                        ->setPassword($this->getConfig()->getConfiguration('transport.password'))
                    ;

                    break;

                case 'sendmail':
                    $transport = new \Swift_SendmailTransport($this->getConfig()->getConfiguration('transport.cmd'));
                    break;

                default:
                    throw new OscarException("Le système de mailing n'est pas configuré.");
            }
        }
        return $transport;
    }

    /**
     * Création d'un mail Oscarifié.
     *
     * @param string $subject
     * @param array $content
     * @return \Swift_Message
     */
    public function newMessage( $subject = "", $content = []){
        $msg = new \Swift_Message();
        $msg->setFrom($this->getConfig()->getConfiguration('from'))
            ->setSubject($this->getConfig()->getConfiguration('subjectPrefix') . $subject)
        ;
        if( $content && is_array($content) && array_key_exists('body', $content) ){
            $body = $content['body'];
            $title = array_key_exists('title', $content) ? $content['title'] : $subject;
            $msg->setBody($this->getBodyTemplate($body, $title), 'text/html');
        }
        return $msg;
    }

    /**
     * Construction d'un contenu de message à partir du template définit dans la configuration.
     *
     * @param $body
     * @param string $title
     * @return string
     */
    public function getBodyTemplate( $body, $title=""){
        ob_start();
        include $this->getConfig()->getConfiguration('template');
        return ob_get_clean();
    }

    /**
     * Envoi du message.
     *
     * @param \Swift_Message $msg
     * @param boolean $force Force l'envoi si le mail du destinataire est un mail administrateur.
     */
    public function send( \Swift_Message $msg ){

        if( $this->getConfig()->getConfiguration('send') ) {
            $this->getMailer()->send($msg);
        }
        else {
            $administrators = $this->getConfig()->getConfiguration('administrators');
            $admins = [];
            foreach ($msg->getTo() as $mail=>$text) {
                if( array_key_exists($mail, $administrators) ){
                    $admins[$mail] = $text;
                }
            }
            if( count($admins) ){
                $msg->setTo($admins)->setCc([]);
                $this->getMailer()->send($msg);
            } else {
                $this->getServiceLocator()->get('Logger')->debug('MAIL NON ENVOYé (Envoi désactivé)');
                $this->getServiceLocator()->get('Logger')->debug($msg->toString());
            }

        }
    }
}