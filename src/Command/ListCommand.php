<?php

namespace Vstm\BeanstalkCli\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\ConsoleOutputInterface;
use Symfony\Component\Console\Output\ConsoleSectionOutput;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand('list', 'List all the tubes and the number of jobs in them', ['ls', 'watch'])]
class ListCommand extends AbstractPheanstalkCommand
{
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        // $input->getArgument('command')

        $pheanstalk = $this->connect($input);

        $tableOutput = $output;
        if ($output instanceof ConsoleOutputInterface) {
            $tableOutput = $output->section();
        }
        while(true) {
            $tubes = $pheanstalk->listTubes();

            $tubeStats = [];

            foreach ($tubes as $tube) {
                $status = $pheanstalk->statsTube($tube);

                $tubeStats[] = [
                    $status->name,
                    $status->currentJobsReserved,
                    $status->currentJobsBuried,
                    $status->currentJobsDelayed,
                    $status->currentJobsReady,
                    $status->currentJobsUrgent,
                ];

            }

            if ($tableOutput instanceof ConsoleSectionOutput) {
                $tableOutput->clear();
            }

            $tableOutput->writeln(date('c'));
            $table = new Table($tableOutput);
            $table->setHeaders(['Name', 'Reserved', 'Buried', 'Delayed', 'Ready', 'Urgent']);
            $table->setRows($tubeStats);
            $table->render();

            sleep(2);
        }

        return 0;
    }
}