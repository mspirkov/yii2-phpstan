<?php

declare(strict_types=1);

namespace app\common\components;

use MSpirkov\Yii2\PHPStan\Tests\Extensions\Source\ContainerGet\ClockFactory;
use MSpirkov\Yii2\PHPStan\Tests\Extensions\Source\ContainerGet\FileLogger;
use MSpirkov\Yii2\PHPStan\Tests\Extensions\Source\ContainerGet\Logger;
use MSpirkov\Yii2\PHPStan\Tests\Extensions\Source\ContainerGet\MailerInterface;
use Yii;
use yii\di\Container;

use function PHPStan\Testing\assertType;

function resolved(Container $container): void
{
    assertType('MSpirkov\Yii2\PHPStan\Tests\Extensions\Source\ContainerGet\Mailer', $container->get(MailerInterface::class));
    assertType('MSpirkov\Yii2\PHPStan\Tests\Extensions\Source\ContainerGet\Mailer', Yii::$container->get(MailerInterface::class));
    assertType('MSpirkov\Yii2\PHPStan\Tests\Extensions\Source\ContainerGet\Mailer', $container->get('mailer'));
    assertType('MSpirkov\Yii2\PHPStan\Tests\Extensions\Source\ContainerGet\Mailer', $container->get('mailer.instance'));
    assertType('MSpirkov\Yii2\PHPStan\Tests\Extensions\Source\ContainerGet\FileLogger', $container->get('logger'));
    assertType('MSpirkov\Yii2\PHPStan\Tests\Extensions\Source\ContainerGet\Logger', $container->get('logger.legacy'));
    assertType('MSpirkov\Yii2\PHPStan\Tests\Extensions\Source\ContainerGet\Logger', $container->get('logger.withParams'));
    assertType('MSpirkov\Yii2\PHPStan\Tests\Extensions\Source\ContainerGet\Logger', $container->get(Logger::class));
    assertType('MSpirkov\Yii2\PHPStan\Tests\Extensions\Source\ContainerGet\FileLogger', $container->get(FileLogger::class));
    assertType('MSpirkov\Yii2\PHPStan\Tests\Extensions\Source\ContainerGet\FileLogger', $container->get('logger.shared'));
    assertType('MSpirkov\Yii2\PHPStan\Tests\Extensions\Source\ContainerGet\Clock', $container->get('clock'));
    assertType('MSpirkov\Yii2\PHPStan\Tests\Extensions\Source\ContainerGet\Clock|null', $container->get('clock.nullable'));
    assertType('MSpirkov\Yii2\PHPStan\Tests\Extensions\Source\ContainerGet\Clock', $container->get('clock.factory'));
    assertType('MSpirkov\Yii2\PHPStan\Tests\Extensions\Source\ContainerGet\Clock', $container->get('clock.factoryInstance'));
    assertType('MSpirkov\Yii2\PHPStan\Tests\Extensions\Source\ContainerGet\Clock|null', $container->get('clock.nullableFactory'));
    assertType('MSpirkov\Yii2\PHPStan\Tests\Extensions\Source\ContainerGet\Clock', $container->get('clock.invokable'));
    assertType('MSpirkov\Yii2\PHPStan\Tests\Extensions\Source\ContainerGet\Clock', $container->get('clock.instance'));
    assertType('MSpirkov\Yii2\PHPStan\Tests\Extensions\Source\ContainerGet\Clock', $container->get('clock.overridden'));
}

function unions(Container $container, bool $flag): void
{
    assertType('MSpirkov\Yii2\PHPStan\Tests\Extensions\Source\ContainerGet\FileLogger|MSpirkov\Yii2\PHPStan\Tests\Extensions\Source\ContainerGet\Mailer', $container->get($flag ? 'mailer' : 'logger'));
    assertType('object', $container->get($flag ? 'mailer' : 'untyped'));
}

function skipped(Container $container, string $id): void
{
    assertType('object', $container->get('untyped'));
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
    assertType('MSpirkov\Yii2\PHPStan\Tests\Extensions\Source\ContainerGet\ClockFactory', $container->get(ClockFactory::class));
    assertType('object', $container->get($id));
    assertType('object', $container->get());
    assertType('bool', $container->has('mailer'));
}
