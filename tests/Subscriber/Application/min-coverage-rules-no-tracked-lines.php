<?php

use RobinIngelbrecht\PHPUnitCoverageTools\MinCoverage\MinCoverageRule;

return [
    new MinCoverageRule(
        pattern: '*CommandHandler',
        minCoverage: 20,
        exitOnLowCoverage: true
    ),
];
