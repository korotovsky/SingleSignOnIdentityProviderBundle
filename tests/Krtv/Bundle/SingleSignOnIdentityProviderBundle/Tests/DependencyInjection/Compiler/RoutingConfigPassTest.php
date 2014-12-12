<?php

namespace Krtv\Bundle\SingleSignOnIdentityProviderBundle\DependencyInjection;

use Krtv\Bundle\SingleSignOnIdentityProviderBundle\DependencyInjection\Compiler\RoutingConfigPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

/**
 * Class RoutingConfigPassTest
 * @package Krtv\Bundle\SingleSignOnIdentityProviderBundle\DependencyInjection
 */
class RoutingConfigPassTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ContainerBuilder|\PHPUnit_Framework_MockObject_MockObject
     */
    private $container;

    /**
     *
     */
    protected function setUp()
    {
        $this->container = $this->getMockBuilder('Symfony\Component\DependencyInjection\ContainerBuilder')
            ->getMock();
        $this->container->expects($this->any())
            ->method('getParameter')
            ->willReturnMap(array(
                array('krtv_single_sign_on_identity_provider.host', 'idp.example.com'),
                array('krtv_single_sign_on_identity_provider.login_path', '/sso/login/'),
            ));

        $loader = $this->getMockBuilder('Symfony\Component\DependencyInjection\Definition')
            ->getMock();
        $loader->expects($this->exactly(2))
            ->method('replaceArgument')
            ->withConsecutive(
                array(0, 'idp.example.com'),
                array(1, '/sso/login/')
            )
            ->willReturnSelf();

        $this->container->expects($this->any())
            ->method('getDefinition')
            ->willReturnMap(array(
                array('krtv_single_sign_on_identity_provider.routing.loader', $loader),
            ));
    }

    /**
     *
     */
    public function testProcess()
    {
        $pass = new RoutingConfigPass();
        $pass->process($this->container);
    }
}
