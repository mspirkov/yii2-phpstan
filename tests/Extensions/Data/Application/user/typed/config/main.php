<?php

declare(strict_types=1);

use MSpirkov\Yii2\PHPStan\Tests\Extensions\Source\Application\Clock;
use MSpirkov\Yii2\PHPStan\Tests\Extensions\Source\Application\WebUser;

return [
    'components' => [
        'user' => ['class' => WebUser::class],
        'formatter' => ['class' => Clock::class],
    ],
];
