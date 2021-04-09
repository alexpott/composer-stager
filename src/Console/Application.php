<?php

namespace PhpTuf\ComposerStager\Console;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class Application extends \Symfony\Component\Console\Application
{
    private const NAME = 'Composer Stager';
    private const VERSION = 'v1.0.x-dev';

    public function __construct()
    {
        parent::__construct(static::NAME, static::VERSION);
    }

    protected function getDefaultInputDefinition(): InputDefinition
    {
        $inputDefinition = parent::getDefaultInputDefinition();

        $inputDefinition->addOption(
            new InputOption(
                ApplicationOptions::ACTIVE_DIR,
                'd',
                InputOption::VALUE_REQUIRED,
                'Use the given directory as active directory'
            )
        );

        $inputDefinition->addOption(
            new InputOption(
                ApplicationOptions::STAGING_DIR,
                's',
                InputOption::VALUE_REQUIRED,
                'Use the given directory as staging directory'
            )
        );

        return $inputDefinition;
    }

    protected function doRunCommand(
        Command $command,
        InputInterface $input,
        OutputInterface $output
    ): int {
        $output->getFormatter()->setStyle(
            'error',
            new OutputFormatterStyle('red')
        );
        return parent::doRunCommand($command, $input, $output);
    }
}
