<?php

declare(strict_types=1);

use MSpirkov\Yii2\PHPStan\Tests\Extensions\Source\Application\Clock;
use MSpirkov\Yii2\PHPStan\Tests\Extensions\Source\Application\ClockFactory;
use MSpirkov\Yii2\PHPStan\Tests\Extensions\Source\Application\Logger;
use MSpirkov\Yii2\PHPStan\Tests\Extensions\Source\Application\Mailer;
use MSpirkov\Yii2\PHPStan\Tests\Extensions\Source\Application\PaymentGatewayInterface;
use MSpirkov\Yii2\PHPStan\Tests\Extensions\Source\Application\StripeGateway;

use function PHPStan\Testing\assertType;

return [
    'on beforeRequest' => static function (): void {
        assertType(StripeGateway::class, Yii::$app->payments);
        assertType(StripeGateway::class, Yii::$app->get('payments'));
        assertType(Mailer::class, Yii::$app->mailer);
        assertType('object', Yii::$app->get('frontendOnly'));
        assertType('string', Yii::$app->params['adminEmail']);
    },
    'container' => [
        'definitions' => [
            PaymentGatewayInterface::class => StripeGateway::class,
            'logger.default' => Logger::class,
        ],
    ],
    'components' => [
        'payments' => ['class' => StripeGateway::class],
        'gateway' => ['class' => PaymentGatewayInterface::class],
        'mailer' => ['class' => Mailer::class],
        'user' => ['identityClass' => 'app\models\User'],
        'logger' => 'logger.default',
        'legacyLogger' => ['__class' => Logger::class],
        'clock' => static fn(): Clock => new Clock(),
        'clockFactory' => new ClockFactory(),
        'factoryClock' => [ClockFactory::class, 'create'],
        'untyped' => static fn() => new Clock(),
        'invalid' => 42,
        'removed' => ['class' => Clock::class],
    ],
    'params' => [
        'adminEmail' => 'admin@example.com',
        'pageSize' => 20,
        'ratio' => 0.5,
        'maintenance' => false,
        'senderName' => null,
        'languages' => ['en', 'ru'],
        'ids' => [1, 'two'],
        'empty' => [],
        'mail' => ['host' => 'localhost', 'port' => 25],
        'clock' => new Clock(),
        'anonymous' => new class {},
        5 => 'five',
    ],
];
