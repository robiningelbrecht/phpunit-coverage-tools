<?php

namespace RobinIngelbrecht\PHPUnitCoverageTools\MinCoverage;

class MinCoverageResult
{
    private function __construct(
        private readonly string $pattern,
        private readonly int $expectedMinCoverage,
        private readonly float $actualMinCoverage,
        private readonly int $numberOfTrackedLines,
        private readonly int $numberOfCoveredLines,
    ) {
    }

    public function getPattern(): string
    {
        return $this->pattern;
    }

    public function getStatus(): ResultStatus
    {
        if (0 === $this->getNumberOfTrackedLines()) {
            return ResultStatus::WARNING;
        }

        return $this->expectedMinCoverage <= $this->actualMinCoverage ? ResultStatus::SUCCESS : ResultStatus::FAILED;
    }

    public function getExpectedMinCoverage(): int
    {
        return $this->expectedMinCoverage;
    }

    public function getActualMinCoverage(): float
    {
        return $this->actualMinCoverage;
    }

    public function getNumberOfTrackedLines(): int
    {
        return $this->numberOfTrackedLines;
    }

    public function getNumberOfCoveredLines(): int
    {
        return $this->numberOfCoveredLines;
    }

    public static function fromPatternAndNumbers(
        string $pattern,
        int $expectedMinCoverage,
        float $actualMinCoverage,
        int $numberOfTrackedLines,
        int $numberOfCoveredLines,
    ): self {
        return new self(
            $pattern,
            $expectedMinCoverage,
            $actualMinCoverage,
            $numberOfTrackedLines,
            $numberOfCoveredLines,
        );
    }

    /**
     * @param \RobinIngelbrecht\PHPUnitCoverageTools\MinCoverage\CoverageMetric[] $metrics
     *
     * @return \RobinIngelbrecht\PHPUnitCoverageTools\MinCoverage\MinCoverageResult[]
     */
    public static function mapFromRulesAndMetrics(
        MinCoverageRules $minCoverageRules,
        array $metrics,
        CoverageMetric $metricTotal = null,
    ): array {
        $results = [];
        foreach ($minCoverageRules->getRules() as $pattern => $minCoverage) {
            if (MinCoverageRules::TOTAL === $pattern && $metricTotal) {
                $results[] = MinCoverageResult::fromPatternAndNumbers(
                    $pattern,
                    $minCoverage,
                    $metricTotal->getTotalPercentageCoverage(),
                    $metricTotal->getNumberOfTrackedLines(),
                    $metricTotal->getNumberOfCoveredLines()
                );
                continue;
            }

            $metricsForPattern = array_filter($metrics, fn (CoverageMetric $metric) => fnmatch($pattern, $metric->getForClass(), FNM_NOESCAPE));
            $totalTrackedLines = array_sum(array_map(fn (CoverageMetric $metric) => $metric->getNumberOfTrackedLines(), $metricsForPattern));
            $totalCoveredLines = array_sum(array_map(fn (CoverageMetric $metric) => $metric->getNumberOfCoveredLines(), $metricsForPattern));

            $coveragePercentage = 0;
            foreach ($metricsForPattern as $metric) {
                $weight = $metric->getNumberOfTrackedLines() / $totalTrackedLines;
                $coveragePercentage += ($metric->getTotalPercentageCoverage() * $weight);
            }

            $results[] = MinCoverageResult::fromPatternAndNumbers(
                $pattern,
                $minCoverage,
                round($coveragePercentage, 2),
                $totalTrackedLines,
                $totalCoveredLines
            );
        }

        uasort($results, function (MinCoverageResult $a, MinCoverageResult $b) {
            if (MinCoverageRules::TOTAL === $a->getPattern()) {
                return 1;
            }
            if ($a->getStatus() === $b->getStatus()) {
                return 0;
            }

            return ($a->getStatus()->getWeight() < $b->getStatus()->getWeight()) ? 1 : -1;
        });

        return $results;
    }
}
