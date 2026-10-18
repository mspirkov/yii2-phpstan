<?php

declare(strict_types=1);

namespace app\frontend\controllers;

use MSpirkov\Yii2\PHPStan\Tests\Extensions\Source\Application\Clock;
use MSpirkov\Yii2\PHPStan\Tests\Extensions\Source\Application\ClockFactory;
use MSpirkov\Yii2\PHPStan\Tests\Extensions\Source\Application\Logger;
use MSpirkov\Yii2\PHPStan\Tests\Extensions\Source\Application\Mailer;
use MSpirkov\Yii2\PHPStan\Tests\Extensions\Source\Application\PaypalGateway;
use Yii;
use yii\base\Module;
use yii\web\Application;
use yii\web\IdentityInterface;
use yii\web\User;

use function PHPStan\Testing\assertType;

function components(Application $app): void
{
    assertType(PaypalGateway::class, $app->payments);
    assertType(PaypalGateway::class, Yii::$app->payments);
    assertType(Mailer::class, $app->mailer);
    assertType(Logger::class, $app->logger);
    assertType(Logger::class, $app->legacyLogger);
    assertType(Clock::class, $app->clock);
    assertType(ClockFactory::class, $app->clockFactory);
    assertType(Clock::class, $app->factoryClock);
    assertType(Clock::class, $app->untyped);
    assertType(Clock::class, $app->frontendOnly);
}

function params(Application $app): void
{
    assertType('array{adminEmail: string, pageSize: int, ratio: float, maintenance: bool, senderName: mixed, languages: non-empty-list<string>, ids: non-empty-list<int|string>, empty: array, mail: array{host: string, port: int}, clock: ' . Clock::class . ', anonymous: mixed, 5: string, frontendKey: string}', $app->params);
    assertType('string', $app->params['adminEmail']);
    assertType('int', Yii::$app->params['mail']['port']);
}

function skipped(Application $app, Module $module, string $name): void
{
    assertType(User::class . '<' . IdentityInterface::class . '>', $app->user);
    assertType('object', $app->invalid);
    assertType('*ERROR*', $module->payments);
    assertType('mixed', $app->$name);

    $app->params = ['overridden' => true];
    assertType('array{overridden: true}', $app->params);
}
