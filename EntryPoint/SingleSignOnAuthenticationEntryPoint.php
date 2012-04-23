<?php

namespace FM\SingleSignOnBundle\EntryPoint;

use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Security\Http\EntryPoint\AuthenticationEntryPointInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

class SingleSignOnAuthenticationEntryPoint implements AuthenticationEntryPointInterface
{
    protected $options;
    protected $defaults;

    public function __construct(array $options = array(), $container)
    {
        $this->options = new ParameterBag($options);
        $this->container = $container;
    }

    public function start(Request $request, AuthenticationException $authException = null)
    {
        $host = $this->container->getParameter('fm_single_sign_on_host');
        $path = rtrim($this->container->getParameter('fm_single_sign_on_login_path'), '/');
        $checkPath = $this->options->get('check_path');

        $targetPathParameter = $this->options->get('target_path_parameter');

        $redirectUri = $request->getUriForPath($checkPath);

        // make sure we keep the target path after login
        if ($targetUrl = $this->determineTargetUrl($request)) {
            $redirectUri = sprintf('%s/?%s=%s', rtrim($redirectUri, '/'), $targetPathParameter, rawurlencode($targetUrl));
        }

        $loginUrl = sprintf('http://%s%s/?%s=%s', $host, $path, $targetPathParameter, rawurlencode($redirectUri));

        return new RedirectResponse($loginUrl);
    }

    /**
     * @see Symfony\Component\Security\Http\Firewall\AbstractAuthenticationListener:determineTargetUrl
     */
    protected function determineTargetUrl(Request $request)
    {
        if ($this->options->get('always_use_default_target_path') === true) {
            return $this->options->get('default_target_path');
        }

        if ($targetUrl = $request->get($this->options->get('target_path_parameter'), null, true)) {
            return $targetUrl;
        }

        $session = $request->getSession();
        if ($targetUrl = $session->get('_security.target_path')) {
            $session->remove('_security.target_path');

            return $targetUrl;
        }

        if ($this->options->get('use_referer') && $targetUrl = $request->headers->get('Referer')) {
            return $targetUrl;
        }

        return $this->options->get('default_target_path');
    }
}