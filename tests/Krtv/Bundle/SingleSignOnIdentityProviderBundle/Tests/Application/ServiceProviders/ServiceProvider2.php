<?php

namespace Krtv\Bundle\SingleSignOnIdentityProviderBundle\Tests\Application\ServiceProviders;

use Krtv\Bundle\SingleSignOnIdentityProviderBundle\Manager\ServiceProviderInterface;

/**
 * Class ServiceProvider2
 * @package Krtv\Bundle\SingleSignOnIdentityProviderBundle\Tests\Application\ServiceProviders
 */
class ServiceProvider2 implements ServiceProviderInterface
{
    /**
     * @return string
     */
    public function getName()
    {
        return 'consumer2';
    }

    /**
     * @param array $parameters
     * @return string
     */
    public function getServiceIndexUrl($parameters = array())
    {
        return 'http://consumer2.com/';
    }

    /**
     * @param array $parameters
     * @return string
     */
    public function getServiceLogoutUrl($parameters = array())
    {
        return 'http://consumer2.com/logout';
    }
}
