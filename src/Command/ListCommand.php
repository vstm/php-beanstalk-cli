<?php

namespace Vstm\BeanstalkCli\Command;

use Pheanstalk\Pheanstalk;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\ConsoleOutputInterface;
use Symfony\Component\Console\Output\ConsoleSectionOutput;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand('list', 'List all the tubes and the number of jobs in them', ['ls', 'watch'])]
class ListCommand extends AbstractPheanstalkCommand
{
    private const DEFAULT_WAIT_INTERVAL=2;
    protected function configure(): void
    {
        parent::configure();

        $this->addOption('watch', 'w', InputOption::VALUE_OPTIONAL, 'Watch', self::DEFAULT_WAIT_INTERVAL);
        $this->addOption('watch-iterations', 'i', InputOption::VALUE_REQUIRED, 'Watch iterations (-1) = forever', -1);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $watch = $input->getArgument('command') === 'watch' || $input->hasParameterOption(['-w', '--watch'], true);
        $watchInterval = ($input->getOption('watch') === null ? self::DEFAULT_WAIT_INTERVAL : (int)$input->getOption('watch'));
        if ($watchInterval < 1) {
            $watchInterval = 1;
            $watch = false;
        }

        $watchIterations = (int)$input->getOption('watch-iterations');
        $count = 0;

        $pheanstalk = $this->connect($input);

        $tableOutput = $output;
        if ($output instanceof ConsoleOutputInterface) {
            $tableOutput = $output->section();
        }

        do {
            $this->listTubes($pheanstalk, $tableOutput);

            if ($watch) {
                ++$count;
                sleep($watchInterval);
            }
        } while($watch && ($watchIterations < 0 || $count < $watchIterations));

        return 0;
    }


    public function listTubes(Pheanstalk $pheanstalk, OutputInterface|ConsoleSectionOutput $tableOutput): void
    {
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
    }
}