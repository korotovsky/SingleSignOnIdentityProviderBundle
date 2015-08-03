<?php

namespace Krtv\Bundle\SingleSignOnIdentityProviderBunde\Tests\Routing;

use Krtv\Bundle\SingleSignOnIdentityProviderBundle\Security\Http\HttpUtils;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\UriSigner;

/**
 * Class KrtvSingleSignOnIdentityProviderBundleTest
 * @package Krtv\Bundle\SingleSignOnIdentityProviderBunde\Tests
 */
class HttpUtilsTest extends \PHPUnit_Framework_TestCase
{
    /**
     *
     */
    public function testCreateRedirectResponse()
    {
        $request = new Request();
        $httpUtils = $this->getHttpUtils();

        $response = $httpUtils->createRedirectResponse($request, '/foo');

        $this->assertInstanceOf('Symfony\Component\HttpFoundation\RedirectResponse', $response);
        $this->assertEquals('/foo', $response->getTargetUrl());
    }

    /**
     *
     */
    public function testCreateSignedRedirectResponse()
    {
        $request = new Request();
        $httpUtils = $this->getHttpUtils();

        $response = $httpUtils->createSignedRedirectResponse($request, '/foo');

        $this->assertInstanceOf('Symfony\Component\HttpFoundation\RedirectResponse', $response);
        $this->assertEquals('/foo?_hash=D4ayURdJUtGtSe6A7nG5j1Mnhy2mgSUgKpr%2BjLyZmvI%3D', $response->getTargetUrl());
    }

    /**
     *
     */
    public function testCreateWrappedTargetPath()
    {
        $request = new Request(array('_target_path' => '/foo'));
        $httpUtils = $this->getHttpUtils();

        $wrappedTargetPath = $httpUtils->createWrappedTargetPath($request, 'xyz');

        $this->assertEquals('/foo&_otp=xyz', $wrappedTargetPath);
    }

    /**
     *
     */
    public function testHasTargetPath()
    {
        $request = new Request();
        $httpUtils = $this->getHttpUtils();

        $expected = false;
        $actual = $httpUtils->hasTargetPath($request);

        $this->assertEquals($expected, $actual);

        $request = new Request(array('_target_path' => '/foo'));
        $httpUtils = $this->getHttpUtils();

        $expected = true;
        $actual = $httpUtils->hasTargetPath($request);

        $this->assertEquals($expected, $actual);
    }

    /**
     *
     */
    public function testCheckUrl()
    {
        $httpUtils = $this->getHttpUtils();

        $expected = true;
        $actual = $httpUtils->checkUrl('/foo?_hash=D4ayURdJUtGtSe6A7nG5j1Mnhy2mgSUgKpr%2BjLyZmvI%3D');

        $this->assertEquals($expected, $actual);

        $expected = false;
        $actual = $httpUtils->checkUrl('/foo?_hash=D4ayURdJUt__BROKEN__SIGNATURE__yZmvI%3D');

        $this->assertEquals($expected, $actual);
    }

    /**
     * @return HttpUtils
     */
    private function getHttpUtils()
    {
        $signerMock = $this->getMockBuilder('Symfony\Component\HttpKernel\UriSigner')
            ->setConstructorArgs(array('secret'))
            ->enableProxyingToOriginalMethods()
            ->getMock();
        $httpUtilsMock = $this->getMockBuilder('Symfony\Component\Security\Http\HttpUtils')
            ->setMethods(array('generateUri'))
            ->disableOriginalConstructor()
            ->getMock();
        $httpUtilsMock->expects($this->any())
            ->method('generateUri')
            ->willReturnCallback(function(Request $request, $path) {
                return $path;
            });

        return new HttpUtils($signerMock, $httpUtilsMock, '_otp', '_target_path');
    }
}
