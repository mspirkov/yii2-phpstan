<?php

declare(strict_types=1);

namespace app\common\components;

use MSpirkov\Yii2\PHPStan\Tests\Extensions\Source\ContainerGet\Clock;
use MSpirkov\Yii2\PHPStan\Tests\Extensions\Source\ContainerGet\ClockFactory;
use MSpirkov\Yii2\PHPStan\Tests\Extensions\Source\ContainerGet\FileLogger;
use MSpirkov\Yii2\PHPStan\Tests\Extensions\Source\ContainerGet\Logger;
use MSpirkov\Yii2\PHPStan\Tests\Extensions\Source\ContainerGet\Mailer;
use MSpirkov\Yii2\PHPStan\Tests\Extensions\Source\ContainerGet\MailerInterface;
use Yii;
use yii\di\Container;

use function PHPStan\Testing\assertType;

function resolved(Container $container): void
{
    assertType(MailerInterface::class, $container->get('mailer'));
    assertType(MailerInterface::class, $container->get('mailer.instance'));
    assertType(Mailer::class, $container->get('mailer.default'));
    assertType(Mailer::class, Yii::$container->get('mailer.default'));
    assertType(FileLogger::class, $container->get('logger'));
    assertType(Logger::class, $container->get('logger.legacy'));
    assertType(Logger::class, $container->get('logger.withParams'));
    assertType(FileLogger::class, $container->get('logger.shared'));
    assertType(Clock::class, $container->get('clock'));
    assertType(Clock::class . '|null', $container->get('clock.nullable'));
    assertType(Clock::class, $container->get('clock.untyped'));
    assertType(Clock::class . '|null', $container->get('clock.untypedNullable'));
    assertType(Clock::class, $container->get('clock.factory'));
    assertType(Clock::class, $container->get('clock.factoryInstance'));
    assertType(Clock::class . '|null', $container->get('clock.nullableFactory'));
    assertType(Clock::class, $container->get('clock.invokable'));
    assertType(Clock::class, $container->get('clock.instance'));
    assertType(Clock::class, $container->get('clock.overridden'));
}

function unions(Container $container, bool $flag): void
{
    assertType(FileLogger::class . '|' . Mailer::class, $container->get($flag ? 'mailer.default' : 'logger'));
    assertType('object', $container->get($flag ? 'mailer' : 'untyped'));
}

function skipped(Container $container, string $id): void
{
    assertType('object', $container->get('untyped'));
    assertType('object', $container->get('untyped.self'));
    assertType('object', $container->get('untyped.sameLine'));
    assertType('object', $container->get('untyped.internal'));
    assertType('object', $container->get('builtin'));
    assertType('object', $container->get('missingReturnClass'));
    assertType('object', $container->get('anonymous'));
    assertType('object', $container->get('factory.object'));
    assertType('object', $container->get('factory.missingMethod'));
    assertType('object', $container->get('factory.missingClass'));
    assertType('object', $container->get('missingClass'));
    assertType('object', $container->get('cycle.a'));
    assertType('object', $container->get('invalidClass'));
    assertType('object', $container->get('noClass'));
    assertType('object', $container->get('invalidType'));
    assertType('object', $container->get('empty'));
    assertType('object', $container->get('undefined'));
    assertType(MailerInterface::class, $container->get(MailerInterface::class));
    assertType(MailerInterface::class, Yii::$container->get(MailerInterface::class));
    assertType(Logger::class, $container->get(Logger::class));
    assertType(FileLogger::class, $container->get(FileLogger::class));
    assertType(ClockFactory::class, $container->get(ClockFactory::class));
    assertType('object', $container->get($id));
    assertType('object', $container->get());
    assertType('bool', $container->has('mailer'));
}
