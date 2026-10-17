<?php

declare(strict_types=1);

namespace app\frontend\controllers;

use Yii;
use yii\base\Module;
use yii\web\Application;

use function PHPStan\Testing\assertType;

function components(Application $app): void
{
    assertType('MSpirkov\Yii2\PHPStan\Tests\Extensions\Source\Application\PaypalGateway', $app->payments);
    assertType('MSpirkov\Yii2\PHPStan\Tests\Extensions\Source\Application\PaypalGateway', Yii::$app->payments);
    assertType('MSpirkov\Yii2\PHPStan\Tests\Extensions\Source\Application\Mailer', $app->mailer);
    assertType('MSpirkov\Yii2\PHPStan\Tests\Extensions\Source\Application\Logger', $app->logger);
    assertType('MSpirkov\Yii2\PHPStan\Tests\Extensions\Source\Application\Logger', $app->legacyLogger);
    assertType('MSpirkov\Yii2\PHPStan\Tests\Extensions\Source\Application\Clock', $app->clock);
    assertType('MSpirkov\Yii2\PHPStan\Tests\Extensions\Source\Application\ClockFactory', $app->clockFactory);
    assertType('MSpirkov\Yii2\PHPStan\Tests\Extensions\Source\Application\Clock', $app->factoryClock);
    assertType('MSpirkov\Yii2\PHPStan\Tests\Extensions\Source\Application\Clock', $app->frontendOnly);
}

function params(Application $app): void
{
    assertType("array{adminEmail: string, pageSize: int, ratio: float, maintenance: bool, senderName: mixed, languages: non-empty-list<string>, ids: non-empty-list<int|string>, empty: array, mail: array{host: string, port: int}, clock: MSpirkov\Yii2\PHPStan\Tests\Extensions\Source\Application\Clock, anonymous: mixed, 5: string, frontendKey: string}", $app->params);
    assertType('string', $app->params['adminEmail']);
    assertType('int', Yii::$app->params['mail']['port']);
}

function skipped(Application $app, Module $module, string $name): void
{
    assertType('yii\web\User<yii\web\IdentityInterface>', $app->user);
    assertType('object', $app->untyped);
    assertType('object', $app->invalid);
    assertType('*ERROR*', $module->payments);
    assertType('mixed', $app->$name);

    $app->params = ['overridden' => true];
    assertType('array{overridden: true}', $app->params);
}
