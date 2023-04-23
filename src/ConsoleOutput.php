<?php

namespace RobinIngelbrecht\PHPUnitCoverageTools;

use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Helper\FormatterHelper;
use Symfony\Component\Console\Output\OutputInterface;

class ConsoleOutput
{
    public function __construct(
        private readonly OutputInterface $output,
    ) {
        $this->output->setDecorated(true);
        $this->output->getFormatter()->setStyle(
            'success',
            new OutputFormatterStyle(null, 'green')
        );
    }

    /**
     * @param string[] $messages
     */
    public function success(array $messages): void
    {
        $this->output->writeln((new FormatterHelper())->formatBlock($messages, 'success', true));
    }

    /**
     * @param string[] $messages
     */
    public function error(array $messages): void
    {
        $this->output->writeln((new FormatterHelper())->formatBlock($messages, 'error', true));
    }
}
