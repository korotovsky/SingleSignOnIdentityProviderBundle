<?php

namespace Krtv\Bundle\SingleSignOnIdentityProviderBunde\Tests\Manager;

use Krtv\Bundle\SingleSignOnIdentityProviderBundle\Manager\ServiceManager;
use Krtv\Bundle\SingleSignOnIdentityProviderBundle\Manager\ServiceProviderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;
use Symfony\Component\Routing\RouterInterface;

/**
 * Class ServiceManagerTest
 * @package Krtv\Bundle\SingleSignOnIdentityProviderBunde\Tests\Manager
 */
class ServiceManagerTest extends \PHPUnit_Framework_TestCase
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
     * @expectedException RuntimeException
     * @expectedExceptionMessage No ServiceProvider managers found. Make sure that you have at least one ServiceProvider manager tagged with "sso.service_provider"
     */
    public function testInvalidInvocation()
    {
        new ServiceManager(new RequestStack(), $this->getSessionMock(), 'main', array(), array(
            'service_parameter' => 'service',
            'service_extra_parameter' => 'service_extra',
            'target_path_parameter' => '_target_path',
        ));
    }

    /**
     *
     */
    public function testGetRegisteredServiceNames()
    {
        $serviceManager = new ServiceManager(new RequestStack(), $this->getSessionMock(), 'main', array(
            'consumer1' => $this->getConsumerMock('consumer1')
        ), array(
            'service_parameter' => 'service',
            'service_extra_parameter' => 'service_extra',
            'target_path_parameter' => '_target_path',
        ));

        $actual = $serviceManager->getServices();
        $expected = array('consumer1');

        $this->assertEquals($expected, $actual);
    }

    /**
     *
     */
    public function testGetServiceManager()
    {
        $serviceManager = new ServiceManager(new RequestStack(), $this->getSessionMock(), 'main', array(
            'consumer1' => $consumer1 = $this->getConsumerMock('consumer1')
        ), array(
            'service_parameter' => 'service',
            'service_extra_parameter' => 'service_extra',
            'target_path_parameter' => '_target_path',
        ));

        $actual = $serviceManager->getServiceManager('consumer1');
        $expected = $consumer1;

        $this->assertEquals($expected, $actual);
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Unknown service consumer2
     */
    public function testExceptionWhenGetServiceManager()
    {
        $serviceManager = new ServiceManager(new RequestStack(), $this->getSessionMock(), 'main', array(
            'consumer1' => $consumer1 = $this->getConsumerMock('consumer1')
        ), array(
            'service_parameter' => 'service',
            'service_extra_parameter' => 'service_extra',
            'target_path_parameter' => '_target_path',
        ));

        $actual = $serviceManager->getServiceManager('consumer2');
    }

    /**
     *
     */
    public function testGetServiceNameStoredInSession()
    {
        $sessionMock = $this->getSessionMock();
        $sessionMock->set('_target/service', 'consumer1');

        $serviceManager = new ServiceManager(new RequestStack(), $sessionMock, 'main', array(
            'consumer1' => $consumer1 = $this->getConsumerMock('consumer1')
        ), array(
            'service_parameter' => 'service',
            'service_extra_parameter' => 'service_extra',
            'target_path_parameter' => '_target_path',
        ));

        $actual = $serviceManager->getSessionService();
        $expected = 'consumer1';

        $this->assertEquals($expected, $actual);
    }

    /**
     *
     */
    public function testSetAndGetServiceNameFromRequest()
    {
        $requestMock = new Request();

        $requestStackMock = new RequestStack();
        $requestStackMock->push($requestMock);

        $serviceManager = new ServiceManager($requestStackMock, $this->getSessionMock(), 'main', array(
            'consumer1' => $consumer1 = $this->getConsumerMock('consumer1')
        ), array(
            'service_parameter' => 'service',
            'service_extra_parameter' => 'service_extra',
            'target_path_parameter' => '_target_path',
        ));

        $actual = $serviceManager->getRequestService();
        $expected = null;

        $this->assertEquals($expected, $actual);

        $requestMock = new Request(array('service' => 'consumer1'));

        $requestStackMock = new RequestStack();
        $requestStackMock->push($requestMock);

        $serviceManager = new ServiceManager($requestStackMock, $this->getSessionMock(), 'main', array(
            'consumer1' => $consumer1 = $this->getConsumerMock('consumer1')
        ), array(
            'service_parameter' => 'service',
            'service_extra_parameter' => 'service_extra',
            'target_path_parameter' => '_target_path',
        ));

        $actual = $serviceManager->getRequestService();
        $expected = 'consumer1';

        $this->assertEquals($expected, $actual);
    }

    /**
     *
     */
    public function testSetServiceNameInSession()
    {
        $sessionMock = $this->getSessionMock();

        $serviceManager = new ServiceManager(new RequestStack(), $sessionMock, 'main', array(
            'consumer1' => $consumer1 = $this->getConsumerMock('consumer1')
        ), array(
            'service_parameter' => 'service',
            'service_extra_parameter' => 'service_extra',
            'target_path_parameter' => '_target_path',
        ));

        $actual = $serviceManager->getSessionService();
        $expected = null;

        $this->assertEquals($expected, $actual);

        $serviceManager->setSessionService('consumer1');

        $this->assertEquals('consumer1', $serviceManager->getSessionService());
        $this->assertEquals('consumer1', $sessionMock->get('_target/service'));
    }

    /**
     *
     */
    public function testSetDefaultsSessionState()
    {
        $sessionMock = $this->getSessionMock();

        $serviceManager = new ServiceManager(new RequestStack(), $sessionMock, 'main', array(
            'consumer1' => $consumer1 = $this->getConsumerMock('consumer1')
        ), array(
            'service_parameter' => 'service',
            'service_extra_parameter' => 'service_extra',
            'target_path_parameter' => '_target_path',
        ));

        $actual = $serviceManager->setDefaults();

        $this->assertTrue($actual);
        $this->assertEquals(false, $sessionMock->get('_target/service'));

        $actual = $serviceManager->setDefaults();

        $this->assertFalse($actual);
    }

    /**
     *
     */
    public function testSessionClear()
    {
        $sessionMock = $this->getSessionMock();
        $sessionMock->set('_target/service', 'consumer1');
        $sessionMock->set('_security.main.target_path', 'http://consumer1.com/');

        $serviceManager = new ServiceManager(new RequestStack(), $sessionMock, 'main', array(
            'consumer1' => $consumer1 = $this->getConsumerMock('consumer1')
        ), array(
            'service_parameter' => 'service',
            'service_extra_parameter' => 'service_extra',
            'target_path_parameter' => '_target_path',
        ));

        $this->assertEquals('consumer1', $sessionMock->get('_target/service'));
        $this->assertEquals('http://consumer1.com/', $sessionMock->get('_security.main.target_path'));

        $serviceManager->clear();

        $this->assertNull($sessionMock->get('_target/service'));
        $this->assertNull($sessionMock->get('_security.main.target_path'));
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
