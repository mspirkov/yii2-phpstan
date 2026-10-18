<?php

declare(strict_types=1);

namespace app\console\controllers;

use MSpirkov\Yii2\PHPStan\Tests\Extensions\Source\Application\Clock;
use MSpirkov\Yii2\PHPStan\Tests\Extensions\Source\Application\PaypalGateway;
use MSpirkov\Yii2\PHPStan\Tests\Extensions\Source\Application\StripeGateway;
use yii\console\Application;

use function PHPStan\Testing\assertType;

function console(Application $app): void
{
    assertType(PaypalGateway::class . '|' . StripeGateway::class, $app->payments);
    assertType(Clock::class, $app->untyped);
    assertType('object', $app->get('payments'));
    assertType('array', $app->params);
}
