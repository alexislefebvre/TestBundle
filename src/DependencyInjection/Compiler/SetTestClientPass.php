<?php

declare(strict_types=1);

/*
 * This file is part of the Liip/FunctionalTestBundle
 *
 * (c) Lukas Kahwe Smith <smith@pooteeweet.org>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace AlexisLefebvre\TestBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Alias;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class SetTestClientPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        if (null === $container->getParameter('alexis_lefebvre_test.query.max_query_count')) {
            $container->removeDefinition('alexis_lefebvre_test.query.count_client');

            return;
        }

        if ($container->hasDefinition('test.client')) {
            // test.client is a definition.
            // Register it again as a private service to inject it as the parent
            $definition = $container->getDefinition('test.client');
            $definition->setPublic(false);
            $container->setDefinition('alexis_lefebvre_test.query.count_client.parent', $definition);
        } elseif ($container->hasAlias('test.client')) {
            // Symfony <2.8
            $container->setAlias(
                'alexis_lefebvre_test.query.count_client.parent',
                new Alias((string) $container->getAlias('test.client'), false)
            );
        } else {
            throw new \Exception('The AlexisLefebvreTestBundle\'s Query Counter can only be used in the test environment.'.PHP_EOL.'See https://github.com/liip/AlexisLefebvreTestBundle#only-in-test-environment');
        }

        $container->setAlias('test.client', new Alias('alexis_lefebvre_test.query.count_client', true));
    }
}
