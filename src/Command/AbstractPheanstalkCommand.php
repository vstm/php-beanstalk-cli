<?php

namespace Vstm\BeanstalkCli\Command;

use Symfony\Component\Console\Command\Command;

abstract class AbstractPheanstalkCommand extends Command
{
    use PheanstalkCommandTrait;

    protected function configure(): void
    {
        parent::configure();
        $this->addCommandOptions($this);
    }
}