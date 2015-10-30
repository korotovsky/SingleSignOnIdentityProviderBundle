<?php

namespace Krtv\Bundle\SingleSignOnIdentityProviderBundle\Manager;

use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

/**
 * Class ServiceManager
 * @package Krtv\Bundle\SingleSignOnIdentityProviderBundle\Manager
 */
class ServiceManager
{
    const SERVICE_SESSION_NS    = '_target';

    /**
     * @var RequestStack
     */
    private $requestStack;

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
     * @var array
     */
    private $options;

    /**
     * @param RequestStack $requestStack
     * @param SessionInterface $session
     * @param string $firewall
     * @param array $services
     * @param array $options
     */
    public function __construct(RequestStack $requestStack, SessionInterface $session, $firewall = 'main', array $services = array(), $options = array())
    {
        if (count($services) === 0) {
            throw new \RuntimeException('No ServiceProvider managers found. Make sure that you have at least one ServiceProvider manager tagged with "sso.service_provider"');
        }

        $this->requestStack = $requestStack;
        $this->session = $session;
        $this->firewall = $firewall;
        $this->services = $services;
        $this->options = $options;
    }

    /**
     * @return string
     */
    public function getServiceParameter()
    {
        return $this->options['service_parameter'];
    }

    /**
     * @return string
     */
    public function getServiceExtraParameter()
    {
        return $this->options['service_extra_parameter'];
    }

    /**
     * @return string
     */
    public function getTargetPathParameter()
    {
        return $this->options['target_path_parameter'];
    }

    /**
     * @return string[]
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
    public function getRequestService()
    {
        $request = $this->requestStack->getMasterRequest();

        return $request->get($this->getServiceParameter());
    }

    /**
     * @return array|null
     */
    public function getRequestServiceExtra()
    {
        $request = $this->requestStack->getMasterRequest();

        return $request->get($this->getServiceExtraParameter());
    }

    /**
     * Get target path from _security.ID.target_path.
     *
     * @return array|null
     */
    public function getRequestTargetPath()
    {
        $request = $this->requestStack->getMasterRequest();

        return $request->get($this->getTargetPathParameter());
    }

    /**
     * Get target service name.
     *
     * @return string|null
     */
    public function getSessionService()
    {
        return $this->session->get($this->getSessionKey());
    }

    /**
     * Get extra data.
     *
     * @return array|null
     */
    public function getSessionExtra()
    {
        return $this->session->get($this->getSessionExtraKey());
    }

    /**
     * Save target service name.
     *
     * @param $service
     * @return bool
     */
    public function setSessionService($service)
    {
        $this->session->set($this->getSessionKey(), $service);

        return true;
    }

    /**
     * Save target path to _security.ID.target_path.
     *
     * @param string $targetPath
     * @return bool
     */
    public function setSessionTargetPath($targetPath)
    {
        $this->session->set($this->getSessionTargetPathKey(), $targetPath);

        return true;
    }

    /**
     * Save extra data.
     *
     * @param array $extra
     *
     * @return bool
     */
    public function setSessionExtra(array $extra = array())
    {
        $this->session->set($this->getSessionExtraKey(), $extra);

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
     * Clear services management session variables.
     */
    public function clear()
    {
        $this->session->remove($this->getSessionKey());
        $this->session->remove($this->getSessionExtraKey());
        $this->session->remove($this->getSessionTargetPathKey());
    }

    /**
     * @return string
     */
    private function getSessionKey()
    {
        return sprintf('%s/%s', static::SERVICE_SESSION_NS, $this->getServiceParameter());
    }

    /**
     * @return string
     */
    private function getSessionExtraKey()
    {
        return sprintf('%s/%s', static::SERVICE_SESSION_NS, $this->getServiceExtraParameter());
    }

    /**
     * @return string
     */
    private function getSessionTargetPathKey()
    {
        return sprintf('_security.%s.target_path', $this->firewall);
    }
}
