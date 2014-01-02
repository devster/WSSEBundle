<?php

namespace Devster\WSSEBundle\DependencyInjection\Security\Factory;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\DefinitionDecorator;
use Symfony\Component\Config\Definition\Builder\NodeDefinition;
use Symfony\Bundle\SecurityBundle\DependencyInjection\Security\Factory\SecurityFactoryInterface;

class WsseFactory implements SecurityFactoryInterface
{
    public function create(ContainerBuilder $container, $id, $config, $userProvider, $defaultEntryPoint)
    {
        var_dump($config);
        $providerId = 'security.authentication.provider.wsse.'.$id;
        $container
            ->setDefinition($providerId, new DefinitionDecorator('devster.wsse.security.authentication.provider'))
            ->replaceArgument(0, new Reference($userProvider))
            ->replaceArgument(1, $config)
        ;

        $listenerId = 'security.authentication.listener.wsse.'.$id;
        $container
            ->setDefinition($listenerId, new DefinitionDecorator('devster.wsse.security.authentication.listener'))
            ->replaceArgument(0, $config['chained'])
        ;

        return array($providerId, $listenerId, $defaultEntryPoint);
    }

    public function getPosition()
    {
        return 'pre_auth';
    }

    public function getKey()
    {
        return 'wsse';
    }

    public function addConfiguration(NodeDefinition $node)
    {
        $node
            ->children()
                ->booleanNode('chained')->defaultFalse()->end()
                ->arrayNode('nonce')
                    ->requiresAtLeastOneElement()
                    ->useAttributeAsKey('name')
                    ->prototype('variable')->end()
                    ->defaultValue(['file' => null])
                ->end()
                ->integerNode('lifetime')
                    ->defaultValue(300)
                ->end()
            ->end()
        ;
    }
}
