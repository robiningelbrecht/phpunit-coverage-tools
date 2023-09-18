<?php

use RobinIngelbrecht\PHPUnitCoverageTools\MinCoverage\MinCoverageRules;

return [
    MinCoverageRules::TOTAL => 20,
    'RobinIngelbrecht\PHPUnitCoverageTools\*' => 80,
    'RobinIngelbrecht\PHPUnitCoverageTools\Subscriber\Application\ApplicationFinishedSubscriber' => 100,
    'RobinIngelbrecht\PHPUnitCoverageTools\*CommandHandler' => 100,
];
