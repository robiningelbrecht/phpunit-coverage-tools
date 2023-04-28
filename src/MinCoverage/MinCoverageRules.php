<?php

namespace RobinIngelbrecht\PHPUnitCoverageTools\MinCoverage;

use Composer\Autoload\ClassLoader;

class MinCoverageRules
{
    public const TOTAL = 'Total';

    private function __construct(
        /** @var array<string, int> */
        private readonly array $rules
    ) {
    }

    /**
     * @return array<string, int>
     */
    public function getRules(): array
    {
        return $this->rules;
    }

    public function hasTotalRule(): bool
    {
        return array_key_exists(self::TOTAL, $this->rules);
    }

    public function hasOtherRulesThanTotalRule(): bool
    {
        foreach ($this->rules as $pattern => $minCoverage) {
            if (self::TOTAL !== $pattern) {
                return true;
            }
        }

        return false;
    }

    public static function fromInt(int $minCoverage): self
    {
        if ($minCoverage < 0 || $minCoverage > 100) {
            throw new \RuntimeException(sprintf('MinCoverage has to be value between 0 and 100. %s given', $minCoverage));
        }

        return new self(
            [self::TOTAL => $minCoverage],
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
        foreach ($rules as $minCoverage) {
            if ($minCoverage < 0 || $minCoverage > 100) {
                throw new \RuntimeException(sprintf('MinCoverage has to be value between 0 and 100. %s given', $minCoverage));
            }
        }

        return new self($rules);
    }
}
