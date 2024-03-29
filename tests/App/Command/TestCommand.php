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

namespace AlexisLefebvre\TestBundle\Tests\App\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\HttpKernel\Kernel;

class TestCommand extends ContainerAwareCommand
{
    private $container;

    protected function configure(): void
    {
        parent::configure();

        $this->setName('liipfunctionaltestbundle:test')
            ->setDescription('Test command');
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     */
    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        parent::initialize($input, $output);

        $this->container = $this->getContainer();
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     */
    protected function execute(InputInterface $input, OutputInterface $output): void
    {
        // Symfony version check
        $version = Kernel::VERSION_ID;
        $output->writeln('Symfony version: '.$version);
        $output->writeln('Environment: '.$this->container->get('kernel')->getEnvironment());
        $output->writeln('Verbosity level set: '.$output->getVerbosity());

        $output->writeln('Environment: '.$this->container->get('kernel')->getEnvironment(), OutputInterface::VERBOSITY_NORMAL);

        // Write a line with OutputInterface::VERBOSITY_NORMAL (also if this level is set by default by Console)
        $output->writeln('Verbosity level: NORMAL', OutputInterface::VERBOSITY_NORMAL);

        // Write a line with OutputInterface::VERBOSITY_VERBOSE
        $output->writeln('Verbosity level: VERBOSE', OutputInterface::VERBOSITY_VERBOSE);

        // Write a line with OutputInterface::VERBOSITY_VERY_VERBOSE
        $output->writeln('Verbosity level: VERY_VERBOSE', OutputInterface::VERBOSITY_VERY_VERBOSE);

        // Write a line with OutputInterface::VERBOSITY_DEBUG
        $output->writeln('Verbosity level: DEBUG', OutputInterface::VERBOSITY_DEBUG);
    }
}
