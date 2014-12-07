<?php

namespace Krtv\Bundle\SingleSignOnIdentityProviderBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;

/**
 * Class ServiceProvidersPass
 * @package Krtv\Bundle\SingleSignOnIdentityProviderBundle\DependencyInjection\Compiler
 */
class ServiceProvidersPass implements CompilerPassInterface
{
    /**
     * {@inheritDoc}
     */
    public function process(ContainerBuilder $container)
    {
        $services = array();

        $activeServices = $container->getParameter('krtv_single_sign_on_identity_provider.services');

        foreach ($container->findTaggedServiceIds('sso.service_provider') as $id => $attributes) {
            $name = $attributes[0]['service'];

            if (in_array($name, $activeServices)) {
                $services[$name] = $container->getDefinition($id);
            }
        }

        $container->getDefinition('krtv_single_sign_on_identity_provider.manager.service_manager')
            ->replaceArgument(2, $services);
    }
}
