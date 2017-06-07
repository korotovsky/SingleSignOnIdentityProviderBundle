<?php

namespace Krtv\Bundle\SingleSignOnIdentityProviderBundle\DependencyInjection;

use Krtv\Bundle\SingleSignOnIdentityProviderBundle\DependencyInjection\Compiler\ServiceProvidersPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

/**
 * Class ServiceProvidersPassTest
 * @package Krtv\Bundle\SingleSignOnIdentityProviderBundle\DependencyInjection
 */
class ServiceProvidersPassTest extends \PHPUnit_Framework_TestCase
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
                array('krtv_single_sign_on_identity_provider.services', array(
                    'consumer1',
                    'consumer2',
                )),
            ));
        $this->container->expects($this->any())
            ->method('findTaggedServiceIds')
            ->willReturnMap(array(
                array('sso.service_provider', false, array(
                    'acme_bundle.sso.consumer1' => array(
                        array(
                            'service' => 'consumer1'
                        )
                    ),
                    'acme_bundle.sso.consumer2' => array(
                        array(
                            'service' => 'consumer2'
                        )
                    ),
                    'acme_bundle.sso.consumer3' => array(
                        array(
                            'service' => 'consumer3'
                        )
                    )
                ))
            ));

        $serviceManager = $this->getMockBuilder('Symfony\Component\DependencyInjection\Definition')
            ->getMock();

        $consumer1 = $this->getMockBuilder('Symfony\Component\DependencyInjection\Definition')
            ->getMock();
        $consumer2 = $this->getMockBuilder('Symfony\Component\DependencyInjection\Definition')
            ->getMock();
        $consumer3 = $this->getMockBuilder('Symfony\Component\DependencyInjection\Definition')
            ->getMock();

        $serviceManager->expects($this->once())
            ->method('replaceArgument')
            ->withConsecutive(
                array(3, array(
                    'consumer1' => $consumer1,
                    'consumer2' => $consumer2,
                ))
            )
            ->willReturnSelf();

        $this->container->expects($this->exactly(3))
            ->method('getDefinition')
            ->willReturnMap(array(
                array('krtv_single_sign_on_identity_provider.manager.service_manager', $serviceManager),
                array('acme_bundle.sso.consumer1', $consumer1),
                array('acme_bundle.sso.consumer2', $consumer2),
                array('acme_bundle.sso.consumer3', $consumer3), // Will not be called
            ));
    }

    /**
     *
     */
    public function testProcess()
    {
        $pass = new ServiceProvidersPass();
        $pass->process($this->container);
    }
}
