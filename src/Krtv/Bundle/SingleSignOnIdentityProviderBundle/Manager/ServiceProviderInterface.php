<?php

namespace Krtv\Bundle\SingleSignOnIdentityProviderBundle\Manager;

/**
 * Interface ServiceProviderInterface
 * @package Krtv\Bundle\SingleSignOnIdentityProviderBundle\Manager
 */
interface ServiceProviderInterface
{
    /**
     * @return string
     */
    public function getName();

    /**
     * @param array $parameters
     * @return string
     */
    public function getServiceIndexUrl($parameters = array());

    /**
     * @param array $parameters
     * @return string
     */
    public function getServiceLogoutUrl($parameters = array());
}
