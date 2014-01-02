<?php

namespace Devster\WSSEBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Devster\WSSEBundle\DependencyInjection\Compiler\NonceRepositoryProviderPass;
use Devster\WSSEBundle\DependencyInjection\Security\Factory\WsseFactory;

class DevsterWSSEBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new NonceRepositoryProviderPass());

        $extension = $container->getExtension('security');
        $extension->addSecurityListenerFactory(new WsseFactory());
    }
}
