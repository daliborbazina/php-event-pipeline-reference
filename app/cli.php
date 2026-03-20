<?php declare(strict_types=1);

// This file acts as the composition root for the CLI commands.

require __DIR__ . '/vendor/autoload.php';

use App\Broker\RabbitMqConfig;
use App\Broker\RabbitMqConnectionFactory;
use App\Broker\RabbitMqConsumer;
use App\Broker\RabbitMqPublisher;
use App\Console\InspectAggregate;
use App\Console\PublishDemoEvent;
use App\Console\PublishInvalidEvent;
use App\Console\RunWorker;
use App\Event\EventDeserializer;
use App\Event\EventSerializer;
use App\Persistence\ActivityAggregateSchema;
use App\Persistence\ActivityAggregateStore;
use App\Persistence\SqliteConfig;
use App\Persistence\SqliteConnectionFactory;
use App\Worker\ActivityRecordedWorker;

$command = $argv[1] ?? null;

if ($command === null) {
    fwrite(STDERR, "Missing command.\n");
    printHelp();
    exit(1);
}

$rabbitMqConfig = RabbitMqConfig::fromEnv();
$rabbitMqConnectionFactory = new RabbitMqConnectionFactory($rabbitMqConfig);
$rabbitMqPublisher = new RabbitMqPublisher($rabbitMqConnectionFactory, $rabbitMqConfig->queue);

$sqliteConfig = SqliteConfig::fromEnv();
$sqliteConnectionFactory = new SqliteConnectionFactory($sqliteConfig);
$sqliteSchema = new ActivityAggregateSchema($sqliteConnectionFactory);
$sqliteSchema->createIfNotExists();

$activityAggregateStore = new ActivityAggregateStore($sqliteConnectionFactory);

switch ($command) {
    case 'init-db':
        $sqliteSchema = new ActivityAggregateSchema($sqliteConnectionFactory);
        $sqliteSchema->createIfNotExists();
        break;

    case 'worker':
        $deserializer = new EventDeserializer();
        $activityRecordedWorker = new ActivityRecordedWorker($deserializer, $activityAggregateStore);
        $rabbitMqConsumer = new RabbitMqConsumer($rabbitMqConnectionFactory, $rabbitMqConfig->queue);
        $worker = new RunWorker($rabbitMqConsumer, $activityRecordedWorker);
        $worker->run();
        break;

    case 'produce':
        $eventSerializer = new EventSerializer();
        $demoEvent = new PublishDemoEvent($rabbitMqPublisher, $eventSerializer);
        $demoEvent->run();
        break;

    case 'produce-invalid':
        $invalidEvent = new PublishInvalidEvent($rabbitMqPublisher);
        $invalidEvent->run();
        break;

    case 'inspect':
        $inspect = new InspectAggregate($activityAggregateStore);
        $inspect->run();
        break;

    default:
        fwrite(STDERR, sprintf("Unknown command: %s\n", $command));
        printHelp();
        exit(1);
}


function printHelp(): void
{
    fwrite(STDERR, "Available commands: worker, produce, produce-invalid, inspect\n");
}
