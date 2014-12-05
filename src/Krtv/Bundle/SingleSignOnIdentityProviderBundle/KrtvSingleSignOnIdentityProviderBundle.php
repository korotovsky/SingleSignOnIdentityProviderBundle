<?php

namespace Krtv\Bundle\SingleSignOnIdentityProviderBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;

use Krtv\Bundle\SingleSignOnIdentityProviderBundle\DependencyInjection\Compiler\AddEncoderSecretPass;
use Krtv\Bundle\SingleSignOnIdentityProviderBundle\DependencyInjection\Compiler\AddUriSignerSecretPass;
use Krtv\Bundle\SingleSignOnIdentityProviderBundle\DependencyInjection\Compiler\AddRoutingConfigPass;
use Krtv\Bundle\SingleSignOnIdentityProviderBundle\DependencyInjection\Compiler\AddServiceProvidersPass;

/**
 * Class KrtvSingleSignOnIdentityProviderBundle
 * @package Krtv\Bundle\SingleSignOnIdentityProviderBundle
 */
class KrtvSingleSignOnIdentityProviderBundle extends Bundle
{
    /**
     * @param ContainerBuilder $container
     */
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new AddEncoderSecretPass());
        $container->addCompilerPass(new AddUriSignerSecretPass());
        $container->addCompilerPass(new AddRoutingConfigPass());
        $container->addCompilerPass(new AddServiceProvidersPass());
    }
}