<?php declare(strict_types=1);
/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\EshopCommunity\Internal\Module\Configuration\Definition;

use OxidEsales\EshopCommunity\Internal\Module\Configuration\DataObject\ModuleSetting;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\NodeInterface;

/**
 * @internal
 */
class TreeBuilderFactory implements TreeBuilderFactoryInterface
{
    /**
     * @return NodeInterface
     */
    public function create(): NodeInterface
    {
        $treeBuilder = new TreeBuilder();

        $rootNode = $treeBuilder->root('projectConfiguration');

        $rootNode
            ->children()
                ->arrayNode('environments')
                    ->arrayPrototype()
                        ->children()
                            ->arrayNode('shops')
                                ->arrayPrototype()
                                    ->children()
                                        ->arrayNode('modules')
                                            ->arrayPrototype()
                                                ->children()
                                                    ->scalarNode('id')
                                                        ->isRequired()
                                                        ->cannotBeEmpty()
                                                    ->end()
                                                    ->scalarNode('autoActive')
                                                    ->end()
                                                    ->scalarNode('title')
                                                    ->end()
                                                    ->arrayNode('description')
                                                    ->end()
                                                    ->scalarNode('lang')
                                                    ->end()
                                                    ->scalarNode('thumbnail')
                                                    ->end()
                                                    ->scalarNode('author')
                                                    ->end()
                                                    ->scalarNode('url')
                                                    ->end()
                                                    ->scalarNode('email')
                                                    ->end()
                                                    ->arrayNode('settings')
                                                        ->children()
                                                            ->scalarNode(ModuleSetting::PATH)
                                                                ->isRequired()
                                                                ->cannotBeEmpty()
                                                            ->end()
                                                            ->scalarNode(ModuleSetting::VERSION)
                                                            ->end()
                                                            ->arrayNode(ModuleSetting::CONTROLLERS)
                                                                ->scalarPrototype()->end()
                                                            ->end()
                                                            ->arrayNode(ModuleSetting::TEMPLATES)
                                                                ->scalarPrototype()->end()
                                                            ->end()
                                                            ->arrayNode(ModuleSetting::SMARTY_PLUGIN_DIRECTORIES)
                                                                ->scalarPrototype()->end()
                                                            ->end()
                                                            ->arrayNode(ModuleSetting::TEMPLATE_BLOCKS)
                                                                ->arrayPrototype()
                                                                    ->children()
                                                                        ->scalarNode('block')
                                                                        ->end()
                                                                        ->scalarNode('position')
                                                                        ->end()
                                                                        ->scalarNode('theme')
                                                                        ->end()
                                                                        ->scalarNode('template')
                                                                        ->end()
                                                                        ->scalarNode('file')
                                                                        ->end()
                                                                    ->end()
                                                                ->end()
                                                            ->end()
                                                            ->arrayNode(ModuleSetting::CLASS_EXTENSIONS)
                                                                ->scalarPrototype()->end()
                                                            ->end()
                                                            ->arrayNode(ModuleSetting::EVENTS)
                                                                ->scalarPrototype()->end()
                                                            ->end()
                                                            ->arrayNode(ModuleSetting::SHOP_MODULE_SETTING)
                                                                ->arrayPrototype()
                                                                    ->children()
                                                                        ->scalarNode('group')
                                                                        ->end()
                                                                        ->scalarNode('name')
                                                                        ->end()
                                                                        ->scalarNode('type')
                                                                        ->end()
                                                                        ->scalarNode('value')
                                                                        ->end()
                                                                    ->end()
                                                                ->end()
                                                            ->end()
                                                        ->end()
                                                    ->end()
                                                ->end()
                                            ->end()
                                        ->end()
                                        ->arrayNode('moduleChains')
                                            ->children()
                                                ->arrayNode('classExtensions')
                                                    ->arrayPrototype()
                                                        ->scalarPrototype()->end()
                                                    ->end()
                                                ->end()
                                            ->end()
                                        ->end()
                                    ->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $treeBuilder->buildTree();
    }
}
