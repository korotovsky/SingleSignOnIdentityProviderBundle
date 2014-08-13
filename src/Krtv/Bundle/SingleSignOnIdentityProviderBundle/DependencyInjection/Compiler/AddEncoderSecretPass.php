<?php

namespace Krtv\Bundle\SingleSignOnIdentityProviderBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;

class AddEncoderSecretPass implements CompilerPassInterface
{
    /**
     * {@inheritDoc}
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition('krtv_single_sign_on_identity_provider.security.authentication.encoder')) {
            return;
        }

        $parameter = $container->getParameter('krtv_single_sign_on_identity_provider.secret_parameter');

        $container->getDefinition('krtv_single_sign_on_identity_provider.security.authentication.encoder')
            ->replaceArgument(0, $container->getParameter($parameter));
    }
}