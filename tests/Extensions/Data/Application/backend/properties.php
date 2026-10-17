<?php

declare(strict_types=1);

namespace app\backend\controllers;

use MSpirkov\Yii2\PHPStan\Tests\Extensions\Source\Application\Clock;
use MSpirkov\Yii2\PHPStan\Tests\Extensions\Source\Application\PaymentGatewayInterface;
use MSpirkov\Yii2\PHPStan\Tests\Extensions\Source\Application\SpecialStripeGateway;
use MSpirkov\Yii2\PHPStan\Tests\Extensions\Source\Application\StripeGateway;
use yii\web\Application;

use function PHPStan\Testing\assertType;

function components(Application $app): void
{
    assertType(StripeGateway::class, $app->payments);
    assertType(PaymentGatewayInterface::class, $app->gateway);
    assertType(Clock::class, $app->removed);

    if ($app->payments instanceof SpecialStripeGateway) {
        assertType(SpecialStripeGateway::class, $app->payments);
    }
}

function otherConfigs(Application $app): void
{
    assertType(Clock::class, $app->frontendOnly);
}
