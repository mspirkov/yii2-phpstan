<?php

declare(strict_types=1);

namespace app\frontend\controllers;

use MSpirkov\Yii2\PHPStan\Tests\Extensions\Source\ContainerGet\FileLogger;
use MSpirkov\Yii2\PHPStan\Tests\Extensions\Source\ContainerGet\FrontendMailer;
use MSpirkov\Yii2\PHPStan\Tests\Extensions\Source\ContainerGet\MailerInterface;
use yii\di\Container;

use function PHPStan\Testing\assertType;

function frontend(Container $container): void
{
    assertType(FrontendMailer::class, $container->get(MailerInterface::class));
    assertType(FrontendMailer::class, $container->get('mailer'));
    assertType(FileLogger::class, $container->get('logger'));
}
