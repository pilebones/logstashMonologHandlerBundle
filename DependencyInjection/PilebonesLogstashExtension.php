<?php

namespace Pilebones\LogstashBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class PilebonesLogstashExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');

        $this->configureLogstashMonologHandler($container, $config);
    }

    /**
     * Configures the janison connector service
     *
     * @param ContainerBuilder $container A container builder instance
     * @param array            $config    An array of configuration
     */
    protected function configureLogstashMonologHandler(ContainerBuilder $container, array $config)
    {
        $definition = $container->getDefinition('pilebones_logstash.monolog.logstash_handler');
        $definition->replaceArgument(1, $config['logstash_address']);
        $definition->replaceArgument(4, $config['custom_log_attributes']);
    }
}
