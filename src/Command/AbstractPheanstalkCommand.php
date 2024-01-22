<?php

namespace Vstm\BeanstalkCli\Command;

use Pheanstalk\Contract\PheanstalkManagerInterface;
use Pheanstalk\Values\Job;
use Symfony\Component\Console\Command\Command;

abstract class AbstractPheanstalkCommand extends Command
{
    use PheanstalkCommandTrait;

    protected function configure(): void
    {
        parent::configure();
        $this->addCommandOptions($this);
    }

    protected static function peekJob(PheanstalkManagerInterface $pheanstalk, string $state): ?Job
    {
        return match ($state) {
            'delayed' => $pheanstalk->peekDelayed(),
            'buried' => $pheanstalk->peekBuried(),
            'ready' => $pheanstalk->peekReady(),
            default => null,
        };
    }
}