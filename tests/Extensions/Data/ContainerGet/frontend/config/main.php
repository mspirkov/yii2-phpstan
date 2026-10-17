<?php

declare(strict_types=1);

use MSpirkov\Yii2\PHPStan\Tests\Extensions\Source\ContainerGet\FrontendMailer;
use MSpirkov\Yii2\PHPStan\Tests\Extensions\Source\ContainerGet\MailerInterface;

return [
    'id' => 'frontend',
    'container' => [
        'definitions' => [
            MailerInterface::class => FrontendMailer::class,
        ],
    ],
];
