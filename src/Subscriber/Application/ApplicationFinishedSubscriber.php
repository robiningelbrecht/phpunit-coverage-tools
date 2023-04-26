<?php

namespace RobinIngelbrecht\PHPUnitCoverageTools\Subscriber\Application;

use Composer\Autoload\ClassLoader;
use PHPUnit\Event\Application\Finished;
use PHPUnit\Event\Application\FinishedSubscriber;
use PHPUnit\Runner\Extension\ParameterCollection;
use PHPUnit\TextUI\Configuration\Configuration;
use RobinIngelbrecht\PHPUnitCoverageTools\ConsoleOutput;
use RobinIngelbrecht\PHPUnitCoverageTools\CoverageMetrics;
use RobinIngelbrecht\PHPUnitCoverageTools\Exitter;
use Symfony\Component\Console\Helper\FormatterHelper;

final class ApplicationFinishedSubscriber extends FormatterHelper implements FinishedSubscriber
{
    public function __construct(
        private readonly string $relativePathToCloverXml,
        private readonly int $minCoverage,
        private readonly bool $exitOnLowCoverage,
        private readonly Exitter $exitter,
        private readonly ConsoleOutput $consoleOutput,
    ) {
    }

    public function notify(Finished $event): void
    {
        /** @var string $reflectionFileName */
        $reflectionFileName = (new \ReflectionClass(ClassLoader::class))->getFileName();
        $absolutePathToCloverXml = dirname($reflectionFileName, 3).'/'.$this->relativePathToCloverXml;

        if (!file_exists($absolutePathToCloverXml)) {
            return;
        }

        $metrics = null;

        $reader = new \XMLReader();
        $reader->open($absolutePathToCloverXml);
        while ($reader->read()) {
            if (\XMLReader::ELEMENT == $reader->nodeType && 'metrics' == $reader->name && 2 === $reader->depth) {
                /** @var \SimpleXMLElement $node */
                $node = simplexml_load_string($reader->readOuterXml());
                $metrics = CoverageMetrics::fromCloverXmlNode($node);
                break;
            }
        }
        $reader->close();

        if (!$metrics) {
            throw new \RuntimeException('Could not determine coverage metrics');
        }

        if ($metrics->getTotalPercentageCoverage() < $this->minCoverage) {
            $this->consoleOutput->error([
                sprintf('Expected %s%% test coverage, got %s%%', $this->minCoverage, $metrics->getTotalPercentageCoverage()),
                sprintf('%s of %s lines covered in %s files', $metrics->getNumberOfCoveredLines(), $metrics->getNumberOfTrackedLines(), $metrics->getNumberOfFiles()),
            ]);

            if ($this->exitOnLowCoverage) {
                $this->exitter->exit(1);
            }

            return;
        }

        $this->consoleOutput->success([
            sprintf('%s%% test coverage (min required is %s%%), give yourself a pat on the back', $metrics->getTotalPercentageCoverage(), $this->minCoverage),
            sprintf('%s of %s lines covered in %s files', $metrics->getNumberOfCoveredLines(), $metrics->getNumberOfTrackedLines(), $metrics->getNumberOfFiles()),
        ]);
    }

    /**
     * @param string[] $args
     */
    public static function fromConfigurationAndParameters(
        Configuration $configuration,
        ParameterCollection $parameters,
        array $args,
    ): ?self {
        if (!$configuration->hasCoverageClover()) {
            return null;
        }

        $minCoverage = null;
        foreach ($args as $arg) {
            if (!str_starts_with($arg, '--min-coverage=')) {
                continue;
            }

            if (!preg_match('/--min-coverage=(?<minCoverage>[\d]+)/', $arg, $matches)) {
                break;
            }

            $minCoverage = $matches['minCoverage'];
        }

        if (is_null($minCoverage)) {
            return null;
        }

        if ((int) $minCoverage < 0 || (int) $minCoverage > 100) {
            return null;
        }

        return new self(
            $configuration->coverageClover(),
            (int) $minCoverage,
            $parameters->has('exitOnLowCoverage') && $parameters->get('exitOnLowCoverage'),
            new Exitter(),
            new ConsoleOutput(new \Symfony\Component\Console\Output\ConsoleOutput()),
        );
    }
}
