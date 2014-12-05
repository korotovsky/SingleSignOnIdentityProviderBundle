<?php

namespace Krtv\Bundle\SingleSignOnIdentityProviderBundle\Routing;

use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\Config\Loader\LoaderResolverInterface;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

/**
 * Class SsoRoutesLoader
 * @package Krtv\Bundle\SingleSignOnIdentityProviderBundle\Routing
 */
class SsoRoutesLoader implements LoaderInterface
{
    /**
     * @var string
     */
    private $ssoHost;

    /**
     * @var string
     */
    private $ssoLoginPath;

    /**
     * @var string
     */
    private $ssoLogoutPath;

    /**
     * @param $ssoHost
     * @param $ssoLoginPath
     * @param $ssoLogoutPath
     */
    public function __construct($ssoHost, $ssoLoginPath, $ssoLogoutPath)
    {
        $this->ssoHost = $ssoHost;
        $this->ssoLoginPath = $ssoLoginPath;
        $this->ssoLogoutPath = $ssoLogoutPath;
    }

    /**
     * @param mixed $resource
     * @param null $type
     * @return RouteCollection
     */
    public function load($resource, $type = null)
    {
        $routes = new RouteCollection();

        $route = new Route($this->ssoLoginPath, array('_controller' => 'KrtvSingleSignOnIdentityProviderBundle:SingleSignOn:ssoLogin'));
        $routes->add('sso_login_path', $route);

        $route = new Route($this->ssoLogoutPath, array('_controller' => 'KrtvSingleSignOnIdentityProviderBundle:SingleSignOn:ssoLogout'));
        $routes->add('sso_logout_path', $route);

        return $routes;
    }

    /**
     * @param mixed $resource
     * @param null $type
     * @return bool
     */
    public function supports($resource, $type = null)
    {
        return $type === 'sso';
    }

    /**
     * @return void
     */
    public function getResolver()
    {
    }

    /**
     * Irrelevant to us, since we don't need a resolver
     *
     * @param LoaderResolverInterface $resolver
     */
    public function setResolver(LoaderResolverInterface $resolver)
    {
    }
}