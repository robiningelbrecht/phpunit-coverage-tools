<?php

namespace RobinIngelbrecht\PHPUnitCoverageTools\MinCoverage;

class CoverageMetric
{
    private function __construct(
        private readonly string $forClass,
        private readonly int $numberOfMethods,
        private readonly int $numberOfCoveredMethods,
        private readonly int $numberOfStatements,
        private readonly int $numberOfCoveredStatements,
        private readonly int $numberOfConditionals,
        private readonly int $numberOfCoveredConditionals,
        private readonly int $numberOfTrackedLines,
        private readonly int $numberOfCoveredLines,
    ) {
    }

    public function getForClass(): string
    {
        return $this->forClass;
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

    public function getTotalPercentageCoverage(): float
    {
        // https://confluence.atlassian.com/clover/how-are-the-clover-coverage-percentages-calculated-79986990.html
        // TPC = (coveredconditionals + coveredstatements + coveredmethods) / (conditionals + statements + methods)
        $divideBy = $this->getNumberOfConditionals() + $this->getNumberOfStatements() + $this->getNumberOfMethods();
        if (0 === $divideBy) {
            return 0.00;
        }

        return round((($this->getNumberOfCoveredConditionals() + $this->getNumberOfCoveredStatements() + $this->getNumberOfCoveredMethods()) /
            $divideBy) * 100, 2);
    }

    public static function fromCloverXmlNode(\SimpleXMLElement $node, string $forClass): self
    {
        /** @var \SimpleXMLElement $attributes */
        $attributes = $node->attributes();

        return new self(
            forClass: $forClass,
            numberOfMethods: (int) $attributes['methods'],
            numberOfCoveredMethods: (int) $attributes['coveredmethods'],
            numberOfStatements: (int) $attributes['statements'],
            numberOfCoveredStatements: (int) $attributes['coveredstatements'],
            numberOfConditionals: (int) $attributes['conditionals'],
            numberOfCoveredConditionals: (int) $attributes['coveredconditionals'],
            numberOfTrackedLines: (int) $attributes['elements'],
            numberOfCoveredLines: (int) $attributes['coveredelements'],
        );
    }
}
