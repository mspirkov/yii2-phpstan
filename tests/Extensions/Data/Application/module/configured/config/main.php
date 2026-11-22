<?php

declare(strict_types=1);

use MSpirkov\Yii2\PHPStan\Tests\Extensions\Source\Application\AdminModule;
use MSpirkov\Yii2\PHPStan\Tests\Extensions\Source\Application\BlogModule;
use MSpirkov\Yii2\PHPStan\Tests\Extensions\Source\Application\Clock;
use MSpirkov\Yii2\PHPStan\Tests\Extensions\Source\Application\ContentModule;
use MSpirkov\Yii2\PHPStan\Tests\Extensions\Source\Application\ForumModule;

return [
    'modules' => [
        'admin' => [
            'class' => AdminModule::class,
            'modules' => [
                'content' => ['class' => ContentModule::class],
            ],
        ],
        'forum' => ForumModule::class,
        'blog' => new BlogModule('blog'),
        'clock' => ['class' => Clock::class],
    ],
];
