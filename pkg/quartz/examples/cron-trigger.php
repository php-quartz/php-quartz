<?php

use Quartz\Core\CronScheduleBuilder;
use Quartz\Core\Job;
use Quartz\Core\JobBuilder;
use Quartz\Core\JobExecutionContext;
use Quartz\Core\SimpleJobFactory;
use Quartz\Scheduler\StdJobRunShell;
use Quartz\Scheduler\StdJobRunShellFactory;
use Quartz\Core\TriggerBuilder;
use Quartz\Scheduler\StdScheduler;
use Quartz\Bridge\Yadm\YadmStore;
use Quartz\Bridge\Yadm\SimpleStoreResource;
use Symfony\Component\EventDispatcher\EventDispatcher;

require_once '../vendor/autoload.php';

$config = [
    'uri' => sprintf('mongodb://%s:%s', getenv('MONGODB_HOST'), getenv('MONGODB_PORT')),
    'dbName' => getenv('MONGODB_DB')
];

class MyJob implements Job
{
    public function execute(JobExecutionContext $context)
    {
        echo sprintf('Now: %s | Scheduled: %s'.PHP_EOL, date('H:i:s'), $context->getTrigger()->getScheduledFireTime()->format('H:i:s'));
    }
}

$job = JobBuilder::newJob(MyJob::class)->build();

$trigger = TriggerBuilder::newTrigger()
    ->forJobDetail($job)
    ->endAt(new \DateTime('+1 minute'))
    ->withSchedule(CronScheduleBuilder::cronSchedule('*/5 * * * * *'))
    ->build();

$store = new YadmStore(new SimpleStoreResource($config));
$store->clearAllSchedulingData();

$scheduler = new StdScheduler($store, new StdJobRunShellFactory(new StdJobRunShell()), new SimpleJobFactory(), new EventDispatcher());
$scheduler->scheduleJob($job, $trigger);
