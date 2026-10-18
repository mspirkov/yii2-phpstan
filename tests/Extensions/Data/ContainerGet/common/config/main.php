<?php

declare(strict_types=1);

use MSpirkov\Yii2\PHPStan\Tests\Extensions\Source\ContainerGet\Clock;
use MSpirkov\Yii2\PHPStan\Tests\Extensions\Source\ContainerGet\ClockFactory;
use MSpirkov\Yii2\PHPStan\Tests\Extensions\Source\ContainerGet\FileLogger;
use MSpirkov\Yii2\PHPStan\Tests\Extensions\Source\ContainerGet\Logger;
use MSpirkov\Yii2\PHPStan\Tests\Extensions\Source\ContainerGet\Mailer;
use MSpirkov\Yii2\PHPStan\Tests\Extensions\Source\ContainerGet\MailerInterface;
use yii\di\Container;
use yii\di\Instance;

return [
    'id' => 'common',
    'container' => [
        'resolveArrays' => true,
        'definitions' => [
            MailerInterface::class => Mailer::class,
            'mailer' => MailerInterface::class,
            'mailer.default' => Mailer::class,
            'mailer.instance' => Instance::of('mailer'),
            'logger' => ['class' => Logger::class, 'level' => 1],
            'logger.legacy' => ['__class' => Logger::class],
            'logger.withParams' => [['class' => Logger::class], [['level' => 2]]],
            Logger::class => ['level' => 3],
            FileLogger::class => [],
            'clock' => static fn(): Clock => new Clock(),
            'clock.nullable' => static fn(): ?Clock => null,
            'clock.factory' => [ClockFactory::class, 'create'],
            'clock.factoryInstance' => [new ClockFactory(), 'create'],
            'clock.nullableFactory' => [ClockFactory::class, 'createNullable'],
            'clock.invokable' => new ClockFactory(),
            'clock.instance' => new Clock(),
            'clock.overridden' => Logger::class,
            'clock.untyped' => static fn() => new Clock(),
            'clock.untypedNullable' => static function (Container $container, array $params) {
                return $params === [] ? null : new Clock();
            },
            'untyped' => static fn() => 42,
            'untyped.self' => static fn(Container $container) => $container->get('untyped.self'),
            'untyped.sameLine' => static fn() => static fn() => new Clock(),
            'untyped.internal' => Closure::fromCallable('tmpfile'),
            'builtin' => static fn(): object => new Clock(),
            'missingReturnClass' => static fn(): \app\MissingClass => new Clock(),
            'anonymous' => new class {},
            'factory.object' => [ClockFactory::class, 'createObject'],
            'factory.missingMethod' => [ClockFactory::class, 'missing'],
            'factory.missingClass' => ['app\MissingFactory', 'create'],
            'missingClass' => 'app\MissingClass',
            'cycle.a' => 'cycle.b',
            'cycle.b' => 'cycle.a',
            'invalidClass' => ['class' => 42],
            'noClass' => ['level' => 1],
            'invalidType' => 42,
            'empty' => [],
            ClockFactory::class => static function () {
                return new ClockFactory();
            },
        ],
        'singletons' => [
            'logger.shared' => [['class' => FileLogger::class], []],
            'clock.overridden' => Clock::class,
        ],
    ],
];
