<?php

use RobinIngelbrecht\PHPUnitCoverageTools\MinCoverage\MinCoverageRule;

return [
    new MinCoverageRule(
        pattern: '*NonExistingPattern',
        minCoverage: 20,
        exitOnLowCoverage: true
    ),
];
