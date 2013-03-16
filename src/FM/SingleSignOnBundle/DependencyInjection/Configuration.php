<?php

namespace FM\SingleSignOnBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    /**
     * Generates the configuration tree builder.
     *
     * @return \Symfony\Component\Config\Definition\Builder\TreeBuilder The tree builder
     */
    public function getConfigTreeBuilder()
    {
        $builder = new TreeBuilder();

        $builder->root('fm_single_sign_on')
            ->children()
                ->scalarNode('host')
                    ->isRequired()
                    ->validate()
                        ->ifTrue(function($v) {
                            return preg_match('/^http(s?):\/\//', $v);
                        })
                        ->thenInvalid('SSO host must only contain the host, and not the url scheme, eg: domain.com')
                    ->end()
                ->end()

                ->scalarNode('login_path')
                    ->isRequired()
                ->end()

                ->scalarNode('otp_parameter')
                    ->defaultValue('_otp')
                ->end()
            ->end()
        ;

        return $builder;
    }
}