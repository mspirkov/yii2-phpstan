<?php

declare(strict_types=1);

namespace app\console\controllers;

use yii\console\Application;

use function PHPStan\Testing\assertType;

function console(Application $app): void
{
    assertType('MSpirkov\Yii2\PHPStan\Tests\Extensions\Source\Application\PaypalGateway|MSpirkov\Yii2\PHPStan\Tests\Extensions\Source\Application\StripeGateway', $app->payments);
    assertType('object', $app->untyped);
    assertType('object', $app->get('payments'));
    assertType('array', $app->params);
}
