<?php

namespace Krtv\Bundle\SingleSignOnIdentityProviderBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;

/**
 * Class AddUriSignerSecretPass
 * @package Krtv\Bundle\SingleSignOnIdentityProviderBundle\DependencyInjection\Compiler
 */
class AddUriSignerSecretPass implements CompilerPassInterface
{
    /**
     * @param ContainerBuilder $container
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition('krtv_single_sign_on_identity_provider.uri_signer')) {
            return;
        }

        $parameter = $container->getParameter('krtv_single_sign_on_identity_provider.secret_parameter');

        $container->getDefinition('krtv_single_sign_on_identity_provider.uri_signer')
            ->replaceArgument(0, $container->getParameter($parameter));
    }
}