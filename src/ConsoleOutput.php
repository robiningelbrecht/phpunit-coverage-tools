<?php

namespace RobinIngelbrecht\PHPUnitCoverageTools;

use RobinIngelbrecht\PHPUnitCoverageTools\MinCoverage\MinCoverageResult;
use RobinIngelbrecht\PHPUnitCoverageTools\MinCoverage\ResultStatus;
use RobinIngelbrecht\PHPUnitCoverageTools\Timer\ResourceUsageFormatter;
use RobinIngelbrecht\PHPUnitCoverageTools\Timer\SystemResourceUsageFormatter;
use SebastianBergmann\Timer\Duration;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Helper\TableCell;
use Symfony\Component\Console\Helper\TableCellStyle;
use Symfony\Component\Console\Helper\TableSeparator;
use Symfony\Component\Console\Helper\TableStyle;
use Symfony\Component\Console\Output\OutputInterface;

final class ConsoleOutput
{
    public function __construct(
        private readonly OutputInterface $output,
        private readonly ResourceUsageFormatter $resourceUsageFormatter,
    ) {
        $this->output->setDecorated(true);
        $this->output->getFormatter()->setStyle(
            'success',
            new OutputFormatterStyle('green', null, ['bold'])
        );
        $this->output->getFormatter()->setStyle(
            'failed',
            new OutputFormatterStyle('red', null, ['bold'])
        );
        $this->output->getFormatter()->setStyle(
            'warning',
            new OutputFormatterStyle('yellow', null, ['bold'])
        );
        $this->output->getFormatter()->setStyle(
            'bold',
            new OutputFormatterStyle(null, null, ['bold'])
        );
    }

    public static function create(): self
    {
        return new self(
            output: new \Symfony\Component\Console\Output\ConsoleOutput(),
            resourceUsageFormatter: SystemResourceUsageFormatter::create()
        );
    }

    /**
     * @param \RobinIngelbrecht\PHPUnitCoverageTools\MinCoverage\MinCoverageResult[] $results
     */
    public function print(array $results, Duration $duration): void
    {
        $statusWeights = array_map(fn (MinCoverageResult $result) => $result->getStatus()->getWeight(), $results);
        $finalStatus = ResultStatus::fromWeight(max($statusWeights));

        $this->output->writeln('');
        $tableStyle = new TableStyle();
        $tableStyle
            ->setHeaderTitleFormat('<fg=black;bg=yellow;options=bold> %s </>')
            ->setCellHeaderFormat('<bold>%s</bold>')
            ->setPadType(STR_PAD_BOTH);

        $table = new Table($this->output);
        $table
            ->setStyle($tableStyle)
            ->setHeaderTitle('Code coverage results')
            ->setHeaders(['Pattern', 'Expected', 'Actual', '', 'Exit on fail?'])
            ->setColumnMaxWidth(1, 10)
            ->setColumnMaxWidth(2, 8)
            ->setColumnMaxWidth(4, 11)
            ->setRows([
                ...array_map(fn (MinCoverageResult $result) => [
                    new TableCell(
                        $result->getPattern(),
                        [
                            'style' => new TableCellStyle([
                                'align' => 'left',
                            ]),
                        ]
                    ),
                    $result->getExpectedMinCoverage().'%',
                    sprintf('<%s>%s%%</%s>', $result->getStatus()->value, $result->getActualMinCoverage(), $result->getStatus()->value),
                    new TableCell(
                        $result->getNumberOfTrackedLines() > 0 ?
                            sprintf('<bold>%s</bold> of %s lines covered', $result->getNumberOfCoveredLines(), $result->getNumberOfTrackedLines()) :
                            'No lines to track...?',
                        [
                            'style' => new TableCellStyle([
                                'align' => 'left',
                            ]),
                        ]
                    ),
                   $result->exitOnLowCoverage() ? 'Yes' : 'No',
                ], $results),
                new TableSeparator(),
                [
                    new TableCell(
                        $finalStatus->getMessage(),
                        [
                            'colspan' => 5,
                            'style' => new TableCellStyle([
                                    'align' => 'center',
                                    'cellFormat' => '<'.$finalStatus->value.'>%s</'.$finalStatus->value.'>',
                                ]
                            ),
                        ]
                    ),
                ],
            ]);
        $table->render();
        $this->output->writeln($this->resourceUsageFormatter->resourceUsage($duration));
    }
}
