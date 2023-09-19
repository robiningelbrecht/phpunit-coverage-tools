<?php

namespace RobinIngelbrecht\PHPUnitCoverageTools\Subscriber\Application;

use Composer\Autoload\ClassLoader;
use PHPUnit\Event\Application\Finished;
use PHPUnit\Event\Application\FinishedSubscriber;
use PHPUnit\Runner\Extension\ParameterCollection;
use PHPUnit\TextUI\Configuration\Configuration;
use RobinIngelbrecht\PHPUnitCoverageTools\ConsoleOutput;
use RobinIngelbrecht\PHPUnitCoverageTools\Exitter;
use RobinIngelbrecht\PHPUnitCoverageTools\MinCoverage\CoverageMetric;
use RobinIngelbrecht\PHPUnitCoverageTools\MinCoverage\MinCoverageResult;
use RobinIngelbrecht\PHPUnitCoverageTools\MinCoverage\MinCoverageRule;
use RobinIngelbrecht\PHPUnitCoverageTools\MinCoverage\MinCoverageRules;
use RobinIngelbrecht\PHPUnitCoverageTools\MinCoverage\ResultStatus;
use Symfony\Component\Console\Helper\FormatterHelper;

final class ApplicationFinishedSubscriber extends FormatterHelper implements FinishedSubscriber
{
    public function __construct(
        private readonly string $relativePathToCloverXml,
        private readonly MinCoverageRules $minCoverageRules,
        private readonly bool $cleanUpCloverXml,
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

        /** @var CoverageMetric[] $metrics */
        $metrics = [];
        $metricTotal = null;

        // @TODO: Move this to static function in CoverageMetric?
        $reader = new \XMLReader();
        $reader->open($absolutePathToCloverXml);
        while ($reader->read()) {
            if ($this->minCoverageRules->hasTotalRule() && \XMLReader::ELEMENT == $reader->nodeType && 'metrics' == $reader->name && 2 === $reader->depth) {
                /** @var \SimpleXMLElement $node */
                $node = simplexml_load_string($reader->readOuterXml());
                $metricTotal = CoverageMetric::fromCloverXmlNode($node, MinCoverageRule::TOTAL);
                continue;
            }
            if ($this->minCoverageRules->hasOtherRulesThanTotalRule() && \XMLReader::ELEMENT == $reader->nodeType && 'class' == $reader->name && 3 === $reader->depth) {
                /** @var \SimpleXMLElement $node */
                $node = simplexml_load_string($reader->readInnerXml());
                /** @var string $className */
                $className = $reader->getAttribute('name');
                $metrics[] = CoverageMetric::fromCloverXmlNode($node, $className);
            }
        }
        $reader->close();

        if ($this->cleanUpCloverXml) {
            unlink($absolutePathToCloverXml);
        }

        if (!$metrics && !$metricTotal) {
            throw new \RuntimeException('Could not determine coverage metrics');
        }

        $results = MinCoverageResult::mapFromRulesAndMetrics(
            minCoverageRules: $this->minCoverageRules,
            metrics: $metrics,
            metricTotal: $metricTotal,
        );
        $this->consoleOutput->print($results);

        $needsExit = !empty(array_filter(
            $results,
            fn (MinCoverageResult $minCoverageResult) => $minCoverageResult->exitOnLowCoverage() && ResultStatus::FAILED === $minCoverageResult->getStatus())
        );
        if ($needsExit) {
            $this->exitter->exit();
        }
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

        $rules = null;
        foreach ($args as $arg) {
            if (!str_starts_with($arg, '--min-coverage=')) {
                continue;
            }

            try {
                if (preg_match('/--min-coverage=(?<minCoverage>[\d]+)/', $arg, $matches)) {
                    $rules = MinCoverageRules::fromInt(
                        minCoverage: (int) $matches['minCoverage'],
                        exitOnLowCoverage: $parameters->has('exitOnLowCoverage') && (int) $parameters->get('exitOnLowCoverage')
                    );
                    break;
                }

                if (preg_match('/--min-coverage=(?<minCoverage>[\S]+)/', $arg, $matches)) {
                    $rules = MinCoverageRules::fromConfigFile(trim($matches['minCoverage'], '"'));
                    break;
                }
            } catch (\RuntimeException) {
                return null;
            }
        }

        if (empty($rules) || empty($rules->getRules())) {
            return null;
        }

        if (!$cleanUpCloverXml = in_array('--clean-up-clover-xml', $args, true)) {
            $cleanUpCloverXml = $parameters->has('cleanUpCloverXml') && (int) $parameters->get('cleanUpCloverXml');
        }

        return new self(
            relativePathToCloverXml: $configuration->coverageClover(),
            minCoverageRules: $rules,
            cleanUpCloverXml: $cleanUpCloverXml,
            exitter: new Exitter(),
            consoleOutput: new ConsoleOutput(new \Symfony\Component\Console\Output\ConsoleOutput()),
        );
    }
}
