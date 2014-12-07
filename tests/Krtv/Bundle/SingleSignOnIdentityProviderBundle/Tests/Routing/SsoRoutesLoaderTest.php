<?php

namespace Krtv\Bundle\SingleSignOnIdentityProviderBunde\Tests\Routing;

use Krtv\Bundle\SingleSignOnIdentityProviderBundle\KrtvSingleSignOnIdentityProviderBundle;
use Krtv\Bundle\SingleSignOnIdentityProviderBundle\Routing\SsoRoutesLoader;

/**
 * Class KrtvSingleSignOnIdentityProviderBundleTest
 * @package Krtv\Bundle\SingleSignOnIdentityProviderBunde\Tests
 */
class KrtvSingleSignOnIdentityProviderBundleTest extends \PHPUnit_Framework_TestCase
{
    /**
     *
     */
    public function testRoutesLoadCount()
    {
        $routes = $this->getSsoRoutesLoader()->load('sso');

        $this->assertCount(2, $routes);
    }

    /**
     *
     */
    public function testRoutesLoaderSupports()
    {
        $this->assertTrue($this->getSsoRoutesLoader()->supports('.', 'sso'));
        $this->assertFalse($this->getSsoRoutesLoader()->supports('.', 'sso1'));
    }

    /**
     * @return SsoRoutesLoader
     */
    private function getSsoRoutesLoader()
    {
        return new SsoRoutesLoader('idp.example.com', '/sso/login', '/sso/logout');
    }
}