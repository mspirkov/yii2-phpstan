<?php

declare(strict_types=1);

use MSpirkov\Yii2\PHPStan\Tests\Extensions\Source\ContainerGet\Clock;
use MSpirkov\Yii2\PHPStan\Tests\Extensions\Source\ContainerGet\FrontendMailer;
use MSpirkov\Yii2\PHPStan\Tests\Extensions\Source\ContainerGet\MailerInterface;
use yii\di\Container;

use function PHPStan\Testing\assertType;

return [
    'definitions' => [
        MailerInterface::class => FrontendMailer::class,
        'mailer.default' => FrontendMailer::class,
        'clock.checked' => static function (Container $container): Clock {
            assertType(FrontendMailer::class, $container->get('mailer.default'));
            assertType(MailerInterface::class, $container->get('mailer'));
            assertType(Clock::class, $container->get('clock'));
            assertType('object', $container->get('undefined'));

            return new Clock();
        },
    ],
];
