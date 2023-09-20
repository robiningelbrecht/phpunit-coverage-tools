<?php

namespace RobinIngelbrecht\PHPUnitCoverageTools\Timer;

use SebastianBergmann\Timer\Duration;

interface ResourceUsageFormatter
{
    public function resourceUsage(Duration $duration): string;

    public function resourceUsageSinceStartOfRequest(): string;
}
