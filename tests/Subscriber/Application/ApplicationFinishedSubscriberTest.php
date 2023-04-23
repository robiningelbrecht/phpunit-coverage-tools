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
use RobinIngelbrecht\PHPUnitCoverageTools\Subscriber\Application\ApplicationFinishedSubscriber;
use Spatie\Snapshots\MatchesSnapshots;
use Tests\SpyOutput;

class ApplicationFinishedSubscriberTest extends TestCase
{
    use MatchesSnapshots;

    public function testNotifyWhenCoverageTooLow(): void
    {
        $spyOutput = new SpyOutput();
        $subscriber = new ApplicationFinishedSubscriber(
            'tests/clover.xml',
            90,
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
                    new GarbageCollectorStatus(0, 0, 0, 0, null, null, null, null)
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

     public function testNotifyWhenCoverageTooLowWithExit(): void
     {
         $exitter = $this->createMock(Exitter::class);

         $exitter
             ->expects($this->once())
             ->method('exit')
             ->with(1);

         $spyOutput = new SpyOutput();
         $subscriber = new ApplicationFinishedSubscriber(
             'tests/clover.xml',
             90,
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
                     new GarbageCollectorStatus(0, 0, 0, 0, null, null, null, null)
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
             20,
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
                     new GarbageCollectorStatus(0, 0, 0, 0, null, null, null, null)
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

     public function testNotifyWithInvalidCloverFile(): void
     {
         $spyOutput = new SpyOutput();
         $subscriber = new ApplicationFinishedSubscriber(
             'tests/clover-wrong.xml',
             90,
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
                     new GarbageCollectorStatus(0, 0, 0, 0, null, null, null, null)
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

     public function testFromConfigurationAndParameters(): void
     {
         $this->assertEquals(
             new ApplicationFinishedSubscriber(
                 'tests/clover.xml',
                 90,
                 false,
                 new Exitter(),
                 new ConsoleOutput(new \Symfony\Component\Console\Output\ConsoleOutput()),
             ),
             ApplicationFinishedSubscriber::fromConfigurationAndParameters(
                 (new Builder())->build([
                     '--coverage-clover=tests/clover.xml',
                 ]),
                 ParameterCollection::fromArray([]),
                 ['--min-coverage=90']
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
}
