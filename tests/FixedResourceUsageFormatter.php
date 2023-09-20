<?php

namespace Tests;

use RobinIngelbrecht\PHPUnitCoverageTools\Timer\ResourceUsageFormatter;
use SebastianBergmann\Timer\Duration;

final class FixedResourceUsageFormatter implements ResourceUsageFormatter
{
    private function __construct(
        private readonly float $usageInMb
    ) {
    }

    public static function withUsageInMb(float $usageInMb): self
    {
        return new self($usageInMb);
    }

    public function resourceUsage(Duration $duration): string
    {
        return sprintf(
            'Time: %s, Memory: %s MB',
            $duration->asString(),
            number_format($this->usageInMb, 2, '.', ''),
        );
    }

    public function resourceUsageSinceStartOfRequest(): string
    {
        return sprintf(
            'Time: 00:00.350, Memory: %s MB',
            number_format($this->usageInMb, 2, '.', ''),
        );
    }
}
