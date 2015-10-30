<?php

namespace Krtv\Bundle\SingleSignOnIdentityProviderBundle\Controller;

use Krtv\Bundle\SingleSignOnIdentityProviderBundle\Event\SsoAuthorizedEvent;
use Krtv\Bundle\SingleSignOnIdentityProviderBundle\Event\SsoEvents;
use Krtv\Bundle\SingleSignOnIdentityProviderBundle\Manager\ServiceManager;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * Class SingleSignOnController
 * @package Krtv\Bundle\SingleSignOnIdentityProviderBundle\Controller
 */
class SingleSignOnController extends Controller
{
    /**
     * @param Request $request
     * @return RedirectResponse
     */
    public function ssoLoginAction(Request $request)
    {
        $httpUtils = $this->get('krtv_single_sign_on_identity_provider.security.http_utils');

        if (!$httpUtils->hasTargetPath($request)) {
            throw new BadRequestHttpException('Target path not specified');
        }

        if (false === $httpUtils->checkUrl($request->getSchemeAndHttpHost().$request->getRequestUri())) {
            throw new BadRequestHttpException('Malformed uri');
        }

        if (false === $this->get('security.authorization_checker')->isGranted('ROLE_USER') && $request->get('_failure_path')) {
            return $httpUtils->createRedirectResponse($request, $request->get('_failure_path'));
        } elseif (false === $this->get('security.authorization_checker')->isGranted('ROLE_USER')) {
            throw new AccessDeniedException();
        }

        $this->get('event_dispatcher')->dispatch(SsoEvents::SSO_AUTHORIZED, new SsoAuthorizedEvent());

        $otpOrmManager = $this->get('krtv_single_sign_on_identity_provider.security.authentication.otp_manager.orm');
        $otpEncoder = $this->get('krtv_single_sign_on_identity_provider.security.authentication.encoder');

        $otp = $otpOrmManager->create(
            $otpEncoder->generateOneTimePasswordValue($this->getUser()->getUsername(), microtime(true) + 300)
        );

        return $httpUtils->createSignedRedirectResponse($request, $httpUtils->createWrappedTargetPath($request, $otp));
    }

    /**
     * @param Request $request
     * @return RedirectResponse
     */
    public function ssoLogoutAction(Request $request)
    {
        $serviceManager = $this->get('krtv_single_sign_on_identity_provider.manager.service_manager');
        $logoutManager = $this->get('krtv_single_sign_on_identity_provider.manager.logout_manager');
        $httpUtils = $this->get('krtv_single_sign_on_identity_provider.security.http_utils');

        if (!$serviceManager->getRequestService()) {
            $serviceManager->setDefaults();
        }
 
        return $httpUtils->createRedirectResponse($request, $logoutManager->getNextLogoutUrl());
    }
}
