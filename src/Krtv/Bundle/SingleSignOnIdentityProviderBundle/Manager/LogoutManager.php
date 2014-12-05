<?php

namespace Krtv\Bundle\SingleSignOnIdentityProviderBundle\Manager;

use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\RouterInterface;

/**
 * Class LogoutManager
 * @package Krtv\Bundle\SingleSignOnIdentityProviderBundle\Manager
 */
class LogoutManager
{
    const SERVICE_SESSION_NS = '_logout/processed';

    /**
     * @var RouterInterface
     */
    private $router;

    /**
     * @var SessionInterface
     */
    private $session;

    /**
     * @var ServiceManager
     */
    private $serviceManager;

    /**
     * @param ServiceManager $serviceManager
     * @param SessionInterface $session
     * @param RouterInterface $router
     */
    public function __construct(ServiceManager $serviceManager, SessionInterface $session, RouterInterface $router)
    {
        $this->serviceManager = $serviceManager;
        $this->session = $session;
        $this->router = $router;
    }

    /**
     * @return string
     */
    public function getNextLogoutUrl()
    {
        $referrerService = $this->serviceManager->getRequestService();

        if ($referrerService !== null) {
            $this->addProcessedService($referrerService);
        }

        $availableServices = $this->serviceManager->getServices();
        $completedServices = $this->getCompletedServices();

        $nextService = null;
        foreach ($availableServices as $service) {
            if (in_array($service, $completedServices)) {
                continue;
            }

            $nextService = $service;

            break;
        }

        if ($nextService !== null) {
            $serviceManager = $this->serviceManager->getServiceManager($nextService);

            return $serviceManager->getServiceLogoutUrl();
        }

        return $this->router->generate('_security_logout');
    }

    /**
     * @param $service
     */
    private function addProcessedService($service)
    {
        $services = $this->getCompletedServices();
        $services[$service] = $service;

        $this->session->set($this->getSessionKey(), $services);
    }

    /**
     * @return mixed
     */
    private function getCompletedServices()
    {
        return $this->session->get($this->getSessionKey(), array());
    }

    /**
     * @return string
     */
    private function getSessionKey()
    {
        return static::SERVICE_SESSION_NS;
    }
}