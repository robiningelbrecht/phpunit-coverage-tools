<?php

namespace RobinIngelbrecht\PHPUnitCoverageTools\MinCoverage;

use Composer\Autoload\ClassLoader;

class MinCoverageRules
{
    /** @deprecated Use MinCoverageRule::TOTAL  */
    public const TOTAL = 'Total';

    private function __construct(
        /** @var \RobinIngelbrecht\PHPUnitCoverageTools\MinCoverage\MinCoverageRule[] */
        private readonly array $rules
    ) {
    }

    /**
     * @return \RobinIngelbrecht\PHPUnitCoverageTools\MinCoverage\MinCoverageRule[]
     */
    public function getRules(): array
    {
        return $this->rules;
    }

    public function hasTotalRule(): bool
    {
        foreach ($this->rules as $rule) {
            if ($rule->isTotalRule()) {
                return true;
            }
        }

        return false;
    }

    public function hasOtherRulesThanTotalRule(): bool
    {
        foreach ($this->rules as $rule) {
            if (!$rule->isTotalRule()) {
                return true;
            }
        }

        return false;
    }

    public static function fromInt(int $minCoverage, bool $exitOnLowCoverage): self
    {
        return new self(
            [new MinCoverageRule(
                pattern: self::TOTAL,
                minCoverage: $minCoverage,
                exitOnLowCoverage: $exitOnLowCoverage
            )],
        );
    }

    public static function fromConfigFile(string $filePathToConfigFile): self
    {
        /** @var string $reflectionFileName */
        $reflectionFileName = (new \ReflectionClass(ClassLoader::class))->getFileName();
        $absolutePathToConfigFile = dirname($reflectionFileName, 3).'/'.$filePathToConfigFile;

        if (!file_exists($absolutePathToConfigFile)) {
            throw new \RuntimeException(sprintf('Config file %s not found', $absolutePathToConfigFile));
        }

        $rules = require $absolutePathToConfigFile;
        foreach ($rules as $minCoverageRule) {
            if (!$minCoverageRule instanceof MinCoverageRule) {
                throw new \RuntimeException('Make sure all coverage rules are of instance '.MinCoverageRule::class);
            }
        }

        return new self($rules);
    }
}
