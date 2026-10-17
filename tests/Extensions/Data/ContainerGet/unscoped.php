<?php

declare(strict_types=1);

use MSpirkov\Yii2\PHPStan\Tests\Extensions\Source\ContainerGet\MailerInterface;
use yii\di\Container;

use function PHPStan\Testing\assertType;

function unscoped(Container $container): void
{
    assertType(MailerInterface::class, $container->get(MailerInterface::class));
    assertType('object', $container->get('mailer'));
}
