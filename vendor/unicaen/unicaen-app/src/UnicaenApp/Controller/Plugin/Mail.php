<?php
namespace UnicaenApp\Controller\Plugin;

/**
 * Description of Mail
 *
 * @author Bertrand GAUTHIER <bertrand.gauthier at unicaen.fr>
 */
class Mail extends \Zend\Mvc\Controller\Plugin\AbstractPlugin
{
    const SUBJECT_SUFFIX = ' {REDIR}';
    const CURRENT_USER = 'CURRENT_USER';
    const BODY_TEXT_TEMPLATE = <<<EOS

-----------------------------------------------------------------------
Ce mail a été redirigé.
Destinataires originaux :
To: %s
Cc: %s
Bcc: %s
EOS;
    const BODY_HTML_TEMPLATE = <<<EOS
<p>Ce mail a été redirigé.</p>
<p>
Destinataires originaux :<br />
To: %s<br />
Cc: %s<br />
Bcc: %s
</p>
EOS;
//    const CURRENT_USER = 'CURRENT_USER';
    
    /**
     * @var \Zend\Mail\Transport\TransportInterface
     */
    protected $transport;
    
    /**
     * @var array
     */
    protected $redirectTo = array();
    
    /**
     * @var bool
     */
    protected $doNotSend = false;
    
    /**
     * @var \UnicaenApp\Entity\Ldap\People
     */
    protected $identity;
    
    /**
     * Constructeur.
     * 
     * @param \Zend\Mail\Transport\TransportInterface $transport Mode de transport à utiliser
     */
    public function __construct(\Zend\Mail\Transport\TransportInterface $transport)
    {
        $this->setTransport($transport);
    }
    
    /**
     * 
     * @return self
     */
    public function __invoke()
    {
        return $this;
    }
    
    /**
     * Envoit le message.
     * 
     * @param \Zend\Mail\Message $message Message à envoyer
     * @return \Zend\Mail\Message Message effectivement envoyé, différent de l'original si la redirection est activée
     */
    public function send(\Zend\Mail\Message $message)
    {
        $msg = $this->prepareMessage($message);
        
        if (!$this->getDoNotSend()) {
            $this->getTransport()->send($msg);
        }
        
        return $msg;
    }
    
    /**
     * 
     * @param \Zend\Mail\Message $message
     * @return \Zend\Mail\Message
     */
    protected function prepareMessage(\Zend\Mail\Message $message)
    {
        if (!$this->getRedirectTo()) {
            return $message;
        }
        
        // collecte des destinataires originaux pour les afficher à la fin du mail
        $to  = array();
        $cc  = array();
        $bcc = array();
        foreach ($message->getTo() as $addr) { /* @var $addr \Zend\Mail\Address */
            $to[] = $addr->getEmail() . ($addr->getName() ? ' <' . $addr->getName() . '>' : null);
        }
        foreach ($message->getCc() as $addr) { /* @var $addr \Zend\Mail\Address */
            $cc[] = $addr->getEmail() . ($addr->getName() ? ' <' . $addr->getName() . '>' : null);
        }
        foreach ($message->getBcc() as $addr) { /* @var $addr \Zend\Mail\Address */
            $bcc[] = $addr->getEmail() . ($addr->getName() ? ' <' . $addr->getName() . '>' : null);
        }
        $to   = implode(", ", $to);
        $cc   = implode(", ", $cc);
        $bcc  = implode(", ", $bcc);
        $body = $message->getBody();

        /**
         * Si corps de mail en HTML
         */
        if ($body instanceof \Zend\Mime\Message) {
            $template = self::BODY_HTML_TEMPLATE;
            $part = new \Zend\Mime\Part(sprintf($template, $to, $cc, $bcc));
            $part->type = \Zend\Mime\Mime::TYPE_HTML;
            $part->charset = $message->getEncoding();
            $body->addPart($part);
        }
        /**
         * Si corps de mail texte ou autre
         */
        else {
            $template = self::BODY_TEXT_TEMPLATE;
            $body .= sprintf($template, $to, $cc, $bcc);
        }
            
        // si 'CURRENT_USER' est trouvé dans les adresses de redirection, l'utilisateur connecté est ajouté aux destinataires
        if (($identity = $this->getIdentity())) {
            foreach ($redirectTo = $this->getRedirectTo() as $key => $value) {
                if (self::CURRENT_USER === $key || self::CURRENT_USER === $value) {
                    $redirectTo[$identity->getMail()] = $identity->getNomComplet(true);
                    unset($redirectTo[$key]);
                }
            }
            $this->setRedirectTo($redirectTo);
        }
        
        $msg = new \Zend\Mail\Message();
        $msg->setSubject($message->getSubject() . self::SUBJECT_SUFFIX)
            ->setFrom($message->getFrom())
            ->setTo($this->getRedirectTo())
            ->setCc(array())
            ->setBcc(array())
            ->setBody($body)
            ->setEncoding($message->getEncoding());
        
        return $msg;
    }
    
    /**
     * Retourne l'identité de l'utilisateur connecté.
     * 
     * @return \UnicaenApp\Entity\Ldap\People
     */
    public function getIdentity()
    {
        if (null === $this->identity) {
            try {
                $this->identity = $this->getController()->zfcUserAuthentication()->getIdentity();
            }
            catch (\Zend\ServiceManager\Exception\ServiceNotFoundException $snfe) {
                $this->identity = null;
            }
            if (!$this->identity instanceof \UnicaenApp\Entity\Ldap\People) {
                $this->identity = null;
            }
        }
        return $this->identity;
    }

    /**
     * Spécifie l'identité de l'utilisateur connecté.
     * 
     * @param \UnicaenApp\Entity\Ldap\People $identity
     * @return self
     */
    public function setIdentity(\UnicaenApp\Entity\Ldap\People $identity)
    {
        $this->identity = $identity;
        return $this;
    }
    
    /**
     * Retourne le mode de transport à utiliser.
     * 
     * @return \Zend\Mail\Transport\TransportInterface
     */
    public function getTransport()
    {
        return $this->transport;
    }

    /**
     * Spécifie le mode de transport à utiliser.
     * 
     * @param \Zend\Mail\Transport\TransportInterface $transport
     * @return self
     */
    public function setTransport(\Zend\Mail\Transport\TransportInterface $transport)
    {
        $this->transport = $transport;
        return $this;
    }
    
    /**
     * Retourne les adresses vers lesquelles rediriger les mails.
     * NB: elles sont substituées aux adresses originales.
     * 
     * @return array
     */
    public function getRedirectTo()
    {
        return $this->redirectTo ? (array)$this->redirectTo : array();
    }

    /**
     * Spécifie les adresses vers lesquelles rediriger les mails.
     * NB: elles sont substituées aux adresses originales.
     * 
     * @param array $redirectTo
     * @return self
     */
    public function setRedirectTo(array $redirectTo = array())
    {
        $this->redirectTo = $redirectTo;
        return $this;
    }

    /**
     * Ajoute des adresses vers lesquelles rediriger les mails.
     * 
     * @param array $redirectTo
     * @return self
     */
    public function addRedirectTo(array $redirectTo = array())
    {
        $this->setRedirectTo(array_merge($this->getRedirectTo(), $redirectTo));
        return $this;
    }
    
    /**
     * Retourne le flag indiquant si l'envoi des mails est désactivé.
     * 
     * @return bool
     */
    public function getDoNotSend()
    {
        return $this->doNotSend;
    }

    /**
     * Spécifie le flag indiquant si l'envoi des mails est désactivé.
     * 
     * @param bool $doNotSend
     * @return self
     */
    public function setDoNotSend($doNotSend = true)
    {
        $this->doNotSend = (bool)$doNotSend;
        return $this;
    }
}