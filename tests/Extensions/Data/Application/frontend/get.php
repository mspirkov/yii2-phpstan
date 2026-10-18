<?php

declare(strict_types=1);

namespace app\frontend\controllers;

use MSpirkov\Yii2\PHPStan\Tests\Extensions\Source\Application\Clock;
use MSpirkov\Yii2\PHPStan\Tests\Extensions\Source\Application\Logger;
use MSpirkov\Yii2\PHPStan\Tests\Extensions\Source\Application\PaymentGatewayInterface;
use MSpirkov\Yii2\PHPStan\Tests\Extensions\Source\Application\PaypalGateway;
use Yii;
use yii\base\Module;
use yii\web\Application;

use function PHPStan\Testing\assertType;

function resolved(Application $app, bool $flag): void
{
    assertType(PaypalGateway::class, $app->get('payments'));
    assertType(PaymentGatewayInterface::class, $app->get('gateway'));
    assertType(PaypalGateway::class, Yii::$app->get('payments'));
    assertType(PaypalGateway::class, $app->get('payments', false));
    assertType(Logger::class . '|' . PaypalGateway::class, $app->get($flag ? 'payments' : 'logger'));
    assertType(Clock::class, $app->get('untyped'));
}

function skipped(Application $app, Module $module, string $id, bool $flag): void
{
    assertType('object', $app->get($flag ? 'payments' : 'user'));
    assertType('object', $app->get('user'));
    assertType('object', $app->get('removed'));
    assertType('object', $app->get('undefined'));
    assertType('object|null', $app->get('undefined', false));
    assertType('object', $app->get($id));
    assertType('object', $app->get());
    assertType('object', $module->get('payments'));
    assertType('bool', $app->has('payments'));
}
