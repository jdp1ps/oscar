<?php

namespace Oscar\View\Factories;

use Interop\Container\ContainerInterface;
use Oscar\View\Helpers\UsurpationHelper;
use UnicaenAuthentification\Options\ModuleOptions;
use UnicaenAuthentification\Service\UserContext;
use Laminas\View\Helper\Url;

class UsurpationHelperFactory
{
    /**
     * @param ContainerInterface $container
     * @return UsurpationHelper
     */
    public function __invoke(ContainerInterface $container)
    {
        /** @var Url $urlHelper */
        $urlHelper = $container->get('ViewHelperManager')->get('url');
        $url = $urlHelper->__invoke('utilisateur/default', ['action' => 'usurper-identite']);

        /** @var UserContext $userContextService */
        $userContextService = $container->get('AuthUserContext');

        /** @var ModuleOptions $moduleOptions */
        $moduleOptions = $container->get('unicaen-auth_module_options');

        $usurpationAllowed = in_array(
            $userContextService->getIdentityUsername(),
            $moduleOptions->getUsurpationAllowedUsernames());
        $usurpationEnCours = $userContextService->isUsurpationEnCours();

        $helper = new UsurpationHelper($userContextService);
        $helper->setUrl($url);
        $helper->setUsurpationEnabled($usurpationAllowed);
        $helper->setUsurpationEnCours($usurpationEnCours);

        return $helper;
    }
}