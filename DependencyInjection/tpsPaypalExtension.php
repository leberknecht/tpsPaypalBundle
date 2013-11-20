<?php

namespace tps\PaypalBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;
use tps\PaypalBundle\lib\Tools;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class tpsPaypalExtension extends Extension
{
    private $restConfigKeys = array(
        'mode',
        'http',
        'mode',
        'log'
    );

    private $apiConfigKeys = array(
        'client',
        'secret'
    );

    /**
     * {@inheritDoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);
        $kernelRootDir = $container->getParameter('kernel.logs_dir');
        $restConfig = Tools::arraySliceAssoc($config, $this->restConfigKeys);
        $restConfig = Tools::flatenArray($restConfig, '.');
        $restConfig['log.FileName'] = $kernelRootDir . DIRECTORY_SEPARATOR . $restConfig['log.FileName'];
        $container->setParameter('tps_paypal.restConfig', $restConfig);
        $container->setParameter('tps_paypal.apiConfig', Tools::arraySliceAssoc($config, $this->apiConfigKeys));
        $apiconfig = Tools::flatenArray($config['classic_api'], '.');
        $container->setParameter('tps_paypal.classicApiConfig',  $apiconfig);
        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.yml');
    }
}
