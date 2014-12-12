<?php

namespace Krtv\Bundle\SingleSignOnIdentityProviderBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\DefinitionDecorator;

/**
 * Class ResolveSecretPass
 * @package Krtv\Bundle\SingleSignOnIdentityProviderBundle\DependencyInjection\Compiler
 */
class ResolveSecretPass implements CompilerPassInterface
{
    /**
     * {@inheritDoc}
     */
    public function process(ContainerBuilder $container)
    {
        $parameter = $container->getParameter('krtv_single_sign_on_identity_provider.secret_parameter');

        $container->getDefinition('krtv_single_sign_on_identity_provider.security.authentication.encoder')
            ->replaceArgument(0, $container->getParameter($parameter));

        $container->getDefinition('krtv_single_sign_on_identity_provider.uri_signer')
            ->replaceArgument(0, $container->getParameter($parameter));
    }
}
