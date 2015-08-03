<?php

namespace Krtv\Bundle\SingleSignOnIdentityProviderBundle\Event;

/**
 * Class SsoEvents
 * @package Krtv\Bundle\SingleSignOnIdentityProviderBundle\Event
 */
final class SsoEvents
{
    const SSO_AUTHORIZED = 'sso.identity_provider.sso_login_authorized';

    final private function __construct() {}
}
