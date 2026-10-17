<?php

declare(strict_types=1);

namespace app\frontend\controllers;

use MSpirkov\Yii2\PHPStan\Tests\Extensions\Source\ContainerGet\MailerInterface;
use yii\di\Container;

use function PHPStan\Testing\assertType;

function frontend(Container $container): void
{
    assertType('MSpirkov\Yii2\PHPStan\Tests\Extensions\Source\ContainerGet\FrontendMailer', $container->get(MailerInterface::class));
    assertType('MSpirkov\Yii2\PHPStan\Tests\Extensions\Source\ContainerGet\FrontendMailer', $container->get('mailer'));
    assertType('MSpirkov\Yii2\PHPStan\Tests\Extensions\Source\ContainerGet\FileLogger', $container->get('logger'));
}
