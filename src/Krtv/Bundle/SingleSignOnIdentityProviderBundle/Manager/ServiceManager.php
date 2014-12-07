<?php

namespace Krtv\Bundle\SingleSignOnIdentityProviderBundle\Manager;

use Symfony\Component\HttpFoundation\Session\SessionInterface;

/**
 * Class ServiceManager
 * @package Krtv\Bundle\SingleSignOnIdentityProviderBundle\Manager
 */
class ServiceManager
{
    const SERVICE_SESSION_NS = '_target';
    const SERVICE_PARAM = 'service';

    /**
     * @var SessionInterface
     */
    private $session;

    /**
     * @var string
     */
    private $firewall;

    /**
     * @var ServiceProviderInterface[]
     */
    private $services;

    /**
     * @var string|null
     */
    private $requestService;

    /**
     * @param SessionInterface $session
     * @param string $firewall
     * @param array $services
     */
    public function __construct(SessionInterface $session, $firewall = 'main', array $services = array())
    {
        if (count($services) === 0) {
            throw new \RuntimeException('No ServiceProvider managers found. Make sure that you have at least one ServiceProvider manager tagged with "sso.service_provider"');
        }

        $this->session = $session;
        $this->firewall = $firewall;
        $this->services = $services;
    }

    /**
     * @return array
     */
    public function getServices()
    {
        return array_keys($this->services);
    }

    /**
     * @param $service
     * @return ServiceProviderInterface
     */
    public function getServiceManager($service)
    {
        if (!isset($this->services[$service])) {
            throw new \InvalidArgumentException('Unknown service ' . $service);
        }

        return $this->services[$service];
    }

    /**
     * @return string|null
     */
    public function getSessionService()
    {
        return $this->session->get($this->getSessionKey());
    }

    /**
     * @return string|null
     */
    public function getRequestService()
    {
        return $this->requestService;
    }

    /**
     * @param string $requestService
     */
    public function setRequestService($requestService)
    {
        $this->requestService = $requestService;
    }

    /**
     * Save target service name and write to _security.ID.target_path
     *
     * @param $service
     * @return bool
     */
    public function setSessionService($service)
    {
        $serviceManager = $this->getServiceManager($service);

        $this->session->set($this->getSessionKey(), $service);
        $this->session->set($this->getSecurityKey(), $serviceManager->getServiceIndexUrl());

        return true;
    }

    /**
     * @return bool
     */
    public function setDefaults()
    {
        if ($this->getSessionService() === false) {
            return false;
        }

        $this->session->set($this->getSessionKey(), false);

        return true;
    }

    /**
     * Clear services management session variables
     */
    public function clear()
    {
        $this->session->remove($this->getSessionKey());
        $this->session->remove($this->getSecurityKey());
    }

    /**
     * @return string
     */
    private function getSessionKey()
    {
        return sprintf('%s/%s', static::SERVICE_SESSION_NS, static::SERVICE_PARAM);
    }

    /**
     * @return string
     */
    private function getSecurityKey()
    {
        return sprintf('_security.%s.target_path', $this->firewall);
    }
}
