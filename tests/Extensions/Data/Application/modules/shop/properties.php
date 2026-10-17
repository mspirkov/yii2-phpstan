<?php

declare(strict_types=1);

namespace app\modules\shop\controllers;

use MSpirkov\Yii2\PHPStan\Tests\Extensions\Source\Application\Mailer;
use MSpirkov\Yii2\PHPStan\Tests\Extensions\Source\Application\PaypalGateway;
use MSpirkov\Yii2\PHPStan\Tests\Extensions\Source\Application\StripeGateway;
use yii\web\Application;

use function PHPStan\Testing\assertType;

function components(Application $app): void
{
    assertType(PaypalGateway::class . '|' . StripeGateway::class, $app->payments);
    assertType(PaypalGateway::class . '|' . StripeGateway::class, $app->get('payments'));
    assertType(Mailer::class, $app->mailer);
    assertType('object', $app->get('frontendOnly'));
}

function params(Application $app): void
{
    assertType('string', $app->params['adminEmail']);

    // Through a variable: PHPStan 2.3 seems to narrow `$app->params[...] ?? ...` from the declared `array` type.
    $params = $app->params;
    assertType('string', $params['frontendKey'] ?? 'default');
}
