<?php

namespace Tests;

use RobinIngelbrecht\PHPUnitCoverageTools\Timer\Timer;
use SebastianBergmann\Timer\Duration;

final class PausedTimer implements Timer
{
    private function __construct(
        private readonly Duration $duration
    ) {
    }

    public static function withDuration(Duration $duration): self
    {
        return new self($duration);
    }

    public function start(): void
    {
    }

    public function stop(): Duration
    {
        return $this->duration;
    }
}
