<?php

namespace Krtv\Bundle\SingleSignOnIdentityProviderBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;

/**
 * Class RoutingConfigPass
 * @package Krtv\Bundle\SingleSignOnIdentityProviderBundle\DependencyInjection\Compiler
 */
class RoutingConfigPass implements CompilerPassInterface
{
    /**
     * {@inheritDoc}
     */
    public function process(ContainerBuilder $container)
    {
        $container->getDefinition('krtv_single_sign_on_identity_provider.routing.loader')
            ->replaceArgument(0, $container->getParameter('krtv_single_sign_on_identity_provider.host'))
            ->replaceArgument(1, $container->getParameter('krtv_single_sign_on_identity_provider.login_path'))
            ->replaceArgument(2, $container->getParameter('krtv_single_sign_on_identity_provider.logout_path'));
    }
}
