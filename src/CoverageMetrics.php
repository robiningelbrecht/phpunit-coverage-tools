<?php

namespace RobinIngelbrecht\PHPUnitCoverageTools;

class CoverageMetrics
{
    private function __construct(
        private readonly int $numberOfMethods,
        private readonly int $numberOfCoveredMethods,
        private readonly int $numberOfStatements,
        private readonly int $numberOfCoveredStatements,
        private readonly int $numberOfConditionals,
        private readonly int $numberOfCoveredConditionals,
        private readonly int $numberOfTrackedLines,
        private readonly int $numberOfCoveredLines,
        private readonly int $numberOfFiles,
    ) {
    }

    public function getNumberOfMethods(): int
    {
        return $this->numberOfMethods;
    }

    public function getNumberOfCoveredMethods(): int
    {
        return $this->numberOfCoveredMethods;
    }

    public function getNumberOfStatements(): int
    {
        return $this->numberOfStatements;
    }

    public function getNumberOfCoveredStatements(): int
    {
        return $this->numberOfCoveredStatements;
    }

    public function getNumberOfConditionals(): int
    {
        return $this->numberOfConditionals;
    }

    public function getNumberOfCoveredConditionals(): int
    {
        return $this->numberOfCoveredConditionals;
    }

    public function getNumberOfTrackedLines(): int
    {
        return $this->numberOfTrackedLines;
    }

    public function getNumberOfCoveredLines(): int
    {
        return $this->numberOfCoveredLines;
    }

    public function getNumberOfFiles(): int
    {
        return $this->numberOfFiles;
    }

    public function getTotalPercentageCoverage(): float
    {
        // https://confluence.atlassian.com/clover/how-are-the-clover-coverage-percentages-calculated-79986990.html
        // TPC = (coveredconditionals + coveredstatements + coveredmethods) / (conditionals + statements + methods)

        return round((($this->getNumberOfCoveredConditionals() + $this->getNumberOfCoveredStatements() + $this->getNumberOfCoveredMethods()) /
            ($this->getNumberOfConditionals() + $this->getNumberOfStatements() + $this->getNumberOfMethods())) * 100, 2);
    }

    public static function fromCloverXmlNode(\SimpleXMLElement $node): self
    {
        /** @var \SimpleXMLElement $attributes */
        $attributes = $node->attributes();

        return new self(
            (int) $attributes['methods'],
            (int) $attributes['coveredmethods'],
            (int) $attributes['statements'],
            (int) $attributes['coveredstatements'],
            (int) $attributes['conditionals'],
            (int) $attributes['coveredconditionals'],
            (int) $attributes['elements'],
            (int) $attributes['coveredelements'],
            (int) $attributes['files'],
        );
    }
}
