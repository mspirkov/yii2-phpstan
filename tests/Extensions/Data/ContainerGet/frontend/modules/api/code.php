<?php

declare(strict_types=1);

namespace app\frontend\modules\api\controllers;

use MSpirkov\Yii2\PHPStan\Tests\Extensions\Source\ContainerGet\ApiMailer;
use MSpirkov\Yii2\PHPStan\Tests\Extensions\Source\ContainerGet\Logger;
use MSpirkov\Yii2\PHPStan\Tests\Extensions\Source\ContainerGet\MailerInterface;
use yii\di\Container;

use function PHPStan\Testing\assertType;

function api(Container $container): void
{
    assertType(ApiMailer::class, $container->get(MailerInterface::class));
    assertType(Logger::class, $container->get('logger'));
}
