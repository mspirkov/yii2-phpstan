<?php

declare(strict_types=1);

use MSpirkov\Yii2\PHPStan\Tests\Extensions\Source\Application\Clock;
use MSpirkov\Yii2\PHPStan\Tests\Extensions\Source\Application\PaypalGateway;
use MSpirkov\Yii2\PHPStan\Tests\Extensions\Source\Application\StripeGateway;
use yii\mail\MailerInterface;
use yii\web\Application;

use function PHPStan\Testing\assertType;

function unscoped(Application $app): void
{
    assertType(PaypalGateway::class . '|' . StripeGateway::class, $app->payments);
    assertType(Clock::class, $app->frontendOnly);
    assertType(MailerInterface::class, $app->mailer);
    assertType('*ERROR*', $app->undefined);
    assertType('array', $app->params);
}
