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
     * @param $ssoHost
     * @param $ssoLoginPath
     */
    public function __construct($ssoHost, $ssoLoginPath)
    {
        $this->ssoHost = $ssoHost;
        $this->ssoLoginPath   = $ssoLoginPath;
    }

    /**
     * @param mixed $resource
     * @param null $type
     * @return RouteCollection
     */
    public function load($resource, $type = null)
    {
        $route1 = new Route($this->ssoLoginPath, array(
            '_controller' => 'KrtvSingleSignOnIdentityProviderBundle:SingleSignOn:ssoLogin'
        ), array(), array(), $this->ssoHost);

        $routes = new RouteCollection();
        $routes->add('sso_login_path', $route1);

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
