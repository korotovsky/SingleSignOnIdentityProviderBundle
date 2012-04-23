<?php

namespace FM\SingleSignOnBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class SingleSignOnController extends Controller
{
    public function ssoLoginAction(Request $request)
    {
        if ($request->get('_target_path') == '') {
            throw new \Exception('Target path not specified');
        }

        $otpParameter = $this->container->getParameter('fm_single_sign_on_otp_parameter');

        $user = $this->get('security.context')->getToken()->getUser();
        $encoder = $this->get('fm_sso.security.authentication.encoder');
        $om = $this->get('fm_sso.security.authentication.manager.otp');

        $expires = time() + 300; // expires in 5 minutes
        $value = $encoder->generateOneTimePasswordValue(get_class($user), $user->getUsername(), $expires, $user->getPassword());
        $otp = $om->create($value, $expires);

        $redirectUri = $request->get('_target_path');
        $redirectUri .= sprintf('&%s=%s', $otpParameter, rawurlencode($otp));

        return $this->redirect($redirectUri);
    }
}