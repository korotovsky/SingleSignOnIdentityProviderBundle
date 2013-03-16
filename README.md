Disclaimer
--------
I am by no means a security expert. I'm not bad at it either, but I cannot vouch for the security of this bundle. 
You can use this in production if you want, but please do so at your own risk. 
That said, if you'd like to contribute to make this bundle better/safer, you can always [create an issue](https://github.com/financial-media/FMSingleSignOnBundle/issues) or send [a pull request](https://github.com/financial-media/FMSingleSignOnBundle/pulls).

Description
-----------
This bundle provides an easy way to integrate a single-sign-on in your website. It uses an existing ('main') firewall for the actual authentication,
and redirects all configured SSO-routes to authenticate via a one-time-password.


Installation
------------
Install using composer:

```
php composer.phar require "fm/sso-bundle" 
```

Enable the bundle in the kernel:

``` php
// app/AppKernel.php
$bundles[] = new FM\SingleSignOnBundle\FMSingleSignOnBundle();
```

Configuration
-------------

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
            pattern: ^/
```

This makes sure the user has to authenticate first (using a login form).

Now for each firewall (other than the main one), you can configure single-sign-on authentication using a one-time-password.
The only thing you have to provide is a path and user provider. Everything else is handled by the bundle.

``` yaml
# app/config/security.yml:
security:
    firewalls:
        sso:
            pattern: ^/
            fm_sso:
                provider: main
                check_path: /otp/ # path where otp will be authenticated
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
