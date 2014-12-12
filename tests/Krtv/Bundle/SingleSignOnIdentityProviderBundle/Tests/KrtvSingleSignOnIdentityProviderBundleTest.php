<?php

namespace Krtv\Bundle\SingleSignOnIdentityProviderBunde\Tests;
use Krtv\Bundle\SingleSignOnIdentityProviderBundle\KrtvSingleSignOnIdentityProviderBundle;

/**
 * Class KrtvSingleSignOnIdentityProviderBundleTest
 * @package Krtv\Bundle\SingleSignOnIdentityProviderBunde\Tests
 */
class KrtvSingleSignOnIdentityProviderBundleTest extends \PHPUnit_Framework_TestCase
{
    /**
     *
     */
    public function testCompilerPassesAreRegistered()
    {
        $container = $this->getMock('Symfony\Component\DependencyInjection\ContainerBuilder');

        $container->expects($this->exactly(2))
            ->method('addCompilerPass')
            ->with($this->isInstanceOf('Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface'));

        $bundle = new KrtvSingleSignOnIdentityProviderBundle();
        $bundle->build($container);
    }
}
