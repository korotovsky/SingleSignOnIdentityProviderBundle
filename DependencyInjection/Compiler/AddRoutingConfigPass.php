<?php

namespace FM\SingleSignOnBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;

class AddRoutingConfigPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition('fm_sso.routing.loader')) {
            return;
        }

        $def = $container->getDefinition('fm_sso.routing.loader');
        $def->replaceArgument(0, $container->getParameter('fm_single_sign_on_host'));
        $def->replaceArgument(1, $container->getParameter('fm_single_sign_on_login_path'));
    }
}