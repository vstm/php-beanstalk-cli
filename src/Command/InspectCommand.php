<?php

namespace Vstm\BeanstalkCli\Command;

use Pheanstalk\Contract\PheanstalkPublisherInterface;
use Pheanstalk\Values\TubeName;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand('inspect', 'Dumps the job ID\'s and payloads in the ready state for the given tube')]
class InspectCommand extends AbstractPheanstalkCommand
{
    protected function configure(): void
    {
        parent::configure();
        $this->addOption('limit', 'l', InputOption::VALUE_REQUIRED, 'Limit of jobs to inspect (0 = unlimited)', 1);
        $this->addArgument('tube', InputArgument::REQUIRED, 'Tube to inspect');
    }


    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $pheanstalk = $this->connect($input);

        $limit = (int)$input->getOption('limit');

        $tube = (string)$input->getArgument('tube');
        $tubeName = new TubeName($tube);
        $pheanstalk->watch($tubeName);

        $jobsToRelease = [];

        try {
            while (
                ($limit === 0 || ($limit > count($jobsToRelease)))
                && ($job = $pheanstalk->reserveWithTimeout(0)) !== null) {
                $output->writeln(sprintf('Job %s', $job->getId()));
                $output->writeln($job->getData());
                try {
                    $stats = $pheanstalk->statsJob($job);
                    $jobsToRelease[] = [$job, $stats->priority];
                } catch (\Exception $e) {
                    $jobsToRelease[] = [$job, PheanstalkPublisherInterface::DEFAULT_PRIORITY];
                    throw $e;
                }
            }
        } finally {
            foreach ($jobsToRelease as [$job, $priority]) {
                $pheanstalk->release($job, $priority);
            }
        }

        return 0;
    }
}