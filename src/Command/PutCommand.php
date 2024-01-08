<?php

namespace Vstm\BeanstalkCli\Command;

use Pheanstalk\Contract\PheanstalkPublisherInterface;
use Pheanstalk\Values\TubeName;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\StreamableInputInterface;
use Symfony\Component\Console\Output\ConsoleOutputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand('put', 'Add a new job to a given tube')]
class PutCommand extends AbstractPheanstalkCommand
{
    protected function configure(): void
    {
        parent::configure();
        $this->addOption('tube', 't', InputOption::VALUE_REQUIRED, 'Tube to use', 'default');
        $this->addOption('priority', null, InputOption::VALUE_REQUIRED, 'Priority (uses default priority)', PheanstalkPublisherInterface::DEFAULT_PRIORITY);
        $this->addOption('delay', null, InputOption::VALUE_REQUIRED, 'Delay (uses default delay)', PheanstalkPublisherInterface::DEFAULT_DELAY);
        $this->addOption('time-to-release', null, InputOption::VALUE_REQUIRED, 'Time to release (uses default delay)', PheanstalkPublisherInterface::DEFAULT_TTR);

        $this->addArgument('payload', InputArgument::OPTIONAL, 'Payload of job (use - to read from stdin)', '');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $stderr = $output instanceof ConsoleOutputInterface ? $output->getErrorOutput() : $output;
        $tubeName = (string)$input->getOption('tube');

        $payload = (string)$input->getArgument('payload');

        if ($payload === '-' && $input instanceof StreamableInputInterface) {
            $stdinStream = $input->getStream();
            if (!$stdinStream) {
                $stderr->writeln('Stdin not available');
                return 1;
            }
            $payload = stream_get_contents($stdinStream);
        }

        $pheanstalk = $this->connect($input);

        $tube = new TubeName($tubeName);
        $pheanstalk->useTube($tube);
        $jobId = $pheanstalk->put(
            data: $payload,
            priority: (int)$input->getOption('priority'),
            delay: (int)$input->getOption('delay'),
            timeToRelease: (int)$input->getOption('time-to-release'),
        );

        $output->writeln('Job id ' . $jobId->getId() . ' added');

        return 0;
    }
}