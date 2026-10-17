<?php

declare(strict_types=1);

namespace app\backend\controllers;

use MSpirkov\Yii2\PHPStan\Tests\Extensions\Source\Application\SpecialStripeGateway;
use yii\web\Application;

use function PHPStan\Testing\assertType;

function components(Application $app): void
{
    assertType('MSpirkov\Yii2\PHPStan\Tests\Extensions\Source\Application\StripeGateway', $app->payments);
    assertType('MSpirkov\Yii2\PHPStan\Tests\Extensions\Source\Application\Clock', $app->removed);

    if ($app->payments instanceof SpecialStripeGateway) {
        assertType('MSpirkov\Yii2\PHPStan\Tests\Extensions\Source\Application\SpecialStripeGateway', $app->payments);
    }
}

function otherConfigs(Application $app): void
{
    assertType('MSpirkov\Yii2\PHPStan\Tests\Extensions\Source\Application\Clock', $app->frontendOnly);
}
