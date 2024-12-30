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
        private readonly bool $exitOnLowCoverage
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

    public function exitOnLowCoverage(): bool
    {
        return $this->exitOnLowCoverage;
    }

    /**
     * @param \RobinIngelbrecht\PHPUnitCoverageTools\MinCoverage\CoverageMetric[] $metrics
     *
     * @return \RobinIngelbrecht\PHPUnitCoverageTools\MinCoverage\MinCoverageResult[]
     */
    public static function mapFromRulesAndMetrics(
        MinCoverageRules $minCoverageRules,
        array $metrics,
        CoverageMetric|null $metricTotal = null,
    ): array {
        $results = [];
        foreach ($minCoverageRules->getRules() as $minCoverageRule) {
            $pattern = $minCoverageRule->getPattern();
            $minCoverage = $minCoverageRule->getMinCoverage();
            if (MinCoverageRule::TOTAL === $minCoverageRule->getPattern() && $metricTotal) {
                $results[] = new MinCoverageResult(
                    pattern: $pattern,
                    expectedMinCoverage: $minCoverage,
                    actualMinCoverage: $metricTotal->getTotalPercentageCoverage(),
                    numberOfTrackedLines: $metricTotal->getNumberOfTrackedLines(),
                    numberOfCoveredLines: $metricTotal->getNumberOfCoveredLines(),
                    exitOnLowCoverage: $minCoverageRule->exitOnLowCoverage()
                );
                continue;
            }

            $metricsForPattern = array_filter($metrics, fn (CoverageMetric $metric) => fnmatch($pattern, $metric->getForClass(), FNM_NOESCAPE));
            $totalTrackedLines = array_sum(array_map(fn (CoverageMetric $metric) => $metric->getNumberOfTrackedLines(), $metricsForPattern));
            $totalCoveredLines = array_sum(array_map(fn (CoverageMetric $metric) => $metric->getNumberOfCoveredLines(), $metricsForPattern));

            $coveragePercentage = 0;
            foreach ($metricsForPattern as $metric) {
                if (0 === $totalTrackedLines) {
                    continue;
                }
                $weight = $metric->getNumberOfTrackedLines() / $totalTrackedLines;
                $coveragePercentage += ($metric->getTotalPercentageCoverage() * $weight);
            }

            $results[] = new MinCoverageResult(
                pattern: $pattern,
                expectedMinCoverage: $minCoverage,
                actualMinCoverage: round($coveragePercentage, 2),
                numberOfTrackedLines: $totalTrackedLines,
                numberOfCoveredLines: $totalCoveredLines,
                exitOnLowCoverage: $minCoverageRule->exitOnLowCoverage()
            );
        }

        uasort($results, function (MinCoverageResult $a, MinCoverageResult $b) {
            if (MinCoverageRule::TOTAL === $a->getPattern()) {
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
