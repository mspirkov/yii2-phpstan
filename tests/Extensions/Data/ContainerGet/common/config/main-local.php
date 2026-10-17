<?php

declare(strict_types=1);

use MSpirkov\Yii2\PHPStan\Tests\Extensions\Source\ContainerGet\FileLogger;

return [
    'container' => [
        'definitions' => [
            'logger' => ['class' => FileLogger::class],
        ],
    ],
];
