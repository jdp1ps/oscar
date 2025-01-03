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
use Oscar\Strategy\Mailer\Swift_Transport_FileOutput;
use Oscar\Strategy\Mailer\SwiftTransportFileOutput;
use Oscar\Traits\UseEntityManager;
use Oscar\Traits\UseEntityManagerTrait;
use Oscar\Traits\UseLoggerService;
use Oscar\Traits\UseLoggerServiceTrait;
use Oscar\Traits\UseOscarConfigurationService;
use Oscar\Traits\UseOscarConfigurationServiceTrait;
use Oscar\Utils\StringUtils;
use UnicaenApp\Service\EntityManagerAwareInterface;
use UnicaenApp\Service\EntityManagerAwareTrait;
use UnicaenApp\ServiceManager\ServiceLocatorAwareInterface;
use UnicaenApp\ServiceManager\ServiceLocatorAwareTrait;

/**
 * Ce service centralise toutes les opérations liées à l'envoi d'informations par EMAIL.
 *
 * Class MailingService
 * @package Oscar\Service
 */
class MailingService implements UseEntityManager, UseOscarConfigurationService, UseLoggerService
{
    use UseEntityManagerTrait, UseOscarConfigurationServiceTrait, UseLoggerServiceTrait;

    /**
     * Retourne la configuration Oscar pour l'envoi des mails.
     *
     * @return ConfigurationParser
     */
    protected function getConfig(){
        static $config;
        if( $config === null )
            $config = $this->getOscarConfigurationService()->getConfiguration('mailer');

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

            switch( $this->getOscarConfigurationService()->getConfiguration('mailer.transport.type') ){
                case 'smtp':
                    $transport = (new \Swift_SmtpTransport(
                        $this->getOscarConfigurationService()->getConfiguration('mailer.transport.host'),
                        $this->getOscarConfigurationService()->getConfiguration('mailer.transport.port'),
                        $this->getOscarConfigurationService()->getConfiguration('mailer.transport.security')))
                        ->setUsername($this->getOscarConfigurationService()->getConfiguration('mailer.transport.username'))
                        ->setPassword($this->getOscarConfigurationService()->getConfiguration('mailer.transport.password'));
                    break;

                case 'sendmail':
                    $transport = new \Swift_SendmailTransport($this->getOscarConfigurationService()->getConfiguration('mailer.transport.cmd'));
                    break;

                case 'file':
                    return new SwiftTransportFileOutput($this->getOscarConfigurationService()->getConfiguration('mailer.transport.path', '/tmp'));

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
        $msg->setFrom($this->getOscarConfigurationService()->getConfiguration('mailer.from'))
            ->setSubject($this->getOscarConfigurationService()->getConfiguration('mailer.subjectPrefix') . $subject);

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
        $conf = $this->getOscarConfigurationService()->getConfigArray();
        $appName = $conf['unicaen-app']['app_infos']['nom'];
        ob_start();
        include $this->getOscarConfigurationService()->getConfiguration('mailer.template');
        return ob_get_clean();
    }

    /**
     * Envoi du message.
     *
     * @param \Swift_Message $msg
     * @param boolean $force Force l'envoi si le mail du destinataire est un mail administrateur.
     */
    public function send( \Swift_Message $msg ){

        $send = $this->getOscarConfigurationService()->getConfiguration('mailer.send', false);
        $exceptions = $this->getOscarConfigurationService()->getConfiguration('mailer.send_false_exception', []);
        $logger = $this->getLoggerService();

        if( $send == false ){
            // On teste si le mail est dans l'exception
            if( count($exceptions) > 0 ){
                $newTo = [];
                foreach ($msg->getTo() as $mail=>$text) {
                    if( in_array($mail, $exceptions) ){
                        $newTo[$mail] = $text;
                    }
                }
                if( count($newTo) ){
                    $msg->setTo($newTo)->setCc([]);
                    $this->getMailer()->send($msg);
                    $logger->info(sprintf(' + MAIL DISTRIBUÉ : %s', StringUtils::formatMail($msg->getTo())));
                } else {
                    $logger->info(sprintf(' ~ Email pour %s non-distribué (Hors exceptions)', StringUtils::formatMail($msg->getTo())));
                }
            } else {
                $logger->info(sprintf(' ~ Email pour %s non distribué (Pas d\'exception)', StringUtils::formatMail($msg->getTo())));
            }
        } else {
            $logger->info("Envoi de mail " . implode(',', $msg->getTo()));
            $this->getMailer()->send($msg);
        }
    }
}