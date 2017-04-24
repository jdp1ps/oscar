<?php

namespace UnicaenAuth\View;

use Zend\Mvc\MvcEvent;
use BjyAuthorize\View\UnauthorizedStrategy;

/**
 * Modification du mécanisme standard : pas de redirection s'il s'agit d'une requête AJAX.
 *
 * @author Bertrand GAUTHIER <bertrand.gauthier at unicaen.fr>
 */
class RedirectionStrategy extends \BjyAuthorize\View\RedirectionStrategy
{
    /**
     * Handles redirects in case of dispatch errors caused by unauthorized access
     *
     * @param \Zend\Mvc\MvcEvent $event
     */
    public function onDispatchError(MvcEvent $event)
    {
        $request        = $event->getRequest();
        $router         = $event->getRouter();
        $sl             = $event->getApplication()->getServiceManager();
        $authService    = $sl->get('Zend\Authentication\AuthenticationService'); //'zfcuser_auth_service'
        $unauthStrategy = $sl->get('BjyAuthorize\View\UnauthorizedStrategy'); /* @var $unauthorizedStrategy UnauthorizedStrategy */

        // s'il s'agit d'une requête issue d'une console (CLI), délégation à la stratégie standard
        if ($request instanceof \Zend\Console\Request) {
            return parent::onDispatchError($event);
        }

        // en cas de requête AJAX, on délègue à la stratégie Unauthorized (revoi d'une réponse 403)
        if ($request->isXmlHttpRequest()) {
            return $unauthStrategy->onDispatchError($event);
        }

        // si une identité authentifiée est disponible, pas besoin de se réauthentifier :
        // on délègue donc à la stratégie Unauthorized (réponse 403)
	if ($authService->hasIdentity()) {
	    return $unauthStrategy->onDispatchError($event);
	}

        // cuisine nécessaire pour ajouter en paramètre GET l'URL demandée avant redirection vers la page d'authentification
        if (null === $this->redirectUri) {
            if (($uri = $router->getRequestUri()) && $uri->getPath()) { /* @var $uri \Zend\Uri\Uri */
                $this->redirectUri = $router->assemble([], [
                    'name' => $this->redirectRoute,
                    'query' => ['redirect' => $uri->toString()]]);
            }
        }

        // délégation à la stratégie standard de redirection vers la page d'authentification
        return parent::onDispatchError($event);
    }
}