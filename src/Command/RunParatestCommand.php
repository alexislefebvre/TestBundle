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

namespace AlexisLefebvre\TestBundle\Command;

use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Symfony\Component\Process\Process;

/**
 * Command used to update the project.
 */
class RunParatestCommand extends Command implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    private $output;

    private $process;

    private $testDbPath;

    private $phpunit;

    /**
     * Configuration of the command.
     */
    protected function configure(): void
    {
        $this
            ->setName('paratest:run')
            ->setDescription('Run phpunit tests with multiple processes')
            // Pass arguments from this command "paratest:run" to the paratest command.
            ->addArgument('options', InputArgument::OPTIONAL, 'Options')
        ;
    }

    protected function prepare(): void
    {
        $this->phpunit = $this->container->getParameter('alexis_lefebvre_test.paratest.phpunit');
        $this->process = $this->container->getParameter('alexis_lefebvre_test.paratest.process');

        $this->testDbPath = $this->container->get('kernel')->getCacheDir();
        $this->output->writeln("Cleaning old dbs in $this->testDbPath ...");
        $createDirProcess = new Process('mkdir -p '.$this->testDbPath);
        $createDirProcess->run();
        $cleanProcess = new Process("rm -fr $this->testDbPath/dbTest.db $this->testDbPath/dbTest*.db*");
        $cleanProcess->run();
        $this->output->writeln("Creating Schema in $this->testDbPath ...");
        $application = new Application($this->container->get('kernel'));
        $input = new ArrayInput(['doctrine:schema:create', '--env' => 'test']);
        $application->run($input, $this->output);

        $this->output->writeln('Initial schema created');
        $input = new ArrayInput([
            'doctrine:fixtures:load',
            '-n' => '',
            '--env' => 'test',
        ]);
        $application->run($input, $this->output);

        $this->output->writeln('Initial schema populated, duplicating....');
        for ($a = 0; $a < $this->process; ++$a) {
            $test = new Process("cp $this->testDbPath/dbTest.db ".$this->testDbPath."/dbTest$a.db");
            $test->run();
        }
    }

    /**
     * Content of the command.
     *
     * @param InputInterface  $input
     * @param OutputInterface $output
     */
    protected function execute(InputInterface $input, OutputInterface $output): void
    {
        $this->output = $output;
        $this->prepare();
        if (true !== is_file('vendor/bin/paratest')) {
            $this->output->writeln('Error : Install paratest first');
        } else {
            $this->output->writeln('Done...Running test.');
            $runProcess = new Process('vendor/bin/paratest '.
                '-c phpunit.xml.dist '.
                '--phpunit '.$this->phpunit.' '.
                '--runner WrapRunner '.
                '-p '.$this->process.' '.
                $input->getArgument('options')
            );
            $runProcess->run(function ($type, $buffer) use ($output): void {
                $output->write($buffer);
            });
        }
    }
}
