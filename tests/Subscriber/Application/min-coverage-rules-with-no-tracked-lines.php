<?php

use RobinIngelbrecht\PHPUnitCoverageTools\MinCoverage\MinCoverageRule;

return [
    new MinCoverageRule(
        pattern: '*NonExistingPattern',
        minCoverage: 100,
        exitOnLowCoverage: true
    ),
];
