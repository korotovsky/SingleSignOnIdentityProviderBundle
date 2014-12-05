<?php

namespace Krtv\Bundle\SingleSignOnIdentityProviderBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\DefinitionDecorator;

/**
 * Class AddServiceProvidersPass
 * @package Krtv\Bundle\SingleSignOnIdentityProviderBundle\DependencyInjection\Compiler
 */
class AddServiceProvidersPass implements CompilerPassInterface
{
    /**
     * @param ContainerBuilder $container
     */
    public function process(ContainerBuilder $container)
    {
        $services = array();

        foreach ($container->findTaggedServiceIds('sso.service_provider') as $id => $attributes) {
            $services[$attributes[0]['service']] = $container->getDefinition($id);
        }

        $container->getDefinition('krtv_single_sign_on_identity_provider.manager.service_manager')
            ->replaceArgument(2, $services);
    }
}