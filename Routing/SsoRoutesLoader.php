<?php

namespace FM\SingleSignOnBundle\Routing;

use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\Config\Loader\LoaderResolver;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

class SsoRoutesLoader implements LoaderInterface
{
    private $ssoHost;
    private $ssoPath;

    public function __construct($ssoHost, $ssoPath)
    {
        $this->ssoHost = $ssoHost;
        $this->ssoPath   = $ssoPath;
    }

    public function load($resource, $type = null)
    {
        $routes = new RouteCollection();

        $route = new Route($this->ssoPath, array('_controller' => 'FMSingleSignOnBundle:SingleSignOn:ssoLogin'));
        $routes->add('sso_login_path', $route);

        return $routes;
    }

    public function supports($resource, $type = null)
    {
        return $type === 'sso';
    }

    public function getResolver()
    {
    }

    public function setResolver(LoaderResolver $resolver)
    {
        // irrelevant to us, since we don't need a resolver
    }
}