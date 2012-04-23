SingleSignOnBundle
==================

This bundle provides an easy way to integrate a single-sign-on in your website. It uses an existing ('main') firewall for the actual authentication,
and redirects all configured SSO-routes to authenticate via a one-time-password.


Installation
------------
Installation is done the usual Symfony2 way:

### Step 1: Download

Add to deps:

```
[FMSingleSignOnBundle]
    git=http://github.com/financial-media/SingleSignOnBundle.git
    target=bundles/FM/SingleSignOnBundle
```

And run `bin/vendors install`


### Step 2: Autoload

Add the folowing entry to your autoloader:

``` php
<?php
// app/autoload.php

$loader->registerNamespaces(array(
    // ...

    'FM'          => __DIR__.'/../vendor/bundles',
));
```

### Step 3: Register

Enable the bundle in the kernel:

``` php
<?php
// app/AppKernel.php

public function registerBundles()
{
    $bundles = array(
        // ...

        new FM\SingleSignOnBundle\FMSingleSignOnBundle(),
    );
}
```


### Step 4: Configure

Enable sso-routes:

``` yaml
# app/config/routing.yml:
sso:
    resource: .
    type:     sso

otp:
    # this needs to be the same as the check_path, specified later on in security.yml
    pattern: /otp/
```

Register the security factory:

``` yaml
# app/config/security.yml:

security:
    factories:
        - "%kernel.root_dir%/../vendor/bundles/FM/SingleSignOnBundle/Resources/config/services.xml"
```

The bundle relies on an existing firewall to provide the actual authentication.
To do this, you have to configure the single-sign-on login path to be behind that firewall,
and make sure you need to be authenticated to access that route.

``` yaml
# app/config/config.yml:
fm_single_sign_on:
    host: mydomain.com
    login_path: /sso/

```

``` yaml
# app/config/security.yml
security:
    access_control:
        -
            host: mydomain.com
            path: ^/sso/$
            roles: [IS_AUTHENTICATED_FULLY]
```

``` yaml
# app/config/security.yml:

security:
    firewalls:
        main:
            pattern:   ^/
```

This makes sure the user has to authenticate first (using a login form).

Now for each firewall (other than the main one), you can configure single-sign-on authentication using a one-time-password.
The only thing you have to provide is a path and user provider. Everything else is handled by the bundle.

``` yaml
# app/config/security.yml:

security:
    firewalls:
        sso:
            pattern:                 ^/
            fm_sso:
                provider:            main
                check_path:          /otp/ # path where otp will be authenticated
```

That's it, you're done!


Domain restriction
------------------
Because we're working with multiple domains here, it's wise to configure the firewalls to only work for specific domains.
Say we have domain A and B. Domain A is where the single-sign-on is done (`main` firewall), and domain B is authenticated by the one-time-password (`sso` firewall).
We can use a request matcher service that confines the firewalls to specific domains. First we have to configure the request matchers:

``` yaml
# app/config/security.yml

security:
    firewalls:
        main:
            request_matcher: my.security.request_matcher.main
        sso:
            request_matcher: my.security.request_matcher.sso
```

Now we can implement them as services:

``` yaml
# src/MyAwesomeBundle/Resources/config/services.yml

services:
    my.security.request_matcher.main
        class: %security.matcher.class%
        arguments: ["/", "domain-a.com"]

    my.security.request_matcher.sso
        class: %security.matcher.class%
        arguments: ["/", "domain-b.com"]
```


Issues / TODO's
---------------
# Find a way to invalidate SSO sessions when the 'main' session is invalidated.
# Fix the hard-coded `_otp` parameter in [OneTimePasswordListener](https://github.com/financial-media/SingleSignOnBundle/blob/master/Firewall/OneTimePasswordListener.php#L17)