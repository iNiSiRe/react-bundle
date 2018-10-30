<?php

namespace inisire\ReactBundle;

use inisire\ReactBundle\DependencyInjection\ThreadedServicePass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class ReactBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new ThreadedServicePass());
    }
}
