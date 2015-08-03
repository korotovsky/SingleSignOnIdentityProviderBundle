<?php

namespace Krtv\Bundle\SingleSignOnIdentityProviderBundle\Security\Http;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\UriSigner;
use Symfony\Component\Security\Http;

/**
 * Class HttpUtils
 * @package Krtv\Bundle\SingleSignOnIdentityProviderBundle\Security\Http
 */
class HttpUtils
{
    /**
     * @var UriSigner
     */
    private $uriSigner;

    /**
     * @var Http\HttpUtils
     */
    private $httpUtils;

    /**
     * @var string
     */
    private $otpParameter;

    /**
     * @var string
     */
    private $targetPathParameter;

    /**
     * @param UriSigner      $uriSigner
     * @param Http\HttpUtils $httpUtils
     * @param string         $otpParameter
     * @param string         $targetPathParameter
     */
    public function __construct(UriSigner $uriSigner, Http\HttpUtils $httpUtils, $otpParameter, $targetPathParameter)
    {
        $this->uriSigner = $uriSigner;
        $this->httpUtils = $httpUtils;

        $this->otpParameter = $otpParameter;
        $this->targetPathParameter = $targetPathParameter;
    }

    /**
     * @param Request $request
     * @param string  $path
     * @param int     $status
     * @return RedirectResponse
     */
    public function createRedirectResponse(Request $request, $path, $status = 302)
    {
        return $this->httpUtils->createRedirectResponse($request, $path, $status);
    }

    /**
     * @param Request $request
     * @param string  $path
     * @param int     $status
     * @return RedirectResponse
     */
    public function createSignedRedirectResponse(Request $request, $path, $status = 302)
    {
        return $this->httpUtils->createRedirectResponse($request, $this->uriSigner->sign($path), $status);
    }

    /**
     * @param Request $request
     * @param string  $otp
     * @return string
     */
    public function createWrappedTargetPath(Request $request, $otp)
    {
        return sprintf('%s&%s=%s', $request->get($this->targetPathParameter), $this->otpParameter, rawurlencode($otp));
    }

    /**
     * @param Request $request
     * @return bool
     */
    public function hasTargetPath(Request $request)
    {
        return $request->get($this->targetPathParameter) != '';
    }

    /**
     * @param string $uri
     * @return bool
     */
    public function checkUrl($uri)
    {
        return $this->uriSigner->check($uri);
    }
}
