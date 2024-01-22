<?php

namespace Vstm\BeanstalkCli\Command;
use Pheanstalk\Values\TubeName;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand('peek', 'Peeks a command for a given tube in a given state')]
class PeekCommand extends AbstractPheanstalkCommand
{
    protected function configure(): void
    {
        parent::configure();
        $this->addOption('state', null, InputOption::VALUE_REQUIRED, 'State to peek (one of ' . implode(', ', ['delayed', 'buried', 'ready']) . ')', 'ready');
        $this->addArgument('tube', InputArgument::OPTIONAL, 'Tube to inspect', 'default');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $pheanstalk = $this->connect($input);

        $tube = (string)$input->getArgument('tube');
        $state = (string)$input->getOption('state');
        $tubeName = new TubeName($tube);
        $pheanstalk->useTube($tubeName);

        $job = self::peekJob($pheanstalk, $state);

        if (!$job) {
            $output->writeln(sprintf('No job in state %s in tube %s found', $state, $tube));
            return 0;
        }

        $output->writeln(sprintf('Job %s:', $job->getId()));
        $output->writeln($job->getData());

        return 0;
    }
}