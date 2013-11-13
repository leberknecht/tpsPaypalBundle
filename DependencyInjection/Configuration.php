<?php
namespace tps\PaypalBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class}
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('tps_paypal');
        $rootNode->children()
            ->scalarNode('mode')->defaultValue('sandbox')->info('endpoint, defaults to sandbox-environment')->end()
            ->scalarNode('client')->info('REST-Api client token')->end()
            ->scalarNode('secret')->info('REST-Api secret token')->end()
            ->arrayNode('http')
                ->children()
                    ->scalarNode('ConnectionTimeOut')
                        ->defaultValue(30)
                        ->info('http connection timeout for REST-requests')
                    ->end()
                    ->scalarNode('Retry')
                        ->defaultValue(1)
                        ->info('number of retries for requests')
                    ->end()
                    ->scalarNode('Proxy')
                        ->defaultNull()
                        ->info('proxy to use, can be null')
                    ->end()
                ->end()
            ->end()
            ->arrayNode('log')
                ->addDefaultsIfNotSet()
                ->children()
                    ->scalarNode('LogEnabled')
                        ->defaultValue(true)
                    ->end()
                    ->scalarNode('FileName')
                        ->defaultValue('PayPal.log')
                        ->info('filename for logging, will be placed in app/logs')
                    ->end()
                    ->scalarNode('LogLevel')
                        ->defaultValue('FINE')
                        ->info('log-level, can be FINE, INFO, WARN or ERROR')
                    ->end()
                ->end()
            ->end()
            ->arrayNode('classic_api')
                ->addDefaultsIfNotSet()
                ->children()
                    ->arrayNode('acct1')
                        ->children()
                            ->scalarNode('Username')
                                ->defaultNull()
                                ->info('Your Classic-API username')
                            ->end()
                            ->scalarNode('Password')
                                ->defaultNull()
                                ->info('Your Classic-API password')
                            ->end()
                            ->scalarNode('Signature')
                                ->defaultNull()
                                ->info('Your Classic-API signature')
                            ->end()
                        ->end()
                    ->end()
                    ->scalarNode('mode')
                        ->defaultValue('sandbox')
                        ->info('The environment to use for classic API calls')
                    ->end()
                ->end()
            ->end();

        return $treeBuilder;
    }
}
