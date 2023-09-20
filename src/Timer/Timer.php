<?php

namespace RobinIngelbrecht\PHPUnitCoverageTools\Timer;

use SebastianBergmann\Timer\Duration;

interface Timer
{
    public function start(): void;

    public function stop(): Duration;
}
