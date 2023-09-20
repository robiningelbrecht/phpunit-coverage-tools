<?php

namespace RobinIngelbrecht\PHPUnitCoverageTools\Timer;

use SebastianBergmann\Timer\Duration;
use SebastianBergmann\Timer\Timer as PhpUnitTimer;

final class SystemTimer implements Timer
{
    private function __construct(
        private readonly PhpUnitTimer $timer,
    ) {
    }

    public function start(): void
    {
        $this->timer->start();
    }

    public function stop(): Duration
    {
        return $this->timer->stop();
    }

    public static function create(): self
    {
        return new self(new PhpUnitTimer());
    }
}
