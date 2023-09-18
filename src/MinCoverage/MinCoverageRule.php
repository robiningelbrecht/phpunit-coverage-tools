<?php

namespace RobinIngelbrecht\PHPUnitCoverageTools\MinCoverage;

final class MinCoverageRule
{
    public const TOTAL = 'Total';

    public function __construct(
        private readonly string $pattern,
        private readonly int $minCoverage,
        private readonly bool $exitOnLowCoverage
    ) {
        if ($this->minCoverage < 0 || $this->minCoverage > 100) {
            throw new \RuntimeException(sprintf('MinCoverage has to be value between 0 and 100. %s given', $this->minCoverage));
        }
    }

    public function getPattern(): string
    {
        return $this->pattern;
    }

    public function getMinCoverage(): int
    {
        return $this->minCoverage;
    }

    public function exitOnLowCoverage(): bool
    {
        return $this->exitOnLowCoverage;
    }

    public function isTotalRule(): bool
    {
        return MinCoverageRule::TOTAL === $this->getPattern();
    }
}
