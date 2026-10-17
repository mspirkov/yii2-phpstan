<?php

declare(strict_types=1);

use MSpirkov\Yii2\PHPStan\Tests\Extensions\Source\Application\Clock;
use MSpirkov\Yii2\PHPStan\Tests\Extensions\Source\Application\PaypalGateway;

return [
    'components' => [
        'payments' => ['class' => PaypalGateway::class],
        'frontendOnly' => ['class' => Clock::class],
        'removed' => null,
    ],
    'params' => [
        'frontendKey' => 'value',
    ],
];
