<?php

namespace Krtv\Bundle\SingleSignOnIdentityProviderBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Krtv\Bundle\SingleSignOnIdentityProviderBundle\DependencyInjection\Compiler\ResolveSecretPass;
use Krtv\Bundle\SingleSignOnIdentityProviderBundle\DependencyInjection\Compiler\RoutingConfigPass;

/**
 * Class KrtvSingleSignOnIdentityProviderBundle
 * @package Krtv\Bundle\SingleSignOnIdentityProviderBundle
 */
class KrtvSingleSignOnIdentityProviderBundle extends Bundle
{
    /**
     * {@inheritDoc}
     */
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new RoutingConfigPass());
        $container->addCompilerPass(new ResolveSecretPass());
    }
}
