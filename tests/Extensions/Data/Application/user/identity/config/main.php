<?php

declare(strict_types=1);

use MSpirkov\Yii2\PHPStan\Tests\Extensions\Source\Application\Clock;
use MSpirkov\Yii2\PHPStan\Tests\Extensions\Source\Application\Identity;
use MSpirkov\Yii2\PHPStan\Tests\Extensions\Source\Application\WebUser;

return [
    'components' => [
        'user' => ['identityClass' => Identity::class],
        'webUser' => ['class' => WebUser::class, 'identityClass' => Identity::class],
        'clockUser' => ['class' => Clock::class, 'identityClass' => Identity::class],
        'nonIdentityUser' => ['class' => WebUser::class, 'identityClass' => Clock::class],
        'unknownIdentityUser' => ['class' => WebUser::class, 'identityClass' => 'app\models\User'],
    ],
];
