<?php

namespace Krtv\Bundle\SingleSignOnIdentityProviderBundle\Controller;

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

        $otpParameter = $this->container->getParameter('krtv_single_sign_on_identity_provider.otp_parameter');

        $user = $this->get('security.context')->getToken()->getUser();

        $otpOrmManager = $this->get('krtv_single_sign_on_identity_provider.security.authentication.otp_manager.orm');
        $otpEncoder = $this->get('krtv_single_sign_on_identity_provider.security.authentication.encoder');

        $expires = microtime(true) + 300; // expires in 5 minutes
        $value = $otpEncoder->generateOneTimePasswordValue($user->getUsername(), $expires);
        $otp = $otpOrmManager->create($value);

        $redirectUri = $request->get('_target_path');
        $redirectUri .= sprintf('&%s=%s', $otpParameter, rawurlencode($otp));
        $redirectUri = $uriSigner->sign($redirectUri);

        return $this->get('security.http_utils')->createRedirectResponse($request, $redirectUri);
    }
}