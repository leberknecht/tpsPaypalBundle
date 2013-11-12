<?php

namespace tps\PaypalBundle\Tests\DependencyInjection;

use Symfony\Component\Config\Definition\ArrayNode;
use tps\PaypalBundle\DependencyInjection\Configuration as tpsPaypalConfiguration;

/**
 * This is the class that validates and merges configuration from your app/config files
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class}
 */
class ConfigurationTest extends \PHPUnit_Framework_TestCase
{
    /**
     * {@inheritDoc}
     */
    public function testTreeBuilder()
    {
        $configuration = new tpsPaypalConfiguration();
        $configBuilder = $configuration->getConfigTreeBuilder();
        /** @var ArrayNode $nodeInterface */
        $nodeInterface = $configBuilder->buildTree();
        $children = $nodeInterface->getChildren();
        $this->assertTrue(array_key_exists('mode', $children));
        $this->assertTrue(array_key_exists('client', $children));
        $this->assertTrue(array_key_exists('secret', $children));
        $this->assertTrue(array_key_exists('http', $children));
        $this->assertTrue(array_key_exists('log', $children));
    }
}
