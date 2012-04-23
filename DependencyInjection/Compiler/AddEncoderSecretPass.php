<?php

namespace FM\SingleSignOnBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;

class AddEncoderSecretPass implements CompilerPassInterface
{
    /**
     * {@inheritDoc}
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition('fm_sso.security.authentication.encoder')) {
            return;
        }

        $container->getDefinition('fm_sso.security.authentication.encoder')->replaceArgument(0, $container->getParameter('secret'));
    }
}