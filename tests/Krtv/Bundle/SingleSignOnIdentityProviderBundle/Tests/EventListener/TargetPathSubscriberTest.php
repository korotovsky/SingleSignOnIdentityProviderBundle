<?php

namespace Krtv\Bundle\SingleSignOnIdentityProviderBundle\EventListener;

use Krtv\Bundle\SingleSignOnIdentityProviderBundle\Manager\ServiceProviderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Class TargetPathSubscriberTest
 * @package Krtv\Bundle\SingleSignOnIdentityProviderBundle\EventListener
 */
class TargetPathSubscriberTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var SessionInterface
     */
    private $session;

    /**
     * @var ServiceProviderInterface[]
     */
    private $consumers = array();

    /**
     *
     */
    public function testGetSubscribedEvents()
    {
        $actual = TargetPathSubscriber::getSubscribedEvents();
        $expected = array(
            KernelEvents::REQUEST => array('onKernelRequest', 10),
        );

        $this->assertEquals($expected, $actual);
    }

    /**
     *
     */
    public function testOnKernelRequestIsNotMasterRequest()
    {
        $serviceManagerMock = $this->getMockBuilder('Krtv\Bundle\SingleSignOnIdentityProviderBundle\Manager\ServiceManager')
            ->setConstructorArgs(array(
                $this->getSessionMock(),
                'main',
                array(
                    'consumer1' => $this->getConsumerMock('consumer1')
                )
            ))
            ->getMock();
        $serviceManagerMock->expects($this->never())
            ->method('getSessionService')
            ->willReturn(null);
        $serviceManagerMock->expects($this->never())
            ->method('setRequestService')
            ->willReturn(null);
        $serviceManagerMock->expects($this->never())
            ->method('setSessionService')
            ->willReturn(null);

        $requestMock = $this->getMockBuilder('Symfony\Component\HttpFoundation\Request')->getMock();
        $requestMock->expects($this->never())
            ->method('get')
            ->willReturn(null);

        $eventMock = $this->getMockBuilder('Symfony\Component\HttpKernel\Event\GetResponseEvent')
            ->setConstructorArgs(array(
                $this->getMockBuilder('Symfony\Component\HttpKernel\HttpKernelInterface')->getMock(),
                $requestMock,
                HttpKernelInterface::SUB_REQUEST,
            ))
            ->getMock();
        $eventMock->expects($this->once())
            ->method('isMasterRequest')
                ->willReturn(false);

        $subscriber = new TargetPathSubscriber($serviceManagerMock);
        $subscriber->onKernelRequest($eventMock);
    }

    /**
     *
     */
    public function testOnKernelRequestDoesNotHaveServiceParameter()
    {
        $serviceManagerMock = $this->getMockBuilder('Krtv\Bundle\SingleSignOnIdentityProviderBundle\Manager\ServiceManager')
            ->setConstructorArgs(array(
                $this->getSessionMock(),
                'main',
                array(
                    'consumer1' => $this->getConsumerMock('consumer1'),
                    'consumer2' => $this->getConsumerMock('consumer2')
                )
            ))
            ->getMock();
        $serviceManagerMock->expects($this->never())
            ->method('getSessionService')
            ->willReturn(null);
        $serviceManagerMock->expects($this->never())
            ->method('setRequestService')
            ->willReturn(null);
        $serviceManagerMock->expects($this->never())
            ->method('setSessionService')
            ->willReturn(null);

        $requestMock = $this->getMockBuilder('Symfony\Component\HttpFoundation\Request')->getMock();
        $requestMock->expects($this->once())
            ->method('get')
            ->willReturnMap(array(
                array('service', null, false, null)
            ));

        $eventMock = $this->getMockBuilder('Symfony\Component\HttpKernel\Event\GetResponseEvent')
            ->setConstructorArgs(array(
                $this->getMockBuilder('Symfony\Component\HttpKernel\HttpKernelInterface')->getMock(),
                $requestMock,
                HttpKernelInterface::MASTER_REQUEST,
            ))
            ->getMock();
        $eventMock->expects($this->once())
            ->method('isMasterRequest')
            ->willReturn(true);
        $eventMock->expects($this->once())
            ->method('getRequest')
            ->willReturn($requestMock);

        $subscriber = new TargetPathSubscriber($serviceManagerMock);
        $subscriber->onKernelRequest($eventMock);
    }

    /**
     *
     */
    public function testOnKernelRequestLogoutProcessAlreadyActivatedFromConsumer1()
    {
        $serviceManagerMock = $this->getMockBuilder('Krtv\Bundle\SingleSignOnIdentityProviderBundle\Manager\ServiceManager')
            ->setConstructorArgs(array(
                $this->getSessionMock(),
                'main',
                array(
                    'consumer1' => $this->getConsumerMock('consumer1'),
                    'consumer2' => $this->getConsumerMock('consumer2')
                )
            ))
            ->getMock();
        $serviceManagerMock->expects($this->once())
            ->method('getSessionService')
            ->willReturn('consumer1');
        $serviceManagerMock->expects($this->once())
            ->method('setRequestService')
            ->with('consumer2');
        $serviceManagerMock->expects($this->never())
            ->method('setSessionService')
            ->willReturn(null);

        $requestMock = new Request(array('service' => 'consumer2'), array(), array('_route' => 'sso_logout_path'));

        $eventMock = $this->getMockBuilder('Symfony\Component\HttpKernel\Event\GetResponseEvent')
            ->setConstructorArgs(array(
                $this->getMockBuilder('Symfony\Component\HttpKernel\HttpKernelInterface')->getMock(),
                $requestMock,
                HttpKernelInterface::MASTER_REQUEST,
            ))
            ->getMock();
        $eventMock->expects($this->once())
            ->method('isMasterRequest')
            ->willReturn(true);
        $eventMock->expects($this->once())
            ->method('getRequest')
            ->willReturn($requestMock);

        $subscriber = new TargetPathSubscriber($serviceManagerMock);
        $subscriber->onKernelRequest($eventMock);
    }

    /**
     *
     */
    public function testOnKernelRequestDoNotOverrideOldIntentionConsumer1WithConsumer1()
    {
        $serviceManagerMock = $this->getMockBuilder('Krtv\Bundle\SingleSignOnIdentityProviderBundle\Manager\ServiceManager')
            ->setConstructorArgs(array(
                $this->getSessionMock(),
                'main',
                array(
                    'consumer1' => $this->getConsumerMock('consumer1'),
                    'consumer2' => $this->getConsumerMock('consumer2')
                )
            ))
            ->getMock();
        $serviceManagerMock->expects($this->once())
            ->method('getSessionService')
            ->willReturn('consumer1');
        $serviceManagerMock->expects($this->once())
            ->method('setRequestService')
            ->with('consumer1');
        $serviceManagerMock->expects($this->never())
            ->method('setSessionService');

        $requestMock = new Request(array('service' => 'consumer1'));

        $eventMock = $this->getMockBuilder('Symfony\Component\HttpKernel\Event\GetResponseEvent')
            ->setConstructorArgs(array(
                $this->getMockBuilder('Symfony\Component\HttpKernel\HttpKernelInterface')->getMock(),
                $requestMock,
                HttpKernelInterface::MASTER_REQUEST,
            ))
            ->getMock();
        $eventMock->expects($this->once())
            ->method('isMasterRequest')
            ->willReturn(true);
        $eventMock->expects($this->once())
            ->method('getRequest')
            ->willReturn($requestMock);

        $subscriber = new TargetPathSubscriber($serviceManagerMock);
        $subscriber->onKernelRequest($eventMock);
    }

    /**
     *
     */
    public function testOnKernelRequestOverrideOldIntentionConsumer1WithConsumer2()
    {
        $serviceManagerMock = $this->getMockBuilder('Krtv\Bundle\SingleSignOnIdentityProviderBundle\Manager\ServiceManager')
            ->setConstructorArgs(array(
                $this->getSessionMock(),
                'main',
                array(
                    'consumer1' => $this->getConsumerMock('consumer1'),
                    'consumer2' => $this->getConsumerMock('consumer2')
                )
            ))
            ->getMock();
        $serviceManagerMock->expects($this->once())
            ->method('getSessionService')
            ->willReturn('consumer1');
        $serviceManagerMock->expects($this->once())
            ->method('setRequestService')
            ->with('consumer2');
        $serviceManagerMock->expects($this->once())
            ->method('setSessionService')
            ->with('consumer2');

        $requestMock = new Request(array('service' => 'consumer2'));

        $eventMock = $this->getMockBuilder('Symfony\Component\HttpKernel\Event\GetResponseEvent')
            ->setConstructorArgs(array(
                $this->getMockBuilder('Symfony\Component\HttpKernel\HttpKernelInterface')->getMock(),
                $requestMock,
                HttpKernelInterface::MASTER_REQUEST,
            ))
            ->getMock();
        $eventMock->expects($this->once())
            ->method('isMasterRequest')
            ->willReturn(true);
        $eventMock->expects($this->once())
            ->method('getRequest')
            ->willReturn($requestMock);

        $subscriber = new TargetPathSubscriber($serviceManagerMock);
        $subscriber->onKernelRequest($eventMock);
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
}
