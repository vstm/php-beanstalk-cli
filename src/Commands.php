<?php

namespace Vstm\BeanstalkCli;

use Symfony\Component\Console\Command\Command;
use Vstm\BeanstalkCli\Command\ClearCommand;
use Vstm\BeanstalkCli\Command\DeleteJobCommand;
use Vstm\BeanstalkCli\Command\InspectCommand;
use Vstm\BeanstalkCli\Command\KickCommand;
use Vstm\BeanstalkCli\Command\KickJobCommand;
use Vstm\BeanstalkCli\Command\PeekCommand;
use Vstm\BeanstalkCli\Command\PutCommand;
use Vstm\BeanstalkCli\Command\ListCommand;

final class Commands
{
    /**
     * @return iterable<Command>
     */
    public static function getCommands(): iterable
    {
        yield new PutCommand();
        yield new ListCommand();
        yield new KickCommand();
        yield new KickJobCommand();
        yield new ClearCommand();
        yield new InspectCommand();
        yield new PeekCommand();
        yield new DeleteJobCommand();
    }
}