<?php

namespace Vstm\BeanstalkCli\Command;

use Pheanstalk\Contract\PheanstalkManagerInterface;
use Pheanstalk\Pheanstalk;
use Pheanstalk\Values\Job;
use Pheanstalk\Values\TubeName;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand('clear', 'Removes all the jobs from the given tube for the given states')]
class ClearCommand extends AbstractPheanstalkCommand
{
    protected function configure(): void
    {
        parent::configure();
        $this->addOption('state', null,InputOption::VALUE_IS_ARRAY | InputOption::VALUE_REQUIRED, 'States to clear out', ['delayed', 'buried', 'ready']);
        $this->addArgument('tube', InputArgument::REQUIRED | InputArgument::IS_ARRAY, 'Payload of job');
    }

    private static function peekJob(PheanstalkManagerInterface $pheanstalk, string $state): ?Job
    {
        return match ($state) {
            'delayed' => $pheanstalk->peekDelayed(),
            'buried' => $pheanstalk->peekBuried(),
            'ready' => $pheanstalk->peekReady(),
            default => null,
        };
    }

    private static function clearJobs(Pheanstalk $pheanstalk, string $state): int
    {
        $jobsDeleted = 0;
        while(($job = self::peekJob($pheanstalk, $state)) !== null) {
            $pheanstalk->delete($job);
            ++$jobsDeleted;
        }

        return $jobsDeleted;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        /** @var string[] $states */
        $states = array_unique((array)$input->getOption('state'));

        /** @var string[] $tubes */
        $tubes = (array)$input->getArgument('tube');

        $pheanstalk = $this->connect($input);

        foreach  ($tubes as $tube) {
            $tubeName = new TubeName($tube);
            $pheanstalk->useTube($tubeName);
            foreach ($states as $state) {
                $clearedJobCount = self::clearJobs($pheanstalk, $state);
                $output->writeln(sprintf(
                    'Cleared %d jobs from queue %s in state %s',
                    $clearedJobCount,
                    $tube,
                    $state,
                ));
            }
        }

        return 0;
    }
}