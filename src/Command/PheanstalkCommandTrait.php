<?php

namespace Vstm\BeanstalkCli\Command;

use Pheanstalk\Pheanstalk;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;

trait PheanstalkCommandTrait
{
    public function addCommandOptions(Command $command): void
    {
        $command->addOption('host', null, InputOption::VALUE_REQUIRED, 'Hostname of the beanstalk server', 'localhost');
        $command->addOption('port', 'p', InputOption::VALUE_REQUIRED, 'Port of the beanstalk server', 11300);
    }

    public function connect(InputInterface $input): Pheanstalk
    {
        return Pheanstalk::create(
            (string)$input->getOption('host'),
            (int)$input->getOption('port')
        );
    }
}