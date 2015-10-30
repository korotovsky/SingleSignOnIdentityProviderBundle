CHANGELOG
===================

This changelog references the relevant changes (bug, feature and security fixes)

* 0.3.0 (2015-30-10)
 * Interface of `ServiceManager` has been changed. It's now stateless, but has a dependency on `RequestStack`.
 * Ability to pass `_target_path` to IdP. `_target_path` should be signed to be saved into session.
 * Ability to pass service extra data to IdP. Extra data should be signed.
 * `service_parameter` and `service_extra_parameter` are configurable.

* 0.2.3 (2015-03-09)
 * Added new class `SsoEvents` with `SSO_AUTHORIZED` event.
 * Extracted some code from controller to the new service `krtv_single_sign_on_identity_provider.security.http_utils`.

* 0.2.2 (2015-06-07)
 * Fixed bug with logout when logout started from IdP.

* 0.2.1 (2014-12-12)
 * `_target_path` is now configurable #6

* 0.2.0 (2014-23-08)

 * feature #11 Implemented. Fixed 404 response when OTP is invalid, added correct on failure redirect with _otp_failure=1 parameter.
 * feature #1 Implemented. EntryPoint::start() and OneTimePasswordListener::attemptAuthentication() are now signed.

* 0.1.2 (2014-15-08)

 * bug #9 Fixed.
 * bug #6 Fixed.
 * feature #5 Implemented

* 0.1.1 (2014-14-08)

 * bug #4 Fixed exception "request.CRITICAL: Exception: A one-time-password for hash "<hash>" already exists (uncaught exception)", Fixed typos PHP Doc (korotovsky)
 * 4f5b2d1c420ac3b4eb5a72d839cd64b4a3bc8d7d: Removed dependency on service container in EntryPoint listener, Fixed typos in service.xml configuration (korotovsky)
 * 4f5b2d1c420ac3b4eb5a72d839cd64b4a3bc8d7d: Removed 2-nd parameter $expires from OneTimePasswordManager::create() (korotovsky)
 * bd68fb566a233c6628e992f09af7e6bba285c682: Fixed $created value for OTP entity (korotovsky)

* 0.1.0 (2014-23-11)
 * Split components to library
