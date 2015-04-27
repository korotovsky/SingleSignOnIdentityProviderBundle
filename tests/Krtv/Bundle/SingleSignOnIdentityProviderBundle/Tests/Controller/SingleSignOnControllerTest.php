<?php

namespace Krtv\Bundle\SingleSignOnIdentityProviderBundle\Controller;

use Krtv\Bundle\SingleSignOnIdentityProviderBundle\Tests\Application\AppKernel;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * Class SingleSignOnControllerTest
 * @package Krtv\Bundle\SingleSignOnIdentityProviderBundle\Controller
 */
class SingleSignOnControllerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Application
     */
    private static $application;

    /**
     * @var Request
     */
    private $request;

    /**
     * @var Response
     */
    private $response;

    /**
     * @param string $command
     * @param array $options
     * @return int
     */
    private static function runConsole($command, array $options = array())
    {
        $options['-e'] = 'test';
        $options['-q'] = null;

        $input = new ArrayInput(array_merge($options, array('command' => $command)));
        $result = self::$application->run($input);

        if (0 != $result) {
            throw new \RuntimeException(sprintf('Something has gone wrong, got return code %d for command %s', $result, $command));
        }

        return $result;
    }

    /**
     * It will run before any setUps and tests in given test suite
     * This hook will drop current schema, creat schema and load fixtures
     * then it will create a copy of the databse, so it will be used in the future tests in this suite
     */
    public static function setUpBeforeClass()
    {
        $kernel = new AppKernel('test', false);
        $kernel->boot();

        static::$application = new Application($kernel);
        static::$application->setAutoExit(false);
    }

    /**
     *
     */
    protected function setUp()
    {
        parent::setUp();

        static::runConsole('doctrine:schema:drop', array('-n' => true, '--force' => true));
        static::runConsole('doctrine:database:drop', array('-n' => true, '--force' => true));
        static::runConsole('doctrine:database:create', array('-n' => true));
        static::runConsole('doctrine:schema:create', array('-n' => true));

        $this->request = new Request();
        $this->request->headers->set('HTTP_HOST', 'idp.example.com');
        $this->request->server->set('SERVER_NAME', 'idp.example.com');
        $this->request->server->set('SERVER_PORT', 80);
    }

    /**
     *
     */
    protected function tearDown()
    {
        parent::tearDown();

        $this->request = null;
        $this->response = null;
    }

    /**
     *
     */
    protected function authenticate()
    {
        $this->request->headers->set('Authorization', sprintf('Basic %s', base64_encode('qwerty:ytrewq')));
        $this->request->headers->set('PHP_AUTH_USER', 'qwerty');
        $this->request->headers->set('PHP_AUTH_PW', 'ytrewq');
    }

    /**
     * @param string $requestUri
     * @return string
     */
    protected function getSignedUriHash($requestUri)
    {
        $signer = self::$application->getKernel()->getContainer()->get('krtv_single_sign_on_identity_provider.uri_signer');
        $signedUri = $signer->sign(sprintf('%s://%s%s', $this->request->getScheme(), $this->request->getHost(), $requestUri));

        parse_str($signedUri, $query);

        return $query['_hash'];
    }

    /**
     * @param string $method
     * @param string $url
     * @param array $params
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected function request($method, $url, array $params = array())
    {
        $this->request->server->set('REQUEST_URI', $url);
        $this->request->setMethod($method);

        switch ($method) {
            case 'GET':
                $this->request->query->add($params); break;
            case 'POST':
                $this->request->request->add($params); break;
            default;
        }

        return self::$application->getKernel()->handle($this->request);
    }

    /**
     *
     */
    public function testSsoLoginWithoutTargetPathException()
    {
        $this->response = $this->request('GET', '/sso/login/');

        $this->assertEquals(400, $this->response->getStatusCode());
    }

    /**
     *
     */
    public function testSsoLoginMalformedUriException()
    {
        $targetPath = 'http://consumer1.com/otp/validate/?_target_path=http://consumer1.com/';
        $requestUri = sprintf('/sso/login/?_target_path=%s', $targetPath);

        $hash = $this->getSignedUriHash($requestUri);

        $requestUri = sprintf('%s&_hash=%s', $requestUri, urlencode($hash) . 'BROKEN_HASH');

        $this->request->query->set('_target_path', $targetPath);
        $this->request->query->set('_hash', $hash . 'BROKEN_HASH');

        $this->response = $this->request('GET', $requestUri);

        $this->assertEquals(400, $this->response->getStatusCode());
    }

    /**
     *
     */
    public function testSsoLoginUnauthorizedRedirectToFailurePath()
    {
        $targetPath = 'http://consumer1.com/otp/validate/?_target_path=http://consumer1.com/';
        $failurePath = 'http://consumer1.com/?login_required=1';

        $requestUri = sprintf('/sso/login/?_target_path=%s&_failure_path=%s', $targetPath, $failurePath);

        $hash = $this->getSignedUriHash($requestUri);

        $requestUri = sprintf('%s&_hash=%s', $requestUri, urlencode($hash));

        $this->request->query->set('_target_path', $targetPath);
        $this->request->query->set('_failure_path', $failurePath);
        $this->request->query->set('_hash', $hash);

        $this->response = $this->request('GET', $requestUri);

        $this->assertEquals(302, $this->response->getStatusCode());
        $this->assertEquals('http://consumer1.com/?login_required=1', $this->response->headers->get('location'));
    }

    /**
     *
     */
    public function testSsoLoginUnauthorizedAccessDeniedExceptionCatchedByExceptionListener()
    {
        $targetPath = 'http://consumer1.com/otp/validate/?_target_path=http://consumer1.com/';

        $requestUri = sprintf('/sso/login/?_target_path=%s', $targetPath);

        $hash = $this->getSignedUriHash($requestUri);

        $requestUri = sprintf('%s&_hash=%s', $requestUri, urlencode($hash));

        $this->request->query->set('_target_path', $targetPath);
        $this->request->query->set('_hash', $hash);

        $this->response = $this->request('GET', $requestUri);

        $this->assertEquals(401, $this->response->getStatusCode());
        $this->assertEquals('Basic realm="Secured Demo Area"', $this->response->headers->get('www-authenticate'));
    }

    /**
     *
     */
    public function testSsoLoginWithRedirectToOtpValidation()
    {
        $targetPath = 'http://consumer1.com/otp/validate/?_target_path=http://consumer1.com/';

        $requestUri = sprintf('/sso/login/?_target_path=%s', $targetPath);

        $hash = $this->getSignedUriHash($requestUri);

        $requestUri = sprintf('%s&_hash=%s', $requestUri, urlencode($hash));

        $this->request->query->set('_target_path', $targetPath);
        $this->request->query->set('_hash', $hash);
        $this->authenticate();

        $this->response = $this->request('GET', $requestUri);

        $this->assertEquals(302, $this->response->getStatusCode());

        $query = null;
        $data = parse_url($this->response->headers->get('location'));
        parse_str($data['query'], $query);

        $this->assertNotNull($query['_otp']);
        $this->assertNotNull($query['_target_path']);
        $this->assertNotNull($query['_hash']);

        $this->assertEquals('http://consumer1.com/', $query['_target_path']);
    }

    /**
     * Scenario:
     *      1) http://idp.example.com/sso/logout
     *      1) http://consumer1.com/logout
     *      2) http://idp.example.com/sso/logout?service=consumer1
     *      3) http://consumer2.com/logout
     *      4) http://idp.example.com/sso/logout?service=consumer2
     *      5) http://idp.example.com/logout
     *      6) http://idp.example.com/
     */
    public function testSsoLogoutStartedFromIdentityProvider()
    {
        $this->authenticate();

        $this->response = $this->request('GET', '/sso/logout');

        $this->assertEquals(302, $this->response->getStatusCode());
        $this->assertEquals('http://consumer1.com/logout', $this->response->headers->get('location'));

        $this->response = $this->request('GET', '/sso/logout?service=consumer1', array('service' => 'consumer1'));

        $this->assertEquals(302, $this->response->getStatusCode());
        $this->assertEquals('http://consumer2.com/logout', $this->response->headers->get('location'));

        $this->response = $this->request('GET', '/sso/logout?service=consumer2', array('service' => 'consumer2'));

        $this->assertEquals(302, $this->response->getStatusCode());
        $this->assertEquals('http://idp.example.com/logout', $this->response->headers->get('location'));

        self::$application->getKernel()->getContainer()->get('session')->clear();
    }

    /**
     * Scenario:
     *      1) http://consumer2.com/logout
     *      2) http://idp.example.com/sso/logout?service=consumer2
     *      3) http://consumer1.com/logout
     *      4) http://idp.example.com/sso/logout?service=consumer1
     *      5) http://idp.example.com/logout
     *      6) http://idp.example.com/
     */
    public function testSsoLogoutStartedFromServiceProvider2()
    {
        $this->authenticate();

        $this->response = $this->request('GET', '/sso/logout?service=consumer2', array('service' => 'consumer2'));

        $this->assertEquals(302, $this->response->getStatusCode());
        $this->assertEquals('http://consumer1.com/logout', $this->response->headers->get('location'));

        $this->response = $this->request('GET', '/sso/logout?service=consumer1', array('service' => 'consumer1'));

        $this->assertEquals(302, $this->response->getStatusCode());
        $this->assertEquals('http://idp.example.com/logout', $this->response->headers->get('location'));

        self::$application->getKernel()->getContainer()->get('session')->clear();
    }

    /**
     * Scenario:
     *      1) http://consumer1.com/logout
     *      2) http://idp.example.com/sso/logout?service=consumer1
     *      3) http://consumer2.com/logout
     *      4) http://idp.example.com/sso/logout?service=consumer2
     *      5) http://idp.example.com/logout
     *      6) http://idp.example.com/
     */
    public function testSsoLogoutStartedFromServiceProvider1()
    {
        $this->authenticate();

        $this->response = $this->request('GET', '/sso/logout?service=consumer1', array('service' => 'consumer1'));

        $this->assertEquals(302, $this->response->getStatusCode());
        $this->assertEquals('http://consumer2.com/logout', $this->response->headers->get('location'));

        $this->response = $this->request('GET', '/sso/logout?service=consumer2', array('service' => 'consumer2'));

        $this->assertEquals(302, $this->response->getStatusCode());
        $this->assertEquals('http://idp.example.com/logout', $this->response->headers->get('location'));

        self::$application->getKernel()->getContainer()->get('session')->clear();
    }
}
