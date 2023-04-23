<?php

namespace RobinIngelbrecht\PHPUnitCoverageTools;

class Exitter
{
    public function exit(int $code): void
    {
        exit($code);
    }
}
