<?php

namespace inisire\ReactBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class ThreadedServicePass implements CompilerPassInterface
{
    /**
     * You can modify the container here before it is dumped to PHP code.
     *
     * @param ContainerBuilder $container
     *
     * @throws \Exception
     */
    public function process(ContainerBuilder $container)
    {
        $ids = $container->findTaggedServiceIds('threaded');

        foreach ($ids as $id => $tags) {

            if ($container->hasDefinition($id)) {

                $definition = $container->getDefinition($id);

                $definition
                    ->setPublic(true)
                    ->setPrivate(false);

            } elseif ($container->hasAlias($id)) {

                $alias = $container->getAlias($id);
                $alias
                    ->setPrivate(false)
                    ->setPublic(true);

            }

        }
    }
}