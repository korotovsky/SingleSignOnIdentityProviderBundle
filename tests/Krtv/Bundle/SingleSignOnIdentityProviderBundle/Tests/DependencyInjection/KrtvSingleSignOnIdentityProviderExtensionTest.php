<?php

namespace Krtv\Bundle\SingleSignOnIdentityProviderBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Class KrtvSingleSignOnIdentityProviderExtensionTest
 * @package Krtv\Bundle\SingleSignOnIdentityProviderBundle\DependencyInjection
 */
class KrtvSingleSignOnIdentityProviderExtensionTest extends \PHPUnit_Framework_TestCase
{
    /**
     *
     */
    public function testLoad()
    {
        $container = new ContainerBuilder();
        // remove default aliases of 'service_container' : Psr\Container\ContainerInterface  & Symfony\Component\DependencyInjection\ContainerInterface
        foreach ($container->getAliases() as $id => $alias) {
            $container->removeAlias($id);
        }
        // default definition like of 'service_container'
        foreach ($container->getDefinitions() as $id => $definition) {
            $container->removeDefinition($id);
        }

        $configs = array(
            array(
                'host' => 'idp.example.com',
                'host_scheme' => 'https',
                'login_path' => '/sso/login/',
                'logout_path' => '/sso/logout',
                'services' => array(
                    'consumer1',
                    'consumer2',
                ),
                'otp_parameter' => '_otp',
                'secret_parameter' => 'secret'
            )
        );

        $extension = new KrtvSingleSignOnIdentityProviderExtension();
        $extension->load($configs, $container);

        $services = array(
            'krtv_single_sign_on_identity_provider.routing.loader',
            'krtv_single_sign_on_identity_provider.security.authentication.otp_manager.orm',
            'krtv_single_sign_on_identity_provider.manager.service_manager',
            'krtv_single_sign_on_identity_provider.manager.logout_manager',
            'krtv_single_sign_on_identity_provider.event_listener.service_subscriber',
            'krtv_single_sign_on_identity_provider.security.authentication.encoder',
            'krtv_single_sign_on_identity_provider.security.http_utils',
            'krtv_single_sign_on_identity_provider.uri_signer',
        );

        foreach ($services as $service) {
            $this->assertInstanceOf('Symfony\Component\DependencyInjection\Definition', $container->getDefinition($service));
        }

        $this->assertCount(count($services), $container->getDefinitions());

        $aliases = array(
            'sso_identity_provider.service_manager',
            'sso_identity_provider.otp_manager',
            'sso_identity_provider.encoder',
            'sso_identity_provider.uri_signer',
        );

        foreach ($aliases as $alias) {
            $this->assertInstanceOf('Symfony\Component\DependencyInjection\Alias', $container->getAlias($alias));
        }

        $this->assertCount(count($aliases), $container->getAliases());

        $parameters = array(
            'krtv_single_sign_on_identity_provider.host' => 'idp.example.com',
            'krtv_single_sign_on_identity_provider.host_scheme' => 'https',
            'krtv_single_sign_on_identity_provider.login_path' => '/sso/login/',
            'krtv_single_sign_on_identity_provider.logout_path' => '/sso/logout',
            'krtv_single_sign_on_identity_provider.services' => array('consumer1', 'consumer2'),
            'krtv_single_sign_on_identity_provider.otp_parameter' => '_otp',
            'krtv_single_sign_on_identity_provider.secret_parameter' => 'secret',
            'krtv_single_sign_on_identity_provider.security.firewall_id' => 'main',
        );

        foreach ($parameters as $parameterName => $parameterValue) {
            $this->assertEquals($parameterValue, $container->getParameter($parameterName));
        }
    }

    /**
     *
     */
    public function testGetAlias()
    {
        $extension = new KrtvSingleSignOnIdentityProviderExtension();

        $actual = $extension->getAlias();
        $expected = 'krtv_single_sign_on_identity_provider';

        $this->assertEquals($expected, $actual);
    }
}
