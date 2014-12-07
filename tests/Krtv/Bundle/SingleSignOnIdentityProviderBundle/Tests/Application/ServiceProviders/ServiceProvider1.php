<?php

namespace Krtv\Bundle\SingleSignOnIdentityProviderBundle\Tests\Application\ServiceProviders;

use Krtv\Bundle\SingleSignOnIdentityProviderBundle\Manager\ServiceProviderInterface;

/**
 * Class ServiceProvider1
 * @package Krtv\Bundle\S\Tests\Application\ServiceProviders
 */
class ServiceProvider1 implements ServiceProviderInterface
{
    /**
     * @return string
     */
    public function getName()
    {
        return 'consumer1';
    }

    /**
     * @param array $parameters
     * @return string
     */
    public function getServiceIndexUrl($parameters = array())
    {
        return 'http://consumer1.com/';
    }

    /**
     * @param array $parameters
     * @return string
     */
    public function getServiceLogoutUrl($parameters = array())
    {
        return 'http://consumer1.com/logout';
    }
}
