<?php

namespace Vstm\BeanstalkCli\Command;

use Pheanstalk\Exception\JobNotFoundException;
use Pheanstalk\Values\JobId;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand('delete-job', 'Deletes a job by it\'s job id')]
class DeleteJobCommand extends AbstractPheanstalkCommand
{
    protected function configure(): void
    {
        parent::configure();
        $this->addArgument('job-ids', InputArgument::IS_ARRAY | InputArgument::REQUIRED, 'Job ID\'s to delete');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $pheanstalk = $this->connect($input);

        /** @var string[] $jobIds */
        $jobIds = (array)$input->getArgument('job-ids');

        foreach ($jobIds as $jobId) {
            try {
                $pheanstalk->delete(new JobId($jobId));
                $output->writeln(sprintf('Job ID %s deleted', $jobId));
            } catch (JobNotFoundException $e) {
                $output->writeln(sprintf('Job ID %s was not found', $jobId));
            }
        }

        return 0;
    }
}