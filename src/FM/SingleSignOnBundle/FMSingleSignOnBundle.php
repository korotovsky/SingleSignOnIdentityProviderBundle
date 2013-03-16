<?php

namespace FM\SingleSignOnBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;

use FM\SingleSignOnBundle\DependencyInjection\Compiler\AddEncoderSecretPass;
use FM\SingleSignOnBundle\DependencyInjection\Compiler\AddRoutingConfigPass;
use FM\SingleSignOnBundle\Factory\SingleSignOnFactory;

class FMSingleSignOnBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new AddEncoderSecretPass());
        $container->addCompilerPass(new AddRoutingConfigPass());

        $extension = $container->getExtension('security');
        $extension->addSecurityListenerFactory(new SingleSignOnFactory());
    }
}