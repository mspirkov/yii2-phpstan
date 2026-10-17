<?php

declare(strict_types=1);

use yii\web\Application;

use function PHPStan\Testing\assertType;

function unscoped(Application $app): void
{
    assertType('MSpirkov\Yii2\PHPStan\Tests\Extensions\Source\Application\PaypalGateway|MSpirkov\Yii2\PHPStan\Tests\Extensions\Source\Application\StripeGateway', $app->payments);
    assertType('MSpirkov\Yii2\PHPStan\Tests\Extensions\Source\Application\Clock', $app->frontendOnly);
    assertType('yii\mail\MailerInterface', $app->mailer);
    assertType('*ERROR*', $app->undefined);
    assertType('array', $app->params);
}
