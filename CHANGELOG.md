CHANGELOG
===================

This changelog references the relevant changes (bug, feature and security fixes)

* 1.0.0 (2014-23-11)
 * Split components to library

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
