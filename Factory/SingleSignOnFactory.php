<?php

namespace FM\SingleSignOnBundle\Factory;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\DefinitionDecorator;
use Symfony\Bundle\SecurityBundle\DependencyInjection\Security\Factory\AbstractFactory;

class SingleSignOnFactory extends AbstractFactory
{
    public function __construct()
    {
        $this->addOption('sso_host');
        $this->addOption('sso_path', '/_sso/');
    }

    public function getPosition()
    {
        return 'pre_auth';
    }

    public function getKey()
    {
        return 'fm_sso';
    }

    protected function getListenerId()
    {
        return 'fm_sso.security.authentication.listener';
    }

    protected function createAuthProvider(ContainerBuilder $container, $id, $config, $userProviderId)
    {
        $providerId = 'security.authentication.provider.fm_sso.' . $id;

        $container
            ->setDefinition($providerId, new DefinitionDecorator('fm_sso.security.authentication.provider'))
            ->replaceArgument(0, new Reference($userProviderId))
            ->replaceArgument(2, $id)
        ;

        return $providerId;
    }

    protected function createEntryPoint($container, $id, $config, $defaultEntryPointId)
    {
        $entryPointId = 'security.authentication.entry_point.fm_sso.' . $id;

        $container
            ->setDefinition($entryPointId, new DefinitionDecorator('fm_sso.security.authentication.entry_point'))
            ->replaceArgument(0, $config)
        ;

        // set options to container for use by other classes
        $container->setParameter('fm_sso.options.'.$id, $config);

        return $entryPointId;
    }
}