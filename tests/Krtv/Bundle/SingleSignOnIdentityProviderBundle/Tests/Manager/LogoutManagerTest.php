<?php

namespace Krtv\Bundle\SingleSignOnIdentityProviderBunde\Tests\Manager;

use Krtv\Bundle\SingleSignOnIdentityProviderBundle\Manager\LogoutManager;
use Krtv\Bundle\SingleSignOnIdentityProviderBundle\Manager\ServiceProviderInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;
use Symfony\Component\HttpKernel\UriSigner;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;

/**
 * Class LogoutManagerTest
 * @package Krtv\Bundle\SingleSignOnIdentityProviderBunde\Tests\Manager
 */
class LogoutManagerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var SessionInterface
     */
    private $session;

    /**
     * @var RouterInterface
     */
    private $router;

    /**
     * @var ServiceProviderInterface[]
     */
    private $consumers = array();

    /**
     * @var UriSigner
     */
    private $uriSigner;

    /**
     * @var RequestStack
     */
    private $requestStack;

    /**
     * The next logout url should be http://consumer1.com/logout
     * Initial state: logout started from IdP.
     */
    public function testNextLogoutUrlShouldBeConsumer1()
    {
        $serviceManagerMock = $this->getServiceManagerMock();
        $serviceManagerMock->expects($this->once())
            ->method('getServices')
            ->willReturn(array(
                'consumer1',
                'consumer2',
            ));
        $serviceManagerMock->expects($this->once())
            ->method('getServiceManager')
            ->willReturnMap(array(
                array('consumer1', $this->getConsumerMock('consumer1')),
                array('consumer2', $this->getConsumerMock('consumer2')),
            ));

        $logoutManager = new LogoutManager($serviceManagerMock, $this->getSessionMock(), $this->getRouterMock());

        $this->assertEquals('http://consumer1.com/logout', $logoutManager->getNextLogoutUrl());
    }

    /**
     * The next logout url should be http://consumer2.com/logout
     * Initial state: logout started from IdP and request comes from logout of consumer1
     */
    public function testNextLogoutUrlShouldBeConsumer2()
    {
        $serviceManagerMock = $this->getServiceManagerMock();
        $serviceManagerMock->expects($this->once())
            ->method('getServices')
            ->willReturn(array(
                'consumer1',
                'consumer2',
            ));
        $serviceManagerMock->expects($this->once())
            ->method('getRequestService')
            ->willReturn('consumer1');
        $serviceManagerMock->expects($this->once())
            ->method('getServiceManager')
            ->willReturnMap(array(
                array('consumer1', $this->getConsumerMock('consumer1')),
                array('consumer2', $this->getConsumerMock('consumer2')),
            ));

        $logoutManager = new LogoutManager($serviceManagerMock, $this->getSessionMock(), $this->getRouterMock());

        $this->assertEquals('http://consumer2.com/logout', $logoutManager->getNextLogoutUrl());
    }

    /**
     * The next logout url should be http://idp.example.com/logout
     * Initial state: logout started from IdP and logout on consumer1 and consumer2 already completed
     *      request comes from consumer2
     */
    public function testNextLogoutUrlShouldBeLocalLogout()
    {
        $serviceManagerMock = $this->getServiceManagerMock();
        $serviceManagerMock->expects($this->once())
            ->method('getServices')
            ->willReturn(array(
                'consumer1',
                'consumer2',
            ));
        $serviceManagerMock->expects($this->once())
            ->method('getRequestService')
            ->willReturn('consumer2');
        $serviceManagerMock->expects($this->never())
            ->method('getServiceManager');

        $session = $this->getSessionMock();
        $session->set('_logout/processed', array(
            'consumer1' => 'consumer1'
        ));

        $routerMock = $this->getRouterMock();
        $routerMock->expects($this->once())
            ->method('generate')
            ->willReturnMap(array(
                array('_security_logout', array(), UrlGeneratorInterface::ABSOLUTE_URL, 'http://idp.example.com/logout')
            ));

        $logoutManager = new LogoutManager($serviceManagerMock, $this->getSessionMock(), $routerMock);

        $this->assertEquals('http://idp.example.com/logout', $logoutManager->getNextLogoutUrl());
    }

    /**
     * @param string $consumer
     * @return ServiceProviderInterface
     */
    private function getConsumerMock($consumer)
    {
        if (!isset($this->consumers[$consumer])) {
            $this->consumers[$consumer] = $mock = $this->getMockBuilder('Krtv\Bundle\SingleSignOnIdentityProviderBundle\Manager\ServiceProviderInterface')
                ->setMockClassName(sprintf('%sServiceProvider', ucfirst($consumer)))
                ->getMock();
            $mock->expects($this->any())
                ->method('getName')
                ->willReturn($consumer);
            $mock->expects($this->any())
                ->method('getServiceIndexUrl')
                ->willReturn(sprintf('http://%s.com/', $consumer));
            $mock->expects($this->any())
                ->method('getServiceLogoutUrl')
                ->willReturn(sprintf('http://%s.com/logout', $consumer));
        }

        return $this->consumers[$consumer];
    }

    /**
     * @return Session|SessionInterface
     */
    private function getSessionMock()
    {
        if ($this->session === null) {
            $this->session = new Session(new MockArraySessionStorage());
        }

        return $this->session;
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function getRouterMock()
    {
        if ($this->router === null) {
            $this->router = $this->getMockBuilder('Symfony\Component\Routing\RouterInterface')
                ->getMock();
        }

        return $this->router;
    }

    /**
     * @return RequestStack
     */
    private function getRequestStackMock()
    {
        if ($this->requestStack === null) {
            $this->requestStack = new RequestStack();
        }

        return $this->requestStack;
    }

    /**
     * @return UriSigner
     */
    private function getUriSignerMock()
    {
        if ($this->uriSigner === null) {
            $this->uriSigner = new UriSigner('secret');
        }

        return $this->uriSigner;
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function getServiceManagerMock()
    {
        $serviceManager = $this->getMockBuilder('Krtv\Bundle\SingleSignOnIdentityProviderBundle\Manager\ServiceManager')
            ->setConstructorArgs(array(
                $this->getRequestStackMock(),
                $this->getSessionMock(),
                'main',
                array(
                    'consumer1' => $this->getConsumerMock('consumer1'),
                    'consumer2' => $this->getConsumerMock('consumer2'),
                ),
                array(
                    'service_parameter' => 'service',
                    'service_extra_parameter' => 'service_extra',
                    'target_path_parameter' => '_target_path',
                )
            ))
            ->getMock();

        return $serviceManager;
    }
}

