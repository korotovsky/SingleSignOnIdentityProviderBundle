<?php

namespace Krtv\Bundle\SingleSignOnIdentityProviderBundle\DependencyInjection;

use Krtv\Bundle\SingleSignOnIdentityProviderBundle\DependencyInjection\Compiler\ResolveSecretPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

/**
 * Class ResolveSecretPassTest
 * @package Krtv\Bundle\SingleSignOnIdentityProviderBundle\DependencyInjection
 */
class ResolveSecretPassTest extends \PHPUnit_Framework_TestCase
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
                array('krtv_single_sign_on_identity_provider.secret_parameter', 'secret'),
                array('secret', 'secret_is_very_secret'),
            ));

        $encoder = $this->getMockBuilder('Symfony\Component\DependencyInjection\Definition')
            ->getMock();
        $encoder->expects($this->once())
            ->method('replaceArgument')
            ->with(0, 'secret_is_very_secret');

        $signer = $this->getMockBuilder('Symfony\Component\DependencyInjection\Definition')
            ->getMock();
        $signer->expects($this->once())
            ->method('replaceArgument')
            ->with(0, 'secret_is_very_secret');

        $this->container->expects($this->any())
            ->method('getDefinition')
            ->willReturnMap(array(
                array('krtv_single_sign_on_identity_provider.security.authentication.encoder', $encoder),
                array('krtv_single_sign_on_identity_provider.uri_signer', $signer)
            ));
    }

    /**
     *
     */
    public function testProcess()
    {
        $pass = new ResolveSecretPass();
        $pass->process($this->container);
    }
}
