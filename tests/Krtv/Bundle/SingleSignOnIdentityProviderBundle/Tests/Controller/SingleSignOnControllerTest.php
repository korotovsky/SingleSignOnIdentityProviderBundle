<?php

namespace Krtv\Bundle\SingleSignOnIdentityProviderBundle\Controller;

use Krtv\Bundle\SingleSignOnIdentityProviderBundle\Tests\Application\AppKernel;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\HttpFoundation\Request;
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
    }

    /**
     * @param string $url
     * @param array $params
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected function request($url, array $params = array())
    {
        $request = new Request();
        $request->headers->set('HTTP_HOST', 'idp.example.com');
        $request->headers->set('Authorization', sprintf('Basic %s', base64_encode('qwerty:ytrewq')));
        $request->server->set('SERVER_NAME', 'idp.example.com');
        $request->server->set('SERVER_PORT', 80);
        $request->server->set('REQUEST_URI', $url);

        return self::$application->getKernel()->handle($request);
    }

    /**
     *
     */
    public function testSsoLoginWithoutTargetPathException()
    {
        $response = $this->request('/sso/login/');

        $this->assertEquals(400, $response->getStatusCode());
    }

    public function testSsoLoginMalformedUriException()
    {

    }
}
