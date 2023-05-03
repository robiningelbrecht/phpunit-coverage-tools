<?php

namespace RobinIngelbrecht\PHPUnitCoverageTools;

class Exitter
{
    public function exit(int $code): never
    {
        exit($code);
    }
}
