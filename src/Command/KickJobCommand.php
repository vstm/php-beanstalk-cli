<?php

namespace Vstm\BeanstalkCli\Command;

use Pheanstalk\Exception\JobNotFoundException;
use Pheanstalk\Values\JobId;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand('kick-job', 'Kicks a specific job using its ID')]
class KickJobCommand extends AbstractPheanstalkCommand
{
    protected function configure(): void
    {
        parent::configure();
        $this->addArgument('jobId', InputArgument::IS_ARRAY, 'Job ID\'s to kick');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $pheanstalk = $this->connect($input);
        $jobIds = array_map(
            static fn (string $jobId) => new JobId($jobId),
            (array)$input->getArgument('jobId')
        );

        foreach ($jobIds as $jobId) {
            try {
                $pheanstalk->kickJob($jobId);
                $output->writeln('Job id ' . $jobId->getId() . ' kicked');
            } catch (JobNotFoundException $exception) {
                $output->writeln('Job id ' . $jobId->getId() . ' not found');
            }
        }

        return 0;
    }
}