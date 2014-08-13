<?php

namespace Krtv\Bundle\SingleSignOnIdentityProviderBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;

/**
 * Class AddRoutingConfigPass
 * @package Krtv\Bundle\SingleSignOnIdentityProviderBundle\DependencyInjection\Compiler
 */
class AddRoutingConfigPass implements CompilerPassInterface
{
    /**
     * @param ContainerBuilder $container
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition('krtv_single_sign_on_identity_provider.routing.loader')) {
            return;
        }

        $container->getDefinition('krtv_single_sign_on_identity_provider.routing.loader')
            ->replaceArgument(0, $container->getParameter('krtv_single_sign_on_identity_provider.host'))
            ->replaceArgument(1, $container->getParameter('krtv_single_sign_on_identity_provider.login_path'));
    }
}