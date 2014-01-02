<?php

namespace Devster\WSSEBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Reference;

class NonceRepositoryProviderPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if (! $container->hasDefinition('devster.wsse.nonce.provider')) {
            return;
        }

        $definition = $container->getDefinition('devster.wsse.nonce.provider');

        foreach ($container->findTaggedServiceIds('devster.wsse.nonce.repository') as $id => $tagAttributes) {
            foreach($tagAttributes as $attributes) {
                if (! array_key_exists('alias', $attributes)) {
                    throw new \Exception(
                        sprintf('An alias is required for the service %s for its tag devster.wsse.nonce.repository', $id)
                    );
                }

                $definition->addMethodCall('add', [$attributes['alias'], new Reference($id)]);
            }
        }
    }
}
