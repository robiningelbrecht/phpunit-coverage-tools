<?php

namespace Tests\Subscriber\Application;

use PHPUnit\Event\Application\Finished;
use PHPUnit\Event\Telemetry\Duration;
use PHPUnit\Event\Telemetry\GarbageCollectorStatus;
use PHPUnit\Event\Telemetry\HRTime;
use PHPUnit\Event\Telemetry\Info;
use PHPUnit\Event\Telemetry\MemoryUsage;
use PHPUnit\Event\Telemetry\Snapshot;
use PHPUnit\Framework\TestCase;
use PHPUnit\Runner\Extension\ParameterCollection;
use PHPUnit\TextUI\Configuration\Builder;
use RobinIngelbrecht\PHPUnitCoverageTools\ConsoleOutput;
use RobinIngelbrecht\PHPUnitCoverageTools\Exitter;
use RobinIngelbrecht\PHPUnitCoverageTools\MinCoverage\MinCoverageRules;
use RobinIngelbrecht\PHPUnitCoverageTools\Subscriber\Application\ApplicationFinishedSubscriber;
use Spatie\Snapshots\MatchesSnapshots;
use Tests\SpyOutput;

class ApplicationFinishedSubscriberTest extends TestCase
{
    use MatchesSnapshots;

    public function testNotifyWithAtLeastOneFailedRule(): void
    {
        $exitter = $this->createMock(Exitter::class);

        $exitter
            ->expects($this->once())
            ->method('exit')
            ->with(1);

        $spyOutput = new SpyOutput();
        $subscriber = new ApplicationFinishedSubscriber(
            'tests/clover.xml',
            MinCoverageRules::fromConfigFile('tests/Subscriber/Application/min-coverage-rules-with-failed-rule.php'),
            true,
            false,
            $exitter,
            new ConsoleOutput($spyOutput),
        );

        $subscriber->notify(new Finished(
            new Info(
                new Snapshot(
                    HRTime::fromSecondsAndNanoseconds(1, 0),
                    MemoryUsage::fromBytes(100),
                    MemoryUsage::fromBytes(100),
                    new GarbageCollectorStatus(0, 0, 0, 0, null, null, null, null, null, null, null, null)
                ),
                Duration::fromSecondsAndNanoseconds(1, 0),
                MemoryUsage::fromBytes(100),
                Duration::fromSecondsAndNanoseconds(1, 0),
                MemoryUsage::fromBytes(100),
            ),
            0
        ));

        $this->assertMatchesTextSnapshot($spyOutput);
    }

    public function testNotifyWithAWarning(): void
    {
        $spyOutput = new SpyOutput();
        $subscriber = new ApplicationFinishedSubscriber(
            'tests/clover.xml',
            MinCoverageRules::fromConfigFile('tests/Subscriber/Application/min-coverage-rules-with-warning.php'),
            false,
            false,
            new Exitter(),
            new ConsoleOutput($spyOutput),
        );

        $subscriber->notify(new Finished(
            new Info(
                new Snapshot(
                    HRTime::fromSecondsAndNanoseconds(1, 0),
                    MemoryUsage::fromBytes(100),
                    MemoryUsage::fromBytes(100),
                    new GarbageCollectorStatus(0, 0, 0, 0, null, null, null, null, null, null, null, null)
                ),
                Duration::fromSecondsAndNanoseconds(1, 0),
                MemoryUsage::fromBytes(100),
                Duration::fromSecondsAndNanoseconds(1, 0),
                MemoryUsage::fromBytes(100),
            ),
            0
        ));

        $this->assertMatchesTextSnapshot($spyOutput);
    }

    public function testNotifyWhenCoverageIsOk(): void
    {
        $spyOutput = new SpyOutput();
        $subscriber = new ApplicationFinishedSubscriber(
            'tests/clover.xml',
            MinCoverageRules::fromConfigFile('tests/Subscriber/Application/min-coverage-rules-success.php'),
            false,
            false,
            new Exitter(),
            new ConsoleOutput($spyOutput),
        );

        $subscriber->notify(new Finished(
            new Info(
                new Snapshot(
                    HRTime::fromSecondsAndNanoseconds(1, 0),
                    MemoryUsage::fromBytes(100),
                    MemoryUsage::fromBytes(100),
                    new GarbageCollectorStatus(0, 0, 0, 0, null, null, null, null, null, null, null, null)
                ),
                Duration::fromSecondsAndNanoseconds(1, 0),
                MemoryUsage::fromBytes(100),
                Duration::fromSecondsAndNanoseconds(1, 0),
                MemoryUsage::fromBytes(100),
            ),
            0
        ));

        $this->assertMatchesTextSnapshot($spyOutput);
    }

    public function testNotifyWhitOnlyTotal(): void
    {
        $spyOutput = new SpyOutput();
        $subscriber = new ApplicationFinishedSubscriber(
            'tests/clover.xml',
            MinCoverageRules::fromConfigFile('tests/Subscriber/Application/min-coverage-rules-total-only.php'),
            false,
            false,
            new Exitter(),
            new ConsoleOutput($spyOutput),
        );

        $subscriber->notify(new Finished(
            new Info(
                new Snapshot(
                    HRTime::fromSecondsAndNanoseconds(1, 0),
                    MemoryUsage::fromBytes(100),
                    MemoryUsage::fromBytes(100),
                    new GarbageCollectorStatus(0, 0, 0, 0, null, null, null, null, null, null, null, null)
                ),
                Duration::fromSecondsAndNanoseconds(1, 0),
                MemoryUsage::fromBytes(100),
                Duration::fromSecondsAndNanoseconds(1, 0),
                MemoryUsage::fromBytes(100),
            ),
            0
        ));

        $this->assertMatchesTextSnapshot($spyOutput);
    }

    public function testNotifyWhitInvalidRules(): void
    {
        $spyOutput = new SpyOutput();

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('MinCoverage has to be value between 0 and 100. 203 given');

        new ApplicationFinishedSubscriber(
            'tests/clover.xml',
            MinCoverageRules::fromConfigFile('tests/Subscriber/Application/min-coverage-rules-invalid.php'),
            false,
            false,
            new Exitter(),
            new ConsoleOutput($spyOutput),
        );
    }

    public function testNotifyWithNonExistingCloverFile(): void
    {
        $spyOutput = new SpyOutput();
        $subscriber = new ApplicationFinishedSubscriber(
            'tests/clover-wrong.xml',
            MinCoverageRules::fromConfigFile('tests/Subscriber/Application/min-coverage-rules-success.php'),
            false,
            false,
            new Exitter(),
            new ConsoleOutput($spyOutput),
        );

        $subscriber->notify(new Finished(
            new Info(
                new Snapshot(
                    HRTime::fromSecondsAndNanoseconds(1, 0),
                    MemoryUsage::fromBytes(100),
                    MemoryUsage::fromBytes(100),
                    new GarbageCollectorStatus(0, 0, 0, 0, null, null, null, null, null, null, null, null)
                ),
                Duration::fromSecondsAndNanoseconds(1, 0),
                MemoryUsage::fromBytes(100),
                Duration::fromSecondsAndNanoseconds(1, 0),
                MemoryUsage::fromBytes(100),
            ),
            0
        ));

        $this->assertEmpty((string) $spyOutput);
    }

    public function testNotifyWithInvalidCloverFile(): void
    {
        $spyOutput = new SpyOutput();
        $subscriber = new ApplicationFinishedSubscriber(
            'tests/clover-invalid.xml',
            MinCoverageRules::fromConfigFile('tests/Subscriber/Application/min-coverage-rules-success.php'),
            false,
            false,
            new Exitter(),
            new ConsoleOutput($spyOutput),
        );

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Could not determine coverage metrics');

        $subscriber->notify(new Finished(
            new Info(
                new Snapshot(
                    HRTime::fromSecondsAndNanoseconds(1, 0),
                    MemoryUsage::fromBytes(100),
                    MemoryUsage::fromBytes(100),
                    new GarbageCollectorStatus(0, 0, 0, 0, null, null, null, null, null, null, null, null)
                ),
                Duration::fromSecondsAndNanoseconds(1, 0),
                MemoryUsage::fromBytes(100),
                Duration::fromSecondsAndNanoseconds(1, 0),
                MemoryUsage::fromBytes(100),
            ),
            0
        ));
    }

    public function testNotifyWithCleanUpCloverFile(): void
    {
        copy(dirname(__DIR__, 2).'/clover.xml', dirname(__DIR__, 2).'/clover-to-delete.xml');
        $exitter = $this->createMock(Exitter::class);

        $exitter
            ->expects($this->once())
            ->method('exit')
            ->with(1);

        $spyOutput = new SpyOutput();
        $subscriber = new ApplicationFinishedSubscriber(
            'tests/clover-to-delete.xml',
            MinCoverageRules::fromConfigFile('tests/Subscriber/Application/min-coverage-rules-with-failed-rule.php'),
            true,
            true,
            $exitter,
            new ConsoleOutput($spyOutput),
        );

        $subscriber->notify(new Finished(
            new Info(
                new Snapshot(
                    HRTime::fromSecondsAndNanoseconds(1, 0),
                    MemoryUsage::fromBytes(100),
                    MemoryUsage::fromBytes(100),
                    new GarbageCollectorStatus(0, 0, 0, 0, null, null, null, null, null, null, null, null)
                ),
                Duration::fromSecondsAndNanoseconds(1, 0),
                MemoryUsage::fromBytes(100),
                Duration::fromSecondsAndNanoseconds(1, 0),
                MemoryUsage::fromBytes(100),
            ),
            0
        ));

        $this->assertFileDoesNotExist(dirname(__DIR__, 2).'/clover-to-delete.xml');
    }

    public function testFromConfigurationAndParameters(): void
    {
        $this->assertEquals(
            new ApplicationFinishedSubscriber(
                'tests/clover.xml',
                MinCoverageRules::fromInt(90),
                false,
                true,
                new Exitter(),
                new ConsoleOutput(new \Symfony\Component\Console\Output\ConsoleOutput()),
            ),
            ApplicationFinishedSubscriber::fromConfigurationAndParameters(
                (new Builder())->build([
                    '--coverage-clover=tests/clover.xml',
                ]),
                ParameterCollection::fromArray([]),
                ['--min-coverage=90', '--clean-up-clover-xml']
            ),
        );
    }

    public function testFromConfigurationAndParametersFromFile(): void
    {
        $this->assertEquals(
            new ApplicationFinishedSubscriber(
                'tests/clover.xml',
                MinCoverageRules::fromConfigFile('tests/Subscriber/Application/min-coverage-rules-success.php'),
                false,
                false,
                new Exitter(),
                new ConsoleOutput(new \Symfony\Component\Console\Output\ConsoleOutput()),
            ),
            ApplicationFinishedSubscriber::fromConfigurationAndParameters(
                (new Builder())->build([
                    '--coverage-clover=tests/clover.xml',
                ]),
                ParameterCollection::fromArray([]),
                ['--min-coverage="tests/Subscriber/Application/min-coverage-rules-success.php"']
            ),
        );
    }

    public function testFromConfigurationAndParametersWhenInvalidMinCoverage(): void
    {
        $this->assertNull(
            ApplicationFinishedSubscriber::fromConfigurationAndParameters(
                (new Builder())->build([
                    '--coverage-clover=tests/clover.xml',
                ]),
                ParameterCollection::fromArray([]),
                ['--min-coverage=a-word']
            ),
        );
    }

    public function testFromConfigurationAndParametersWhenCoverageTooHigh(): void
    {
        $this->assertNull(
            ApplicationFinishedSubscriber::fromConfigurationAndParameters(
                (new Builder())->build([
                    '--coverage-clover=tests/clover.xml',
                ]),
                ParameterCollection::fromArray([]),
                ['--min-coverage=101']
            ),
        );
    }

    public function testFromConfigurationAndParametersWhenRulesAreEmpty(): void
    {
        $this->assertNull(
            ApplicationFinishedSubscriber::fromConfigurationAndParameters(
                (new Builder())->build([
                    '--coverage-clover=tests/clover.xml',
                ]),
                ParameterCollection::fromArray([]),
                ['--min-coverage="tests/Subscriber/Application/min-coverage-rules-empty.php"']
            ),
        );
    }
}
