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

namespace AlexisLefebvre\TestBundle\Tests\DependencyInjection\Compiler;

use AlexisLefebvre\TestBundle\DependencyInjection\Compiler\SetTestClientPass;
use PHPUnit\Framework\TestCase;

/**
 * Test DependencyInjection\Compiler\SetTestClientPass with mocks.
 *
 * try/catch block is based on PHPUnit internal test:
 *
 * @see https://github.com/sebastianbergmann/phpunit/blob/b12b9c37e382c096b93c3f26e7395775f59a5eea/tests/Framework/AssertTest.php#L3560-L3574
 */
class SetTestClientPassMockTest extends TestCase
{
    /**
     * Simulate Symfony 2.8.
     */
    public function testSetTestClientPassHasAlias(): void
    {
        /* @see http://gianarb.it/blog/symfony-unit-test-controller-with-phpunit#expectations */
        /** @var \Symfony\Component\DependencyInjection\ContainerBuilder $container */
        $container = $this->getMockBuilder('Symfony\Component\DependencyInjection\ContainerBuilder')
            ->getMock();

        $container->expects($this->any())
            ->method('getParameter')
            ->will($this->returnValue(true));

        $container->expects($this->any())
            ->method('hasDefinition')
            ->will($this->returnValue(false));

        $container->expects($this->once())
            ->method('hasAlias')
            ->will($this->returnValue(true));

        $container->expects($this->exactly(2))
            ->method('setAlias')
            ->will($this->returnValue(true));

        $setTestClientPass = new SetTestClientPass($container);
        $setTestClientPass->process($container);
    }

    /**
     * Simulate a wrong environment.
     */
    public function testSetTestClientPassElse(): void
    {
        /* @see http://gianarb.it/blog/symfony-unit-test-controller-with-phpunit#expectations */
        /** @var \Symfony\Component\DependencyInjection\ContainerBuilder $container */
        $container = $this->getMockBuilder('Symfony\Component\DependencyInjection\ContainerBuilder')
            ->getMock();

        $container->expects($this->any())
            ->method('getParameter')
            ->will($this->returnValue(true));

        $container->expects($this->any())
            ->method('hasDefinition')
            ->will($this->returnValue(false));

        $container->expects($this->any())
            ->method('hasAlias')
            ->will($this->returnValue(false));

        try {
            $setTestClientPass = new SetTestClientPass($container);
            $setTestClientPass->process($container);
        } catch (\Exception $e) {
            $this->assertSame(
                'The AlexisLefebvreTestBundle\'s Query Counter can only be used in the test environment.'.
                PHP_EOL.
                'See https://github.com/liip/AlexisLefebvreTestBundle#only-in-test-environment',
                $e->getMessage()
            );

            return;
        }

        $this->fail('Test failed.');
    }
}
