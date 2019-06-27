<?php

namespace Pacolmg\SymfonyFilterBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder('pacolmg_symfony_filter_bundle');
        $rootNode = method_exists(TreeBuilder::class, 'getRootNode') ? $treeBuilder->getRootNode() : $treeBuilder->root('pacolmg_symfony_filter_bundle');

        $rootNode
            ->children()
            ->integerNode('default_limit')->defaultValue(10)->info('Default number of elements per page.')->end()
            ->end();

        return $treeBuilder;
    }
}
