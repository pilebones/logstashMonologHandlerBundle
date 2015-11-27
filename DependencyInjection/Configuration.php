<?php

namespace Pilebones\LogstashBundle\DependencyInjection;

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
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('pilebones_logstash_monolog_handler');

        // Here you should define the parameters that are allowed to
        // configure your bundle. See the documentation linked above for
        // more information on that topic.

        $rootNode->children()
                    ->scalarNode('logstash_address')
                        ->isRequired()
                        ->defaultValue('tcp://localhost:25826')
                        ->cannotBeEmpty()
                    ->end()
                    ->arrayNode('custom_log_attributes')
                        ->prototype('scalar')
                    ->end()
                ->end()
            ->end();


        return $treeBuilder;
    }
}
