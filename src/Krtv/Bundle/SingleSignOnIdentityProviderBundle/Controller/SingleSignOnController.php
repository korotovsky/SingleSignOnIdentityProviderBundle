<?php

namespace Krtv\Bundle\SingleSignOnIdentityProviderBundle\Controller;

use Krtv\Bundle\SingleSignOnIdentityProviderBundle\Manager\ServiceManager;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * Class SingleSignOnController
 * @package Krtv\Bundle\SingleSignOnIdentityProviderBundle\Controller
 */
class SingleSignOnController extends Controller
{
    /**
     * @param Request $request
     * @return RedirectResponse
     * @throws \Exception
     */
    public function ssoLoginAction(Request $request)
    {
        if ($request->get('_target_path') == '') {
            throw new BadRequestHttpException('Target path not specified');
        }

        $uriSigner = $this->get('krtv_single_sign_on_identity_provider.uri_signer');
        if (false === $uriSigner->check($request->getSchemeAndHttpHost().$request->getRequestUri())) {
            throw new BadRequestHttpException('Malformed uri');
        }

        $httpUtils = $this->get('security.http_utils');

        $securityContext = $this->get('security.context');
        if (false === $securityContext->isGranted('ROLE_USER') && $request->get('_failure_path')) {
            return $httpUtils->createRedirectResponse($request, $request->get('_failure_path'));
        } elseif (false === $securityContext->isGranted('ROLE_USER')) {
            return $httpUtils->createRedirectResponse($request, $this->generateUrl('_security_login'));
        }

        $otpParameter = $this->container->getParameter('krtv_single_sign_on_identity_provider.otp_parameter');
        $otpOrmManager = $this->get('krtv_single_sign_on_identity_provider.security.authentication.otp_manager.orm');
        $otpEncoder = $this->get('krtv_single_sign_on_identity_provider.security.authentication.encoder');

        $user = $this->get('security.context')->getToken()->getUser();

        $expires = microtime(true) + 300; // expires in 5 minutes
        $value = $otpEncoder->generateOneTimePasswordValue($user->getUsername(), $expires);
        $otp = $otpOrmManager->create($value);

        $redirectUri = $request->get('_target_path');
        $redirectUri .= sprintf('&%s=%s', $otpParameter, rawurlencode($otp));
        $redirectUri = $uriSigner->sign($redirectUri);

        return $httpUtils->createRedirectResponse($request, $redirectUri);
    }

    /**
     * @param Request $request
     * @return RedirectResponse
     * @throws \Exception
     */
    public function ssoLogoutAction(Request $request)
    {
        $serviceManager = $this->get('krtv_single_sign_on_identity_provider.manager.service_manager');
        $logoutManager = $this->get('krtv_single_sign_on_identity_provider.manager.logout_manager');

        if (!$request->get(ServiceManager::SERVICE_PARAM)) {
            $serviceManager->setDefaults();
        }

        $httpUtils = $this->get('security.http_utils');

        return $httpUtils->createRedirectResponse($request, $logoutManager->getNextLogoutUrl());
    }
}