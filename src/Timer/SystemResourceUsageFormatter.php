<?php

namespace RobinIngelbrecht\PHPUnitCoverageTools\Timer;

use SebastianBergmann\Timer\Duration;
use SebastianBergmann\Timer\ResourceUsageFormatter as PhpUnitResourceUsageFormatter;

final class SystemResourceUsageFormatter implements ResourceUsageFormatter
{
    private function __construct(
        private readonly PhpUnitResourceUsageFormatter $resourceUsageFormatter
    ) {
    }

    public static function create(): self
    {
        return new self(new PhpUnitResourceUsageFormatter());
    }

    /**
     * @codeCoverageIgnore
     */
    public function resourceUsage(Duration $duration): string
    {
        return $this->resourceUsageFormatter->resourceUsage($duration);
    }

    /**
     * @codeCoverageIgnore
     */
    public function resourceUsageSinceStartOfRequest(): string
    {
        return $this->resourceUsageFormatter->resourceUsageSinceStartOfRequest();
    }
}
