<?php

declare(strict_types=1);

use MSpirkov\Yii2\PHPStan\Tests\Extensions\Source\ContainerGet\ApiMailer;
use MSpirkov\Yii2\PHPStan\Tests\Extensions\Source\ContainerGet\MailerInterface;

return [
    'container' => [
        'definitions' => [
            MailerInterface::class => ApiMailer::class,
            'mailer.default' => ApiMailer::class,
        ],
    ],
];
