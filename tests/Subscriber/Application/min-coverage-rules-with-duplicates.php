<?php

use RobinIngelbrecht\PHPUnitCoverageTools\MinCoverage\MinCoverageRule;

return [
    new MinCoverageRule(
        pattern: 'RobinIngelbrecht\PHPUnitCoverageTools\PhpUnitExtension',
        minCoverage: 100,
        exitOnLowCoverage: true
    ),
    new MinCoverageRule(
        pattern: 'RobinIngelbrecht\PHPUnitCoverageTools\PhpUnitExtension',
        minCoverage: 100,
        exitOnLowCoverage: true
    ),
];
