<?php

namespace Vstm\BeanstalkCli\Command;

use Pheanstalk\Values\TubeName;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand('kick', 'Kicks jobs in the delayed state of a given tube')]
class KickCommand extends AbstractPheanstalkCommand
{
    protected function configure(): void
    {
        parent::configure();
        $this->addOption('max', 'm', InputOption::VALUE_REQUIRED, 'Maximum number of jobs to kick', 1);
        $this->addArgument('tubes', InputArgument::IS_ARRAY | InputArgument::OPTIONAL, 'Tube(s) to kick', ['default']);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $pheanstalk = $this->connect($input);

        /** @var string[] $tubes */
        $tubes = (array)$input->getArgument('tubes');
        $maxKicks = (int)$input->getOption('max');

        foreach ($tubes as $tube) {
            $tubeName = new TubeName($tube);
            $pheanstalk->useTube($tubeName);

            $jobsKicked = $pheanstalk->kick($maxKicks);
            $output->writeln(sprintf('%s: Kicked %d jobs', $tube, $jobsKicked));
        }

        return 0;
    }
}