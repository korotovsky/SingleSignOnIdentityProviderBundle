<?php

namespace FM\SingleSignOnBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use FM\SingleSignOnBundle\DependencyInjection\Compiler\AddEncoderSecretPass;
use FM\SingleSignOnBundle\DependencyInjection\Compiler\AddRoutingConfigPass;

class FMSingleSignOnBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new AddEncoderSecretPass());
        $container->addCompilerPass(new AddRoutingConfigPass());
    }
}