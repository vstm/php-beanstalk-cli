<?php

namespace Vstm\BeanstalkCli;

use Symfony\Component\Console\Application as BaseApplication;

class Application extends BaseApplication
{
    public function __construct(string $name = 'beanstalk-cli', string $version = '@git-version@')
    {
        parent::__construct($name, $version);
    }

    public function getLongVersion(): string
    {
        if (!str_starts_with($this->getVersion(), '@')) {
            return sprintf(
                '<info>%s</info> version <comment>%s</comment> build <comment>%s</comment>',
                $this->getName(),
                $this->getVersion(),
                '@git-commit@'
            );
        }

        return '<info>' . $this->getName() . '</info> (repo)';
    }
}